<?php


namespace iflow\Swoole\GraphQL\Types;


use GraphQL\Type\Definition\ObjectType;

class Query extends AbstractType
{

    public function __construct(
        protected typeName $typeName,
        protected typeFields $typeFields
    ) {}

    /**
     * @param typeName $typeName
     * @return static
     */
    public function setTypeName(typeName $typeName): static
    {
        $this->typeName = $typeName;
        return $this;
    }

    /**
     * @param typeFields $typeFields
     * @return static
     */
    public function setTypeFields(typeFields $typeFields): static
    {
        $this->typeFields = $typeFields;
        return $this;
    }

    /**
     * 获取字段信息
     * @return array
     */
    public function fields(): array
    {
        return [
            'name' => $this->typeName -> getTypeName(),
            'description' => $this->typeName -> getTypeDescription(),
            'fields' => $this->typeFields -> fields()
        ];
    }

    /**
     * 获取 ObjectType
     * @return ObjectType
     */
    public function buildQueryFieldsObject(): ObjectType
    {
        return new ObjectType($this->fields());
    }
}