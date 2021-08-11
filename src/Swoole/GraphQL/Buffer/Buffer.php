<?php


namespace iflow\Swoole\GraphQL\Buffer;


use iflow\Swoole\GraphQL\exception\fieldException;
use iflow\Swoole\GraphQL\exception\TableException;
use iflow\Utils\basicTools;

class Buffer
{
    protected array $map = [
        // tableName => ['fieldID' => 'field']
    ];

    protected array $result   = [
        // tableName => ['resultName' => 'resultFunction']
    ];

    /**
     * 初始化表单
     * @param string $tableName
     * @throws TableException
     */
    public function initMapTable(string $tableName)
    {
        if (isset($this->map[$tableName])) throw new TableException('Maps Table exists');
        $this->map[$tableName] = [];
        $this->result[$tableName] = [];
    }

    /**
     * 向指定表单 添加数据
     * @param string $tableName
     * @param string $fieldId
     * @param mixed|array $fieldValue
     * @return $this
     * @throws TableException
     * @throws fieldException
     */
    public function add(string $tableName, string $fieldId = '', mixed $fieldValue = []): Buffer
    {
        $fieldId = $fieldId ?: (new basicTools()) -> create_uuid();
        if (empty($this->map[$tableName])) throw new TableException('Maps Table does exists');
        if (isset($this->map[$tableName][$fieldId])) throw new fieldException("${fieldId} field exists");

        $this->map[$tableName][$fieldId] = $fieldValue;
        return $this;
    }

    public function exec(string $tableName, string $resultName, \Closure $callback): \Closure
    {
        if (empty($this->result[$tableName])) throw new TableException('Maps Table does exists');
        if (empty($this->result[$tableName][$resultName])) $this->result[$tableName][$resultName] = $callback;

        return $this->result[$tableName][$resultName];
    }

    /**
     * 更新数据
     * @param string $tableName
     * @param string $fieldId
     * @param mixed|array $fieldValue
     * @return bool
     */
    public function update(string $tableName, string $fieldId = '', mixed $fieldValue = []): bool
    {
        if (isset($this->map[$tableName]) && isset($this->map[$tableName][$fieldId])) {
            $this->map[$tableName][$fieldId] = $fieldValue;
            return true;
        }
        return false;
    }

    /**
     * 获取数据
     * @param string $tableName
     * @param string $fieldId
     * @return mixed
     */
    public function get(string $tableName, string $fieldId = ''): mixed
    {
        if (isset($this->map[$tableName]) && isset($this->map[$tableName][$fieldId])) {
            return $this->map[$tableName][$fieldId];
        }
        return null;
    }

    /**
     * 移除数据
     * @param string $tableName
     * @param string $fieldId
     * @return bool
     */
    public function delete(string $tableName, string $fieldId = ''): bool
    {
        if (isset($this->map[$tableName]) && isset($this->map[$tableName][$fieldId])) {
            unset($this->map[$tableName][$fieldId]);
            return true;
        }
        return false;
    }

    /**
     * 清空数据
     * @param string $tableName
     * @return bool
     */
    public function clean(string $tableName): bool {
        if (isset($this->map[$tableName])) {
            unset($this->map[$tableName]);
            return true;
        }
        return false;
    }

    // 重置Buffer数据
    public function cleanAll() {
        $this->map      = [];
        $this->result   = [];
    }
}