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
        $node = $this->DOMNodeParser -> nextSibling;
        $this->nextSibling = $this->DOMNodeParser -> getNextNode($node) ?: new DOMNodeParser($node);
        return $this;
    }

    abstract public function getInstructionCode(): string;
}