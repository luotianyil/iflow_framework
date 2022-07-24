<?php


namespace iflow\GraphQL\Types;


use GraphQL\Type\Definition\InterfaceType;

class TypeName
{

    public function __construct(
        protected string $typeName = '',
        protected string $typeDescription = '',
        protected ?\Closure $resolveType = null,
        protected ?InterfaceType $interfaceType = null,
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

    /**
     * @param \Closure $resolveType
     * @return typeName
     */
    public function setResolveType(\Closure $resolveType): typeName
    {
        $this->resolveType = $resolveType;
        return $this;
    }


    /**
     * @return \Closure|null
     */
    public function getResolveType(): ?\Closure
    {
        return $this->resolveType;
    }

    /**
     * @param InterfaceType $interfaceType
     * @return typeName
     */
    public function setInterfaceType(InterfaceType $interfaceType): typeName
    {
        $this->interfaceType = $interfaceType;
        return $this;
    }

    /**
     * @return InterfaceType|null
     */
    public function getInterfaceType(): ?InterfaceType
    {
        return $this->interfaceType;
    }

}