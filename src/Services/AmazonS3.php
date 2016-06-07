<?php
namespace Kaleidoscope\Services;

use Kaleidoscope\Exception\RuntimeException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Aws\S3\S3Client;
use Kaleidoscope\Validator;

class AmazonS3 extends AbstractGateway
{

    /**
     * Amazon S3 Client
     */
    protected $client;

    public function initialize(array $parameters = array())
    {
        parent::initialize($parameters);

        $this->client = $this->getDefaultClient();
    }

    protected function getDefaultParameters()
    {
        return [
            // Important!!
            // We needs "path" parameters for this storage when new instance.
            // Follow below:
            'path' => [
                'kaleidoscope.image' => "images/:id/original",
                'kaleidoscope.audio' => "audios/:id/original",
                'kaleidoscope.video' => "videos/:id/original",
                'kaleidoscope.paper' => "papers/:id/original",
                'kaleidoscope.other' => ":filename/original"
            ]
        ];
    }

    protected function getDefaultClient()
    {
        $client_config = $this->getParameter('client_config');

        // Get credential from configure.
        $credentials = [
            'key' => Arr::get($client_config, 'key'),
            'secret' => Arr::get($client_config, 'secret')
        ];

        $s3Client = S3Client::factory([
            'version' => Arr::get($client_config, 'version'),
            'region' => Arr::get($client_config, 'region'),
            'credentials' => $credentials
        ]);

        return $s3Client;
    }

    /**
     *
     * Get remote file
     *
     * @param string $key
     * @return string|resource
     */
    public function get($key)
    {
        // Load configures from initialize data
        $object_config = $this->getParameter('object_config');

        $obj_s3 = array_merge($object_config, [
            'Key' => $key
        ]);

        $s3_response = $this->client->getObject($obj_s3);

        return $s3_response->get('Body')->__toString();
    }

    /**
     * Store file, it does not support without file extension.
     *
     * @param mixed $data
     * @param \Closure $beforeStore
     * @param \Closure $afterStore
     * @throws \Exception
     * @return array
     */
    public function store(array $data, \Closure $beforeStore = null, \Closure $afterStore = null, array $options = [])
    {
        list ($name, $ext, $resource) = $this->prepareStore($data, $options);

        if ($beforeStore != null) {
            call_user_func($beforeStore, $this);
        }

        // Note:
        //     我們將名稱帶入 parameter 中，是因為如果型態是 other 這邊的值會取代
        //     path 之中所設定的欄位（找到 :filename 的字串）
        // PS. 這個感覺有點怪，往後在修改看看
        $this->setParameter('filename', $name);

        $responseData = $this->performSave(get_file_format($ext));

        // Try to upload file to Amazon S3 service.
        $this->pushData($responseData['path'], $resource);

        if ($afterStore != null) {
            call_user_func_array($afterStore, [
                // If got error from Amazon S3, that should be throw exception at this time.
                // So, we were ensured it can write here as "ture"
                true,
                &$responseData
            ]);
        }

        return $responseData;
    }

    /**
     *
     * @param array $data
     * @param array $options
     * @throws RuntimeException
     * @return array
     */
    protected function prepareStore($data, $options = [])
    {
        // Require
        $path = Arr::get($data, 'file', '');

        $originalName =  Arr::get($data, 'name', $path);

        // Replace default key-value.
        $maxSize = Arr::get($options, 'max_size', $this->getParameter('max_size'));

        // Replace default key-value.
        $allowedTypes = Arr::get($options, 'types', $this->getParameter('types'));

        $validate = new Validator([
            'path' => $originalName
        ], [
            'path' => "string|max_size:$maxSize|allowed_types:$allowedTypes"
        ]);

        if ($validate->fails()) {
            throw new \Exception((string) $validate->errors());
        }

        // Detemined if tatger is local file path, else try to get cont
        $resource = is_file($path) ? fopen($path, 'r+') : file_get_contents($path);

        return [
            pathinfo($originalName, PATHINFO_BASENAME),
            pathinfo($originalName, PATHINFO_EXTENSION),
            $resource
        ];
    }

    /**
     * Push contents to Amazon S3 service.
     *
     * @param string $key
     * @param string $contents
     * @param array $options
     * @return \Aws\Result
     */
    private function pushData($key, $contents, $options = [])
    {
        // Load configures from initialize data
        $object_config = $this->getParameter('object_config');

        // 合併參數，URL和File的上傳參數不一樣，由外部傳入。
        $obj_s3 = array_merge($object_config, $options, [
            'Key' => $key,
            'Body' => $contents
        ]);

        // 上傳到S3
        return $this->client->putObject($obj_s3);
    }
}
