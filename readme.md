[![Php Version](https://img.shields.io/badge/php-%3E=8.0.1-brightgreen.svg)](https://secure.php.net/)
[![Swoole Version](https://img.shields.io/badge/swoole-%3E=4.5.0-brightgreen.svg)](https://github.com/swoole/swoole-src)
[![iflow Framework Version](https://img.shields.io/badge/iflow_framework-%3E=0.0.1-brightgreen.svg)](https://github.com/luotianyil/iflow_framework)
[![think-orm Version](https://img.shields.io/badge/think/orm-%3E=2.0.x-brightgreen.svg)](https://www.kancloud.cn/manual/think-orm/1257998)
[![iflowFramework Doc](https://img.shields.io/badge/docs-passing-green.svg?maxAge=2592000)](https://www.yuque.com/youzhiyuandemao/ftorkm)

# iflowFramework



iflowFramework是基于 Php 8.0+ 和 Swoole 4.5+ 的高性能、简单易用的开发框架。支持在 Swoole Server/FPM 同时 支持 windows 上运行 (无需安装swoole扩展、仅支持http服务)。内置了 Http ，Tcp，WebSocket，MQTT，RPC服务。




# 代码

Github : https://github.com/luotianyil/iflow_framework

Gitee : https://gitee.com/mkccl/iflow_application

# 安装

composer install 前更改 composer 国内源

```
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
```


## 初始化项目



示例项目

git初始化
```
git clone https://gitee.com/mkccl/iflow_application

cd iflow_application

sudo composer install / sudo php iflow install
```

composer 初始化项目
```
composer create-project iflow/application
```

启动

```
php iflow start or php iflow start-service
```


# 文档

[旧文档地址，已停止更新](https://mzshe.cn/#/open_api)

[最新文档地址](https://www.yuque.com/youzhiyuandemao/ftorkm)



# 演示
https://framework.mzshe.cn


# 功能


- 基于 Swoole 扩展

- 容器 (PSR-11)

- HTTP 服务器

- RPC 服务器

- WebSocket 服务器

- MVC 分层设计

- AOP

- 中间件

- 视图模板

- i18n 国际化

- 注解路由 (PHP8.0 新特性)

- 数据库连接池

- ORM 模型 ([think-orm](https://www.kancloud.cn/manual/think-orm/1257998))

- 日志系统 (PSR-3) (支持类型：File/Elasticsearch)

- 缓存 （支持类型： File/Redis）

- 自定义配置

- 自定义指令

- 事件注册

- Cookie

- Session

- 自定义权限验证

- SMTP 发送邮件

- TCP/HTTP 自定义请求

- Elasticsearch 客户端

- Kafka

- DHT 爬虫

- 更多有趣的助手函数
