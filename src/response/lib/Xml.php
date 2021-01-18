<?php


namespace iflow\response\lib;


use iflow\Response;

class Xml extends Response
{

    public string $contentType = 'text/xml';

    // 输出参数
    public array $options = [
        // 根节点名
        'root_node' => 'xml',
        // 根节点属性
        'root_attr' => '',
        //数字索引的子节点名
        'item_node' => 'item',
        // 数字索引子节点key转换的属性名
        'item_key'  => 'id',
        // 数据编码
        'encoding'  => 'utf-8',
    ];

    public function __construct(array $data = [], int $code = 200)
    {
        $this->init($data, $code);
    }

    protected function output($data): string
    {
        if (is_string($data)) {
            if (0 !== strpos($data, '<?xml')) {
                $xml      = "<?xml version=\"1.0\" encoding=\"{$this->options['encoding']}\"?>";
                $data     = $xml . $data;
            }
            return $data;
        }
        return $this->xmlEncode($data);
    }


    public function xmlEncode($data = []): string
    {
        $xml = "<?xml version=\"1.0\" encoding=\"{$this->options['encoding']}\"?>";
        $root_attr = '';

        if (is_array($this->options['root_attr'])) {
            foreach ($this->options['root_attr'] as $key => $value) {
                $root_attr[] = "${key}=\"${value}\"";
            }
            $root_attr = implode(' ', $root_attr);
        }

        $root_attr = trim($root_attr);
        $root_attr = empty($root_attr) ? '' : " $root_attr";
        $xml .= "<{$this -> options['root_node']}{$root_attr}>";
        $xml .= $this->dataToXml($data);
        $xml .= "</{$this -> options['root_node']}>";

        return $xml;
    }

    public function dataToXml($data): string
    {
        $xml = $attr = '';
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $this->options['item_key'] && $attr = " {$this->options['item_key']}=\"$key\"";
                $key = $this->options['item_node'];
            }

            $xml .= "<{$key}{$attr}>";
            $xml .= (is_array($value) || is_object($value)) ? $this->dataToXml($value) : $value;
            $xml .= "</{$key}>";
        }

        return $xml;
    }

}