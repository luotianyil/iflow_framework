<?php

namespace iflow\swoole\implement\Server\implement;

class ModbusRtuCrc {

    public function RtuCrc16(string $data): string {
        $string = unpack('H*', $data)[1];
        $crc = 0xFFFF;
        for ($x = 0, $xMax = \strlen($string); $x < $xMax; $x++) {
            $crc ^= \ord($string[$x]);
            for ($y = 0; $y < 8; $y++) {
                if (($crc & 0x0001) === 0x0001) {
                    $crc = (($crc >> 1) ^ 0xA001);
                } else {
                    $crc >>= 1;
                }
            }
        }

        return \chr($crc & 0xFF) . \chr($crc >> 8);
    }

    public function toHexCode(string $data): array {
        $data = str_replace(' ', '', $data);
        $str = pack('H*', $data);
        $rtuCrc = $this->RtuCrc16($str);

        return [
            strtoupper(unpack("H*", $rtuCrc)[1]),
            strtoupper(unpack("H*", $str . $rtuCrc)[1])
        ];
    }

    public function hexToData(string $data = ''): array {
        $data = unpack('H*', $data)[1];
        $data = [
            'crc' => substr($data, 0, 2),
            'f_code' => substr($data, 2, 2),
            'address' => substr($data, 4, 4),
            'data' => substr($data, 8, strlen($data) - 4),
            'crc_check_code' => substr($data, strlen($data) - 4, 4)
        ];
        $data['dec'] = $this->hexDec($data['data']);
        $data['address_dec'] = $this->hexDec($data['address']);
        return $data;
    }

    protected function hexDec(string $hex): int | float {
        $dec = hexdec($hex);
        if (hexdec($hex[0]) < 8) return $dec;
        $bin = decbin($dec - 1); $strlen = strlen($bin); $fan = '';
        for ($i = 0; $i < $strlen; $i++) $fan .= $bin[$i] == 1 ? '0' : '1';
        return -bindec($fan);
    }
    
}