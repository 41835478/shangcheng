<?php
define('IN_PRINCE', true);
require (dirname(__FILE__) . '/includes/init.php');
if ((DEBUG_MODE & 2) != 2) {
	$smarty->caching = true;
}
$nowtime = time();

if (1) {
	assign_template();
	$goods_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0; //获取商品ID
	$uid = isset($_REQUEST['u']) ? intval($_REQUEST['u']) : 0; //获取商品ID
	if ($_SESSION['user_id'] || $uid) {
		$user_id = $uid?$uid:$_SESSION['user_id'];
	} else {
		show_message('您还没有登陆，登陆后才能使用该功能', '返回登陆', 'user.php');
		exit;
	}
	$goods_share = $GLOBALS['db']->getRow("SELECT * FROM " . $GLOBALS['yp']->table('weixin_qcode') . " WHERE `goods_id` = '$goods_id' AND `user_id` = '$user_id'");
	$goods_share_img = $goods_share['qr_path'];
	if ($goods_share_img && file_exists(PC_ROOT_PATH . $goods_share_img)) {
		$goods_share_img = $goods_share['qr_path'];
	} else {
		$user_info = $GLOBALS['db']->getRow("SELECT * FROM " . $GLOBALS['yp']->table('users') . " WHERE `user_id` = '$user_id'");
		$h_imgsrc = $user_info['headimg']; //头像
		$user_name = $user_info['nickname'] ? $user_info['nickname'] : $user_info['user_name']; //用户昵称水印
		//add by prince start
		require (dirname(__FILE__) . '/includes/phpqrcode.php');
		$data = str_replace('{id}', $goods_id, $_CFG['erweima_wapurl']);
		$data = $data . '&u=' . $_SESSION['user_id']; // 添加u
		$logo = str_replace("..", ".", $_CFG['erweima_logo']); //   暂时未用
		$errorCorrectionLevel = 'L'; //容错级别
		$matrixPointSize = 6; //生成图片大小
		QRcode::png($data, PC_ROOT_PATH . IMAGE_DIR . '/product_qrcode/' . $user_id . '_qrcode.png', $errorCorrectionLevel, $matrixPointSize, 2);
		$QR = PC_ROOT_PATH . IMAGE_DIR . '/product_qrcode/' . $user_id . '_qrcode.png'; //已经生成的原始二维码
		if ($logo !== FALSE) {
			$QR = imagecreatefromstring(file_get_contents($QR));
			$logo = imagecreatefromstring(file_get_contents($logo));
			$QR_width = imagesx($QR); //二维码图片宽度
			$QR_height = imagesy($QR); //二维码图片高度
			$logo_width = imagesx($logo); //logo图片宽度
			$logo_height = imagesy($logo); //logo图片高度
			$logo_qr_width = $QR_width / 5;
			$scale = $logo_width / $logo_qr_width;
			$logo_qr_height = $logo_height / $scale;
			$from_width = ($QR_width - $logo_qr_width) / 2;
			//重新组合图片并调整大小
			imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
		}
		$time = time(); // prince 20161013
		$imgsrc = PC_ROOT_PATH . IMAGE_DIR . '/product_qrcode/' . $user_id . '_product.jpg'; //  prince 20161013
		Imagejpeg($QR, $imgsrc); // prince 20161013
		//echo '<img src="product.png">';
		//exit;
		//add by prince end
		$width = 200;
		$height = 200;
		$q_time = $user_id . "_goodsqrcode";
		$name = resize_jpg($imgsrc, $width, $height, $q_time);
		$imgs = $name;
		//处理头像
		$width = 60;
		$height = 60;
		$hu = "u_" . $user_id . "_h";
		if (strpos($h_imgsrc, 'http') !== false) {
			$h_imageinfo = downloadimageformweixin($h_imgsrc);
		} else {
			$h_imageinfo = $h_imgsrc;
		}
		$h_imgsrc = PC_ROOT_PATH . IMAGE_DIR . '/product_qrcode/' . $hu . ".jpg";
		$local_file = fopen($h_imgsrc, 'a');
		if (false !== $local_file) {
			fwrite($local_file, $h_imageinfo);
			fclose($local_file);
		}
		$name = resize_jpg($h_imgsrc, $width, $height, $hu . '_60x60');
		$h_imgs = $name;
		//处理商品缩略图
		$weburl = $_SERVER['SERVER_NAME'] ? HTTP_TYPE."://" . $_SERVER['SERVER_NAME'] . "/" : HTTP_TYPE."://" . $_SERVER['HTTP_HOST'] . "/";

		
		$goods_info = $GLOBALS['db']->getRow("SELECT * FROM " . $GLOBALS['yp']->table('goods') . " WHERE `goods_id` = '$goods_id'"); //获取商品信息
		$g_imgsrc = strpos($goods_info['goods_thumb'], 'http') !== false ? $goods_info['goods_thumb'] : $weburl . $goods_info['goods_thumb'];
		$width = 530;
		$height = 450;
		$g_time = 'g_' . $goods_id . "_530x450";
		$name = resize_jpg($g_imgsrc, $width, $height, $g_time);
		$g_imgs = $name;
		//处理商品名背景图
		$b_imgs = "weixin/images/b_goods.jpg"; //背景图1
		// $width = 530;
		//  $height = 100;
		//  $time = time();
		//  $b_time = "b_goods";
		//  $name = resize_jpg($b_imgsrc,$width,$height,$b_time);
		//  $b_imgs = $name;
		$target = 'weixin/images/prince-wx-120029121.jpg'; // 背景图2
		$target_img = Imagecreatefromjpeg($target);
		$source = Imagecreatefromjpeg($imgs);
		$h_source = Imagecreatefromjpeg($h_imgs);
		$g_source = Imagecreatefromjpeg($g_imgs);
		$b_source = Imagecreatefromjpeg($b_imgs);
		imagecopy($target_img, $source, 35, 578, 0, 0, 200, 200);
		imagecopy($target_img, $h_source, 60, 28, 0, 0, 60, 60);
		imagecopy($target_img, $g_source, 1, 100, 0, 0, 530, 450);
		imagecopymerge($target_img, $b_source, 1, 450, 0, 0, 530, 100, 50);
		$fontfile = "weixin/simsun.ttf";
		#打昵称水印
		$textcolor = imagecolorallocate($target_img, 0, 0, 255);
		imagettftext($target_img, 18, 0, 268, 59, $textcolor, $fontfile, $user_name);
		#代言水印
		$wx_title = $GLOBALS['db']->getOne("SELECT title FROM " . $GLOBALS['yp']->table('weixin_config') . " WHERE `id` = 1"); //获取微信 uid
		$daiyan = "我为" . $wx_title . "代言";
		$textcolor = imagecolorallocate($target_img, 128, 255, 0);
		imagettftext($target_img, 16, 0, 215, 90, $textcolor, $fontfile, $daiyan);
		#商品售价水印
		$shop_price = "售价：￥" . $goods_info['shop_price'] . "元";
		$textcolor = imagecolorallocate($target_img, 255, 255, 0);
		imagettftext($target_img, 18, 0, 15, 530, $textcolor, $fontfile, $shop_price);
		//商品名称 水印
		$goods_name = mb_substr($goods_info['goods_name'], 0, 30, 'utf-8');
		$textcolor = imagecolorallocate($target_img, 255, 255, 255);
		imagettftext($target_img, 16, 0, 5, 490, $textcolor, $fontfile, $goods_name);
		Imagejpeg($target_img, PC_ROOT_PATH . IMAGE_DIR . '/product_qrcode/u' . $user_id . '_g' . $goods_id . '_goods.jpg');
		$goods_share_img = IMAGE_DIR . '/product_qrcode/u' . $user_id . "_g" . $goods_id . "_goods.jpg";
		$sql = "INSERT INTO " . $GLOBALS['yp']->table('weixin_qcode') . " (`qr_path`,`user_id`,`goods_id`,`shop_price`) VALUES
					('$goods_share_img','$user_id','$goods_id', '$goods_info[shop_price]')";
		$GLOBALS['db']->query($sql);
		@unlink(PC_ROOT_PATH . IMAGE_DIR . '/product_qrcode/' . $user_id . "_qrcode.png");
		@unlink(PC_ROOT_PATH . IMAGE_DIR . '/product_qrcode/' . $user_id . "_product.jpg");
		@unlink(PC_ROOT_PATH . IMAGE_DIR . '/product_qrcode/' . $user_id . "_goodsqrcode.jpg");
		@unlink(PC_ROOT_PATH . IMAGE_DIR . '/product_qrcode/u_' . $user_id . "_h.jpg");
		@unlink(PC_ROOT_PATH . IMAGE_DIR . '/product_qrcode/u_' . $user_id . "_h_60x60.jpg");
		@unlink(PC_ROOT_PATH . IMAGE_DIR . '/product_qrcode/g_' . $goods_id . "_530x450.jpg");
	}
	$position = assign_ur_here();
	$smarty->assign('page_title', $position['title']); // 页面标题
	$smarty->assign('ur_here', $position['ur_here']); // 当前位置
	$smarty->assign('goods_share_img', get_pc_url() . '/' . $goods_share_img);
	
}
$smarty->display('goods_share.dwt', $cache_id);
function resize_jpg($imgsrc, $imgwidth, $imgheight, $imgname) {
	//$imgsrc jpg格式图像路径  $imgwidth要改变的宽度 $imgheight要改变的高度   $imgname jpg格式图像名字
	$arr = getimagesize($imgsrc);
	//header("Content-type: image/jpg");
	$imgWidth = $imgwidth;
	$imgHeight = $imgheight;
	$imgsrc = imagecreatefromjpeg($imgsrc);
	$image = imagecreatetruecolor($imgWidth, $imgHeight);
	imagecopyresampled($image, $imgsrc, 0, 0, 0, 0, $imgWidth, $imgHeight, $arr[0], $arr[1]);
	$name = PC_ROOT_PATH . IMAGE_DIR . '/product_qrcode/' . $imgname . ".jpg";
	Imagejpeg($image, $name);
	return $name;
}
function downloadimageformweixin($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_URL, $url);
	ob_start();
	curl_exec($ch);
	$return_content = ob_get_contents();
	ob_end_clean();
	$return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	return $return_content;
}
?>