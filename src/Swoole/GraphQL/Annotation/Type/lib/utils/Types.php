<?php


namespace iflow\Swoole\GraphQL\Annotation\Type\lib\utils;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class Types
{

    /**
     * 获取Type类型
     * @param string|array|Type $type
     * @return Type
     * @throws \ReflectionException
     */
    public function getType(string|array|Type $type): Type
    {
        if (!is_array($type) && $typeObject = $this->isObjectType($type)) {
            return $typeObject;
        }

        $types = null;
        $type = array_reverse(
            explode('->', $type)
        );

        array_map(
            function ($val) use (&$types) {
                $val = trim($val);
                $typeObject = $this->isObjectType($val);

                $types = $typeObject === null
                    ? call_user_func([Type::class, $val], ...$types ? [$types] : [])
                    : $typeObject;
            },
            $type
        );

        return $types;
    }


    /**
     * 验证是否为Type类型对象
     * @param string|Type $type
     * @return Type|null
     * @throws \ReflectionException
     */
    public function isObjectType(string|Type $type): ?Type
    {
        if ($type instanceof Type) return $type;
        if (class_exists($type)) {
            $type = new $type;
            if ($type instanceof Type) return $type;
        }
        return $this->getObjectType($type);
    }


    /**
     * 解析字符串获取对象
     * @param string $type
     * @return Type|null
     * @throws \ReflectionException
     */
    public function getObjectType(string $type): Type|null
    {
        $typeAnnotation = explode('@', $type);
        if (count($typeAnnotation) > 1) {
            if (!class_exists($typeAnnotation[1])) return null;
            $ref = new \ReflectionClass($typeAnnotation[1]);
            $annotation = $ref -> getAttributes($typeAnnotation[0]);
            if (!$annotation) return null;

            $annotationType = $annotation[0] -> newInstance();
            return $annotationType -> __make(app(), $ref) ?-> getTypeObject();
        }
        return null;
    }
}