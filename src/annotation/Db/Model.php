<?php

namespace iflow\annotation\Db;

use think\Model as TModel;
use think\model\concern\{
    Attribute, RelationShip, ModelEvent, TimeStamp, Conversion
};

class Model extends TModel {

    use Attribute, RelationShip, ModelEvent, TimeStamp, Conversion;

    public function __construct(object|array $data = []) {
        // 设置数据
        $this->data($data);

        // 记录原始数据
        $this->origin = $this->data;

        if (empty($this->name)) {
            // 当前模型名
            $name       = str_replace('\\', '/', static::class);
            $this->name = basename($name);
        }
    }

    public function __make(): void {

        if (!empty(static::$maker)) {
            foreach (static::$maker as $maker) call_user_func($maker, $this);
        }

        // 执行初始化操作
        $this->initialize();
    }

    private function initialize(): void {
        if (!isset(static::$initialized[static::class])) {
            static::$initialized[static::class] = true;
            static::init();
        }
    }

    public function pk(bool|array|string $pk) {
        $this -> pk = $pk;
        return $this;
    }

}