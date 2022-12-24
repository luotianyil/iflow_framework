<?php

namespace iflow\template\Adapter\Regx\implement\Abstracts;

use iflow\template\Adapter\Regx\Config;
use iflow\template\Adapter\Regx\implement\Tag\Traits\Attribute;
use iflow\template\Adapter\Regx\Interfaces\TagInterface;

class TagAbstract implements TagInterface {

    use Attribute;

    // 源模块代码
    protected string $source = '';

    // 生成后的代码
    protected string $html = '';

    // 标签
    protected string $tag = '';

    // 属性
    protected array $attrs = [];

    protected array $args = [];
    protected array $props = [];

    protected bool $isCloseLabel;

    public function __construct(protected Config $config, protected array $tagMap) {
    }

    public function parser(string $tag, string $html, array $args): TagInterface {
        // TODO: Implement parser() method.
        $this->tag = $tag;
        $this->source = $html;

        $this->args = $args;

        $this->attr = $this->attrs = $args['tagAttrs']['attrs'];
        $this->props = $this->attrs['props'] ?? [];
        $this->isCloseLabel = isset($args['isCloseLabel'])
            && $args['isCloseLabel'] === true;

        $this->instruction();
        return $this;
    }


    /**
     * 指令处理
     * @return void
     */
    protected function instruction(): void {
        $this->runAttributeQueue = $this->config -> getInstruction();
    }

    /**
     * 设置变量前缀
     * @param string $code
     * @return string
     */
    protected function setParamsStartCode(string $code = ''): string {
        if ($code === '') return '';
        if (str_starts_with($code, '$')) return $code;
        return '$'.$code;
    }

    /**
     * 获取下一个节点
     * @param int $selfId
     * @param array $tree
     * @return array|false
     */
    public function getNext(int $selfId, array $tree = []): array|false {
        $next = $tree[$selfId + 1] ?? [];
        return $next ?: false;
    }


    public function toHtml(): string {
        // TODO: Implement toHtml() method.
        return $this->html ?: $this->source;
    }
}