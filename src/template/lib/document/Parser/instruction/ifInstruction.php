<?php


namespace iflow\template\lib\document\Parser\instruction;


use iflow\template\lib\document\Parser\DOMNodeParser;

class ifInstruction extends instructionAbstract
{

    public function parser(DOMNodeParser $DOMNodeParser): static
    {
        parent::parser($DOMNodeParser); // TODO: Change the autogenerated stub
        $this->instructionCode = $this->DOMNodeParser -> getAttributes('i-'.$this->selfInstruction()) ?: '';
        return $this;
    }


    /**
     * 检测if 是否存在分支语句
     * @return bool
     */
    public function nextSiblingInstruction(): bool
    {
        return $this->nextSibling -> getAttributes('i-else')
        || $this->nextSibling -> getAttributes('i-elseif');
    }


    /**
     * 获取当前 if 指令
     * @return string
     */
    public function selfInstruction(): string
    {
        if ($this->DOMNodeParser -> getAttributes('i-if')) {
            return "if";
        }

        if ($this->DOMNodeParser -> getAttributes('i-elseif')) {
            return "elseif";
        }

        if ($this->DOMNodeParser -> getAttributes('i-else') !== null) {
            return "else";
        }

        return "";
    }

    /**
     * 生成指令代码
     * @return string
     */
    public function getInstructionCode(): string
    {
        $self = $this->selfInstruction();
        if ($self === 'else') {
            return "<?php $self: ?>%s<?php endif;?>";
        }
        if ($this->nextSiblingInstruction()) {
            return "<?php $self({$this -> instructionCode}): ?>%s";
        }
        return "<?php $self({$this -> instructionCode}): ?>%s<?php endif;?>";
    }

}