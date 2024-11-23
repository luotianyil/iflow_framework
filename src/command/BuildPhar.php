<?php


namespace iflow\command;


use iflow\console\Adapter\Command;
use iflow\Utils\BuildPhar as UBuildPhar;

/**
 * 生成Phar包
 * Class buildPhar
 * @package iflow\command
 */
class BuildPhar extends Command
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
        $this -> Console -> writeConsole -> writeLine('build start ....');
        (new UBuildPhar($info, $this)) -> build();
    }
}