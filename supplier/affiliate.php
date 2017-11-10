<?php

/**
 * QQ120029121 程序说明
 * ===========================================================
 * 演示地址: http://demo.coolhong.com；
 * ==========================================================
 * $Author: prince $
 * $Id: affiliate.php 17217 2017-04-01 06:29:08Z prince $
 */

define('IN_PRINCE', true);
require(dirname(__FILE__) . '/includes/init.php');
admin_priv('affiliate');
$config = get_affiliate();

$sql = "SELECT s.*,sr.*".
				"FROM " . $GLOBALS['yp']->table("supplier") . " as s left join " . $GLOBALS['yp']->table("supplier_rank") . " as sr on s.rank_id = sr.rank_id
					WHERE s.supplier_id = ".$_SESSION['supplier_id'];
$supp = $db->getRow($sql);
if ($supp['fenxiao_status'] == 0 ) {
	echo "<script>alert('您当前的【".$supp['rank_name']."】套餐无法使用分销功能，请升级套餐');window.location.href=\"supplier_rank.php?act=list\";</script>";
	exit;
}
	
/*------------------------------------------------------ */
//-- 分成管理页
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    assign_query_info();
    if (empty($_REQUEST['is_ajax']))
    {
        $smarty->assign('full_page', 1);
    }
//$config['on'] = 1;
        $config['config']['separate_by'] = 0;
    $smarty->assign('ur_here', $_LANG['distrib_set']);  /*微分销*/
    $smarty->assign('config', $config);
    $smarty->display('affiliate.htm');
}
elseif ($_REQUEST['act'] == 'query')
{
    $smarty->assign('ur_here', $_LANG['affiliate']);
    $smarty->assign('config', $config);
    make_json_result($smarty->fetch('affiliate.htm'), '', null);
}
/*------------------------------------------------------ */
//-- 增加下线分配方案
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add')
{
    if (count($config['item']) < 5)
    {
        //下线不能超过5层
        $_POST['level_point'] = (float)$_POST['level_point'];
        $_POST['level_money'] = (float)$_POST['level_money'];
        $maxpoint = $maxmoney = 100;
        foreach ($config['item'] as $key => $val)
        {
            $maxpoint -= $val['level_point'];
            $maxmoney -= $val['level_money'];
        }
        $_POST['level_point'] > $maxpoint && $_POST['level_point'] = $maxpoint;
        $_POST['level_money'] > $maxmoney && $_POST['level_money'] = $maxmoney;
        if (!empty($_POST['level_point']) && strpos($_POST['level_point'],'%') === false)
        {
            $_POST['level_point'] .= '%';
        }
        if (!empty($_POST['level_money']) && strpos($_POST['level_money'],'%') === false)
        {
            $_POST['level_money'] .= '%';
        }
        $items = array('level_point'=>$_POST['level_point'],'level_money'=>$_POST['level_money']);
        $links[] = array('text' => $_LANG['affiliate'], 'href' => 'affiliate.php?act=list');
        $config['item'][] = $items;
        $config['on'] = 1;
        $config['config']['separate_by'] = 0;

        put_affiliate($config);
    }
    else
    {
       make_json_error($_LANG['level_error']);
    }

    yp_header("Location: affiliate.php?act=query\n");
    exit;
}
/*------------------------------------------------------ */
//-- 修改配置
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'updata')
{

    $separate_by = (intval($_POST['separate_by']) == 1) ? 1 : 0;

    $_POST['expire'] = (float) $_POST['expire'];
    $_POST['level_point_all'] = (float)$_POST['level_point_all'];
    $_POST['level_money_all'] = (float)$_POST['level_money_all'];
	$parent_id = $_POST['parent_id'];
	$ex_fenxiao_flag=$_POST['ex_fenxiao_flag'];
	$ex_fenxiao_personal=$_POST['ex_fenxiao_personal'];//
	//$level_money_personal=$_POST['level_money_personal'];
	//$level_point_personal=$_POST['level_point_personal'];
    if(!empty($parent_id)){
		
    $supplier_id = $_SESSION['supplier_id'];
      
	$parent_info = $db->getOne('SELECT user_name FROM ' . $GLOBALS['yp']->table('users') . " WHERE upplier_id = '$supplier_id' AND user_id= '".$parent_id."'");
	
	if(empty($parent_info)){
		
		$links[] = array('text' => $_LANG['affiliate'], 'href' => 'affiliate.php?act=list');
		sys_msg("输入ID对应会员不存在", 0 ,$links);
		}
	}

    $_POST['level_money_all'] > 100 && $_POST['level_money_all'] = 100;
    $_POST['level_point_all'] > 100 && $_POST['level_point_all'] = 100;

    if (!empty($_POST['level_point_all']) && strpos($_POST['level_point_all'],'%') === false)
    {
        $_POST['level_point_all'] .= '%';
    }
    if (!empty($_POST['level_money_all']) && strpos($_POST['level_money_all'],'%') === false)
    {
        $_POST['level_money_all'] .= '%';
    }
    $_POST['level_register_all'] = intval($_POST['level_register_all']);
    $_POST['level_register_up'] = intval($_POST['level_register_up']);
    $temp = array();
    
     $sql = "select value from " . $GLOBALS['yp']->table('ypmart_shop_config',1) ." WHERE code = 'affiliate'";
     $affiliate = $GLOBALS['db']->getOne($sql);
     $affiliate = unserialize($affiliate);
     $level_money_personal = $affiliate['config']['level_money_personal'];
     $level_point_personal = $affiliate['config']['level_point_personal'];
	 $personal_lever_money = $affiliate['config']['personal_lever_money'];
     
    $temp['config'] = array('expire'                	=> $_POST['expire'],        //COOKIE过期数字
                            'expire_unit'           	=> $_POST['expire_unit'],   //单位：小时、天、周
                            'separate_by'           	=> $separate_by,            //分成模式：0、注册 1、订单
                            'level_point_all'       	=> $_POST['level_point_all'],    //积分分成比
                            'level_money_all'       	=> $_POST['level_money_all'],    //金钱分成比
                            'level_register_all'    	=> $_POST['level_register_all'], //推荐注册奖励积分
                            'level_register_up'     	=> $_POST['level_register_up'] ,  //推荐注册奖励积分上限
							'parent_id'     			=> $parent_id ,  //自定义会员默认上级  
							'ex_fenxiao_flag'       	=> $ex_fenxiao_flag, //成为分销商的  状态改变  条件   
							'ex_fenxiao_personal'   	=> $ex_fenxiao_personal, //设置购买自己有提成：0关闭，1开启
				    		'level_money_personal'      => $level_money_personal, //消费者金钱比例
				    		'level_point_personal'      => $level_point_personal, //消费者积分比例
				    		'personal_lever_money'       => $personal_lever_money //最低购买金额
							
          );
    $temp['item'] = $config['item'];
    $temp['on'] = 1;
    put_affiliate($temp);
    $links[] = array('text' => $_LANG['affiliate'], 'href' => 'affiliate.php?act=list');
    sys_msg($_LANG['edit_ok'], 0 ,$links);
}
/*------------------------------------------------------ */
//-- 推荐开关
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'on')
{

    $on = (intval($_POST['on']) == 1) ? 1 : 0;

    $config['on'] = $on;
    put_affiliate($config);
    $links[] = array('text' => $_LANG['affiliate'], 'href' => 'affiliate.php?act=list');
    sys_msg($_LANG['edit_ok'], 0 ,$links);
}
/*------------------------------------------------------ */
//-- Ajax修改设置
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_point')
{

    /* 取得参数 */
    $key = trim($_POST['id']) - 1;
    $val = (float)trim($_POST['val']);
    $maxpoint = 100;
    
    /* 关联购买自己有提成的比例，使总比例不超过100  sta*/
    $sql = "select value from " . $GLOBALS['yp']->table('supplier_shop_config',1) ." WHERE code = 'affiliate'";
    $affiliate = $GLOBALS['db']->getOne($sql);
    $affiliate = unserialize($affiliate);
    $level_point_personal = $affiliate['config']['level_point_personal'];
    $maxpoint -= $level_point_personal;
    /* 关联购买自己有提成的比例，使总比例不超过100  end*/
    
    foreach ($config['item'] as $k => $v)
    {
        if ($k != $key)
        {
            $maxpoint -= $v['level_point'];
        }
    }
    $val > $maxpoint && $val = $maxpoint;
    if (!empty($val) && strpos($val,'%') === false)
    {
        $val .= '%';
    }
    $config['item'][$key]['level_point'] = $val;
    $config['on'] = 1;
    put_affiliate($config);
    make_json_result(stripcslashes($val));
}
/*------------------------------------------------------ */
//-- Ajax修改设置
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_money')
{
    $key = trim($_POST['id']) - 1;
    $val = (float)trim($_POST['val']);
    $maxmoney = 100;
    
    /* 关联购买自己有提成的比例，使总比例不超过100  sta*/
    $sql = "select value from " . $GLOBALS['yp']->table('supplier_shop_config',1) ." WHERE code = 'affiliate'";
    $affiliate = $GLOBALS['db']->getOne($sql);
    $affiliate = unserialize($affiliate);
    $level_money_personal = $affiliate['config']['level_money_personal'];
    $maxmoney -= $level_money_personal;
    /* 关联购买自己有提成的比例，使总比例不超过100  end*/
    
    foreach ($config['item'] as $k => $v)
    {
        if ($k != $key)
        {
            $maxmoney -= $v['level_money'];
        }
    }
    $val > $maxmoney && $val = $maxmoney;
    if (!empty($val) && strpos($val,'%') === false)
    {
        $val .= '%';
    }
    $config['item'][$key]['level_money'] = $val;
    $config['on'] = 1;
    put_affiliate($config);
    make_json_result(stripcslashes($val));
}
/*------------------------------------------------------ */
//-- Ajax修改设置  新增购买自己有提成金钱比例设置
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_personal_money')
{
    $val = (float)trim($_POST['val']);
    
    $sql = "select value from " . $GLOBALS['yp']->table('supplier_shop_config',1) ." WHERE code = 'affiliate'";
    $affiliate = $GLOBALS['db']->getOne($sql);
    $affiliate = unserialize($affiliate);
    $maxmoney = 100;
    $item = $affiliate['item'];
    foreach ($item as $v)
    {
    	$maxmoney -= $v['level_money'];
    }
    if ($val > $maxmoney)
    {
    	$val = $maxmoney;
    }
    $config['config']['level_money_personal'] = $val;
    put_affiliate($config);
    
    make_json_result(stripcslashes($val));
}

/*------------------------------------------------------ */
//-- Ajax修改设置  新增购买自己有提成积分比例设置
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_personal_point')
{
	$val = (float)trim($_POST['val']);

	$sql = "select value from " . $GLOBALS['yp']->table('supplier_shop_config',1) ." WHERE code = 'affiliate'";
	$affiliate = $GLOBALS['db']->getOne($sql);
	$affiliate = unserialize($affiliate);
	$maxpoint = 100;
	$item = $affiliate['item'];
	foreach ($item as $v)
	{
		$maxpoint -= $v['level_point'];
	}
	if ($val > $maxpoint)
	{
		$val = $maxpoint;
	}
	$config['config']['level_point_personal'] = $val;
	put_affiliate($config);

	make_json_result(stripcslashes($val));
}
/*------------------------------------------------------ */
//-- Ajax修改设置  新增 设置购买自己有提成最低消费金额
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_personal_lever_money')
{
	$val = trim($_POST['val']);

	$config['config']['personal_lever_money'] = $val;
	put_affiliate($config);

	make_json_result(stripcslashes($val));
}

/*------------------------------------------------------ */
//-- 删除下线分成
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'del')
{
    $key = trim($_GET['id']) - 1;
    unset($config['item'][$key]);
    $temp = array();
    foreach ($config['item'] as $key => $val)
    {
        $temp[] = $val;
    }
    $config['item'] = $temp;
    $config['on'] = 1;
    $config['config']['separate_by'] = 0;
    put_affiliate($config);
    yp_header("Location: affiliate.php?act=list\n");
    exit;
}

function get_affiliate()
{
    $supplier_id = $_SESSION['supplier_id'];
    $sql = "SELECT value FROM " . $GLOBALS['yp']->table('supplier_shop_config') . " WHERE code = 'affiliate' AND supplier_id = '$supplier_id'";

    $config = $GLOBALS['db']->getOne($sql);
    
    $config = unserialize($config);
    empty($config) && $config = array();

    return $config;
}

function put_affiliate($config)
{
    $supplier_id = $_SESSION['supplier_id'];
    $temp = serialize($config);
    $sql = "UPDATE " . $GLOBALS['yp']->table('supplier_shop_config',1) .
           "SET  value = '$temp'" .
           "WHERE code = 'affiliate' AND supplier_id = '$supplier_id'";
    $GLOBALS['db']->query($sql);
	
    
    clear_all_files();
}
?>