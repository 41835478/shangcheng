<?php define('IN_PRINCE', true);
require (dirname(__FILE__) . '/includes/init.php');
require_once (ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_goods.php');
if ($_REQUEST['act'] == 'comment') {
	$goods_id = intval($_REQUEST['goods_id']);
	$sql = "SELECT * FROM " . $yp->table('goods') . " WHERE goods_id = '$goods_id'";
	$goods = $db->getRow($sql);
	$goods_name = $goods['goods_name'];
	$smarty->assign('goods_id', $goods_id);
	$smarty->assign('goods_name', $goods_name);
	$smarty->display('comment.htm');
} elseif ($_REQUEST['act'] == 'comment_preview') {

	check_authz_json('goods_manage');
	$goods_id = intval($_REQUEST['goods_id']);
	$taourl = $_POST['taourl'];
	$keywords = $_POST['keyword'];
	$max_num = $_POST['maxNum'];
	$GetNum = $_POST['GetNum'];
	$_SESSION['TimeSplit'] = $_POST['TimeSplit'];
	$_SESSION['ghost_count'] = intval($_POST['ghost_count']);// 20161003 prince qq 120029121

	if (empty($taourl)) {
		$link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		sys_msg('淘宝商品地址不能为空', 0, $link);
		exit;
	}

	/*if (stristr($taourl, ".taobao.com")) {
		$GetGoods_ID = GetGoodsID($taourl);
		$Getselerdid_tb = Getselerdid_tb($taourl);
		$reviews_url = "http://rate.taobao.com/detail_rate.htm?userNumId=$Getselerdid_tb&auctionNumId=$GetGoods_ID&showContent=1&currentPage=1&ismore=0&siteID=7";
	} else {	

		$GetGoods_ID = GetGoodsID($taourl);
		$selerdid = Getselerdid($taourl);
		$reviews_url = "http://rate.tmall.com/list_detail_rate.htm?itemId=$GetGoods_ID&spuId=0&sellerId=$selerdid&order=1";
	}*/
	
	$GetGoods_ID = GetGoodsID($taourl);
	$id=$GetGoods_ID;
	$json = file_get_contents_curl("https://hws.alicdn.com/cache/wdetail/5.0/?id={$id}");
	$data = json_decode($json,1);
	$selerdid = $data['data']['seller']['userNumId'];

	$page = $GetNum;
	for ($i = 1;$i <= $page;$i++) {
		$pageContents = '';
		//$reviews_url = str_replace('currentPage', '', $reviews_url);
		//$reviews_url = $reviews_url . "&currentPage=$i";
		if(1){
			$reviews_url = "http://112.74.125.57/get_comment_collect.php?itemId=$GetGoods_ID&spuId=0&sellerId=$selerdid&order=1&currentPage=$i";
		}else{
			$reviews_url = "https://rate.tmall.com/list_detail_rate.htm?itemId=$GetGoods_ID&spuId=0&sellerId=$selerdid&order=1&currentPage=$i";
		}
		$pageContents = file_get_contents_curl($reviews_url);

		$pageContents = iconv('GB2312', 'UTF-8', $pageContents);
		preg_match_all('/,\"rateContent\"\:\"(.*?)\",\"/i', $pageContents, $match1);
		preg_match_all('/displayUserNick\"\:\"(.*?)\",\"/i', $pageContents, $match2);
		preg_match_all('/rateDate\"\:\"(.*?)\",\"/i', $pageContents, $match3);
		$comment_list[] = $match1[1];
		$user_list[] = $match2[1];
		$dateList[] = $match3[1];
	}
	$comment_list_temp = array();
	$user_list_temp = array();
	$dateList_temp = array();
	foreach ($comment_list as $key => $val) {
		foreach ($val as $k => $v) {
			$comment_list_temp[$user_list[$key][$k]] = $v;
			$dateList_temp[$user_list[$key][$k]] = $dateList[$key][$k];
		}
	}
	$comment_list = $comment_list_temp;
	$dateList = $dateList_temp;
	$comments = array();
	$i = 0;
	foreach ($comment_list as $key => $val) {
		if ($i >= $max_num) {
			continue;
		}
		if (!empty($keywords)) {
			if (strpos($val, $keywords) == false) {
				continue;
			}
		}
		$comments[$key]['comment_type'] = 0;
		$comments[$key]['id_value'] = $goods_id;
		$comments[$key]['email'] = '';
		$comments[$key]['user_name'] = $key;
		$comments[$key]['content'] = $val;
		$rank = mt_rand(4, 5);
		$comments[$key]['comment_rank'] = $rank;
		if (isset($dateList[$key])) {
			$time = strtotime(str_replace('.', '-', $dateList[$key]));
		} else {
			$time = gmtime();
		}
		$time = $time - 87591;
		$comments[$key]['add_time'] = $time;
		$comments[$key]['ip_address'] = real_ip();
		$comments[$key]['status'] = 1;
		$comments[$key]['parent_id'] = 0;
		$comments[$key]['user_id'] = 0;
		$comments[$key]['goods_name'] = "$goods_name";
		$comments[$key]['format_time'] = local_date($GLOBALS['_CFG']['time_format'], $time);
		$comments[$key]['id'] = $i;
		$comments[$key]['show_id'] = $i+1;// Mod by q q 1 200 29 121
		$i++;
	}
	$arrdata['result'] = 'success';
	$arrdata['data'] = $comments;
	$arrdata['count'] = count($comments);
	if ($arrdata['result'] == 'error') {
		exit($arrdata['data']);
	}
	if ($arrdata['result'] == 'success') {
		$_SESSION['comment_arrdata'] = $arrdata;
		$smarty->assign('comment_list', $arrdata['data']);
	}
	$smarty->assign('goods_id', $goods_id);
	$smarty->assign('goods_name', $goods_name);
	$smarty->assign('c_count', $arrdata['count']);
	$smarty->assign('c_goods', '<a href="../goods.php?id=' . $goods_id . '" target="_blank" >查看商品</a>');
	$smarty->assign('source_url', $arrdata['source_url']);
	$smarty->assign('c_manage', "<a href='comment_manage.php?act=list' target='_blank' >进入评论后台管理</a>");
	$smarty->display('comment_preview.htm');
} elseif ($_REQUEST['act'] == 'comment_batch_import') {
	check_authz_json('goods_manage');
	$ids = isset($_POST['checkboxes']) ? $_POST['checkboxes'] : array();
	$auto_clear = empty($_POST['auto_clear']) ? 0 : $_POST['auto_clear'];
	if ($auto_clear == 1) {
		$sql = "DELETE FROM " . $yp->table('comment') . " WHERE id_value=" . $_GET['goods_id'];
		$result = $db->query($sql);
	}
	$arrdata = $_SESSION['comment_arrdata'];
	if ($arrdata['result'] == 'error') {
		exit($arrdata['data']);
	}
	if ($arrdata['result'] == 'success') {
		$array_name = array();
		$sql = "SELECT distinct user_name FROM " . $yp->table('comment') . " WHERE id_value=" . $_GET['goods_id'];
		$names = $db->getAll($sql);
		for ($i = 0;$i < count($names);$i++) {
			$array_name[] = $names[$i]['user_name'];
		}
		foreach ($arrdata['data'] as $comment) {
			if (in_array($comment['id'], $ids)) {
				$user_name = $comment['user_name'];
				if (in_array($user_name, $array_name)) {
					continue;
				}
				date_default_timezone_set("PRC");
				$comment_type = $comment['comment_type'];
				$id_value = $comment['id_value'];
				$email = $comment['email'];
				$user_name = $_POST['user_name'][$comment['id']];
				$content = $_POST['content'][$comment['id']];
				$comment_rank = $_POST['comment_rank'][$comment['id']];
				$ttt = $_POST['add_time'][$comment['id']];
				$rr = strtotime($ttt);
				$add_time = $rr;
				$ip_address = $comment['ip_address'];
				$status = $_POST['is_show'][$comment['id']];
				$parent_id = $comment['parent_id'];
				$user_id = $comment['user_id'];
				$goods_id = $_POST['goods_id'];
				$buy_num = $_POST['buy_num'];
				$buy_time = $_POST['buy_time'];
				$password = time();
				$reg_date = strtotime($_POST['add_time'][$comment['id']]) - 30 * 24 * 3600;
				$ip = '';
				if (check_user($user_name) == true) {
					$user_name = $user_name . "_" . time();
				}
				$sql = "INSERT INTO " . $yp->table('comment') . "(comment_type, id_value, email, user_name, content, comment_rank, add_time, ip_address, status, parent_id, user_id) VALUES " . "('" . $comment_type . "', '" . $id_value . "', '$email', '$user_name', '" . $content . "', '" . $comment_rank . "', '" . $add_time . "', '" . $ip_address . "', '$status', '$parent_id', '$user_id')";
				$db->query($sql);
				$ghost_count = $_SESSION['ghost_count'];// P R I N C E   １２００２９１２１
				$sql ="update ".$yp->table('goods')." set ghost_count=ghost_count+$ghost_count where goods_id=$id_value ";
			    $db->query($sql);// P R I N C E   １２００２９１２１
				/*// Mod by q q 1 200 29 121  今 天  优 品
				$db->query('INSERT INTO ' . $GLOBALS['yp']->table("users") . "(`email`, `user_name`, `password`, `reg_time`, `last_login`, `last_ip`) VALUES ('$email', '$user_name', '$password', '$reg_date', '$reg_date', '$ip')");
				$userid = $db->getOne("SELECT user_id FROM " . $GLOBALS['yp']->table("users") . " WHERE user_name = '" . $user_name . "'");
				$order_sn = get_order_sn();
				$order_status = 5;
				$shipping_status = 2;
				$pay_status = 2;
				$addtime = strtotime($_POST['add_time'][$comment['id']]);
				$pay_time = strtotime($_POST['add_time'][$comment['id']]) - ($buy_time * 24 * 3600);
				$goods_number = rand(1, $buy_num);
				$goods_info = $db->getRow("SELECT goods_name,goods_sn,market_price,shop_price FROM " . $GLOBALS['yp']->table("goods") . " WHERE goods_id = " . $goods_id);
				$xxc = $addtime - $_SESSION['TimeSplit'];
				$db->query('INSERT INTO ' . $GLOBALS['yp']->table("order_info") . "(`order_sn`, `user_id`, `order_status`, `shipping_status`, `pay_status`, `add_time`, `pay_time`) VALUES ('$order_sn', '$userid', '$order_status', '$shipping_status', '$pay_status', '$xxc', '$pay_time')");
				$orderid = $db->getOne("SELECT order_id FROM " . $GLOBALS['yp']->table("order_info") . " WHERE order_sn = '" . $order_sn . "'");
				$db->query('INSERT INTO ' . $GLOBALS['yp']->table("order_goods") . "(`order_id`, `goods_id`, `goods_name`, `goods_sn`, `goods_number`, `market_price`, `goods_price`, `send_number`, `is_real`) VALUES ('$orderid', '$goods_id', '" . $goods_info['goods_name'] . "', '" . $goods_info['goods_sn'] . "', '$goods_number', " . $goods_info['market_price'] . ", " . $goods_info['shop_price'] . ", '$goods_number', 1)");
				*/// Mod by q q 1 200 29 121 今天优品多商户 系统
			}
		}
	}
	unset($_SESSION['comment_arrdata']);
	unset($_SESSION['ghost_count']);// P R I N C E   １２００２９１２１
	$link[] = array('href' => 'goods.php?act=list', 'text' => "进入商品列表");
	sys_msg("批量评论采集成功", 1, $link);
}
function get_order_sn() {
	mt_srand((double)microtime() * 1000000);
	return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}
function check_user($user_name) {
	$sql = "SELECT user_id FROM " . $GLOBALS['yp']->table("users") . " WHERE user_name='$user_name'";
	$row = $GLOBALS['db']->getOne($sql);
	if ($row > 0) {
		return true;
	} else {
		return false;
	}
}
function GetGoodsID($Url) {
	$b = (explode("&", $Url));
	foreach ($b as $v) {
		if (stristr($v, "id=")) {
			$str = $v . ">";
			preg_match("/id=(.*)>/", $str, $c);
			$reslt = $c[1];
			return $reslt;
			break;
		}
	}
}
function Getselerdid_tb($Url) {
	$tb_content = file_get_contents_curl($Url);
	preg_match("/sellerId:\"(.*)\",/", $tb_content, $c);
	return $c[1];
}
function Getselerdid($Url) {

	$tmall_content = file_get_contents_curl($Url);
	preg_match("/sellerId:\"(.*)\",shopId:/", $tmall_content, $c);
	return $c[1];
} 
function file_get_contents_curl($url){
	/*$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
	//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true); // 从证书中检查SSL加密算法是否存在
	curl_setopt ($ch, CURLOPT_URL, $url); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,10); 
	$dxycontent = curl_exec($ch); 
	return $dxycontent;*/
	
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
?>