<?php
// 今天优品多商户系统   QQ  120029121 309485552  

require(dirname(__FILE__) . '/api.class.php');
require(dirname(__FILE__) . '/wechat.class.php');
$weixinconfig = $GLOBALS['db']->getRow ( "SELECT * FROM " . $GLOBALS['yp']->table('weixin_config') . " WHERE `id` = 1" );
if($weixinconfig['debug_status']){
	echo $_GET['echostr'];exit;  // 今天优品多商户系统 Added by PRINCE QQ 120029121
}
$pc_root_path=PC_ROOT_PATH;

//多微信帐号支持
$id = intval($_GET['id']);
if($id > 0){
	$otherconfig = $GLOBALS['db']->getRow ( "SELECT * FROM " . $GLOBALS['yp']->table('weixin_config') . " WHERE `id` = $id" );
	if($otherconfig){
		$weixinconfig['token'] = $otherconfig['token'];
		$weixinconfig['appid'] = $otherconfig['appid'];
		$weixinconfig['appsecret'] = $otherconfig['appsecret'];
	}
}
$weixin = new core_lib_wechat($weixinconfig);

$pc_url = $_SERVER['SERVER_NAME'] ? HTTP_TYPE."://".$_SERVER['SERVER_NAME']."/" : HTTP_TYPE."://".$_SERVER['HTTP_HOST']."/";
$wap_url_sql = "SELECT `wap_url` FROM " . $GLOBALS['yp']->table('weixin_config') . " WHERE `id`=1";//20160927
$wap_url = $GLOBALS['db'] -> getOne($wap_url_sql);//20160927
					
$weixin->valid();
$api = new weixinapi();
//$pc_url .= $api->dir;// by prince 20170427
$type = $weixin->getRev()->getRevType();
$wxid = $weixin->getRev()->getRevFrom();
$data = $weixin->getRevData();
//上报地理信息
$loc = $weixin->getRev()->getUserLocation();
if($loc){
 	$api->updatelocation($wxid, $loc);
}
$reMsg = "";
switch($type) {
	case 'text':
		$content = $weixin->getRev()->getRevContent();
		break;
	case 'event':
		$event = $weixin->getRev()->getRevEvent();
		$content =  json_encode($event);
		break;
	case 'image':
		$content = json_encode($weixin->getRev()->getRevPic());
		$reMsg = "图片很美！";
		break;
	case 'location':
		//$content = json_encode($weixin->getRev()->getRevGeo());
		//$reMsg = "您所在的位置很安全！";
		$content =$weixin->getRev()->getRevGeo(); 

        $newsData = array();
			
		$newsData[] = array("Title"=>"百度地图导航","description"=>"","PicUrl"=>"","Url"=>"");
		
		if($k == 9)
		{
			break; 
		}
			
	
		echo $weixin->news($newsData)->reply();exit;
		break;
        










		//$reMsg = "微信经度：".$content['y']."\r\n微信纬度：".$content['x'];
		break;
	default:
		$reMsg = $weixinconfig['help'];
}
$api->saveMsg($content,$wxid,$type);
if($reMsg){
	echo $weixin->text($reMsg)->reply();exit;
}

$followInfo = $api->getFollowUserInfo($wxid);
if(!$followInfo or $followInfo['expire_in']-86400<time()){
	
	$info = $weixin->getUserInfo($wxid);
	if($info && $event['event'] != "subscribe") $api->followUser($wxid,$info);//新增或更新信息
}


if ($event['event'] == "subscribe") { //用户关注
    //新关注  reg_type 1关注自动注册 2 邮箱+密码  3用户名+密码	
	if(empty($followInfo['user_name']) && $weixinconfig['reg_type'] == 1){
		
		$rs = $api->subscribe($wxid,$info);
		
		if($rs === false){
			echo $weixin->text("系统繁忙,请稍后再试")->reply();exit;
		}
		$user_info = $GLOBALS['db'] -> getRow("SELECT `user_id`,`user_name`,`passwd_weixin`FROM " . $GLOBALS['yp']->table('users') . "  WHERE `fake_id`= '$wxid'"); 
		$user_name=$user_info['user_name'];
		$pwd=$user_info['passwd_weixin'];
		
		$weixinconfig['followmsg'] .= "\r\n系统已为您生成了帐号。\r\n会员名称：$user_name \r\n账号密码：$pwd \r\n此账号可用于您在PC端登陆使用。回复gn查看更多特色功能演示";
		$weixinconfig['followmsg'] .= "\r\n请认准官方客服微信：120029121、309485552。以免上当受骗！";//运营时此行请删除 cheater

		if(!empty($data['Ticket']) || $scene_id ){
			$sql = "select content from ".$GLOBALS['yp']->table('weixin_qcode')." where qcode = '".$data['Ticket']."'";
			$uid = $GLOBALS['db']->getOne($sql);

			if(intval($uid) > 0){
				 $r = $api->bind_distrib($wxid,$uid);
				 if($r === false){
					 echo $weixin->text("系统繁忙,请稍后再试")->reply();exit; 
				 }
				 $sql = "SELECT user_name,user_id FROM " . $GLOBALS['yp']->table('users') . " WHERE user_id = '$uid'";
			     $parent_info = $GLOBALS['db']->getRow($sql);
				 if($parent_info){
					 $parent_id=$parent_info['user_id'];
					 $parent_name=$parent_info['user_name'];
				 	$weixinconfig['followmsg'] .= "\r\n您已成为[".$parent_name."]的会员 \r\n";
                    @file_get_contents($wap_url."weixin/weixin_remind.php?notice=5&up_uid=".$parent_id."&my_name=".$user_name);
				 }
			}
		}
	}
	
	//prince 120029121
	$scene_id_arr=explode("qrscene_", $event['key']);
	$scene_id = $scene_id_arr[1];
	if($scene_id){
		$user_id = $GLOBALS['db']->getOne ( "SELECT user_id FROM " . $GLOBALS['yp']->table('users') . " WHERE `fake_id` = '$wxid'" );
		$GLOBALS['db']->query("UPDATE " . $GLOBALS['yp']->table('weixin_login') . " SET `uid`='$user_id' WHERE `value` = '$scene_id'");
	}
	//prince 120029121

	//关注送红包
	if($weixinconfig['bonustype2'] > 0){
		$bonus_sn = $api->sendBonus($wxid,$weixinconfig['bonustype2']);
	}
	$bonus_msg =  $bonus_sn ? "\r\n恭喜您获得红包一个。\r\n": "";
	$bonus_msg .=$bonus_sn ?"<a href='{$pc_url}mobile/user.php?act=bonus'>点击查看红包详情>></a>\r\n": "";
	
	//返回地址
	$continue_url = $GLOBALS['db'] -> getOne("SELECT `continue_url` FROM  " . $GLOBALS['yp']->table('users') . "  WHERE `fake_id`= '$wxid'"); 
	if(!empty($continue_url)){
		$continue_url= "\r\n\n".'<a href="' . $continue_url . '">点击继续浏览>></a>';
		$GLOBALS['db'] -> query("UPDATE  " . $GLOBALS['yp']->table('users') . "  SET `continue_url` = '' WHERE `fake_id` ='$wxid'");
	}
	
	//发送欢迎信息
	echo $weixin->text($weixinconfig['followmsg'].$bonus_msg.$continue_url)->reply();
	exit;
}
if ($event['event'] == "unsubscribe"){ // 取消关注
	$api->unFollowUser($wxid);	
	exit;
}
//查询用户输入是否为指定命令
if($type == "text"){
	$userKey = $api->keywordsToKey($content,$diy_type);//20170328
	if($userKey) $event = array('event'=>'CLICK','key'=>$userKey);
	if($content=='qrcode') $event = array('event'=>'CLICK','key'=>$content);
	if($content=='cxsc') $event = array('event'=>'CLICK','key'=>$content);
	if($content=='jcbd') $event = array('event'=>'CLICK','key'=>$content);
	if($content=='test') $event = array('event'=>'CLICK','key'=>$content);
}
//$pc_url .= $api->createTokenLoginUrl($wxid,$api->dir);// by prince 20170427
//判断用户是否点击的菜单
if ($event['event'] == "CLICK"){
	$content = $event['key'];
	if(count($event) == 5)
	{
		$userKey = $api->keywordsToKey($content,$diy_type);//20170328
	}
	$api->sendIntegral($wxid,$num=0,$content);
	switch($content){
		case "best":
		case "new":
		case "hot":
			$newsData = array();
			$reMsg = $api->getGoods($content);
			if($reMsg){
				foreach($reMsg as $k=>$v){
					$newsData[$k]['Title'] = $v['name'];
					$newsData[$k]['Description'] = strip_tags($v['name']);
					$newsData[$k]['PicUrl'] = (strpos($v['goods_thumb'],'http') !== false ? $v['goods_thumb'] : $pc_url.$v['goods_thumb']);
					$newsData[$k]['Url'] = $pc_url."mobile/".$v['url'];
					if($k == 9)
					{
						break; 
					}
				}
			}
			echo $weixin->news($newsData)->reply();exit;
		break;
		case "ddcx":
			$reMsg = $api->getOrder($wxid);
			if($reMsg === false){
				echo $weixin->text("您还没有绑定帐号！")->reply();exit;
			}else{
				$os = array(0=>'未确认',1=>'已确认',2=>'取消',3=>'无效',4=>'退款',5=>'已分单');
				$ps = array(0=>'未付款',1=>'部分支付',2=>'已付款');
				$ss = array(0=>'未发货',1=>'已发货',2=>'确认收货',3=>'配货中',4=>'已发货(部分商品)');
				foreach ($reMsg as $v){
					//$text .= "订单编号：<a href='{$pc_url}mobile/user.php?act=order_detail"."%26"."order_id=".$v['order_id']."'>".$v['order_sn']."</a>\r\n";
					$text .= "订单编号：<a href='{$pc_url}mobile/user.php?act=order_detail"."&"."order_id=".$v['order_id']."'>".$v['order_sn']."</a>\r\n";
					$text .= "订单金额：".$v['order_amount']."\r\n";
					$text .= "订单状态：".$os[$v['order_status']]."\r\n";
					$text .= "付款状态：".$ps[$v['pay_status']]."\r\n";
					$text .= "发货状态：".$ss[$v['shipping_status']]."\r\n";
				}
			}
			$text = $text ? $text : "您还没有购买任何商品！";
			echo $weixin->text($text)->reply();exit;
		break;
		case "jcbd":
			if($followInfo['user_id'] == 0){
				echo $weixin->text("您还没有绑定账号!")->reply();exit;
			}
			$api->unBindUser($wxid);
			echo $weixin->text("帐号绑定解除，请马上绑定新账号！")->reply();exit;
		break;
		case "bdhy":
			if($api->isBindUser($wxid)){
				echo $weixin->text("您已经绑定帐号，无需重复绑定！如需解绑请回复：jcbd")->reply();exit;
			}
			echo $weixin->text($weixinconfig['reg_notice'])->reply();exit;
		break;
		case "mima":
			$user = $api->getUserInfo($wxid);
		    $username = $user['user_name'];
		    $passwd_weixin = $user['passwd_weixin'];
			$text="您的账号是：".$username ."\r\n账号密码是：".$passwd_weixin."\r\n\r\n如需修改密码请回复：\r\nmima=新密码\r\n如新密码为hi123，则回复\r\nmima=hi123";
			echo $weixin->text($text)->reply();exit;
		break;
		//20170101 add by prince 获取当前位置坐标 start
		case "weizhi":
			$user = $api->getUserInfo($wxid);
			if($user['Latitude'] && $user['Longitude']){
				$translate=txmap_translate($user['Latitude'], $user['Longitude']);
				$text="经度：".$translate['lng']."\r\n纬度：".$translate['lat'];
			}else{
				$text="请先设置允许向本公众号提供位置信息";
			}
			echo $weixin->text($text)->reply();exit;
		break;
		//20170101 add by prince 获取当前位置坐 标end
		case "info":
			$reMsg = $api->getUserInfo($wxid);
			if($reMsg === false){
				echo $weixin->text("您还没有绑定帐号！")->reply();exit;
			}else{
				$text= "账号：{$reMsg['user_name']}\r\n";

				if(!empty($reMsg['email']))
				{
					$text .= "邮箱：{$reMsg['email']}\r\n";
				}
				if(!empty($reMsg['mobile_phone']))
				{
					$text .= "手机：{$reMsg['mobile_phone']}\r\n"; 
				}
				$text .= "余额：{$reMsg['user_money']}\r\n";
				$text .= "积分：{$reMsg['pay_points']}\r\n";
				$text .="<a href='{$pc_url}mobile/user.php'>查看详情</a>";
			}
			echo $weixin->text($text)->reply();exit;
			break;
			case "gn"://特色功能
			    $text = "特色功能展示\r\n";
				$text .="单品推广：<a href='{$pc_url}mobile/tuiguang.php?id=142'>》》点击查看详情</a>\r\n";
				$text .="附近店铺：<a href='{$pc_url}mobile/supplier_near.php'>》》点击查看详情</a>\r\n";
				$text .="虚拟团购：<a href='{$pc_url}mobile/virtual_group.php'>》》点击查看详情</a>\r\n";
				$text .="新版拼团：<a href='{$pc_url}mobile/extpintuan.php'>》》点击查看详情</a>\r\n";
				$text .="新版砍价：<a href='{$pc_url}mobile/cut.php'>》》点击查看详情</a>\r\n";
				$text .="新版云购：<a href='{$pc_url}mobile/lucky_buy.php'>》》点击查看详情</a>\r\n";
				$text .="优品早市：<a href='{$pc_url}mobile/morning_market.php'>》》点击查看详情</a>\r\n";
				$text .="\r\n<a href='{$pc_url}mobile/index.php'>浏览手机商城</a>".$_SESSION['location_suppId'];
			
			echo $weixin->text($text)->reply();exit;
			break;
		case "qd":
			if(($num = $api->userSign($wxid)) === false){
				$text = join('', (array)$GLOBALS['err']->last_message());
			}else{
				$text = "签到成功！获取积分{$num}。";
			}
			echo $weixin->text($text)->reply();exit;
			break;
		case "kf":
			$access_token = prince_access_token();	// 今天优品多商户系统 Added by PRINCE QQ 120029121
			echo kefutips($access_token, $wxid);// 今天优品多商户系统 Added by PRINCE QQ 120029121
			echo $weixin->kefu()->reply();exit;
			break;
		case "test":
			$text=$api->gettestinfo($GLOBALS['db'],$wxid);// q q 12 00 29121 
			echo $weixin->text($text)->reply();exit;
			break;
		case 'qdcx':
			$order = $api->queryKuaidi($wxid,$wap_url);//20160927
			if($order === false){
				$text = join('', (array)$GLOBALS['err']->last_message());
			}else{
				$text = '';
				foreach ($order as $o){
					$text .= "订单：{$o['order_sn']}\r\n";
					$text .= "快递名称：{$o['shipping_name']}\r\n";
					$text .= "快递单号：{$o['invoice_no']}\r\n";
					$text .= "最新状态：{$o['kuaidi']['context']}\r\n";
				}
				$text .="\r\n<a href='http://m.kuaidi100.com/'>自助查询，请点击这里</a>\r\n";//20160927
			}
			echo $weixin->text($text)->reply();exit;
			break;
		case 'qrcode':
			$user = $api->getUserInfo($wxid);
		    $user_id = $user['user_id'];
			prince_get_qrcode($wxid,$weixin,$user_id,$pc_root_path);
			break;
		case 'cxsc':
			$user = $api->getUserInfo($wxid);
		    $user_id = $user['user_id'];
			$GLOBALS['db']->query("DELETE FROM ".$GLOBALS['yp']->table('weixin_qcode')." WHERE `content`='$user_id' or `user_id`='$user_id' ");
			@unlink($pc_root_path."images/qrcode/".$user_id.".jpg");
			prince_get_qrcode($wxid,$weixin,$user_id,$pc_root_path);
			break;
	}
	if(strpos($content,"article_") !== false){
		$articleId = str_replace('article_','',$content);
		$artInfo = $GLOBALS['db']->getRow("select * from ".$GLOBALS['yp']->table('article')." where article_id='{$articleId}'");
		if($diy_type == 1){//20170328 waiting
			echo $weixin->text($artInfo['description'])->reply();exit;
		}
		$newsData[0]['Title'] = $artInfo['title'];
		$newsData[0]['Description'] = $artInfo['description'];
		$newsData[0]['PicUrl'] = (strpos($artInfo['file_url'], 'http') !== false ? $artInfo['file_url'] : $pc_url."mobile/".$artInfo['file_url']);
		$newsData[0]['Url'] = (strpos($artInfo['link'], 'http') !== false ? $artInfo['link'] : $pc_url."mobile/".$artInfo['link']);
		echo $weixin->news($newsData)->reply();exit;
	}
	echo $weixin->text("未定义菜单事件{$content}")->reply();exit;
}

//处理用户扫一扫
if ($event['event'] == "SCAN"){
	$content = intval($event['key']);//场景值ID，临时二维码时为32位非0整型，永久二维码时最大值为100000
	$res = $GLOBALS['db']->getRow("select * from " . $GLOBALS['yp']->table('weixin_qcode') . " where id='$content'");
	if($res){
		if($res['type'] == 1){
			$goodsInfo = $GLOBALS['db']->getRow("select * from ".$GLOBALS['yp']->table('goods')." where goods_id='{$res['content']}'");
			$newsData[0]['Title'] = $goodsInfo['goods_name'];
			$newsData[0]['Description'] = strip_tags($goodsInfo['goods_desc']);
			$newsData[0]['PicUrl'] = (strpos($goodsInfo['goods_thumb'], 'http') !== false ? $goodsInfo['goods_thumb'] :$pc_url.$goodsInfo['goods_thumb']);
			$newsData[0]['Url'] = $pc_url."mobile/goods.php?id=".$goodsInfo['goods_id'];
			echo $weixin->news($newsData)->reply();exit;
		}elseif($res['type'] == 2){
			$artInfo = $GLOBALS['db']->getRow("select * from ".$GLOBALS['yp']->table('article')." where article_id='{$res['content']}'");
			$newsData[0]['Title'] = $artInfo['title'];
			$newsData[0]['Description'] = strip_tags($artInfo['description']);
			$newsData[0]['PicUrl'] = (strpos($artInfo['file_url'], 'http') !== false ? $artInfo['file_url'] : $pc_url.'mobile/'.$artInfo['file_url']);
			$newsData[0]['Url'] = $pc_url."mobile/article.php?id=".$artInfo['article_id'];
			echo $weixin->news($newsData)->reply();exit;
		}elseif($res['type'] == 3){
			echo $weixin->text($res['content'])->reply();exit;
		}elseif($res['type'] == 4){
			if($api->isBindUser($wxid) === false)
			{
				$api->bind_record($wxid,$res['content']);
			}
		}elseif($res['type'] == 6)
		{
			$sql = "SELECT value FROM " . 
				 	$GLOBALS['yp']->table('supplier_shop_config') . 
					" WHERE code = 'shop_name'" . 
					" AND supplier_id = '" . $res['content'] . "'";
			 $shop_name = $GLOBALS['db']->getOne($sql);
			 $sql = "SELECT value FROM " . 
				 	$GLOBALS['yp']->table('supplier_shop_config') . 
					" WHERE code = 'shop_desc'" . 
					" AND supplier_id = '" . $res['content'] . "'";;
			$shop_desc = $GLOBALS['db']->getOne($sql);
			$sql = "SELECT value FROM " . 
				 	$GLOBALS['yp']->table('supplier_shop_config') . 
					" WHERE code = 'shop_logo'" . 
					" AND supplier_id = '" . $res['content'] . "'";;
			$shop_logo = $GLOBALS['db']->getOne($sql);
			$newsData[0]['Title'] = $shop_name;
			$newsData[0]['Description'] = $shop_desc;
			$newsData[0]['PicUrl'] = $pc_url.$shop_logo;
			$newsData[0]['Url'] = $pc_url."mobile/supplier.php?suppId=" . $res['content'];
			echo $weixin->news($newsData)->reply();exit;
		}elseif($res['type'] == 99){
			echo $weixin->text("欢迎您的回来")->reply();exit;
		}else{
			echo $weixin->text($res['content'])->reply();exit;
		}
	}

	//PC扫码
	if($api->scanLogin($content,$wxid) === true){
		echo $weixin->text("您使用扫一扫功能登陆网站成功！如网页没有跳转请点击底部按钮，谢谢！")->reply();exit;
	}
}
//处理用户的输入
if($content){
	//寒冰 判断是否为绑定内容
	$content = str_replace('+',' ',$content);
	$bindInfo = explode(' ',$content);
	$RegExp='/^[a-z0-9_]{6,12}$/';
	$username = '';
	if(preg_match($RegExp,$bindInfo[0]) && $weixinconfig['reg_type'] == 3){
		$username = $bindInfo[0];
		$bindInfo[0] .= "@163.com";
	}
						

	if(is_email($bindInfo[0]) && $api->isBindUser($wxid)===false){
		$rs = $api->bindUser($wxid,$bindInfo[0],$bindInfo[1],$username);
		if($rs === false){
			$err = $GLOBALS['err']->last_message();
			echo $weixin->text("绑定出错！原因：".$err[0])->reply();exit;
		}
		if($weixinconfig['bonustype'] > 0){
			$bonus_sn = $api->sendBonus($wxid,$weixinconfig['bonustype']);
			if($bonus_sn){
				$bonus_msg =  $bonus_sn ? "\r\n恭喜您获得红包一个。\r\n": "";
				$bonus_msg .=$bonus_sn ?"<a href='{$pc_url}mobile/user.php?act=bonus'>点击查看红包详情>></a>\r\n": "";
			}
		}
		
		echo $weixin->text($weixinconfig['bindmsg'].$bonus_msg)->reply();//发送欢迎信息
	}
	//20161225 新增密码修改 prince 1200-29121 start
	if(false !== strpos($content,'mima=')){
		 $user = $api->getUserInfo($wxid);
		 $user_id = $user['user_id'];
		 $setInfo = explode('=',$content);
	     $new_password=$setInfo[1];
		 if(empty($setInfo[1])){
			 echo $weixin->text("您输入的密码为空，请重新输入")->reply();exit;
		 }
		 $md5_password =md5($new_password);
		 $GLOBALS['db']->query("UPDATE " .$GLOBALS['yp']->table('users').
                 " SET ec_salt='0', password='" .$md5_password . "',passwd_weixin='$new_password' ".
                 " WHERE user_id='$user_id'");
		 $GLOBALS['db']->query("UPDATE " .$GLOBALS['yp']->table('supplier_admin_user').
                 " SET ec_salt='0', password='" .$md5_password . "' ".
                 " WHERE uid='$user_id'");
		 $text="您已成功修改密码\r\n新密码为：".$new_password;
		 echo $weixin->text($text)->reply();exit;
	}
	//20161225 新增密码修改 prince 1200-29121 end

	if($content == '客服'){
		echo $weixin->kefu()->reply();exit;
	}
}
$reMsg = $api->getGoodsByKey($content);
if($reMsg){
	$k = 0;
	foreach($reMsg as $v){
		$newsData[$k]['Title'] = $v['goods_name'];
		$newsData[$k]['Description'] = strip_tags($v['goods_name']);
		$newsData[$k]['PicUrl'] = (strpos($v['thumb'],'http') !== false ? $v['thumb'] : $pc_url.$v['thumb']);
		$newsData[$k]['Url'] = $pc_url.$v['url'];
		$k++;
	}
	echo $weixin->news($newsData)->reply();exit;
}

function prince_access_token() {
	$ret = $GLOBALS['db']->getRow("SELECT * FROM " .$GLOBALS['yp']->table('weixin_config')." WHERE `id` = 1");
	$appid = $ret['appid'];
	$appsecret = $ret['appsecret'];
	$access_token = $ret['access_token'];
	$dateline = $ret['expire_in'];
	$time = time();

	//if(($time - $dateline) >= 7200) {
	if(1) {
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
		$ret_json = prince_curl_get_contents($url);
		$ret = json_decode($ret_json);
		if($ret->access_token){
			$GLOBALS['db']->query("UPDATE " .$GLOBALS['yp']->table('weixin_config')." SET `access_token` = '$ret->access_token',`expire_in` = '$time' WHERE `id` =1;");
			return $ret->access_token;
		}
	}elseif(empty($access_token)) {
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
		$ret_json = prince_curl_get_contents($url);
		$ret = json_decode($ret_json);
		if($ret->access_token){
			$GLOBALS['db']->query("UPDATE " .$GLOBALS['yp']->table('weixin_config')." SET `access_token` = '$ret->access_token',`expire_in` = '$time' WHERE `id` =1;");
			return $ret->access_token;
		}
	}else {
		return $access_token;
	}
}
function prince_curl_get_contents($url) 
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_TIMEOUT, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
	curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	$r = curl_exec($ch);
	curl_close($ch);
	return $r;
}
function prince_curl_grab_page($url,$data,$proxy='',$proxystatus='',$ref_url='') {
    $header = array('Expect:');  
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
	curl_setopt($ch, CURLOPT_TIMEOUT, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if ($proxystatus == 'true') {
		curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
		curl_setopt($ch, CURLOPT_PROXY, $proxy);
	}
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_URL, $url);
	if(!empty($ref_url)){
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_REFERER, $ref_url);
	}
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);  
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	ob_start();
	return curl_exec ($ch);
	ob_end_clean();
	curl_close ($ch);
	unset($ch);

}

function prince_get_qrcode($wxid,$weixin,$user_id,$pc_root_path){


				$access_token = prince_access_token();	
				echo customText($access_token, $wxid);
				
				if(empty($user_id)){
					$text = "您还未绑定会员账号，请先绑定后进行操作";
					echo $weixin->text($text)->reply();exit;
				}
				$pc_url = $_SERVER['SERVER_NAME'] ? HTTP_TYPE."://".$_SERVER['SERVER_NAME']."/" : HTTP_TYPE."://".$_SERVER['HTTP_HOST']."/";
					
				$user_name = $GLOBALS['db']->getOne("SELECT `user_name` FROM " .$GLOBALS['yp']->table('users')." WHERE `user_id`='$user_id'");				
				$qr_path = $GLOBALS['db']->getOne("SELECT `qr_path` FROM " .$GLOBALS['yp']->table('weixin_qcode')." WHERE `user_id`='$user_id'");
				$exists_file=fopen($pc_root_path.$qr_path,'r');	
					
				if(!empty($qr_path) && $exists_file){
					$data=$pc_root_path.$qr_path;
				}else{
				    $GLOBALS['db']->query("DELETE FROM " .$GLOBALS['yp']->table('weixin_qcode')." WHERE `user_id`='$user_id'");
					$insert_sql = "INSERT INTO " .$GLOBALS['yp']->table('weixin_qcode')." (`type`,`content`,`user_name`,`user_id`) VALUES
					('99','$user_id','$user_name', '$user_id')";
					$GLOBALS['db']->query($insert_sql);
					$id = $GLOBALS['db'] -> insert_id();
					
					$action_name="QR_LIMIT_SCENE";
					$json_arr = array('action_name'=>$action_name,'action_info'=>array('scene'=>array('scene_id'=>$id)));
					$data = json_encode($json_arr);

					if(strlen($access_token) >= 64) {
						$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token;
						$res_json =prince_curl_grab_page($url, $data);
						$json = json_decode($res_json);	
					}
					$ticket = $json->ticket;
					
					if($ticket){
						$ticket_url = urlencode($ticket);
						$ticket_url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket_url;
						$imageinfo=downloadimageformweixin($ticket_url);
						if(empty($imageinfo)){
								$text = "下载二维码失败，请检查服务器环境后重试";
								echo $weixin->text($text)->reply();exit;
						}
						$qr = $GLOBALS['db']->getRow ( "SELECT * FROM " . $GLOBALS['yp']->table('weixin_qrcode_config') . " WHERE `id` = 1" );//取出海报设置
						$time = time();	
						$url=$_SERVER['HTTP_HOST'];			
						$path = $pc_root_path.'images/qrcode/'.$user_id.'_qrcode.jpg';
						$local_file=fopen($path,'a');
						$h_path=$pc_root_path.'images/qrcode/'.$user_id.'_head.jpg';
						$h_local_file=fopen($h_path,'a');
						$headimg = $GLOBALS['db']->getOne("SELECT `headimg` FROM " . $GLOBALS['yp']->table('users') . " WHERE `fake_id`='$wxid'");    
						if(strpos($headimg, 'http') === false ){
							$headimg=$pc_url.$headimg;
						}
						$h_imageinfo=downloadimageformweixin($headimg);
						if(false !==$local_file){	
							 fwrite($local_file,$imageinfo);
							 fwrite($h_local_file,$h_imageinfo);
							 fclose($local_file);
							 fclose($h_local_file);
						}else{		
								$text = "保存二维码图片的路径images/qrcode没可写权限，请修改！";
								echo $weixin->text($text)->reply();exit;
						}
					}else{
						$text = "获取ticket失败请检查appid和appsecret是否正确";
						echo $weixin->text($text)->reply();exit;
					}
					$qr = $GLOBALS['db']->getRow ( "SELECT * FROM " . $GLOBALS['yp']->table('weixin_qrcode_config') . " WHERE `id` = 1" );//取出海报设置
					$imgsrc =$path ;
					$h_imgsrc=$h_path;
					$width = 200; 
					$height = 200;
					$name=resizejpg($imgsrc,$width,$height,$user_id.'_qrcode'.'200x200',$pc_root_path); 
					$imgs = $name;				
					//处理头像
					$width = 60; 
					$height = 60;
					$h_name=resizejpg($h_imgsrc,$width,$height,$user_id.'_head'.'60x60',$pc_root_path); 
					$h_imgs = $h_name;				
					if(file_exists($pc_root_path.$qr['qr_url'])){
				  	  	$target = $pc_root_path.$qr['qr_url'];
					}else{
						$target = 'images/prince-qq-120029121.jpg';
					}

					$target_img = Imagecreatefromjpeg($target);
					$source = Imagecreatefromjpeg($imgs);
					$h_source = Imagecreatefromjpeg($h_imgs);
					imagecopy($target_img,$source,$qr['q_x'],$qr['q_y'],0,0,200,200);
					imagecopy($target_img,$h_source,$qr['h_x'],$qr['h_y'],0,0,60,60);
					$fontfile = "simsun.ttf";
					#水印文字
					$nickname = $GLOBALS['db']->getOne("SELECT `nickname` FROM " . $GLOBALS['yp']->table('users') . " WHERE `fake_id`='$wxid'");
					
					#打水印
					$textcolor = imagecolorallocate($target_img, 0, 0, 255);
					imagettftext($target_img,18,0,$qr['n_x'],$qr['n_y'],$textcolor,$fontfile,$nickname);				
					Imagejpeg($target_img,$pc_root_path.'images/qrcode/'.$user_id.'.jpg');
					$data=$pc_root_path."images/qrcode/".$user_id.".jpg";
					$s_data="images/qrcode/".$user_id.".jpg";
					$GLOBALS['db']->query("UPDATE " . $GLOBALS['yp']->table('weixin_qcode') . " SET `qr_path`='$s_data' ,`nickname`='$nickname',`qcode`='$ticket' WHERE `id` = '$id'");
					
					
				}
				
				$filedata=array("media"=>curl_file_create($data));
				//$access_token = prince_access_token();	//20161124 prince 
				if(strlen($access_token) >= 64) {
					$url = 'https://file.api.weixin.qq.com/cgi-bin/media/upload?access_token='.$access_token.'&type=image';
					$res_json =https_request($url, $filedata);
					$json = json_decode($res_json);	
				}
				
				if($json->media_id){
					$media_id=$json->media_id;
					@unlink($pc_root_path."images/qrcode/".$user_id."_qrcode.jpg");
					@unlink($pc_root_path."images/qrcode/".$user_id."_head.jpg");
					@unlink($pc_root_path."images/qrcode/".$user_id."_qrcode200x200.jpg");
					@unlink($pc_root_path."images/qrcode/".$user_id."_head60x60.jpg");
					echo $weixin->image($media_id)->reply();exit;
				}else{
					$text = "系统维护中，如有问题请联系微信 120029121";
					echo $weixin->text($text)->reply();exit;
				}
}
function downloadimageformweixin($url) {  
        $ch = curl_init ();  
        curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );  
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );  
        curl_setopt ( $ch, CURLOPT_URL, $url );  
        ob_start ();  
        curl_exec ( $ch );  
        $return_content = ob_get_contents ();  
        ob_end_clean ();  
        $return_code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );  
        return $return_content;  
}

function resizejpg($imgsrc,$imgwidth,$imgheight,$imgname,$pc_root_path) { 
		//$imgsrc jpg格式图像路径  $imgwidth要改变的宽度 $imgheight要改变的高度   $time jpg格式图像名字
		$arr = getimagesize($imgsrc); 
		header("Content-type: image/jpg"); 
		$imgWidth = $imgwidth; 
		$imgHeight = $imgheight; 
		$imgsrc = imagecreatefromjpeg($imgsrc); 
		$image = imagecreatetruecolor($imgWidth, $imgHeight);
		imagecopyresampled($image, $imgsrc, 0, 0, 0, 0,$imgWidth,$imgHeight,$arr[0], $arr[1]);
		$name=$pc_root_path."images/qrcode/".$imgname.".jpg";  
		Imagejpeg($image,$name);
		return $name;
}
function https_request($url, $data = null){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}
function customText($access_token, $openid){
    $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$access_token;
     $data = '{
						"touser":"'.$openid.'",
						"msgtype":"text",
						"text":
						{
							 "content":"正在为您生成您的专属推广二维码，如果生成失败请重试，需要重置请回复:cxsc"
						}
			}';
    $result = https_request($url, $data);
}

// 今天优品多商户系统 Added by PRINCE QQ 120029121 2016年7月18日
function kefutips($access_token, $openid){
    $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$access_token;
     $data = '{
						"touser":"'.$openid.'",
						"msgtype":"text",
						"text":
						{
							 "content":"您好，请添加客服微信：120029121"
						}
			}';
    $result = https_request($url, $data);
}

?>