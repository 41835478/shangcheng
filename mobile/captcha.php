<?php

/**
 * QQ120029121 生成验证码
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: prince $
 * $Id: captcha.php 17217 2017-04-01 06:29:08Z prince $
*/

define('IN_PRINCE', true);
define('INIT_NO_SMARTY', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/cls_captcha.php');

$img = new captcha('data/captcha/', $_CFG['captcha_width'], $_CFG['captcha_height']);

@ob_end_clean(); //清除之前出现的多余输入
if (isset($_REQUEST['is_login']))
{
    $img->session_word = 'captcha_login';
}
$img->generate_image();

?>