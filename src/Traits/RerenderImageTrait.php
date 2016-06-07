<?php
namespace Kaleidoscope\Traits;

use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;
use Intervention\Image\Image as Canvas;

trait RerenderImageTrait
{

    /**
     * Statement delimiter
     *
     * @var string
     */
    protected $delimiter = ',';

    /**
     * Defined inputs from request.
     *
     * @var array
     */
    protected $fillable = [
        'id' => '',
        'type' => '',
        'input' => '',
        'ext' => 'jpg',
        'quality' => 90
    ];

    /**
     * Basic handlers
     *
     * @var array
     */
    protected $handlers = [
        'canvas' => [
            'task' => 'canvas',
            'length' => 3
        ],
        'circle' => [
            'task' => 'circle',
            'length' => 3
        ],
        'ellipse' => [
            'task' => 'ellipse',
            'length' => 4
        ],
        'resize' => [
            'task' => 'resize',
            'length' => 2
        ],
        'widen' => [
            'task' => 'widen',
            'length' => 1
        ],
        'blur' => [
            'task' => 'blur',
            'length' => 1
        ],
        'contrast' => [
            'task' => 'contrast',
            'length' => 1
        ],
        'opacity' => [
            'task' => 'opacity',
            'length' => 1
        ],
        'gamma' => [
            'task' => 'gamma',
            'length' => 1
        ],
        'rotate' => [
            'task' => 'rotate',
            'length' => 1
        ]
    ];

    private function _parameters(Request $request)
    {
        $params = $this->routeParameters($request);
        $results = [];

        foreach ($this->fillable as $key => $val) {
            if (!isset($params[$key])) {
                $results[$key] = $request->get($key, $val);
            } else {
                $results[$key] = $params[$key];
            }
        }

        return $results;
    }

    /**
     * @param string $name
     * @param string $task
     */
    protected function addHandler($name, $task)
    {
        $params = [
            'task' => $task,
            'length' => 0
        ];

        foreach (explode('|', $task) as $task) {
            if (!isset($this->handlers[$task])) {
                throw new \ErrorException("Undefined task name: $task");
            }

            $params['length'] += $this->handlers[$task]['length'];
        }

        $this->handlers[$name] = $params;
    }

    /**
     * @param array $params
     */
    protected function addHandlers(array $handers)
    {
        foreach ($handers as $name => $task) {
            $this->addHandler($name, $task);
        }
    }

    /**
     * 顯示原始的圖片
     *
     * @param Request $request
     * @return Response
     */
    public function getOriginal(Request $request)
    {
        $results = $this->_parameters($request);

        list ($id, $type, $input, $ext, $quality) = array_values($results);

        $image = Image::make($this->getRemoteResource($id, $results));

        return $image->response("", $quality);
    }

    /**
     * 顯示處理的圖片
     *
     * @param Request $request
     * @return Response
     */
    public function getRender(Request $request)
    {
        $results = $this->_parameters($request);

        list ($id, $type, $input, $ext, $quality) = array_values($results);

        $tasks = $this->routeParameters($request, function ($key, $val) {
            return isset($this->handlers[$key], $this->handlers[$key]['task']);
        });

        // Determine if not found any custom handler in route parameters,
        // So, it'll be get a type value in route parameters.
        if (empty($tasks)) {
            $tasks = [
                "$type" => $input
            ];
        }

        $image = Image::make($this->getRemoteResource($id, $results));

        // Delimiting characters
        $inputs = explode($this->delimiter, reset($tasks));
        $this->fireRender(key($tasks), $inputs, $image);

        return $image->response($ext, $quality);
    }

    private function getRemoteResource($id, array $parameters)
    {
        // 由應用層決定 storage 物件
        $storage = $this->getStorage();

        if (preg_match('/^[0-9]+$/', $id)) {
            $key = $storage->getRelativePath("kaleidoscope.image", $parameters);
        } else {
            $key = str_replace('_', '/', trim($id));
        }

        return $storage->get($key);
    }

    /**
     *
     * @param Request $request
     * @param \Closure $feedback
     * @return array
     */
    private function routeParameters(Request $request, \Closure $feedback = null)
    {
        $route = $request->route();
        $isLumen = is_array($route);

        $results = $isLumen ? $route[2] : $route->parameters();

        if ($feedback) {
            foreach ($results as $key => $val) {
                if (!call_user_func($feedback, $key, $val)) {
                    unset($results[$key]);
                }
            }
        }

        return $results;
    }

    /**
     * Fire render
     *
     * @param string $task
     * @param array $data
     * @param \Intervention\Image\Image $image
     */
    protected function fireRender($task, array &$data, Canvas $image)
    {
        if ($index = strripos($task, '|')) {
            $this->fireRender(substr($task, 0, $index), $data, $image);
        }

        $type = trim(substr($task, $index), '|');

        if ($type != $this->handlers[$type]['task']) {
            $this->fireRender($this->handlers[$type]['task'], $data, $image);
        } else {
            call_user_func_array([
                $image,
                $type
            ], array_splice($data, 0, $this->handlers[$type]['length']));
        }
    }
}
