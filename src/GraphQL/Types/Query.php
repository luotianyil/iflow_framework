<?php


namespace iflow\GraphQL\Types;


use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\UnionType;

class Query extends AbstractType
{

    public function __construct(
        protected TypeName $typeName,
        protected TypeFields $typeFields
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
     * @return typeFields
     */
    public function getTypeFields(): typeFields
    {
        return $this->typeFields;
    }

    /**
     * @return typeName
     */
    public function getTypeName(): typeName
    {
        return $this->typeName;
    }

    /**
     * 获取字段信息
     * @return array
     */
    public function fields(): array
    {
        $fields = [
            'name' => $this->typeName -> getTypeName(),
            'description' => $this->typeName -> getTypeDescription(),
            'fields' => $this->typeFields -> fields()
        ];

        // 设置闭包方法
        if ($this->typeName -> getResolveType() !== null) {
            $fields['resolveType'] = $this->typeName -> getResolveType();
        }

        // 设立共享接口
        if ($this->typeName -> getInterfaceType() !== null) {
            $fields['interfaces'] = $this->typeName -> getInterfaceType();
        }

        return $fields;
    }

    /**
     * 获取 ObjectType
     * @return ObjectType
     */
    public function buildQueryFieldsObject(): ObjectType
    {
        return new ObjectType($this->fields());
    }

    /**
     * 获取 ObjectType
     * @return ObjectType
     */
    public function getTypeObject(): ObjectType
    {
        return $this->buildQueryFieldsObject();
    }

    /**
     * 生成UnionType
     * @param string $name
     * @param array $types
     * @param string $description
     * @param \Closure $resolveType
     * @return UnionType
     */
    public function buildQueryFieldsUnionType(string $name, array $types, string $description, \Closure $resolveType): UnionType
    {
        return new UnionType([
            'name' => $name,
            'types' => $types,
            'description' => $description,
            'resolveType' => $resolveType
        ]);
    }

    /**
     * 生成 InterfaceType
     * @param \Closure $resolveType
     * @return InterfaceType
     */
    public function buildQueryFieldsInterfaceType(\Closure $resolveType): InterfaceType
    {
        $fields = $this->fields();
        $fields['resolveType'] = $resolveType;
        return new InterfaceType($fields);
    }
}