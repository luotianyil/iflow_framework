<?php

namespace iflow\template\Adapter\Regx\RegxInterpreter\Traits;

use iflow\template\Adapter\Regx\Config;

trait ReadRegxTagMap {

    protected array $tree = [];

    protected string $endRegx = '/^{\/(.*)}$/';
    protected string $startRegx = '/^{(.*)}$/';

    private   string $_template = '';

    /**
     * 获取树状节点数据
     * @return array
     */
    public function toTree(): array {

        if (empty($this->tagMap) || $this->tree) return $this->tree;
        $this->_template = $this -> template;
        $selfIndex = 0;
        $tagMap = $this -> tagMap;

        while (!empty($tagMap)) {
            $tree = $this->getTagContext($tagMap, $selfIndex);

            $tree['children'] = $this->getTagMapChildren($tagMap, $tree['selfTag'], $tree['endTag']);
            $tagMap = array_slice($tagMap, $tree['nextIndex'] + 1);

            $this->tree[] = $tree;
        }
        return $this->tree;
    }

    /**
     * 获取当前节点数据
     * @param array $tagMap
     * @param int $selfIndex
     * @return array
     */
    protected function getTagContext(array $tagMap, int $selfIndex = 0): array {
        $startTag = $this->getStartTagContext($tagMap[$selfIndex]);
        $startTag['index'] = $selfIndex;
        $startTag['tagSource'] = $tagMap[$selfIndex][0];

        $endTag   = $this -> getEndTag($tagMap, $startTag, $selfIndex);

        return [
            'selfTag' => $startTag,
            'children' => [],
            'endTag' => $endTag,
            'startLine' => $startTag['tagSource'][1] + strlen($startTag['tagSource'][0]),
            'endLine' => $endTag ? $endTag[0][1] : $endTag,
            'nextIndex' => ($endTag['index'] ?? 0)
        ];
    }

    /**
     * 获取起始标签信息
     * @param array $tag
     * @return array
     */
    protected function getStartTagContext(array $tag): array {
        preg_match($this->startRegx, $tag[0][0], $startTagAttrContext);
        return $this->getTagAttrAndContent($startTagAttrContext[1]);
    }

    /**
     * 获取子级节点数据
     * @param array $tagMap
     * @param array $startTagContext
     * @param array $endTag
     * @return array
     */
    protected function getTagMapChildren(array $tagMap, array $startTagContext, array $endTag): array {

        if (empty($endTag)) return [];

        $children = array_slice($tagMap, $startTagContext['index'] + 1, $endTag['index'] - $startTagContext['index']);
        if (count($children) % 2 > 0) array_pop($children);

        if (empty($children)) return [];

        $_children = [];
        while (!empty($children)) {
            $self = $this->getTagContext($children);

            $self['children'] = $this->getTagMapChildren($children, $self['selfTag'], $self['endTag']);

            $children = array_slice($children, $self['nextIndex'] + 1);

            $_children[] = $self;
        }

        return $_children;
    }

    /**
     * 获取闭合末尾节点标签
     * @param array $tagMap
     * @param array $startTagAttr
     * @param int $endIndex
     * @param int $startTagCount
     * @return array
     */
    protected function getEndTag(array $tagMap, array $startTagAttr, int $endIndex = 0, int $startTagCount = 0): array {

        if ($endIndex >= count($tagMap)) return [];

        $endIndex = $endIndex + 1;
        $endTag = $tagMap[$endIndex] ?? [];

        if (empty($endTag)) return [];

        $endTagContent = preg_replace('/\s/', '', $endTag[0][0]);

        if (!str_starts_with($endTagContent, '{/')) {
            return $this->getEndTag($tagMap, $startTagAttr, $endIndex, $startTagCount + 1);
        }


        preg_match($this->endRegx, $endTagContent, $info);

        if (!$info || $endIndex === 0 || $startTagAttr['tag'] !== $info[1] || $startTagCount - 1 === 0)
            return $this->getEndTag($tagMap, $startTagAttr, $endIndex, $startTagCount - 1);

        $endTag['index'] = $endIndex;
        return $endTag;
    }

    /**
     * 获取TAG标签及属性
     * @param string $tag
     * @param string $body
     * @return array
     */
    public function getTagAttrAndContent(string $tag, string $body = ''): array {
        $attrsList = explode(' ', trim($tag));

        $tagName = array_shift($attrsList);

        $attrsRegxList = [];
        preg_match_all("/((\s|)i-(.*?)(?=\s|\s\/))|(.*?=\".*?(\"|\"\s|\/))/", implode(' ', $attrsList)." end", $attrsRegxList);

        $attrs = [];

        foreach ($attrsRegxList[0] as $attr) {
            $attr = explode('=', $attr);
            $attrs[trim($attr[0])] = trim($attr[1] ?? '', "\"");
        }

        return [ 'tag' => $tagName, 'attrs' => $attrs, 'body' => $body ];
    }

    /**
     * @param array $tagMap
     * @return ReadRegxTagMap
     */
    public function setTagMap(array $tagMap): static {
        $this->tagMap = $tagMap;
        $this->tree = [];
        return $this;
    }

    /**
     * @param Config|\iflow\template\config\Config $config
     * @return ReadRegxTagMap
     */
    public function setConfig(Config|\iflow\template\config\Config $config): static {
        $this->config = $config;
        return $this;
    }

    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate(string $template): static {
        $this -> template = $template;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate(): string {
        return $this->_template;
    }

    /**
     * 获取节点数据
     * @return array
     */
    public function getTree(): array {
        return $this->tree;
    }
}
