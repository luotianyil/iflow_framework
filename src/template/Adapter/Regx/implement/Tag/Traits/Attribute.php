<?php

namespace iflow\template\Adapter\Regx\implement\Tag\Traits;

trait Attribute {

    /**
     * 处理队列
     * @var array
     */
    protected array $runAttributeQueue = [];

    protected array $attr = [];

    public function attributeToString(): string {
        $attr = "";

        $setAttr = function ($_attr, $attrKey) use (&$attr): bool {
            if ($_attr !== '') {
                $attr = "{$attr} {$_attr}";
                $this->attr[$attrKey] = $_attr;
                return true;
            }
            return false;
        };

        foreach ($this->attr as $attrKey => $_attr) {
            $bind = $this->bind($attrKey, $_attr);
            if ($setAttr($bind, $attrKey)) continue;

            $_rattr = '';
            foreach ($this->runAttributeQueue as $queue) {
                $_rattr = call_user_func([ $this, $queue ], $attrKey, $_attr);
                if ($setAttr($_rattr, $attrKey)) break;
            }

            if ($_rattr === '') $setAttr("{$attrKey}=\"{$_attr}\"", $attrKey);
        }
        return trim($attr);
    }


    /**
     * @return array
     */
    public function getAttr(): array {
        return $this->attr;
    }

    /**
     * 数值绑定处理
     * @param string $attrKey
     * @param string $_attr
     * @return string
     */
    protected function bind(string $attrKey, string $_attr): string {
        if (!str_starts_with($attrKey, ':')) return '';
        $attrKey = ltrim($attrKey, ':');

        return "{$attrKey}=\"<?= {$_attr} ?>\"";
    }

}