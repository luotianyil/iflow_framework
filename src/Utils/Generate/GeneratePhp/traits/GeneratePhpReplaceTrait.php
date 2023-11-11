<?php

namespace iflow\Utils\Generate\GeneratePhp\traits;

trait GeneratePhpReplaceTrait {

    /**
     * 替换命名空间
     * @param string $phpTemplate
     * @return string
     */
    protected function replaceNamespace(string $phpTemplate): string {
        return str_replace('#__NAMESPACE__', $this -> getNamespace(), $phpTemplate);
    }

    /**
     * @param string $phpTemplate
     * @return string
     */
    protected function replaceClass(string $phpTemplate): string {
        return str_replace('#__CLASS__', $this -> getClassName(), $phpTemplate);
    }

    /**
     * @param string $phpTemplate
     * @return string
     */
    protected function replaceExtend(string $phpTemplate): string {
        $extends = $this -> getExtend();
        return str_replace('#__EXTEND__', $extends ? 'extends '. $extends : '', $phpTemplate);
    }

    /**
     * @param string $phpTemplate
     * @return string
     */
    protected function replaceImplement(string $phpTemplate): string {
        $implements = implode(',', $this -> getImplements());
        return str_replace('#__INTERFACE__', $implements ? 'implements '. $implements : '', $phpTemplate);
    }

    /**
     * @param string $phpTemplate
     * @return string
     */
    protected function replaceTrait(string $phpTemplate): string {
        $trait = implode(', ', $this -> traits);
        return str_replace('#__TRAIT__', $trait ? "use {$trait};" : '', $phpTemplate);
    }

    /**
     * @param string $phpTemplate
     * @return string
     * @throws \ReflectionException
     */
    protected function replaceMethod(string $phpTemplate): string {
        return str_replace('#__METHOD__', $this -> generateMethodPhpCode(), $phpTemplate);
    }

}
