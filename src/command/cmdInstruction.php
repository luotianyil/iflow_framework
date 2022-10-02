<?php


namespace iflow\command;

use iflow\console\Adapter\Command;
use iflow\Response;

class cmdInstruction extends Command
{

    protected array $shell = [];

    protected array $shellCommand = [
        'help' => 'outInputHelp',
        'run' => 'refClassRunFunction'
    ];

    // 命令行 指令监听
    public function handle(array $event = [])
    {
        // TODO: Implement handle() method.
        $this->outInputHelp() -> outIflowStart();
        while (true) {
            $input = str_replace("\r\n", '', fgets(STDIN));
            if ($input === '') {
                $this->outIflowStart();
                continue;
            }

            if ($input === 'exit') {
                $this->Console -> writeConsole -> writeLine('exit success...');
                break;
            }

            $this->ParserInput($input);
        }
    }

    // 反射实例化类并执行方法
    protected function refClassRunFunction()
    {
        if (count($this->shell) > 1) {
            array_shift($this->shell);

            // 处理 并获取执行类型
            $run = [
                '-c' => '',
                '-f' => '',
                '-p' => []
            ];

            foreach ($this->shell as $shell) {
                $shell = explode(':', $shell);
                if (count($shell) > 1) {
                    if ($shell[0] === '-p') {
                        $shell[1] = explode('&', $shell[1]);
                    }
                    $run[$shell[0]] = $shell[1];
                }
            }
            if ($run['-c'] !== '' || class_exists($run['-c'])) {
                $data = call_user_func([$this->app -> make($run['-c']), $run['-f']], ...$run['-p']);
            } else {
                $data = call_user_func($run['-f'], ...$run['-p']);
            }
            $this->refClassRunFunctionData($data);
        }
    }

    // 执行后结果
    protected function refClassRunFunctionData($data)
    {
        if ($data instanceof Response) {
            $data = $data -> data;
        }

        $this->Console -> writeConsole -> writeLine(
            is_string($data) ? $data : var_export($data, "\r\n")
        );
    }


    // 解析shell 并执行相应方法
    protected function ParserInput(string $input)
    {
        $this->shell = explode(' ', trim($input));

        if (array_key_exists($this->shell[0], $this->shellCommand)) {
            if (method_exists($this, $this->shellCommand[$this->shell[0]])) {
                call_user_func([$this, $this->shellCommand[$this->shell[0]]]);
            }
        }
        $this->outIFlowStart();
    }

    protected function outIFlowStart(): static {
        $this->Console -> outWrite('iflow:/> ');
        return $this;
    }

    protected function outInputHelp(): static
    {
        $this->Console -> writeConsole -> writeLine('iflow Shell, your out input: exit');
        $this->Console -> writeConsole -> writeLine('iflow Shell help:');
        $this->Console -> writeConsole -> writeLine('run -c:ClassName -f:Function -p:Params&...');
        $this->Console -> writeConsole -> writeLine('help');
        return $this;
    }
}