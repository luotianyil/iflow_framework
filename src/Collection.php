<?php


namespace iflow;

use iflow\contract\Arrayable;
use ReturnTypeWillChange;
use Traversable;

class Collection implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable, Arrayable
{

    protected array $items = [];

    public function getIterator(): Traversable {
        // TODO: Implement getIterator() method.
        return new \ArrayIterator($this->items);
    }

    public function offsetExists(mixed $offset): bool {
        // TODO: Implement offsetExists() method.
        return isset($this->items[$offset]) || !empty($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed {
        // TODO: Implement offsetGet() method.
        return $this->offsetExists($offset) ? $this->items[$offset] : [];
    }

    #[ReturnTypeWillChange]
    public function offsetSet(mixed $offset, mixed $value) {
        // TODO: Implement offsetSet() method.
        if (is_null($offset)) {
            return $this->items[] = $value;
        }
        return $this->items[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void {
        // TODO: Implement offsetUnset() method.
        unset($this->items[$offset]);
    }

    public function count(): int {
        // TODO: Implement count() method.
        return count($this->items);
    }

    public function jsonSerialize(): array {
        // TODO: Implement jsonSerialize() method.
        return $this->toArray();
    }

    public function toArray(): array {
        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $this->items);
    }

    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string {
        return json_encode($this->toArray(), $options);
    }

    public function all(): array {
        return $this->items;
    }
}