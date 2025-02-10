<?php

namespace iflow\swoole\implement\Server\implement;

class PackFormatter {

    /**
     * HEX 转数字
     * @param string $hex
     * @return int|float
     */
    public static function hexToNumber(string $hex): int | float {
        $dec = hexdec($hex);
        if (hexdec($hex[0]) < 8) return $dec;
        $bin = decbin($dec - 1); $strlen = strlen($bin); $fan = '';
        for ($i = 0; $i < $strlen; $i++) $fan .= $bin[$i] == 1 ? '0' : '1';
        return -bindec($fan);
    }

}