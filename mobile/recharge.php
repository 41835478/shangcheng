<?php
// 今天优品多商户系统   QQ  120029121 309485552  技术团队 热风科技


header('Content-type:text/html;charset=utf-8');
include 'api/class.juhe.recharge.php'; //引入文件

//接口基本信息配置
$appkey = '16a4adfe48ef3955302e9bc37c6ffab2'; //从聚合申请的话费充值appkey
$openid = 'JH1a226ced2f7fc0294e1fcf5a07f3c171'; //注册聚合账号就会分配的openid，在个人中心可以查看
$recharge = new recharge($appkey,$openid);

//检测手机号码以及面额是否可以充值
$telCheckRes = $recharge->telcheck('15521001917',5);
if($telCheckRes){
    //说明支持充值，可以继续充值操作，以下可以根据实际需求修改
    echo "OK";
}else{
    //暂不支持充值，以下可以根据实际需求修改
    exit("对不起，该面额暂不支持充值");
}

//根据手机号码以及面额查询商品信息
$telQueryRes =$recharge->telquery('13570547927',5); #可以选择的面额5、10、20、30、50、100、300
if($telQueryRes['error_code'] == '0'){
    //正常获取到话费商品信息
    $proinfo = $telQueryRes['result'];
    /*
    [cardid] => 191406
    [cardname] => 江苏电信话费10元直充
    [inprice] => 10.02
    [game_area] => 江苏苏州电信
    */
   echo "商品ID：".$proinfo['cardid']."<br>";
   echo "商品名称：".$proinfo['cardname']."<br>";
   echo "进价：".$proinfo['inprice']."<br>";
   echo "手机归属地：".$proinfo['game_area']."<br>";
}else{
    //查询失败，可能维护、不支持面额等情况
    echo $telQueryRes["error_code"].":".$telQueryRes['reason'];
}

//提交话费充值
$orderid = '111111111'; //自己定义一个订单号，需要保证唯一
$telRechargeRes = $recharge->telcz('13570547927',55,$orderid); #可以选择的面额5、10、20、30、50、100、300
if($telRechargeRes['error_code'] =='0'){
    //提交话费充值成功，可以根据实际需求改写以下内容
    echo "充值成功,订单号：".$telRechargeRes['result']['sporder_id'];
    var_dump($telRechargeRes);
}else{
    //提交充值失败，具体可以参考$telRechargeRes['reason']
    var_dump($telRechargeRes);
}

//订单状态查询
$orderid = '111111111'; //商家自定的订单号
$orderStatusRes = $recharge->sta($orderid);

if($orderStatusRes['error_code'] == '0'){
    //查询成功
    if($orderStatusRes['result']['game_state'] =='1'){
        echo "充值成功";
    }elseif($orderStatusRes['result']['game_state'] =='9'){
        echo "充值失败";
    }elseif($orderStatusRes['result']['game_state'] =='-1'){
        echo "提交充值失败"; //可能是如运营商维护、账户余额不足等情况
    }
}else{
    //查询失败
    echo "查询失败:".$orderStatusRes['reason']."(".$orderStatusRes['error_code'].")";

}


// 今天优品多商户系统 Added by PRINCE QQ 120029121  2016年7月18日


?>

