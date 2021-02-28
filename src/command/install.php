<?php


namespace iflow\command;


use iflow\console\lib\Command;
use iflow\Utils\basicTools;
use think\facade\Db;

class install extends Command
{

    protected array $config = [];

    protected array $composerShell = [
        'update',
        'dump-autoload'
    ];

    public function handle() {
        $this->config = config('install');
        $this -> includeDataBase() ->  installLib();
        $this->Console -> outPut -> writeLine('installed');
    }

    protected function includeDataBase(): static
    {
        $files = find_files($this->config['database']['rootPath'], function (\SplFileInfo $item) {
            return $item -> getExtension() === 'sql';
        });
        $install = $this->config['database']['rootPath'] . DIRECTORY_SEPARATOR . 'install.sql';
        $this->dataExecute($install);
        foreach ($files as $file) {
            if ($file -> getPathname() !== $install) {
                $this->dataExecute($file -> getPathname());
            }
        }
        return $this;
    }

    protected function dataExecute(string $filePath = ''): bool
    {
        $this->Console -> outPut -> writeLine('include DataBase file: ' . basename($filePath));
        if (!file_exists($filePath)) return false;
        $sql = file_get_contents($filePath);
        $sql = str_replace("\r", "\n", $sql);
        $sql = explode(";\n", $sql);
        // 写入数据库
        foreach ($sql as $key => $value) {
            $value = trim($value);
            if (empty($value)) {
                continue;
            }
            Db::execute($value);
        }
        return true;
    }

    protected function installLib() {
        $this->Console -> outPut -> writeLine('start install library');
        $php_path = PHP_BINDIR . DIRECTORY_SEPARATOR . 'php';
        $composer = $this->config['composer']['rootPath'];
        foreach($this->composerShell as $key => $value) {
            (new basicTools()) -> execShell($php_path . ' ' . $composer . ' ' . $value);
        }
    }

}