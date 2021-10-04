<?php


namespace iflow\command;


use iflow\console\lib\Command;

/**
 * 生成Phar包
 * Class buildPhar
 * @package iflow\command
 */
class buildPhar extends Command
{

    public function handle(array $event = [])
    {
        // TODO: Implement handle() method.
        $info = [
            'out' => $this->getArgument('out', $this->app -> getDefaultRootPath() . "build.phar"),
            'bin' => $this->getArgument('bin', 'iflow'),
            'webindex' => $this->getArgument('webindex', '/public/index.php'),
            'privatekey' => $this->getArgument('privatekey')
        ];
        $this -> Console -> outPut -> writeLine('build start ....');
        (new \iflow\Utils\buildPhar($info, $this)) -> build();
    }
}