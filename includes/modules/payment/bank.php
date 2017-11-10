<?php

/**
 * QQ120029121 银行汇款（转帐）插件
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: bank.php 17217 2017-04-01 06:29:08Z PRINCE $
 */

if (!defined('IN_PRINCE'))
{
    die('Hacking attempt');
}

$payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/bank.php';

if (file_exists($payment_lang))
{
    global $_LANG;

    include_once($payment_lang);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'bank_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod']  = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online']  = '0';

    /* 作者 */
    $modules[$i]['author']  = '今-天-优-品-研-发-团-队';

    /* 网址 */
    $modules[$i]['website'] = 'http://demo.coolhong.com';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.0';

    /* 配置信息 */
    $modules[$i]['config']  = array();

    return;
}

/**
 * 类
 */
class bank
{
    /**
     * 构造函数
     *
     * @access  public
     * @param
     *
     * @return void
     */

	 /* 代码修改_start  By demo.coolhong.com 今天优品多商户系统 qq 120029121 */
   

    function __construct()
    {
        $this->bank();
    }

	 function bank()
    {
    }

	/* 代码修改_end  By demo.coolhong.com 今天优品多商户系统 qq 120029121 */

    /**
     * 提交函数
     */
    function get_code()
    {
        return '';
    }

    /**
     * 处理函数
     */
    function response()
    {
        return;
    }
}

?>