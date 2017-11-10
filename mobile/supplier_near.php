<?php 

define('IN_PRINCE', true);

require_once(dirname(__FILE__) . '/includes/init_supplier.php');
require_once(dirname(__FILE__) . '/includes/Geohash.php');

$_REQUEST['act'] = $_REQUEST['act'] ? : 'lists';

if ($_REQUEST['act'] == 'lists') {

    if ($_REQUEST['is_ajax']) {
        $address = get_address($_REQUEST['lat'], $_REQUEST['lng'],$_REQUEST['is_baidu'],$_REQUEST['add']);//20170101
        exit($address);
    }

	// mod by prince 20161231 start
	$useragent = addslashes($_SERVER['HTTP_USER_AGENT']);
	if(strpos($useragent, 'MicroMessenger')!==false){
		require_once "wxjs/jssdk.php";
		$ret = $db->getRow("SELECT  *  FROM ".$GLOBALS['yp']->table('weixin_config')."");
		$jssdk = new JSSDK($appid=$ret['appid'], $ret['appsecret']);
		$signPackage = $jssdk->GetSignPackage();
		$smarty->assign('signPackage',  $signPackage);	
		$user_id=$_SESSION['user_id']?$_SESSION['user_id']:0;
		$user_info = $GLOBALS['db']->getRow( "SELECT * FROM " . $GLOBALS['yp']->table('users') . " WHERE `user_id` = '$user_id'" );
		if($user_info['Latitude'] && $user_info['Longitude']){
			 $translate=txmap_translate($user_info['Latitude'], $user_info['Longitude']);
			 $address = get_address($translate['lat'], $translate['lng'],0,'');
		}
	}
	// mod by prince 20161231 end
   
    $latitude = $_SESSION['latitude'];
    $longitude = $_SESSION['longitude'];
    $address = $_SESSION['location_address'];
	//unset($_SESSION['latitude']);exit;
	//echo $_SESSION['location_address'];exit;
    $smarty->assign("address", $address);

    $is_jssdk = true;
    if($latitude && $longitude) { 
        $is_jssdk = false;
    }
    $smarty->assign("is_jssdk", $is_jssdk);
	if(strpos($useragent, 'MicroMessenger')!==false){
	    $smarty->assign("is_weixin", 1);
	}else{
	    $smarty->assign("is_weixin", 0);
	}

    // 分页
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$yp->table('supplier'). " WHERE status=1 AND latitude<>'' AND longitude<>''");
    $pager  = get_pager('supplier_near.php', array(), $record_count, $page);

    // 获取所有店铺
	//20170101 mode by prince start 
	if(strpos($useragent, 'MicroMessenger')!==false){
   		$sql="SELECT supplier_id,user_id,supplier_name,wx_latitude AS latitude,wx_longitude AS longitude FROM ". $yp->table("supplier") ." WHERE status=1 AND wx_latitude<>'' AND wx_longitude<>''";
	}else{
    	$sql="SELECT supplier_id,user_id,supplier_name,latitude,longitude FROM ". $yp->table("supplier") ." WHERE status=1 AND latitude<>'' AND longitude<>''";
	}
	//20170101 mode by prince start 
    $supplier_list = $db->GetAll($sql);

    $geohash = new Geohash();

    foreach ($supplier_list as $key => $supplier) {
        // 获取距离
        $distance = $geohash->getDistance($latitude, $longitude, $supplier['latitude'], $supplier['longitude']);
        $supplier_list[$key]['distance'] = $distance;

        $unit = "m";
        if ($distance > 1000) {
            $unit = 'km';
            $distance = number_format($distance/1000, 1);
        }
        $supplier_list[$key]['distance_'] = $distance.$unit;
    }

    // 按距离排序
    usort($supplier_list, function($a, $b){
        return $a['distance'] > $b['distance'] ? 1 : -1;
    });

    $supplier_list = array_slice($supplier_list, ($page-1)*$pager['size'], $pager['size']);

    foreach ($supplier_list as $key => $supplier) {
        // 获取店铺配置
        $sql = "SELECT code,value FROM ". $yp->table("supplier_shop_config") ." WHERE supplier_id=".$supplier['supplier_id'];
            $supplier_config_list = $db->GetAll($sql);

        foreach ($supplier_config_list as $cof) {
            $code = $cof['code'];
            if ($code == 'shop_address' || $code == 'shop_name' || $code == 'shop_logo' || $code == "service_phone") {
                $supplier_list[$key][$code] = $cof['value'];
            }
            if ($code == 'shop_country' && $cof['value']){
                $supplier_list[$key]['shop_country'] = $db->getOne("SELECT region_name FROM ".$yp->table('region')." WHERE region_id=".$cof['value']);
            }
            if ($code == 'shop_province' && $cof['value']){
                $supplier_list[$key]['shop_province'] = $db->getOne("SELECT region_name FROM ".$yp->table('region')." WHERE region_id=".$cof['value']);
            }
            if ($code == 'shop_city' && $cof['value']){
                $supplier_list[$key]['shop_city'] = $db->getOne("SELECT region_name FROM ".$yp->table('region')." WHERE region_id=".$cof['value']);
            }
        }

        // 查询店铺下的商品
        $sql2 = "SELECT goods_id,goods_name,shop_price,market_price,goods_img,goods_thumb FROM ". $yp->table("goods") ." WHERE supplier_status = 1 and is_on_sale=1 and is_delete=0  AND supplier_id=". $supplier['supplier_id'] ." LIMIT 0,3";
        $goods_list = $db->GetAll($sql2);//2017.02.27  寒冰  qq  309485552   修复  商家未审核 商品显示在mobile前台 bug

        //20171028 prince  if(count($goods_list)<3){
		if(count($goods_list)<1){	
			unset($supplier_list[$key]);
		}else{
			$supplier_list[$key]['goods_list'] = $goods_list;
		}
    }
    $smarty->assign("ROOTPATH", "");
    $smarty->assign('pager', $pager);
    $smarty->assign('supplier_list', $supplier_list);
    $smarty->display("supplier_city.dwt");
}
else if ($_REQUEST['act'] == 'map') {
    $supplier_id = intval($_REQUEST['supplier_id']);
    $sql="SELECT * FROM ". $yp->table("supplier") ." WHERE supplier_id=". $supplier_id;//20161231 prince

    $supplier = $db->getRow($sql);

    if (($supplier['latitude'] && $supplier['longitude']) || ($supplier['wx_latitude'] && $supplier['wx_longitude'])){

		// mod by prince 20161231 start
		$sql = "SELECT value FROM ". $yp->table("supplier_shop_config") ." WHERE code='shop_address' and supplier_id=".$supplier['supplier_id'];
		$address = $db->getOne($sql);
        $smarty->assign('supplier_name', $supplier['supplier_name']);
        $smarty->assign('address', $address);
		$useragent = addslashes($_SERVER['HTTP_USER_AGENT']);
        if(strpos($useragent, 'MicroMessenger')!==false && $supplier['wx_latitude'] && $supplier['wx_longitude']){
			require_once "wxjs/jssdk.php";
			$ret = $db->getRow("SELECT  *  FROM ".$GLOBALS['yp']->table('weixin_config')."");
			$jssdk = new JSSDK($appid=$ret['appid'], $ret['appsecret']);
			$signPackage = $jssdk->GetSignPackage();
			$smarty->assign('signPackage',  $signPackage);	
						
			$smarty->assign('latitude', $supplier['wx_latitude']);
			$smarty->assign('longitude', $supplier['wx_longitude']);
       	 	$smarty->display("supplier_wx_map.dwt");
		}else{
			$latitude = $supplier['latitude'];
			$longitude =$supplier['longitude'];
			$supplier_name = $supplier['supplier_name'];
			$url="http://api.map.baidu.com/marker?location=$latitude,$longitude&title=$supplier_name&content=$address&output=html";
			yp_header("Location: $url\n");
			//$smarty->assign('latitude', $supplier['latitude']);
			//$smarty->assign('longitude', $supplier['longitude']);
        	//$smarty->display("supplier_map.dwt");
		}
		// mod by prince 20161231 end
    }
}
else if ($_REQUEST['act'] == 'refresh') {
		unset($_SESSION['latitude']) ;
		unset($_SESSION['longitude']) ;
		unset($_SESSION['location_address']) ;
		$user_id=$_SESSION['user_id']?$_SESSION['user_id']:0;
		$GLOBALS['db']->query( "update " . $GLOBALS['yp']->table('users') . " set Latitude='',Longitude=''  WHERE `user_id` = '$user_id'" );
		yp_header("Location: supplier_near.php\n");
}

function get_address($lat, $lng,$is_baidu='0',$add=''){
    // 逆地址解析
    
    $key = "75LBZ-H4IWQ-DHP5D-GD7SQ-HTDIQ-67BNZ"; // 腾讯地图开发密钥

    $location = $lat.",".$lng;

    if($is_baidu){
		$_SESSION['latitude'] = $lat;
		$_SESSION['longitude'] = $lng;
		$_SESSION['location_address'] = $add;
		return $add;
	}else{
		$url = "http://apis.map.qq.com/ws/geocoder/v1/?location=$location&key=$key&get_poi=1";
	}

    $res = file_get_contents($url);

    $arr = json_decode($res, true);

    $address = $arr['result']['address'];

    $_SESSION['latitude'] = $lat;
    $_SESSION['longitude'] = $lng;
    $_SESSION['location_address'] = $address;
        
    return $address;
}

?>