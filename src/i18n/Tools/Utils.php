<?php


namespace iflow\i18n\Tools;


use iflow\Container\implement\generate\exceptions\InvokeClassException;

trait Utils {

    protected string $langType = 'zh-cn';

    /**
     * @param string $langType
     * @return static
     * @throws InvokeClassException
     */
    public function setLangType(string $langType = ''): static {

        if ($langType === '') {
            $langType = request() -> getLanguage();
        }

        $this->langType = strtolower($langType);
        return $this;
    }

}