<?php


namespace iflow\Swoole\dht\lib\utils\coding\client;


class Encode
{

    private mixed $data;

    private function __construct(mixed $data){
        $this->data = $data;
    }

    static public function encode($data): string
    {
        if(is_object($data)){
            if(method_exists($data, 'toArray'))
                $data = $data->toArray();
            else
                $data = (array) $data;
        }
        $encode = new self($data);
        return $encode->do_encode();
    }

    private function do_encode($data = null): string
    {
        $data = is_null($data) ? $this->data : $data;

        if(is_array($data) && (isset($data[0]) || empty($data))){
            return $this->encode_list($data);
        }elseif(is_array($data)){
            return $this->encode_dict($data);
        }elseif(is_integer($data) || is_float($data)){
            $data = sprintf("%.0f", round($data, 0));
            return $this->encode_integer($data);
        }else{
            return $this->encode_string($data);
        }
    }

    private function encode_integer($data = null){
        $data = is_null($data) ? $this->data : $data;
        return sprintf("i%.0fe", $data);
    }

    private function encode_string($data = null){
        $data = is_null($data) ? $this->data : $data;
        return sprintf("%d:%s", strlen($data), $data);
    }

    private function encode_list(array $data = null): string
    {
        $data = is_null($data) ? $this->data : $data;
        $list = '';

        foreach($data as $value)
            $list .= $this->do_encode($value);

        return "l{$list}e";
    }

    private function encode_dict(array $data = null): string
    {
        $data = is_null($data) ? $this->data : $data;
        ksort($data);
        $dict = '';

        foreach($data as $key => $value)
            $dict .= $this->encode_string($key) . $this->do_encode($value);

        return "d{$dict}e";
    }

}