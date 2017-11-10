<?php
define('IN_PRINCE', true);
require('../includes/init.php');
//类开始 今天优品
class weixinapi{
	//搜索商品
	function getGoodsByKey($key){
		$key = $this->getstr($key);
		$size = 8;//20170102
		$page = 1;
		$condi = "(goods_sn like '%{$key}%' or goods_name like '%{$key}%' or keywords like '%{$key}%' or goods_desc like '%{$key}%')";
		$condi .= " and is_delete = 0 and is_on_sale = 1 and is_alone_sale = 1";
		$res = $GLOBALS['db']->SelectLimit("select goods_id,goods_name,shop_price,promote_price,promote_start_date,promote_end_date,goods_img,goods_thumb from {$GLOBALS['yp']->table('goods')} where {$condi} {$order}", $size, ($page - 1) * $size);
		while ($row = $GLOBALS['db']->FetchRow($res)){
			$promote_price = 0;
			if ($row['promote_price'] > 0){
				$promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
			}
			$arr[$row['goods_id']]['goods_id']      = $row['goods_id'];
			$arr[$row['goods_id']]['goods_name']      = $row['goods_name'];
			$arr[$row['goods_id']]['shop_price']    = price_format($row['shop_price']);
			$arr[$row['goods_id']]['promote_price'] = ($promote_price > 0) ? price_format($promote_price) : '';
			$arr[$row['goods_id']]['goods_img']      = $row['goods_img'];
			$arr[$row['goods_id']]['thumb']      = $row['goods_thumb'];
			$arr[$row['goods_id']]['url']      = "mobile/goods.php?id=".$row['goods_id'];
		}
		return $arr;
	}
	
	//type:best,new,hot
	function getGoods($type){
		return get_recommend_goods($type);
	}
	//获取促销商品
	function getPromoteGoods(){
		return get_promote_goods();
	}
	
	//获取商品详情
	function getGoodsInfo($id){
		return get_goods_info($id);
	}
	//获取优惠活动
	function favourableInfo(){
		return favourable_info();
	}
	
	//用户相关
	function isBindUser($wxid){
		$user = $this->getFollowUserInfo($wxid);
		if($user['user_id'] > 0 && $user['is_bind']==1) return $user['user_id'];
		return false;
	}
	
	//获取用户信息
	function getFollowUserInfo($wxid){
		$sql = "select * from ".$GLOBALS['yp']->table('users')." where fake_id='{$wxid}'";
		return $GLOBALS['db']->getRow($sql);
	}
	//获取用户信息
	function getUserInfo($wxid){
		$sql = "select * from ".$GLOBALS['yp']->table('users')." where fake_id='{$wxid}'";
		return $GLOBALS['db']->getRow($sql);
	}
	
	function bind_record($wxid,$parent_id)
	{
		 $sql = "SELECT COUNT(*) FROM " . 
		 		$GLOBALS['yp']->table('bind_record') . 
				" WHERE wxid = '$wxid'";
		 $count = $GLOBALS['db']->getOne($sql);
		 if($count > 0)
		 {
			 $GLOBALS['db']->query("UPDATE " . $GLOBALS['yp']->table('bind_record') . 
			 						" SET parent_id = '$parent_id' WHERE wxid = '$wxid'"); 
		 }
		 else
		 {
			 $GLOBALS['db']->query("INSERT INTO " . 
			 					$GLOBALS['yp']->table('bind_record') . 
		 						"(`wxid`,`parent_id`) values('$wxid','$parent_id')"); 
		 }
		 
		 return true;
	}
	
	//绑定用户
	function bindUser($wxid,$email,$pwd,$username=''){

		include_once('../includes/lib_passport.php');
		$condi = $username ? "email='{$email}' or user_name='{$username}'" : "email='{$email}'";
		$user = $GLOBALS['db']->getRow("SELECT * FROM " . $GLOBALS['yp']->table('users') . " where {$condi}");
		if($user){
			if($user['is_bind']){
				$GLOBALS['err']->add("该账号已绑定其它微信号");
				return false;
			}
			
			$userObj = & init_users();
			if($user['password'] == md5($pwd) || $userObj->login($user['user_name'],$pwd)){
				$_SESSION['user_id'] = $user['user_id'];
			}else{
				$GLOBALS['err']->add("密码错误");
				return false;
			}
		}else{
				return false;
		}

		$user_id = $user['user_id'];
		$aite_id='weixin_'.$wxid;;

		
		$GLOBALS['db']->query("UPDATE ".$GLOBALS['yp']->table('users')." SET aite_id='' , fake_id='' WHERE fake_id = '$wxid'");
		$GLOBALS['db']->query("UPDATE ".$GLOBALS['yp']->table('users')." SET aite_id='$aite_id' , `passwd_weixin` =  '$pwd',`fake_id` =  '$wxid',is_bind='1' WHERE user_id = '$user_id'");

		return true;
	}
	
	
	function subscribe($wxid,$info=array()){//适用于首次关注
	    if($info){
			$nickname = $info['nickname'];
			$sex = intval($info['sex']);
			$country = $info['country'];
			$province = $info['province'];
			$city = $info['city'];
			$access_token = $info['access_token'];
			$headimg = $info['headimgurl'];
			$expire_in = time()+48*3600;
			$from_id = intval($_GET['id']) > 0 ? intval($_GET['id']) : 1 ;
		}
	
		$reg_time = time();
		$createymd = date('Y-m-d');
		$sql = "insert into ".$GLOBALS['yp']->table('users')." (`fake_id`,`user_name`,`is_bind`,`reg_time`,`createymd`,`isfollow`,`nickname`,`sex`,`country`,`province`,`city`,`access_token`,`expire_in`,`headimg`,`from_id`,`froms`) 
				value ('{$wxid}','{$wxid}',1,'{$reg_time}','{$createymd}',1,'{$nickname}','{$sex}','{$country}','{$province}','{$city}','{$access_token}','{$expire_in}','{$headimg}','{$from_id}','mobile')";
		$GLOBALS['db']->query($sql);
			
	   	$autoreg = $GLOBALS['db']->getRow("SELECT * FROM " . $GLOBALS['yp']->table('weixin_autoreg') . " WHERE `autoreg_id` = 1");
		$userpwd = $autoreg['userpwd'];//密码前缀
		$autoreg_rand = $autoreg['autoreg_rand'];//随机密码长度
		$s_mima = random_pwdkeys($autoreg_rand);
		$pwd = $userpwd.$s_mima;
		$md5password=md5($pwd);
		$user_id = $GLOBALS['db']->getOne("SELECT user_id FROM " . $GLOBALS['yp']->table('users') . " where  fake_id='$wxid' ");
		$aite_id = 'weixin_'.$wxid;
		$user_name=$autoreg['autoreg_name'].date('ym').$user_id;
		$GLOBALS['db']->query("UPDATE ".$GLOBALS['yp']->table('users')." SET `is_fenxiao`=1,`aite_id`='$aite_id' ,`password`='$md5password',`ec_salt`='', `passwd_weixin` =  '$pwd',`user_name`='$user_name',`isfollow`=1 ,`froms`='mobile',`is_bind`=1 WHERE `user_id` = '$user_id'");
	
		$register_points=$GLOBALS['db']->getOne("SELECT value FROM " . $GLOBALS['yp']->table('ypmart_shop_config') . " where  code='register_points' ");
		if ($register_points){
			log_account_change($user_id, 0, 0, $register_points, $register_points, '注册赠送');
		}
		
		return true;
	}


	//绑定上级
	function bind_distrib($wxid,$uid)
	{
		//根据微信id获取绑定的会员id
		 $sql = "SELECT user_id,parent_id FROM " . $GLOBALS['yp']->table('users') . " WHERE fake_id = '$wxid'";
		 $user_info = $GLOBALS['db']->getRow($sql);
		 if($user_info['user_id'] > 0){
			 //是否存在上级分销商
			 if($user_info['$parent_id'] == 0){
				 //如果不存在上级分销商，绑定上级分销商
				 $user_id=$user_info['user_id'] ;
				 $sql = "UPDATE " . $GLOBALS['yp']->table('users') . " SET parent_id = '$uid' WHERE user_id = '$user_id'";
				 $num = $GLOBALS['db']->query($sql);
				 if($num > 0){
					 return true; 
				 } else{
					 return false; 
				 }
			 }  
		 }
		 else{
			 return false; 
		 }
	}
	//寒冰获取上级会员的aite_id(openid)
	function getupinfo($user_id){
		$sql = "SELECT parent_id FROM " . $GLOBALS['yp']->table('users') . " WHERE user_id = '$user_id'";
		$parent_id = $GLOBALS['db']->getOne($sql);

		$info = $GLOBALS['db']->getRow("SELECT user_id,aite_id FROM ".$GLOBALS['yp']->table('users')." where user_id='$parent_id'");
		if($info['aite_id'])
		{
			$info['aite_id'] = substr($info['aite_id'], 7);
		}
		return $info;
	}

	//解除绑定
	function unBindUser($wxid){
		$user_info=$this->getUserInfo($wxid);
		$user_name=$user_info['user_name'];
		$user_id=$user_info['user_id'];
		$GLOBALS['db']->query("DELETE FROM " . $GLOBALS['yp']->table('sessions') . "  WHERE `data` like '%".$user_name."%' ");
		$GLOBALS['db']->query("DELETE FROM " . $GLOBALS['yp']->table('sessions_data') . "  WHERE `data` like '%".$user_name."%' ");
		$GLOBALS['db']->query("DELETE FROM " . $GLOBALS['yp']->table('sessions') . "  WHERE `userid` = '".$user_id."' ");

		$sql = "update ".$GLOBALS['yp']->table('users')." set is_bind='0' where fake_id='{$wxid}'";
		$GLOBALS['db']->query($sql);
		return true;
	}
	//获取订单信息
	function getOrder($wxid){
		$info = $this->getFollowUserInfo($wxid);
		if($info['user_id'] > 0){
			$uid=$info['user_id'];
			$sql = "SELECT * FROM " . $GLOBALS['yp']->table('order_info') . " where user_id={$uid} order by order_id desc limit 5";
			return (array)$GLOBALS['db']->getAll($sql);
		}
		return false;
	}
	//赠送红包
	function sendBonus($wxid,$type){
		$uid = $this->isBindUser($wxid);
		if($uid){//2017.02.18 修复重复绑定   重复赠送红包问题 qq   3094   85552
		    $row = $GLOBALS['db']->getOne("SELECT count(*) FROM " . $GLOBALS['yp']->table('user_bonus') . " where bonus_type_id={$type} and user_id={$uid}");
			if($row){
				return false;
			}else{
				$bonus_sn = $GLOBALS['db']->getOne("SELECT bonus_sn FROM " . $GLOBALS['yp']->table('user_bonus') . " where bonus_type_id={$type} and used_time=0 and emailed=0 and user_id=0 ");
				if($bonus_sn){
					$GLOBALS['db']->query("update ".$GLOBALS['yp']->table('user_bonus')." set user_id={$uid}, emailed=1 where bonus_sn='{$bonus_sn}'");
					return $bonus_sn;
				}
			}
		}
		return false;
	}
	
	//赠送积分
	//$key 基于什么互动赠送
	function sendIntegral($wxid,$num=0,$key="",$desc="微信互动赠送"){
		$uid = $this->isBindUser($wxid);
		if($uid){
			if($key){
				$sql = "SELECT * FROM ".$GLOBALS['yp']->table('weixin_keywords')." where `key`='{$key}'";
				$rs = $GLOBALS['db']->getRow($sql);
				if($rs && $rs['jf_type']>0 && $rs['jf_num']>0){
					$num = $rs['jf_num'];
					if($rs['jf_type'] == 1){
						$maxNum = $GLOBALS['db']->getOne("SELECT sum(num) FROM ".$GLOBALS['yp']->table('weixin_jflog')." where fake_id='{$wxid}' and `key_id`='{$rs['id']}'");
						if($maxNum > 0) return false;
					}
					if($rs['jf_type'] == 2){
						$ymd = date('Y-m-d');
						$maxNum = $GLOBALS['db']->getOne("SELECT sum(num) FROM ".$GLOBALS['yp']->table('weixin_jflog')." where fake_id='{$wxid}' and `key_id`='{${$rs['id']}}' and createymd='{$ymd}'");
						if($maxNum+$rs['jf_num'] > $rs['jf_maxnum']) return false;
					}
				}
			}
			if($num > 0){
				log_account_change($uid, 0, 0, 0 ,$num, $desc);
				$createtime = time();
				$createymd = date('Y-m-d');
				$GLOBALS['db']->query("insert into ".$GLOBALS['yp']->table('weixin_jflog')." (`fake_id`,`jf_type`,`key_id`,`createtime`,`createymd`,`num`) value (
					'{$wxid}','{$rs['jf_type']}','{$rs['id']}','{$createtime}','{$createymd}','{$num}')");
			}
			return true;
		}
		return false;
	}
	function updatelocation($wxid,$info){
		$Latitude = $info['Latitude'];
		$Longitude = $info['Longitude'];
		$map_precision = $info['Precision'];
		$sql = "update ".$GLOBALS['yp']->table('users')." set
			`Latitude`='{$Latitude}',`Longitude`='{$Longitude}',`map_precision`='{$map_precision}' 
		where  fake_id='{$wxid}'";
		$GLOBALS['db']->query($sql);
		return true;
	}
	//关注
	function followUser($wxid,$info=array()){
		$nickname = $info['nickname'];
		$sex = intval($info['sex']);
		$country = $info['country'];
		$province = $info['province'];
		$city = $info['city'];
		$access_token = $info['access_token'];
		$headimg = $info['headimgurl'];
		$expire_in = time()+48*3600;
		$id = $GLOBALS['db']->getOne("select user_id,headimg from ".$GLOBALS['yp']->table('users')." where fake_id='{$wxid}'");
		$from_id = intval($_GET['id']) > 0 ? intval($_GET['id']) : 1 ;
		if($id>0){
			$set = "";
			if($info){
				$set = ",`nickname`='{$nickname}',`sex`='$sex',`country`='$country',`province`='$province',
					`city`='$city',`access_token`='$access_token',`expire_in`='$expire_in',`headimg`='$headimg'";
			}
			$sql = "update ".$GLOBALS['yp']->table('users')." set isfollow=1{$set} where user_id={$id}";
			$GLOBALS['db']->query($sql);
		}else{
			$reg_time = time();
			$createymd = date('Y-m-d');
			$sql = "insert into ".$GLOBALS['yp']->table('users')." (`fake_id`,`user_name`,`is_bind`,`reg_time`,`createymd`,`isfollow`,`nickname`,`sex`,`country`,`province`,`city`,`access_token`,`expire_in`,`headimg`,`from_id`,`froms`) 
				value ('{$wxid}','{$wxid}',1,'{$reg_time}','{$createymd}',1,'{$nickname}','{$sex}','{$country}','{$province}','{$city}','{$access_token}','{$expire_in}','{$headimg}','{$from_id}','mobile')";
			$GLOBALS['db']->query($sql);
			
	   		$autoreg = $GLOBALS['db']->getRow("SELECT * FROM " . $GLOBALS['yp']->table('weixin_autoreg') . " WHERE `autoreg_id` = 1");
			$userpwd = $autoreg['userpwd'];//密码前缀
			$autoreg_rand = $autoreg['autoreg_rand'];//随机密码长度
			$s_mima = random_pwdkeys($autoreg_rand);
			$pwd = $userpwd.$s_mima;
			$md5password=md5($pwd);
			$user_id = $GLOBALS['db']->getOne("SELECT user_id FROM " . $GLOBALS['yp']->table('users') . " where  fake_id='$wxid' ");
			$aite_id = 'weixin_'.$wxid;
			$user_name=$autoreg['autoreg_name'].date('ym').$user_id;
			$GLOBALS['db']->query("UPDATE ".$GLOBALS['yp']->table('users')." SET `is_fenxiao`=1,`aite_id`='$aite_id' ,`password`='$md5password',`ec_salt`='', `passwd_weixin` =  '$pwd',`user_name`='$user_name',`isfollow`=1 ,`froms`='mobile',`is_bind`=1 WHERE `user_id` = '$user_id'");
			
			$register_points=$GLOBALS['db']->getOne("SELECT value FROM " . $GLOBALS['yp']->table('ypmart_shop_config') . " where  code='register_points' ");
			if ($register_points){
				log_account_change($user_id, 0, 0, $register_points, $register_points, '注册赠送');
			}
			
		}

		return true;
	}
	//更新token时间
	function updateTokenExpire($wxid,$token){
		$expire_in = time()+40*3600;
		$sql = "update ".$GLOBALS['yp']->table('users')." set access_token='$token',expire_in='$expire_in', where fake_id='{$wxid}'";
		$GLOBALS['db']->query($sql);
		return true;
	}
	//取消关注
	function unFollowUser($wxid){
		$_SESSION['user_id'] = '';
		$GLOBALS['db']->query("update ".$GLOBALS['yp']->table('users')." set isfollow=0,expire_in=0 where fake_id='{$wxid}'");
		return true;
	}
	//保存用户输入的数据
	function saveMsg($content,$wxid,$type){
		if($content){
			$user = $this->getFollowUserInfo($wxid);
			$uid = intval($user['id']);
			$createtime = time();
			$createymd = date('Y-m-d');
			$content = $this->getstr($content);
			$sql = "insert into ".$GLOBALS['yp']->table('weixin_msg')." (`uid`,`fake_id`,`createtime`,`createymd`,`content`,`type`) 
				value ({$uid},'{$wxid}','{$createtime}','{$createymd}','{$content}','{$type}')";
			$GLOBALS['db']->query($sql);
			return true;
		}
		return false;
	}
	
	function getstr($str){
		return htmlspecialchars($str,ENT_QUOTES);
	}
	//匹配用户输入是否为系统设置命令
	function keywordsToKey($keys,$diy_type){//prince 20170328
		$keys = $this->getstr($keys);
		$rs = $GLOBALS['db']->getRow("SELECT * FROM ".$GLOBALS['yp']->table('weixin_keywords')." where `keys` like '%{$keys}%' or `key`='{$keys}'");
		if($rs['key']){
			$GLOBALS['db']->query("update ".$GLOBALS['yp']->table('weixin_keywords')." set clicks=clicks+1 where id={$rs['id']}");
			$diy_type = $rs['diy_type'];
			if($diy_type > 0) $rs['key'] = $rs['diy_value'];
			return $rs['key'];
		}
		return false;
	}
	/**
	 * 添加推送给用户消息
	 * $user_id  系统用户ID
	 * $type text普通文本 news 图文
	 * $msg 
	 * type=text 数组结构:
	 *	array('text'=>"msg text")
	 * type=news 数组结构:
	 *  array(
	 *  	[0]=>array(
	 *  		'title'=>'msg title',
	 *  		'description'=>'summary text',
	 *  		'picurl'=>'http://www.domain.com/1.jpg',
	 *  		'url'=>'http://www.domain.com/1.html'
	 *  	),
	 *  	[1]=>....
	 *  )
	**/
	function pushToUserMsg($user_id,$type="text",$msg=array(),$sendtime=0){
		$user = $GLOBALS['db']->getRow("select * from ".$GLOBALS['yp']->table('users')." where user_id='{$user_id}'");
		if($user && $user['fake_id']){
			if($type == 'text'){
				$content = array(
					'touser'=>$user['fake_id'],
					'msgtype'=>'text',
					'text'=>array('content'=>$msg['text'])
				);
			}
			if($type == 'news'){
				$content = array(
					'touser'=>$user['fake_id'],
					'msgtype'=>'news',
					'news'=>array('articles'=>$msg)
				);
			}
			$content = serialize($content);
			$sendtime = $sendtime ? $sendtime : time();
			$createtime = time();
			$sql = "insert into ".$GLOBALS['yp']->table('weixin_corn')." (`user_id`,`content`,`createtime`,`sendtime`,`issend`) 
				value ({$user_id},'{$content}','{$createtime}','{$sendtime}','0')";
			$GLOBALS['db']->query($sql);
			return true;
		}else{
			$GLOBALS['err']->add("用户未绑定");
			return false;
		}
	}
	//创建快捷登录token
	function createTokenLoginUrl($wxid,$dir=''){
		$t = time();
		$token = md5($wxid.TOKEN.$t);
		return $dir."mobile/weixin/redirect.php?token={$token}&t={$t}&wxid={$wxid}&url=";
	}
	//扫描登陆
	function scanLogin($content,$wxid){
		$login = $GLOBALS['db']->getRow ( "SELECT * FROM " . $GLOBALS['yp']->table('weixin_login') . " WHERE `value` = '$content'" );
		if($login && $login['uid'] == 0 && $login['createtime']+600>time()){
			$info = $this->getUserInfo($wxid);
			if($info){
				$uid=$info['user_id'];
				$GLOBALS['db']->query("UPDATE " . $GLOBALS['yp']->table('weixin_login') . " SET `uid`=$uid WHERE `value` = '$content'");
				return true;
			}
		}
		return false;
	}
	//统计剩余抽奖次数
	function getAwardNum($aid , $order_id){
		$act = self::checkAward($aid);
		if(!$act) return 0;
		$uid = $_SESSION['user_id'];
		if($act['type'] == 1){
			$ymd = date('Y-m-d');
			$sql = "SELECT count(1) FROM " . $GLOBALS['yp']->table('weixin_actlog') . " WHERE `uid` = '$uid' and aid = '$aid' and createymd='$ymd'";
		}elseif($act['type'] == 2){
			$sql = "SELECT count(1) FROM " . $GLOBALS['yp']->table('weixin_actlog') . " WHERE `uid` = '$uid' and aid = '$aid'";
		}else{
		$sql = "SELECT count(1) FROM " . $GLOBALS['yp']->table('weixin_actlog') . " WHERE `uid` = '$uid' and aid = '$aid' and order_id = '$order_id'";
		}
		$useNum = $GLOBALS['db']->getOne ( $sql );
		$num = $act['num']>$useNum ? $act['num']-$useNum : 0;
		return $num;
	}
	//抽奖
	
	function doAward($aid , $order_id){
		$act = self::checkAward($aid);
		if(!$act) return array('num'=>0,'msg'=>2,'prize'=>"活动不存在！");;
		$awardNum =$this->getAwardNum($aid , $order_id);
		if($awardNum<=0){
			return array('num'=>0,'msg'=>2,'prize'=>"您的抽奖机会已经用完！");
		}
		//$awardNum = $awardNum-1;
		$time = time();
		$ymd = date('Y-m-d',$time);
		$res = $this->randAward($aid , $order_id);
		$class_name = '';$code = '';$msg = 0;
		$uid = $_SESSION['user_id'];
		$arr = array(2,3,4,6,7,8,11,12);
		$r = $arr[array_rand($arr)];
		if($res){
			$class_name = $res['awardname'];
			$code = $res['code'];
			$msg = 1;
			switch($res['title']){
				case "一等奖":
						$r = 1;
					break;
				case "二等奖":
						$r = 5;
					break;
				case "三等奖":
						$r = 9;
					break;
			}
		}
		$GLOBALS['db']->query("INSERT INTO ".$GLOBALS['yp']->table('weixin_actlog')." (uid,aid,class_name,createymd,createtime,code,issend,order_id) 
		value ($uid,$aid,'$class_name','$ymd','$time','$code',0,$order_id)");
		if($act['type'] == 3 && $order_id){//订单抽奖
		$GLOBALS['db']->query("update " . $GLOBALS['yp']->table('order_info') . " set order_draw_status=1 where order_id='$order_id'");
		
		}
		
		$class_name = $class_name ? $class_name : "非常遗憾没有中奖！";
		
		return array('num'=>$awardNum,'msg'=>$msg,'prize'=>$class_name,'prize_code'=>$code,'r'=>$r);
	}
	function randAward($aid , $order_id){
		//if(intval(rand(1,5)) != 1) return false;
		$actList = $GLOBALS['db']->getAll ( "SELECT title,lid,randnum,awardname,num FROM ".$GLOBALS['yp']->table('weixin_actlist')." where aid=$aid and isopen=1 and num>num2 order by num desc" );
		if($actList){
			foreach($actList as $v){
				if(intval(rand(1,10000)) <= $v['randnum']*100){
					$v['code'] = uniqid();
					$GLOBALS['db']->query("update " . $GLOBALS['yp']->table('weixin_actlist') . " set num2=num2+1 where lid={$v['lid']}");
					return $v;
				}
			}
		}
		return false;
	}
	private function checkAward($aid){
		$act = $GLOBALS['db']->getRow ( "SELECT * FROM " . $GLOBALS['yp']->table('weixin_act') . " where aid=$aid" );
		if($act['isopen'] == 0) return false;
		return $act;
	}
	
	//签到
	function userSign($wxid){
		$info = $this->getFollowUserInfo($wxid);
		$ymd = date('Y-m-d',time());
		if($info['user_id'] > 0){
			$conf = $GLOBALS['db']->getRow ( "SELECT * FROM " . $GLOBALS['yp']->table('weixin_signconf') . " where cid=1 and startymd<='{$ymd}' and endymd>='{$ymd}'" );
			if(!$conf){
				$GLOBALS['err']->add("没有开启签到");
				return false;
			}
			$issign = $GLOBALS['db']->getOne("SELECT wxid FROM " . $GLOBALS['yp']->table('weixin_sign') . " where wxid={$info['user_id']} and signymd='{$ymd}'");
			if($issign){
				$GLOBALS['err']->add("您今天已经签过到了");
				return false;
			}
			$ymd2 = date('Y-m-d',time()-86400);//检查昨天是否签到
			$issign = $GLOBALS['db']->getOne("SELECT sid FROM " . $GLOBALS['yp']->table('weixin_sign') . " where wxid={$info['user_id']} and signymd='{$ymd2}'");
			if($issign){
				$sign_num = $info['sign_num']+1;
			}else{
				$sign_num = 0;
			}
			$num = $conf['num']+$sign_num*$conf['addnum'];
			$num = $num > $conf['bignum'] ? $conf['bignum'] : $num;
			$nowtime = time();
			$this->sendIntegral($wxid,$num,'','签到赠送');
			$GLOBALS['db']->query("insert into " . $GLOBALS['yp']->table('weixin_sign') . " (`wxid`,`signtime`,`signymd`) value ('{$info['user_id']}','{$nowtime}','{$ymd}')");
			$GLOBALS['db']->query("update " . $GLOBALS['yp']->table('users') . " set sign_num=$sign_num where user_id='{$info['user_id']}'");
			return $num;
		}else{
			$GLOBALS['err']->add("没有绑定帐号，不能签到");
			return false;
		}
	}

	
	
	//快递查询
	function queryKuaidi($wxid='',$wap_url=''){
		$info = $this->getFollowUserInfo($wxid);
		if($info['user_id'] > 0){
			require 'kuaidi/config.php';
			$order = array();
			$add_time = time()-2592000;
			$order = $GLOBALS['db']->getAll("SELECT order_id,order_sn,invoice_no,shipping_name FROM " . $GLOBALS['yp']->table('delivery_order') . " where user_id='{$info['user_id']}' and add_time>'{$add_time}'  ");//20160927
			if(!$order){
				$GLOBALS['err']->add("没有进行正在派送的订单！");
				return false;
			}
			//return $order;
			foreach ($order as $k=>$o){
				//$url = "http://api.kuaidi100.com/api?id=$kuaidi100key&nu={$o['invoice_no']}&com=".getKDname($o['shipping_name']);
				$url = "http://www.kuaidi100.com/query?type=".getKDname($o['shipping_name'])."&postid=".$o['invoice_no'];
				$kuaidi = json_decode(file_get_contents($url),true);
				if($kuaidi['message'] == 'ok'){
					$order[$k]['kuaidi'] = $kuaidi['data'][0];
				}else{
					$url = "http://www.kuaidi100.com/applyurl?key=$kuaidi100key&nu={$o['invoice_no']}&com=".getKDname($o['shipping_name']);
					$kdurl = file_get_contents($url);
					//$order[$k]['kuaidi']['context'] = "<a href='$kdurl'>网络异常，请点击这里查看详情</a>";2016927
					$order_id=$o['order_id'];//20160927
					$order[$k]['kuaidi']['context'] = "<a href='{$wap_url}user.php?act=order_detail&order_id={$order_id}'>网络异常，请点击这里查看详情</a>\r\n";//20160927

				}
			}
			return $order;
		}else{
			$GLOBALS['err']->add("您还没有绑定帐号！");
			return false;
		}
	}
	
	// 今天优品多商户系统 获取测试资料 Added by P R I N C E Q Q 1 2 0 0 2 9 1 2 1 2016年7月22日
	function gettestinfo($db,$wxid){
		//if($wxid=='o9rONwpYVyEkqH7LD-VB792fmeT0' || $wxid=='o9rONwuSJ2sGlWMZSsoBO9rBg7c8' ){
		if($wxid=='o86iDs_1zpCrcPjFXpWIQXQnpsoc' || $wxid=='o86iDs8DmXg4VXrStycbxCjH_j24'){
			$s_mima = random_pwdkeys(4);
			$pwd = 'JTYP'.$s_mima;
			$md5_pwd = md5($pwd);
			$GLOBALS['db']->query("UPDATE " . $GLOBALS['yp']->table('admin_user') . " SET `password`='$md5_pwd' ,ec_salt=''  WHERE `user_name` = 'test' ");
			$GLOBALS['db']->query("UPDATE " . $GLOBALS['yp']->table('supplier_admin_user') . " SET `password`='$md5_pwd' ,ec_salt=''  WHERE `user_name` = 'test' ");
			$GLOBALS['db']->query("DELETE FROM " . $GLOBALS['yp']->table('sessions') . "  WHERE `data` like '%test%' ");
			$GLOBALS['db']->query("DELETE FROM " . $GLOBALS['yp']->table('sessions_data') . "  WHERE `data` like '%test%' ");
			
			$text = "演示公众号：今天优品\r\n";
			$text .= "演示小程序：今天优品Lite\r\n";
			$text .= "入驻商小程序：触手可得Lite\r\n";
			$text .= "电脑端：https://newdemo.coolhong.com/ \r\n";
			$text .= "手机端：https://newdemo.coolhong.com/mobile/ \r\n";
			$text .= "电脑端平台后台：https://newdemo.coolhong.com/admin/ \r\n";
			$text .= "电脑端商家后台：https://newdemo.coolhong.com/supplier/ \r\n";
			$text .= "手机端商家后台：https://newdemo.coolhong.com/mobile/supplier/ \r\n";
			$text .= "平台/商家后台账号：test\r\n";
			$text .= "密码均为：".$pwd."\r\n";
			$text .= "安全码：020202\r\n";
			$text .= "客服QQ1：309485552\r\n";
			$text .= "客服QQ2：120029121\r\n";
			$text .= "账号当次有效，请尽快测试，请勿同时登陆平台后台和商家后台，谢谢合作！\r\n";
			return $text;
		}else{
			return "欢迎光临！";
		}
	}
	// 今 天 优 品 多 商 户 系统 获取测试资料 Added by PRINCE QQ 120029121  2016年7月22日
	
	
}
//类结束 qq120029121 

function random_pwdkeys($length)//随机密码
	{
		$pattern='1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
		for($i=0;$i<$length;$i++)
		{
			$key .= $pattern{mt_rand(0,35)};    //生成php随机数
		}
		return $key;
	}	
	
?>