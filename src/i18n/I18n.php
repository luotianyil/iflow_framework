<?php


namespace iflow\i18n;


use iflow\App;
use iflow\i18n\lib\utils;

class I18n
{

    use utils;
    protected array $lang = [];
    protected App $app;

    public function initializer(App $app)
    {
        $this->app = $app;
        $this->setLang();
    }

    protected function setLang(): static {
        $langFiles = config('app@i18n');
        foreach ($langFiles as $key => $value) {
            if (file_exists($value)) $this->lang[strtolower($key)] = loadConfigFile($value);
        }
        config($this->lang, 'i18n');
        return $this;
    }

    public function i18n(string $key, string|array $default = '', $lan = '')
    {
        $this->setLangType($lan);
        $lan = config('i18n@' . $this->langType . '.' .$key);
        if ($lan) return $lan;
        if (is_array($default)) {
            $default = $default[$this->langType] ?? array_values($default)[0];
        }
        return $default;
    }
}