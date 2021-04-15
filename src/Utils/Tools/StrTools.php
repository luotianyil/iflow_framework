<?php


namespace iflow\Utils\Tools;


class StrTools
{
    /**
     * 驼峰转小写
     * @param string $str
     * @param string $separator
     * @return string
     */
    public function humpToLower(string $str, string $separator = '_'): string
    {
         return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $str));
    }

    /**
     * 小写转驼峰
     * @param string $str
     * @param string $separator
     * @return string
     */
    public function unHumpToLower(string $str, string $separator = '_'): string
    {
        $str = $separator . str_replace($separator, " ", strtolower($str));
        return ltrim(str_replace(" ", "", ucwords($str)), $separator);
    }
}