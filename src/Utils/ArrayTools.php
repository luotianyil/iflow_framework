<?php


namespace iflow\Utils;


use iflow\Collection;

class ArrayTools extends Collection
{

    public function __construct(protected array $items = []) {}


    /**
     * 通过key 获取 value 支持多层级
     * @param string $name key
     * @param mixed $default 默认值
     * @return mixed
     */
    public function get(string $name = '', mixed $default = []): mixed {
        if ($name === '') return $this->items;
        $keys = explode('@', $name);

        if (!$this->offsetExists($keys[0])) return [];
        if (empty($keys[1])) return $this->offsetGet($keys[0]);
        $names = explode('.', $keys[1]);

        $info = [];
        if (count($names) <= 1) {
            foreach ($names as $val) {
                if (isset($this->items[$keys[0]][$val])) {
                    $info = $this->items[$keys[0]][$val];
                }
            }
        } else {
            $info = $this->getConfigValue($names, $this->offsetGet($keys[0]));
        }
        return $info ?: $default;
    }

    protected function getConfigValue($names, array $array = []) {
        // 按.拆分成数组进行判断
        if (count($names) === 1) {
            return $array[array_shift($names)] ?: [];
        }
        $key = array_shift($names);
        return empty($array[$key]) ? null: $this->getConfigValue($names, $array[$key]);
    }

}