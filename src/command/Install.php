<?php


namespace iflow\command;

use iflow\console\Adapter\Command;
use iflow\Utils\basicTools;
use think\db\ConnectionInterface;
use think\facade\Db;

class Install extends Command {

    protected array $config = [];

    protected ConnectionInterface $db;

    protected array $composerShell = [
        'update --ignore-platform-reqs',
        'dump-autoload'
    ];

    public function handle(array $event = []) {
        $this->config = config('install');
        $this -> includeDataBase() ?->  installLibrary();
        $this->Console -> writeConsole -> writeLine('installed');
    }

    protected function includeDataBase(): ?Install {
        if (!extension_loaded('pdo_mysql')) {
            $this->Console -> writeConsole -> writeLine('cannot find pdo_mysql drive, please install pdo_mysql');
            return null;
        }

        try {
            // 初始化数据库
            $databaseConfig = config('database');

            $databases = $databaseConfig['connections'][$databaseConfig['default']];
            $databaseConfig['connections'][$databaseConfig['default']]['database'] = 'sys';

            Db::setConfig($databaseConfig);
            $this->db = Db::connect($databaseConfig['default']);

            $this->checkDatabase($databases);

            // 写入数据库
            $files = find_files($this->config['database']['rootPath'], function (\SplFileInfo $item) {
                return $item -> getExtension() === 'sql';
            });
            $install = $this->config['database']['rootPath'] . DIRECTORY_SEPARATOR . 'install.sql';
            $create = $this->dbImportExecute($install);

            // 验证 install.sql 是否执行成功
            if (!$create) return null;

            foreach ($files as $file) {
                if ($file -> getPathname() !== $install) {
                    $this->dbImportExecute($file -> getPathname());
                }
            }
            return $this;
        } catch (\Throwable $exception) {
            $this->Console -> writeConsole -> writeLine($exception -> getMessage());
            return null;
        }
    }

    protected function checkDatabase(array $defaultConfig) {
        $database = $this->db -> query("show databases like '{$defaultConfig['database']}'");

        // Create Database
        if (!$database) {
            $this -> db -> execute("create database `{$defaultConfig['database']}`");
        }

        $this -> db -> execute("use `{$defaultConfig['database']}`");
    }

    /**
     * 导入SQL文件
     * @param string $filePath
     * @return bool
     */
    protected function dbImportExecute(string $filePath = ''): bool {
        $this->Console -> writeConsole -> writeLine('import dbSQL: ' . basename($filePath));
        if (!file_exists($filePath)) return false;
        $sql = file_get_contents($filePath);
        $sql = str_replace("\r", "\n", $sql);
        $sql = explode(";\n", $sql);
        // 写入数据库
        foreach ($sql as $key => $value) {
            $value = trim($value);
            if (empty($value)) continue;
            $this -> db -> execute($value);
        }
        return true;
    }

    protected function installLibrary() {
        $this->Console -> writeConsole -> writeLine('start install library');
        $composer = $this->config['composer']['rootPath'];
        if (file_exists($composer)) {
            foreach($this->composerShell as $key => $value) {
                (new basicTools()) -> execShell(php_run_path() . ' ' . $composer . ' ' . $value);
            }
        } else {
            $this->Console -> writeConsole -> writeLine('install library error: composerPath ' . $composer . ' not exists');
        }
    }
}