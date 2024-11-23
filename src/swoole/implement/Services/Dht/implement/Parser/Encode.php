<?php

namespace iflow\swoole\implement\Services\Dht\implement\Parser;

class Encode {


    public function __construct( protected mixed $data ) {
    }

    public static function encode(mixed $data): string {
        if(is_object($data)){
            $data = method_exists($data, 'toArray') ? $data->toArray() : (array) $data;
        }

        $encode = new self($data);
        return $encode->encodeType();
    }


    protected function encodeType(mixed $data = null): string {
        $data = is_null($data) ? $this->data : $data;

        if(is_array($data) && (isset($data[0]) || empty($data))){
            return $this->listType($data);
        }elseif(is_array($data)){
            return $this->dictType($data);
        }elseif(is_integer($data) || is_float($data)){
            $data = sprintf("%.0f", round($data, 0));
            return $this->integerType($data);
        }else{
            return $this->stringType($data);
        }
    }


    protected function listType(mixed $data): string {
        $data = is_null($data) ? $this->data : $data;
        $list = '';

        foreach($data as $value)
            $list .= $this->encodeType($value);

        return "l{$list}e";
    }

    protected function dictType(mixed $data): string {
        $data = is_null($data) ? $this->data : $data;
        ksort($data);
        $dict = '';

        foreach($data as $key => $value)
            $dict .= $this->stringType($key) . $this->encodeType($value);

        return "d{$dict}e";
    }

    protected function integerType(mixed $data): string {
        $data = is_null($data) ? $this->data : $data;
        return sprintf("i%.0fe", $data);
    }

    protected function stringType(mixed $data): string {
        $data = is_null($data) ? $this->data : $data;
        return sprintf("%d:%s", strlen($data), $data);
    }
}