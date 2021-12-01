<?php


namespace iflow\router\lib;


class Swagger
{
    protected array $swaggerJson = [
        "openapi" => '3.0.1',
        'info' => [
            'title' => 'iflowFrameWork Application Apis',
            'version' => '0.0.1'
        ],
        // 服务器列表
        'server' => [],
        // API 地址
        'paths' => [],
        'components' => [
            'schemas' => []
        ]
    ];
    protected array $routers = [
        // 路由表
        'router' => [],
        // 路由类参数
        'routerParams' => [],
    ];

    protected string $routerConfigKey = 'app@router';

    public function __construct()
    {
        $this->routers = array_replace_recursive(
            config(config($this->routerConfigKey)['key']), $this->routers
        ) ?: $this->routers;
        $this->swaggerJson['info']['title'] = config('app@appName', 'iflowFrameWork Application Apis');
    }

    /**
     * @param string $routerConfigKey
     * @return static
     */
    public function setRouterConfigKey(string $routerConfigKey): static
    {
        $this->routerConfigKey = $routerConfigKey;
        return $this;
    }

    /**
     * 将路由数据 格式成 swagger格式
     */
    public function buildSwaggerApiJson(): array
    {
        $this->swaggerJson['server'][] = [
            'url' => request() -> getHeader('host')
        ];

        foreach ($this->routers['router'] as $routerKey => $routerValue) {
            foreach ($routerValue as $pathKey => $pathValue) {
                $pathValue['parameter'] = $this->getParameters($pathValue);
                $pathValue['rulePath'] =
                    '/'.str_replace('>', '}', str_replace('<', '{',trim($pathValue['rule'], '/') ?: ''));
                $pathValue['tags'] = [$routerKey];
                $pathValue['description'] = $pathValue['options']['description'] ?? '暂无接口描述';
                $this->swaggerJson['paths'][$pathValue['rulePath']] = $this->getRouterMethods($pathValue);
            }
        }
        return $this -> swaggerJson;
    }

    /**
     * 根据方法获取数据
     * @param array $router
     * @return array
     */
    public function getRouterMethods(array $router = []): array
    {

        if (in_array('*', $router['method'])) $router['method'] = ['get', 'post'];

        $routerInfo = [];
        foreach ($router['method'] as $method) {
            if (!array_key_exists($method, $routerInfo)) {
                $routerInfo[$method] = $router;
                if (strtoupper($method) !== 'GET') {
                    $routerInfo[$method]['requestBody'] = [
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/'.md5($router['rule'])
                                ]
                            ]
                        ],
                        'required' => true
                    ];
                } else {
                    $routerInfo[$method]['parameters'] = $router['parameter'];
                }
            }
        }

        return $routerInfo;
    }

    /**
     * 根据路由获取参数
     * @param $router
     * @return array
     */
    public function getParameters($router): array
    {
        $parameters = [];
        foreach ($router['parameter'] as $parameterName => $parameter) {
            if ($parameter['type'][0] === 'class') {
                $parameters[] = [
                    'name' => $parameterName,
                    'schema' => [
                        'type' => 'object'
                    ],
                    'properties' => $this->getClassParameters($parameter)
                ];
            } else {
                $parameters[] = [
                    'name' => $parameterName,
                    'schema' => [
                        'type' => $parameter['type']
                    ]
                ];
            }
        }


        $componentsSchemas = [];
        foreach ($parameters as $parameterName => $parameter) {
            $componentsSchemas[$parameter['name']] = [
                'type' => $parameter['schema']['type'],
                'properties' => []
            ];

            $properties = $parameter['properties'] ?? [];
            foreach ($properties as $property) {
                if (empty($property['name'])) continue;
                $componentsSchemas[$parameter['name']]['properties'][$property['name']] = [
                    'type' => $property['schema']['type'][0] === 'string' ? '' : 'object'
                ];
            }
        }

        $this->swaggerJson['components']['schemas'][md5($router['rule'])] = [
            'type' => 'object',
            'properties' => $componentsSchemas
        ];

        return $parameters;
    }

    /**
     * 获取类参数
     * @param array $parameters
     * @return array
     */
    public function getClassParameters($parameters = []): array
    {
        $parameterInfo = [];
        $selfClass = $parameters['class'];
        $parameters = $this->routers['routerParams'][$selfClass];
        foreach ($parameters as $parameter) {
            if ($parameter['type'][0] === 'class' && $parameter['class'] !== $selfClass) {
                $parameterInfo[] = $this->getClassParameters($parameter);
                continue;
            }

            if ($parameter['type'][0] !== 'class') {
                $parameterInfo[] = [
                    'name' => $parameter['name'],
                    'schema' => [
                        'type' => $parameter['type']
                    ]
                ];
            }
        }
        return $parameterInfo;
    }
}