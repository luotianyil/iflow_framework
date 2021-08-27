<?php


namespace iflow\template\lib\document\Parser;


use iflow\template\lib\config;
use iflow\template\lib\document\Parser\instruction\instructionAbstract;

class ParserInstruction
{

    protected array $instruction = [];
    protected DOMNodeParser $DOMNodeParser;

    public function __construct(protected config $config)
    {
        $this->instruction = $this->config -> getInstruction();
    }

    public function parserInstruction(DOMNodeParser $DOMNodeParser, string $html): string
    {
        $this->DOMNodeParser = $DOMNodeParser;
        $instructionCode = $this->traverseInstruction($this->instruction, "%s");
        return sprintf($instructionCode, $html);
    }

    /**
     * 解析自定义指令
     * @param array $instruction
     * @param $instructionCode
     * @return string
     */
    protected function traverseInstruction(array $instruction, $instructionCode): string
    {
        foreach ($instruction as $instructionName => $instructionValue) {
            if ($this->DOMNodeParser -> getAttributes($instructionName) === null) continue;
            $instructionObject = new $instructionValue;
            if ($instructionObject instanceof instructionAbstract) {
                $instructionCode = sprintf(
                    $instructionCode,
                    $instructionObject
                        -> parser($this->DOMNodeParser)
                        -> getInstructionCode()
                );
            }
        }
        return $instructionCode;
    }

}