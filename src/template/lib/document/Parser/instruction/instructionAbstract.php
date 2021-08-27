<?php


namespace iflow\template\lib\document\Parser\instruction;


use iflow\template\lib\document\Parser\DOMNodeParser;

abstract class instructionAbstract
{

    protected DOMNodeParser $nextSibling;
    protected DOMNodeParser $DOMNodeParser;
    protected string $instructionCode = "";

    public function parser(DOMNodeParser $DOMNodeParser): static
    {
        $this->DOMNodeParser = $DOMNodeParser;
        $this->nextSibling = new DOMNodeParser($this->DOMNodeParser -> nextSibling);
        return $this;
    }

    abstract public function getInstructionCode(): string;
}