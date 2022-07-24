<?php


namespace iflow\GraphQL\Types;


use GraphQL\Type\Definition\Type;

class Paginate extends AbstractType
{

    public function fields(): array
    {
        return  [
            [
                'name'          => 'hasPreviousPage',
                'description'   => '是否有上一页',
                'type'          => Type::nonNull(Type::boolean()),
            ],
            [
                'name'          => 'hasNextPage',
                'description'   => '是否有下一页',
                'type'          => Type::nonNull(Type::boolean()),
            ],
        ];
    }

}