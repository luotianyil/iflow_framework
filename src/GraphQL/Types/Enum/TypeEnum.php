<?php


namespace iflow\GraphQL\Types\Enum;


use GraphQL\Type\Definition\EnumType;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;

abstract class TypeEnum extends AnnotationAbstract {

    protected ?EnumType $enumType = null;

    public function __construct(
        protected string $name,
        protected string $description = '',
        protected array $values = [],
    ) {}

    public function getTypeObject(): EnumType
    {
        if ($this->enumType !== null) return $this->enumType;
        return new EnumType([
            'name' => $this->name,
            'description' => $this->description,
            'values' => $this->values
        ]);
    }

    public function addValue(string $name, array $value): typeEnum
    {
        $this->values[$name] = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $description
     * @return typeEnum
     */
    public function setDescription(string $description): typeEnum
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param string $name
     * @return typeEnum
     */
    public function setName(string $name): typeEnum
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }
}