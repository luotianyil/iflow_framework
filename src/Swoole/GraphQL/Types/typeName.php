<?php


namespace iflow\Swoole\GraphQL\Types;


class typeName
{

    public function __construct(
        protected string $typeName = '',
        protected string $typeDescription = '',
    ) {}

    /**
     * @param string $typeName
     * @return $this
     */
    public function setTypeName(string $typeName): static
    {
        $this->typeName = $typeName;
        return $this;
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return $this->typeName;
    }

    /**
     * @param string $typeDescription
     * @return static
     */
    public function setTypeDescription(string $typeDescription): static
    {
        $this->typeDescription = $typeDescription;
        return $this;
    }

    /**
     * @return string
     */
    public function getTypeDescription(): string
    {
        return $this->typeDescription;
    }

}