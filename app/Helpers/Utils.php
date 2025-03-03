<?php

namespace App\Helpers;

class Utils
{
    public static function image($src)
    {
        $src = is_object($src) ? $src->src : $src;

        if ($src) {
            $src = explode('?', $src);
            $src = $src[0];
            return str_replace('https://cdn.dooca.store/', 'https://api.dooca.store/files/', $src);
        }

        return null;
    }
}
