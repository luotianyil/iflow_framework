<?php


namespace iflow\i18n\lib;


trait utils {

    protected string $langType = 'zh-cn';

    /**
     * @param string $langType
     * @return static
     */
    public function setLangType(string $langType = ''): static {
        if ($langType === '') {
            $langType = request() -> getLanguage();
        }
        $this->langType = strtolower($langType);
        return $this;
    }

}