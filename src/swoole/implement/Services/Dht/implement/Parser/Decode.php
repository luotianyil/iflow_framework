<?php

namespace iflow\swoole\implement\Services\Dht\implement\Parser;

class Decode {

    protected int $size;

    protected int $offset = 0;

    public function __construct(protected string $source) {
        $this->size = strlen($this->source);
    }


    public static function decode(string $source = ''): array|string {

        if ($source === '') return [];

        $decode = new self($source);
        $data = $decode -> decodeTypes();

        if ($decode -> offset !== $decode -> size) return '';
        return $data;
    }


    protected function decodeTypes(): string|array {
        $type = match ($this->getChar()) {
            'i' => function () {
                ++$this->offset;
                return $this->integerType();
            },
            'l' => function () {
                ++$this->offset;
                return $this->listType();
            },
            'd' => function () {
                ++$this->offset;
                return $this->dictType();
            },
            default => fn() => ctype_digit($this->getChar()) ? $this -> stringType()  : '',
        };

        return $type();
    }


    protected function getChar(int $offset = null): string|bool {
        if($offset === null) $offset = $this->offset;

        if(empty($this->source) || $this->offset >= $this->size) return false;
        return $this->source[$offset];
    }


    /**
     * 解码字符串类型数据
     * @return string
     */
    protected function stringType(): string {
        if('0' === $this->getChar() && ':' != $this->getChar($this->offset + 1))
            return '';

        $end = strpos($this->source, ':', $this->offset);

        if($end === false) return '';

        $content_length = (int) substr($this->source, $this->offset, $end);

        if(($content_length + $end + 1) > $this->size) return '';

        $value = substr($this->source, $end + 1, $content_length);
        $this->offset = $end + $content_length + 1;

        return $value;
    }

    protected function integerType(): string {
        $end = strpos($this->source, 'e', $this->offset);

        if($end === false) return '';

        $current_off = $this->offset;

        if($this->getChar($current_off) == '-')
            ++$current_off;

        if($end === $current_off) return '';

        while($current_off < $end){
            if(!ctype_digit($this->getChar($current_off))) return '';
            ++$current_off;
        }

        $value = substr($this->source, $this->offset, $end - $this->offset);
        $absolute_value = (string) abs($value);

        if(1 < strlen($absolute_value) && '0' == $value[0]) return '';

        $this->offset = $end + 1;

        return sprintf("%s0", $value);
    }

    protected function listType(): array|string {
        $list = [];
        $terminated = false;

        while($this->getChar() !== false){
            if($this->getChar() == 'e'){
                $terminated = true;
                break;
            }
            $list[] = $this->decodeTypes();
        }

        if(!$terminated && $this->getChar() === false) return '';

        $this->offset++;
        return $list;
    }

    protected function dictType(): array|string {
        $dict = [];
        $terminated = false;

        while($this->getChar() !== false){
            if($this->getChar() === 'e'){
                $terminated = true;
                break;
            }

            if(!ctype_digit($this->getChar())) return '';

            $key = $this->stringType();

            if(isset($dict[$key])) return [];

            $dict[$key] = $this->decodeTypes();
        }

        if(!$terminated && $this->getChar() === false) return '';

        $this->offset++;

        return $dict;
    }

}