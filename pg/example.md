
## 使用举例
向test.tb_order中插入数据，创建物化视图, 分区表

### 基本操作
``` shell
# 连接pg
sudo -u postgres psql

# 查看数据show databases;
\l

# 创建数据库
create database test;

# 切换数据库use test;
\c test

#  查看所有表
\d

```

### 创建主表
https://blog.csdn.net/weixin_42583514/article/details/123063420

PostgreSQL的数据类型包括:

* 数值类型: SMALLINT, INTEGER, BIGINT, NUMERIC, REAL, DOUBLE PRECISION
* 日期/时间类型: DATE, TIME, TIMESTAMP, INTERVAL
* 字符串类型: CHAR, VARCHAR, TEXT
* 二进制类型: BYTEA
* 其他类型: ENUM, ARRAY, DOMAIN

```sql
CREATE TABLE tb_order (
    id serial,
    order_no varchar(32) NOT NULL DEFAULT '',
    third_order_no varchar(32) NOT NULL DEFAULT '',
    user_id integer NOT NULL DEFAULT '0',
    inviter_id integer NOT NULL DEFAULT '0',
    shop_id integer NOT NULL DEFAULT '0',
    goods_id integer NOT NULL DEFAULT '0',
    goods_cate_id integer NOT NULL DEFAULT '0',
    goods_name varchar(64) NOT NULL DEFAULT '',
    price decimal(14, 2) NOT NULL DEFAULT '0.00',
    amount integer NOT NULL DEFAULT '0',
    total_price decimal(14, 2) NOT NULL DEFAULT '0.00',
    created_time timestamp(0) without time zone,
    coupon_name varchar(64) NOT NULL DEFAULT '',
    pay_channel integer NOT NULL DEFAULT '0',
    coupon_id integer NOT NULL DEFAULT '0',
    status integer NOT NULL DEFAULT '0',
    user_remark varchar(256) NOT NULL DEFAULT '',
    freight_charge decimal(14, 2) NOT NULL DEFAULT '0.00',
    pay_amount decimal(14, 2) NOT NULL DEFAULT '0.00',
    pay_order_no varchar(32) NOT NULL DEFAULT '',
    address_id integer NOT NULL DEFAULT '0',
    pay_time timestamp NULL DEFAULT NULL,
    updated_time timestamp NULL DEFAULT NULL,
    send_out_time timestamp NULL DEFAULT NULL,
    finished_time timestamp NULL DEFAULT NULL,
    coupon_amount decimal(14, 2) NOT NULL DEFAULT '0.00',
    order_month integer NOT NULL DEFAULT '0'
)PARTITION BY RANGE (created_time);
```

### 创建唯一索引

```sql
# 分区表建唯一索引必须包括分区键，也就是created_time
CREATE UNIQUE INDEX idx_order_no on tb_order (order_no,created_time);
```

### 创建分区表
```sql
CREATE TABLE tb_order_202305 PARTITION OF tb_order FOR VALUES FROM ('2023-05-01') TO ('2023-06-01');
CREATE TABLE tb_order_202304 PARTITION OF tb_order FOR VALUES FROM ('2023-04-01') TO ('2023-05-01');
```

### 创建物化视图

```sql
CREATE MATERIALIZED VIEW tb_order_view AS select order_month,user_id,count(*) as count,sum(amount) as amount from tb_order group by order_month,user_id;

# select * from tb_order_view order by count desc limit 10;
```

### 刷新物化视图
```sql
refresh materialized view  tb_order_view;
```

### 分析查询
```sql
EXPLAIN ANALYZE select count(*) from tb_order;

select count(*),sum(amount) from tb_order;
select sum(count),sum(amount),count(*) from tb_order_view;
```