# 用户类目表
drop table if exists `user_group_table`;
create table `user_group_table`
(
    `user_group_id`        bigint primary key auto_increment not null COMMENT '用户组ID',
    `user_group_name`      varchar(255)                      not null default '-1' COMMENT '用户组名称',
    `user_group_parent_id` bigint                            not null default 0 COMMENT '用户组所属上层',
    `user_group_type`      smallint                          not null default 2 COMMENT '用户组类型 1 管理 2用户',
    `level`                int                               not null default 1 COMMENT '角色等级',
    `status`               smallint                          not null default 1 COMMENT '用户组状态',
    `create_time`          varchar(255)                      not null default '0' COMMENT '创建时间',
    `update_time`          varchar(255)                      not null default '0' COMMENT '更新时间',
    index (`user_group_name`)
) ENGINE = innodb
  DEFAULT CHARSET = utf8mb4;

insert into `user_group_table`(`user_group_name`, `user_group_type`)
values ('超级管理组', 1);
insert into `user_group_table`(`user_group_name`)
values ('用户组');

# 用户表 用户可直接绑定权限、用户绑定用户组后可继承用户组权限
drop table if exists `user_table`;
create table `user_table`
(
    `uid`           bigint       not null primary key auto_increment COMMENT '用户id',
    `user_group_id` bigint       not null default 2 COMMENT '用户所属组',
    `user_name`     varchar(255) not null default '-1' COMMENT '用户名',
    `nick_name`     varchar(255) not null default '-1' COMMENT '用户昵称',
    `avatar`        varchar(255) not null default 1 COMMENT '用户头像',
    `user_cover`    varchar(255) not null default 1 COMMENT '用户封面',
    `gender`        smallint     not null default 0 COMMENT '用户性别 0 女 1 男 2 保密',
    `password`      varchar(255) not null default '-1' COMMENT '用户密码',
    `signature`     varchar(255) not null default '-1' COMMENT '用户签名',
    `email`         varchar(255) not null default '-1' COMMENT '用户邮箱',
    `phone`         varchar(255) not null default '-1' COMMENT '用户手机',
    `role`          varchar(255) not null default 'user' COMMENT '用户角色类型 user',
    `status`        smallint     not null default 1 COMMENT '用户状态 0 未验证 1 正常 2 封禁',
    `create_time`   varchar(255) not null default '0' COMMENT '创建时间',
    `update_time`   varchar(255) not null default '0' COMMENT '更新时间',
    unique `user_name` (`user_name`),
    unique `email` (`email`),
    unique `phone` (`phone`),
    index (`user_group_id`)
) ENGINE = innodb
  DEFAULT CHARSET = utf8mb4;