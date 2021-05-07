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
        $command = $this->Console -> input -> getUserCommand();
        $info = [
            'out' => $this->app -> getDefaultRootPath() . "build.phar",
            'bin' => 'iflow',
            'webindex' => 'public/index.php'
        ];
        foreach ($command as $cmd) {
            $cmd = explode("=", $cmd);
            if (count($cmd) > 1) {
                $info[strtolower($cmd[0])] = $cmd[1];
            }
        }
        $this -> Console -> outPut -> writeLine('build start ....');
        (new \iflow\Utils\buildPhar($info, $this)) -> build();
    }
}