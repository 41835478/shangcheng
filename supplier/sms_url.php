<?php

/**
 * QQ120029121 注册短信
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: Prince $
 * $Id: sms_url.php 16654 2009-09-09 10:29:24Z Prince $
 */

$url = '';
if(isset($GLOBALS['_CFG']['certificate_id']))
{

    if($GLOBALS['_CFG']['certificate_id']  == '')
    {
        $certi_id='error';
    }
    else
    {
        $certi_id=$GLOBALS['_CFG']['certificate_id'];
    }

    $sess_id = $GLOBALS['sess']->get_session_id();

    $auth = time();
    $ac = md5($certi_id.'SHOP_SMS'.$auth);
    $url = 'http://demo.coolhong.com/sms/index.php?certificate_id='.$certi_id.'&sess_id='.$sess_id.'&auth='.$auth.'&ac='.$ac;
}

?>