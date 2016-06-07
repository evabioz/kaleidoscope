<?php
namespace Kaleidoscope;

use Illuminate\Validation\Validator as LValidator;
use Illuminate\Support\Str;
use Symfony\Component\Translation\Translator;

/**
 * @method bool     fails() Determine if the data fails the validation rules.
 * @method array    failed() Get the failed validation rules.
 * @method void     sometimes($attribute, $rules, callable $callback) Add conditions
 *                  to a given field based on a Closure.
 * @method $this    after($callback) After an after validation callback.
 */
class Validator extends LValidator
{

    /**
     * Create a new instance
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param string $locale
     */
    public function __construct(array $data, array $rules, array $messages = [], $locale = 'en')
    {
        parent::__construct(new Translator($locale), $data, $rules, $messages);
    }

    /**
     * Validate that an attribute is numeric.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    protected function validateMaxSize($attribute, $value, $parameters)
    {
        // Checking the file size
        $size = is_file($value) ?
            filesize($value) :
            $this->retrieveRemoteFileSize($value);

        return $size < $parameters[0] * 1024;
    }

    private function retrieveRemoteFileSize($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);

        $data = curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        curl_close($ch);
        return $size;
    }

    /**
     * Validate the size of an attribute is less than a maximum value.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @param  array $parameters
     * @return bool
     */
    protected function validateAllowedTypes($attribute, $value, $parameters)
    {
        $file_ext = pathinfo($value, PATHINFO_EXTENSION);

        return !!in_array($file_ext, $parameters);
    }
}
