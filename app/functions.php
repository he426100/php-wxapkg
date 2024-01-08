<?php

function get_home()
{
    $homeDir = getenv("HOME");
    if (!isset($homeDir) || !$homeDir) {
        $homeDir = getenv("USERPROFILE");
    }
    return rtrim($homeDir, DS);
}

function path_join()
{
    return implode(DS, array_map(fn ($e) => trim(str_replace([DS . DS, '//', '/'], DS, $e), '\\\/'), array_filter(func_get_args())));
}
