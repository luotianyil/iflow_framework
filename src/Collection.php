<?php


namespace iflow;


use Exception;
use iflow\contract\Arrayable;
use Traversable;

class Collection implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable, Arrayable
{

    protected array $items = [];

    public function getIterator()
    {
        // TODO: Implement getIterator() method.
        return new \ArrayIterator($this->items);
    }

    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
        return isset($this->items[$offset]) || !empty($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
        return $this->offsetExists($offset) ? $this->items[$offset] : [];
    }

    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
        unset($this->items[$offset]);
    }

    public function count()
    {
        // TODO: Implement count() method.
        return count($this->items);
    }

    public function jsonSerialize(): array
    {
        // TODO: Implement jsonSerialize() method.
        return $this->toArray();
    }

    public function toArray(): array
    {
        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $this->items);
    }

    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function all(): array
    {
        return $this->items;
    }
}