<?php

//安装php扩展
//apt install php-pgsql

$host    = "host=127.0.0.1";
$port    = "port=5432";
$dbname   = "dbname=test";
$credentials = "user=postgres password=123456";
$db = pg_connect( "$host $port $dbname $credentials" );
if(!$db){
    echo "Error : Unable to open database\n";
} else {
    echo "Opened database successfully\n";
}


for($i=0;$i<10000;$i++){
    echo "insert batch $i \n";
    $orders = genOrders(100);
    batchSql($db,$orders);

    if($i%100==99){
        sleep(5);
    }
}

function batchSql($db,$orders){
    foreach ($orders as $order){
        $keyArr = array_keys($order);
        $valArr = array_values($order);

        $keys = implode(",",$keyArr);
        $vals = implode("','",$valArr);
        $sql="insert into tb_order($keys)values('$vals')";
        $ret = pg_query($db, $sql);
        if(!$ret){
                echo pg_last_error($db);exit;
        } else {
            echo "Records created successfully\n";
        }
    }
}


function genOrders($num=1)
{
    $orders = [];
    for ($i = 0; $i < $num; $i++) {
        $goodsId = rand(10000, 99999);
        $userId = rand(10000, 99999);
        $price = $goodsId / 1000;
        $amount = rand(1, 10);
        $couponId = rand(10000, 99999);
        $createTimestamp = time() - rand(0, 40) * 86400;

        $order = [
            'order_no' => date("YmdHis") . rand(10000, 99999) . $i,
            'third_order_no' => date("YmdHis") . rand(1000000, 9999999),
            'user_id' => $userId,
            'inviter_id' => $userId % 44444,
            'shop_id' => rand(1000, 4000),
            'goods_id' => $goodsId,
            'goods_cate_id' => intval($goodsId / 100),
            'goods_name' => "goodsName" . $goodsId,
            'price' => $price,
            'amount' => $amount,
            'total_price' => $price * $amount,
            'coupon_id' => $couponId,
            'coupon_amount' => $couponId % 9,
            'coupon_name' => "优惠劵" . $couponId,
            'pay_channel' => rand(1, 4),
            'created_time' => date("Y-m-d H:i:s", $createTimestamp),
            'status' => rand(1, 6),
            'user_remark' => "用户备注".$userId.rand(1000,9999),
        ];
        $order['order_month'] = intval(date("Ym",strtotime($order['created_time'])));
        $order['freight_charge'] = rand(1,5)*10;
        $order['pay_amount'] = $order['total_price'] - $order['coupon_amount'] + $order['freight_charge'];
        $order['pay_order_no'] = "P" . rand(100000000, 900000000);
        $order['address_id'] = $userId + 30000;
        if ($order['status'] >= 2) {
            $order['pay_time'] = date("Y-m-d H:i:s", $createTimestamp + rand(10, 300));
            $order['updated_time'] = $order['pay_time'];
        }

        if ($order['status'] >= 4) {
            $order['send_out_time'] = date("Y-m-d H:i:s", strtotime( $order['pay_time'] )+rand(16400,86400));
            $order['updated_time'] =  $order['send_out_time'];
        }

        if ($order['status'] >= 5) {
            $order['finished_time'] = date("Y-m-d H:i:s", strtotime( $order['send_out_time'] )+rand(16400,86400));
            $order['updated_time'] =  $order['send_out_time'];
        }

        $orders[] = $order;
    }
    return $orders;
}

?>