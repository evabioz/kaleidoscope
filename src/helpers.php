<?php

if (! function_exists('get_file_format')) {
    /**
     * Retrue type with file extension, if matched.
     *
     * @param string $ext
     * @return string
     */
    function get_file_format($file_ext)
    {
        if (preg_match('/(ai|bmp|dxf|eps|gif|ico|jpg|jpe|jpeg|pdf|png|ps|swf|tif|tiff|wmf)/', $file_ext)) {
            return 'image';
        } elseif (preg_match('/(mp3|m4a|ra|ram|wav|wma|ogg)/', $file_ext)) {
            return 'audio';
        } elseif (preg_match('/(asf|asx|avi|mov|mpg|mpeg|mp4|qt|ra|ram|swf|wmv|flv)/', $file_ext)) {
            return 'video';
        } elseif (preg_match('/(csv|doc|dot|pdf|pot|pps|ppt|rtf|txt|xls)/', $file_ext)) {
            return 'office';
        }
        return 'other';
    }
}
if (! function_exists('str_replace_once')) {
    /**
     * Replace string at once.
     *
     * @param string $pattern
     * @param string $replacement
     * @param string $subject
     * @return string
     */
    function str_replace_once($pattern, $replacement, $subject)
    {
        if ($replacement && strpos($subject, $pattern) !== false) {
            $occurrence = strpos($subject, $pattern);
            return substr_replace($subject, $replacement, strpos($subject, $pattern), strlen($pattern));
        }

        return $subject;
    }
}
