<?php

namespace iflow\template\Adapter\Regx\RegxInterpreter;

use iflow\Container\implement\annotation\tools\data\Inject;
use iflow\Helper\Str\Str;
use iflow\template\config\Config;
use iflow\template\document\Parser\utils\Literal;

class RegxInterpreter {

    /**
     * 获取到的标签列表
     * @var array
     */
    protected array $tagListMap = [];


    /**
     * 标签列表
     * @var array
     */
    protected array $tagMap = [];

    /**
     * Tag解析
     * @var ReadTagTemplate
     */
    protected ReadTagTemplate $readTagTemplate;


    /**
     * @var TagMap 标签节点
     */
    #[Inject]
    protected TagMap $tagNodeMap;

    /**
     * 解析后的PHP模板代码
     * @var string
     */
    protected string $phpTemplateCode = '';


    /**
     * 原样输出
     * @var Literal
     */
    #[Inject]
    public Literal $literal;

    public function __construct(protected string $content, protected Config $config) {
        $this->phpTemplateCode = $this->content;
    }

    /**
     * 获取闭合标签
     * @return RegxInterpreter
     */
    protected function getCloseLabel(): RegxInterpreter {

        // 获取闭合标签
        preg_match_all('/((?<={)[^}]*(?=\/}))/is', $this->phpTemplateCode, $closeLabelMap, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        if (empty($closeLabelMap)) return $this;

        foreach ($closeLabelMap as $index => $label) {
            if (strpos($this->phpTemplateCode, $label[0][0])) {
                $attrs = $this -> tagNodeMap ->getTagAttrAndContent($label[1][0], "{{$label[0][0]} /}");

                $tagUuid = Str::genUuid();

                $this->phpTemplateCode = str_replace("{". $label[0][0] ."/}", $tagUuid, $this->phpTemplateCode);

                $this->tagListMap[$tagUuid] = [
                    'tagAttrs' => $attrs,
                    'tagContent' => $label[0][0],
                    'isCloseLabel' => true,
                    'index' => count($this->tagListMap)
                ];
            }
        }
        return $this;
    }

    /**
     * 获取标签对
     * @return RegxInterpreter
     */
    protected function getLabelPair(): RegxInterpreter {

        preg_match_all('!({/?.*})!', $this->phpTemplateCode, $labelPair, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        if (empty($labelPair)) return $this;

        $this->tagNodeMap
            -> setTemplate($this->phpTemplateCode)
            -> setTagMap($labelPair)
            -> setConfig($this->config)
            -> toTree();

        return $this;
    }

    /**
     * 读取Tag解析成PHP-CODE
     * @param array $templateCode
     * @return string
     * @throws \Exception
     */
    protected function renderTagTemplateToPhpCode(array $templateCode = []): string {
        return $this->readTagTemplate -> toPhpCode($templateCode);
    }

    /**
     * 加载引入文件模板
     * @return string
     * @throws \Exception
     */
    public function getPhpTemplateCode(): string {

        $this->phpTemplateCode = $this->literal -> literal($this->phpTemplateCode);
        $this->getCloseLabel() -> getLabelPair();

        if (empty($this->tagListMap)) return $this->phpTemplateCode;

        $this->readTagTemplate = new ReadTagTemplate($this->config, $this->tagListMap);
        // 处理标签对
        if ($this->tagNodeMap -> getTree())
            $this->phpTemplateCode = $this->readTagTemplate -> labelPairToPhpCode($this->tagNodeMap, $this->phpTemplateCode);

        // 处理单标签
        foreach ($this->tagListMap as $tagId => $tagAttr) {
            $this->phpTemplateCode = str_replace($tagId,
                $this->renderTagTemplateToPhpCode($tagAttr),
                $this->phpTemplateCode
            );
        }

        return $this->literal -> out_literal($this->phpTemplateCode);
    }

}