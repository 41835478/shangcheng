<?php

/**
 * QQ120029121 供货商等级管理程序
 * ============================================================================
 * 演示地址: http://demo.coolhong.com；
 * ============================================================================
 * $Author: 今天优品 $
 * $Id: user_rank.php 17217 2017-04-01 06:29:08Z qq120029121 $
*/

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');

require(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/supplier.php');
$smarty->assign('lang', $_LANG);

$exc = new exchange($yp->table("supplier_rank"), $db, 'rank_id', 'rank_name');
$exc_user = new exchange($yp->table("supplier"), $db, 'user_rank', 'user_rank');

/*------------------------------------------------------ */
//-- 会员等级列表
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'list')
{
	admin_priv('supplier_rank');
    $ranks = array();
    $ranks = $db->getAll("SELECT * FROM " .$yp->table('supplier_rank')." order by sort_order ");

    $smarty->assign('ur_here',      $_LANG['supplier_rank_list']);
    $smarty->assign('action_link',  array('text' => $_LANG['add_supplier_rank'], 'href'=>'supplier_rank.php?act=add'));
    $smarty->assign('full_page',    1);

    $smarty->assign('user_ranks',   $ranks);

    assign_query_info();
    $smarty->display('supplier_rank.htm');
}

/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
	check_authz_json('supplier_rank');
    $ranks = array();
    $ranks = $db->getAll("SELECT * FROM " .$yp->table('supplier_rank')." order by sort_order ");

    $smarty->assign('user_ranks',   $ranks);
    make_json_result($smarty->fetch('supplier_rank.htm'));
}

/*------------------------------------------------------ */
//-- 添加供货商等级
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'add')
{
    admin_priv('supplier_rank');

    $rank['rank_id']      = 0;
    $rank['rank_special'] = 0;
    $rank['sort_order']   = 50;
	$rank['goods_number']   = 0;
    $rank['price']   = 0;
    $rank['available_days']   = 0;
    $rank['caiji_status']   = 0;
    $rank['fenxiao_status']   = 0;
    $rank['xcx_status']   = 0;
    $form_action          = 'insert';

    $smarty->assign('rank',        $rank);
    $smarty->assign('ur_here',     $_LANG['add_supplier_rank']);
    $smarty->assign('action_link', array('text' => $_LANG['supplier_rank_list'], 'href'=>'supplier_rank.php?act=list'));
    $smarty->assign('ur_here',     $_LANG['add_supplier_rank']);
    $smarty->assign('form_action', $form_action);

    assign_query_info();
    $smarty->display('supplier_rank_info.htm');
}

/*------------------------------------------------------ */
//-- 增加供货商等级到数据库
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'insert')
{
    admin_priv('supplier_rank');
     $_POST['goods_number'] = empty($_POST['goods_number']) ? 0 : intval($_POST['goods_number']);
    /* 检查是否存在重名的会员等级 */
    if (!$exc->is_only('rank_name', trim($_POST['rank_name'])))
    {
        sys_msg(sprintf($_LANG['rank_name_exists'], trim($_POST['rank_name'])), 1);
    }
   
    $sql = "INSERT INTO " .$yp->table('supplier_rank') ."( ".
                "rank_name,  sort_order, goods_number,price,available_days,caiji_status,fenxiao_status,xcx_status".
            ") VALUES (".
                "'$_POST[rank_name]', '" .intval($_POST['sort_order']). "',' $_POST[goods_number]',' $_POST[price]',' $_POST[available_days]',' $_POST[caiji_status]',' $_POST[fenxiao_status]',' $_POST[xcx_status]')";
    $db->query($sql);

    /* 管理员日志 */
    clear_cache_files();

    $lnk[] = array('text' => $_LANG['back_list'],    'href'=>'supplier_rank.php?act=list');
    $lnk[] = array('text' => $_LANG['add_continue'], 'href'=>'supplier_rank.php?act=add');
    sys_msg($_LANG['add_rank_success'], 0, $lnk);
}

/*------------------------------------------------------ */
//-- 删除供货商等级
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('supplier_rank');

    $rank_id = intval($_GET['id']);

    $use = $db->getOne("SELECT count(*) FROM " .$yp->table('supplier')." where rank_id='$rank_id' ");
	if($use){
        make_json_error('已有用户使用此套餐，请勿删除');
		exit;
	}


    $rankcount = $db->getOne("SELECT count(*) FROM " .$yp->table('supplier_rank')." ");
	if($rankcount<=1){
        make_json_error('请至少保留一个套餐');
		exit;
	}

    if ($exc->drop($rank_id))
    {
        /* 更新会员表的等级字段 */
        //$exc_user->edit("user_rank = 0", $rank_id);        
        clear_cache_files();
    }

    $url = 'supplier_rank.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    yp_header("Location: $url\n");
    exit;

}
/*
 *  编辑供货商等级名称
 */
elseif ($_REQUEST['act'] == 'edit_name')
{
	check_authz_json('supplier_rank');
    $id = intval($_REQUEST['id']);
    $val = empty($_REQUEST['val']) ? '' : json_str_iconv(trim($_REQUEST['val']));
   
    if ($exc->is_only('rank_name', $val, $id))
    {
        if ($exc->edit("rank_name = '$val'", $id))
        {
            /* 管理员日志 */
            clear_cache_files();
            make_json_result(stripcslashes($val));
        }
        else
        {
            make_json_error($db->error());
        }
    }
    else
    {
        make_json_error(sprintf($_LANG['rank_name_exists'], htmlspecialchars($val)));
    }
}

/*
 *  ajax添加商品数量上限
 */
elseif ($_REQUEST['act'] == 'edit_goods')
{
    check_authz_json('supplier_rank');

    $rank_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
    $val = empty($_REQUEST['val']) ? 0 : intval($_REQUEST['val']);


    if ($exc->edit("goods_number = '$val'", $rank_id))
    {
        $rank_name = $exc->get_name($rank_id);
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}

/*
 *  编辑套餐价格
 */
elseif ($_REQUEST['act'] == 'available_days')
{
    check_authz_json('supplier_rank');

    $rank_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
    $val = empty($_REQUEST['val']) ? 0 : intval($_REQUEST['val']);


    if ($exc->edit("available_days = '$val'", $rank_id))
    {
        $rank_name = $exc->get_name($rank_id);
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}
/*
 *  编辑有效天数
 */
elseif ($_REQUEST['act'] == 'price')
{
    check_authz_json('supplier_rank');

    $rank_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
    $val = empty($_REQUEST['val']) ? 0 : intval($_REQUEST['val']);


    if ($exc->edit("price = '$val'", $rank_id))
    {
        $rank_name = $exc->get_name($rank_id);
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}

/*
 *  编辑是否可以使用采集
 */
elseif ($_REQUEST['act'] == 'caiji_status')
{
    check_authz_json('supplier_rank');

    $rank_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
    $val = empty($_REQUEST['val']) ? 0 : intval($_REQUEST['val']);


    if ($exc->edit("caiji_status = '$val'", $rank_id))
    {
        $rank_name = $exc->get_name($rank_id);
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}
/*
 *  编辑是否可以使用分销
 */
elseif ($_REQUEST['act'] == 'fenxiao_status')
{
    check_authz_json('supplier_rank');

    $rank_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
    $val = empty($_REQUEST['val']) ? 0 : intval($_REQUEST['val']);


    if ($exc->edit("fenxiao_status = '$val'", $rank_id))
    {
        $rank_name = $exc->get_name($rank_id);
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}
/*
 *  编辑是否可以使用小程序
 */
elseif ($_REQUEST['act'] == 'xcx_status')
{
    check_authz_json('supplier_rank');

    $rank_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
    $val = empty($_REQUEST['val']) ? 0 : intval($_REQUEST['val']);


    if ($exc->edit("xcx_status = '$val'", $rank_id))
    {
        $rank_name = $exc->get_name($rank_id);
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}
/*
 *  修改排序
 */
elseif ($_REQUEST['act'] == 'edit_sort')
{
    check_authz_json('supplier_rank');

    $rank_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
    $val = empty($_REQUEST['val']) ? 0 : intval($_REQUEST['val']);

    if ($val < 0 || $val > 255)
    {
        make_json_error($_LANG['js_languages']['sort_order_invalid']);
    }

    if ($exc->edit("sort_order = '$val'", $rank_id))
    {
        $rank_name = $exc->get_name($rank_id);
         clear_cache_files();
         make_json_result($val);
    }
    else
    {
        make_json_error($val);
    }
}




?>