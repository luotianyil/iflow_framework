<?php

namespace iflow\template\Adapter\Regx\RegxInterpreter\Traits;

use iflow\template\Adapter\Regx\RegxInterpreter\TagMap;

trait ReadRegxToPhpCode {

    protected string $templateCode;

    protected TagMap $tagNodeMap;

    /**
     * 标签对处理
     * @param TagMap $tagMap
     * @param string $templateCode
     * @return string
     * @throws \Exception
     */
    public function labelPairToPhpCode(TagMap $tagMap, string $templateCode): string {

        $this->templateCode = $templateCode;
        $this->tagNodeMap = $tagMap;
        $tree = $tagMap -> getTree();

        foreach ($tree as $tIndex => $tNode) {
            $toPhpDatum = $this->getToPhpCodeDatum($tNode, $tIndex, $tree, $templateCode);
            $templateCode = str_replace($tNode['selfTag']['_raw'], $this -> toPhpCode($toPhpDatum), $templateCode);
        }

        return $templateCode;
    }


    /**
     * 获取指定代码块范围
     * @param int $startLine
     * @param int $endLine
     * @return string
     */
    public function getTemplateCodeByTag(int $startLine, int $endLine): string {
        return substr($this->templateCode, $startLine, $endLine);
    }

    /**
     * 获取当前TAG 代码块原代码
     * @param array $startTag
     * @param array $endTag
     * @return string
     */
    public function getRawTemplateCodeByTag(array $startTag, array $endTag): string {
        $offset = strlen($endTag[0][0]);
        return substr($this->templateCode, $startTag['tagSource'][1], $endTag[0][1] + $offset - $startTag['tagSource'][1]);
    }

    /**
     * 设置解析数据
     * @param array $tNode
     * @param int $tIndex
     * @param array $tree
     * @param string $templateCode
     * @return array
     * @throws \Exception
     */
    public function getToPhpCodeDatum(array &$tNode, int $tIndex, array $tree, string $templateCode): array {

        $toPhpCodeMap = $this->traverseChildrenNode($tNode['children'], $templateCode ?: $this->templateCode);
        $tNode['selfTag']['body'] = $this->getTemplateCodeByTag($tNode['startLine'], $tNode['endLine'] - $tNode['startLine']);

        $tNode['selfTag']['_raw'] = $this->getRawTemplateCodeByTag($tNode['selfTag'], $tNode['endTag']);
        $tNode['selfTag']['raw'] = '';

        foreach ($toPhpCodeMap as $codeItem) {
            $tNode['selfTag']['raw'] = str_replace($codeItem['templateCode'], $codeItem['phpCode'], $tNode['selfTag']['body']);
            $tNode['selfTag']['body'] = $tNode['selfTag']['raw'];
        }

        return [
            'tagAttrs' => $tNode['selfTag'],
            'tagContent' => $tNode['selfTag']['body'],
            'body' => $tNode['selfTag']['body'],
            'isCloseLabel' => false,
            'index' => $tIndex,
            'tree' => $tree,
        ];
    }


    /**
     * 遍历树状节点生成PHP模板代码
     * @param array $tree
     * @param string $templateCode
     * @return array
     * @throws \Exception
     */
    public function traverseChildrenNode(array $tree, string $templateCode): array {

        if (empty($tree)) return [];

        $toPhpCodeMap = [];

        foreach ($tree as $tIndex => $tNode) {
            $toPhpDatum = $this->getToPhpCodeDatum($tNode, $tIndex, $tree, $templateCode);
            $toPhpCodeMap[] = [
                'templateCode' => $tNode['selfTag']['_raw'],
                'phpCode' => $this -> toPhpCode($toPhpDatum)
            ];
        }
        return $toPhpCodeMap;
    }

}
