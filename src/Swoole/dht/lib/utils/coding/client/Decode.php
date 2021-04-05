<?php


namespace iflow\Swoole\dht\lib\utils\coding\client;


class Decode
{

    private int $length;

    private int $offset = 0;

    private function __construct(private string $source) {
        $this->length = strlen($source);
    }

    static public function decode(string $source): array|int|string
    {
        // 检查数据是否正确
        if(!is_string($source)) return '';

        // 调用类本身完成解码
        $decode = new self($source);
        $decoded = $decode->do_decode();

        // 验证数据
        if($decode->offset != $decode->length) return '';
        return $decoded;
    }

    private function do_decode(): int|array|string
    {
        // 截取数据字符判断操作类型
        switch($this->get_char()){
            case 'i':
                ++$this->offset;
                return $this->decode_integer();
            case 'l':
                ++$this->offset;
                return $this->decode_list();
            case 'd':
                ++$this->offset;
                return $this->decode_dict();
            default:
                if(ctype_digit($this->get_char()))
                    return $this->decode_string();
        }

        return '';
    }

    private function decode_integer(): int|string
    {
        $offset_e = strpos($this->source, 'e', $this->offset);

        if($offset_e === false)
            return '';

        $current_off = $this->offset;

        if($this->get_char($current_off) == '-')
            ++$current_off;

        if($offset_e === $current_off)
            return '';

        while($current_off < $offset_e){
            if(!ctype_digit($this->get_char($current_off)))
                return '';

            ++$current_off;
        }

        $value = substr($this->source, $this->offset, $offset_e - $this->offset);
        $absolute_value = (string) abs($value);

        if(1 < strlen($absolute_value) && '0' == $value[0])
            return '';

        $this->offset = $offset_e + 1;

        return $value + 0;
    }

    private function decode_string(): string
    {
        if('0' === $this->get_char() && ':' != $this->get_char($this->offset + 1))
            return '';

        $offset_o = strpos($this->source, ':', $this->offset);

        if($offset_o === false)
            return '';

        $content_length = (int) substr($this->source, $this->offset, $offset_o);

        if(($content_length + $offset_o + 1) > $this->length)
            return '';

        $value = substr($this->source, $offset_o + 1, $content_length);
        $this->offset = $offset_o + $content_length + 1;

        return $value;
    }

    private function decode_list(): array
    {
        $list = array();
        $terminated = false;
        while($this->get_char() !== false){
            if($this->get_char() == 'e'){
                $terminated = true;
                break;
            }
            $list[] = $this->do_decode();
        }
        if(!$terminated && $this->get_char() === false) return [];
        $this->offset++;
        return $list;
    }

    private function decode_dict(): array
    {
        $dict = array();
        $terminated = false;
        while($this->get_char() !== false){
            if($this->get_char() == 'e'){
                $terminated = true;
                break;
            }
            if(!ctype_digit($this->get_char())) return [];
            $key = $this->decode_string();
            if(isset($dict[$key])) return [];
            $dict[$key] = $this->do_decode();
        }

        if(!$terminated && $this->get_char() === false) return [];
        $this->offset++;
        return $dict;
    }

    private function get_char($offset = null): bool|string
    {
        if($offset === null)
            $offset = $this->offset;

        if(empty($this->source) || $this->offset >= $this->length)
            return false;

        return $this->source[$offset];
    }
}