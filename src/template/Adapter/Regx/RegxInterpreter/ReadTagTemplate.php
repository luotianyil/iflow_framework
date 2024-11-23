<?php

namespace iflow\template\Adapter\Regx\RegxInterpreter;

use iflow\Container\Container;
use iflow\template\Adapter\Regx\implement\Abstracts\TagAbstract;
use iflow\template\Adapter\Regx\implement\Tag\DefaultTag;
use iflow\template\Adapter\Regx\RegxInterpreter\Traits\ReadRegxToPhpCode;
use iflow\template\config\Config;

class ReadTagTemplate {

    use ReadRegxToPhpCode;

    /**
     *
     * @param Config $config 模板配置
     * @param array $tagMap TAG 标签列表
     */
    public function __construct(protected Config $config, protected array $tagMap) {
    }

    public function toPhpCode(array $templateCode): string {

        if (empty($templateCode)) return "";

        if (
            isset($templateCode['isCloseLabel'])
            && $templateCode['isCloseLabel'] === true
            && str_starts_with($templateCode['tagContent'], ':')
        ) return $this->echoTagToPhpCode($templateCode);

        $tagAttrs = $templateCode['tagAttrs'];
        $tagClazz = $this->config -> getTagByName($tagAttrs['tag']);

        if (empty($tagClazz) || !class_exists($tagClazz['class'])) {
            $tagClazz['class'] = DefaultTag::class;
        }

        $tagObj = Container::getInstance() -> make($tagClazz['class'], [ $this->config, $this->tagMap ]);

        if (!$tagObj instanceof TagAbstract) {
            throw new \Exception('自定义 TAG 解释器异常 Tag: '. $tagAttrs['tag']);
        }

        return $tagObj -> parser($tagAttrs['tag'], $tagAttrs['body'], $templateCode) -> toHtml();
    }


    protected function echoTagToPhpCode(array $tagAttr): string {
        $code = substr($tagAttr['tagContent'], 1);
        return sprintf("<?= %s ?>", $code);
    }

}