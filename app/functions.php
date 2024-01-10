<?php

if (!function_exists('get_home')) {
    /**
     * 
     * @return string 
     */
    function get_home(): string
    {
        $homeDir = getenv("HOME");
        if (!isset($homeDir) || !$homeDir) {
            $homeDir = getenv("USERPROFILE");
        }
        return rtrim($homeDir, DS);
    }
}

if (!function_exists('path_join')) {
    /**
     * 
     * @return string 
     */
    function path_join(): string
    {
        return implode(DS, array_map(fn ($e) => trim(str_replace([DS . DS, '//', '/'], DS, $e), '\\\/'), array_filter(func_get_args())));
    }
}

if (!function_exists('text_ellipsis')) {
    /**
     * 
     * @param string $text 
     * @param int $length 
     * @return string 
     */
    function text_ellipsis(string $text, int $length): string
    {
        return mb_substr($text, 0, $length) . ['', '...'][mb_strlen($text) > $length];
    }
}
