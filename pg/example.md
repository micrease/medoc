
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

### 插入数据
```sql
# testpg.php生成的sql

insert into tb_order(order_no,third_order_no,user_id,inviter_id,shop_id,goods_id,goods_cate_id,goods_name,price,amount,total_price,coupon_id,coupon_amount,coupon_name,pay_channel,created_time,status,user_remark,order_month,freight_charge,pay_amount,pay_order_no,address_id,pay_time,updated_time,send_out_time,finished_time)values('202305241223525251379','202305241223521675588','38232','38232','3242','54947','549','goodsName54947','54.947','1','54.947','19309','4','优惠劵19309','4','2023-05-11 12:23:52','6','用户备注382328544','202305','30','80.947','P217110254','68232','2023-05-11 12:28:04','2023-05-11 18:38:41','2023-05-11 18:38:41','2023-05-12 17:41:05');
insert into tb_order(order_no,third_order_no,user_id,inviter_id,shop_id,goods_id,goods_cate_id,goods_name,price,amount,total_price,coupon_id,coupon_amount,coupon_name,pay_channel,created_time,status,user_remark,order_month,freight_charge,pay_amount,pay_order_no,address_id,pay_time,updated_time)values('202305241223528373480','202305241223525105057','23751','23751','3448','38149','381','goodsName38149','38.149','4','152.596','87470','8','优惠劵87470','3','2023-04-29 12:23:52','3','用户备注237512640','202304','50','194.596','P342032765','53751','2023-04-29 12:25:21','2023-04-29 12:25:21');
insert into tb_order(order_no,third_order_no,user_id,inviter_id,shop_id,goods_id,goods_cate_id,goods_name,price,amount,total_price,coupon_id,coupon_amount,coupon_name,pay_channel,created_time,status,user_remark,order_month,freight_charge,pay_amount,pay_order_no,address_id,pay_time,updated_time,send_out_time,finished_time)values('202305241223523661681','202305241223529589558','56910','12466','3601','52161','521','goodsName52161','52.161','1','52.161','17532','0','优惠劵17532','1','2023-05-21 12:23:52','6','用户备注569107376','202305','20','72.161','P850662710','86910','2023-05-21 12:24:24','2023-05-22 07:16:19','2023-05-22 07:16:19','2023-05-23 00:18:24');
insert into tb_order(order_no,third_order_no,user_id,inviter_id,shop_id,goods_id,goods_cate_id,goods_name,price,amount,total_price,coupon_id,coupon_amount,coupon_name,pay_channel,created_time,status,user_remark,order_month,freight_charge,pay_amount,pay_order_no,address_id,pay_time,updated_time,send_out_time,finished_time)values('202305241223528809476','202305241223522910983','67498','23054','1036','45072','450','goodsName45072','45.072','9','405.648','58741','7','优惠劵58741','4','2023-04-27 12:23:52','6','用户备注674987724','202304','20','418.648','P865931485','97498','2023-04-27 12:28:49','2023-04-28 10:56:45','2023-04-28 10:56:45','2023-04-28 21:41:56');
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

### 无锁刷新物化视图
```sql
#首先需要创建一个唯一索引
CREATE UNIQUE INDEX idx_unique ON tb_order_view (order_month,user_id);

#刷新物化视图
refresh materialized VIEW concurrently tb_order_view;
```

### 分析查询
```sql
EXPLAIN ANALYZE select count(*) from tb_order;

# select count(*),sum(amount) from tb_order;
# select sum(count),sum(amount),count(*) from tb_order_view;
```