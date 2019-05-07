<?php

if (!function_exists('array_rename_key'))
{
    function array_rename_key($array, $old_key, $new_key)
    {
        $array[$new_key] = $array[$old_key];
        unset($array[$old_key]);
        return $array;
    }
}
