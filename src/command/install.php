<?php


namespace iflow\command;


use iflow\console\lib\Command;
use iflow\Utils\basicTools;
use think\facade\Db;

class install extends Command
{

    protected array $config = [];

    protected array $composerShell = [
        'update --ignore-platform-reqs',
        'dump-autoload'
    ];

    public function handle(array $event = []) {
        $this->config = config('install');
        $this -> includeDataBase() ?->  installLib();
        $this->Console -> outPut -> writeLine('installed');
    }

    protected function includeDataBase(): static|null
    {
        if (!extension_loaded('pdo_mysql')) {
            $this->Console -> outPut -> writeLine('pdo_mysql not extension! initializer database fail');
            return null;
        }
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
        $composer = $this->config['composer']['rootPath'];
        if (file_exists($composer)) {
            foreach($this->composerShell as $key => $value) {
                (new basicTools()) -> execShell(php_run_path() . ' ' . $composer . ' ' . $value);
            }
        } else {
            $this->Console -> outPut -> writeLine('install library error: composerPath ' . $composer . ' not exists');
        }
    }
}