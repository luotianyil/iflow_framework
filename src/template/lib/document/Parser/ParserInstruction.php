<?php


namespace iflow\template\lib\document\Parser;


use iflow\template\lib\document\Parser\instruction\forInstruction;
use iflow\template\lib\document\Parser\instruction\ifInstruction;
use iflow\template\lib\document\Parser\instruction\instructionAbstract;

class ParserInstruction
{

    protected array $instruction = [
        'i-if' => ifInstruction::class,
        'i-for' => forInstruction::class
    ];

    protected DOMNodeParser $DOMNodeParser;

    protected string $code = "<?php %s; ?>";

    public function parserInstruction(DOMNodeParser $DOMNodeParser, string $html): string
    {
        $this->DOMNodeParser = $DOMNodeParser;
        $instructionCode = $this->traverseInstruction($this->instruction, "%s");
        return sprintf($instructionCode, $html);
    }

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