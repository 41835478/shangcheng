<?php

/**
 * QQ120029121 管理中心供货商管理
 * ============================================================================
 * 演示地址: http://demo.coolhong.com；
 * ============================================================================
 * $Author: 今天优品 $
 * $Id: suppliers.php 15013 2016-05-13 09:31:42Z 今天优品 $
 */

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/supplier.php');
$smarty->assign('lang', $_LANG);
$supplier_id = $_SESSION['supplier_id']; 
/*------------------------------------------------------ */
//-- 续费
/*------------------------------------------------------ */
if ($_REQUEST['act']== 'renew')
{
  
   
    $sql = "SELECT * FROM " . $yp->table('supplier') . " WHERE supplier_id = '$supplier_id'";//qq1取出商家数据
    $supplier = $db->getRow($sql);
   $sql = "select uid from ". $yp->table('supplier_admin_user') ." where user_id = ".$_SESSION['supplier_user_id'];
    $supplier_uid = $db->getOne($sql);

   if ($supplier_uid  != $supplier['user_id']) {//非店铺创始人
       /* 提示信息 */
    $links[] = array('href' => 'supplier_rank.php?act=list', 'text' => $_LANG['supplier_rank_list']);
    sys_msg('您不是店长，无法进行续费操作！！', 1, $links);
   }
  // 取出 套餐等级
  $sql = "select * from ". $yp->table('supplier_rank') ." where rank_id = ".$supplier['rank_id'];
  $supplier_rank = $db->getRow($sql);
  if ($supplier_rank['price'] == 0.00) {
       /* 提示信息 */
    $links[] = array('href' => 'up_rank.php?act=upgrade', 'text' => $_LANG['supplier_rank_list']);
    sys_msg($supplier_rank['rank_name'].'无法续费，请升级套餐！！', 1, $links);
   }
  //取出店铺创始人资料
  $sql = "select * from ". $yp->table('users') ." where user_id = ".$supplier['user_id'];
  $user = $db->getRow($sql);
if ($supplier_rank['price'] > $user['user_money']) {//续费套餐价格大于 用户余额   无法重装
   /* 提示信息 */
    $pc_url = $_SERVER['SERVER_NAME'] ? HTTP_TYPE."://".$_SERVER['SERVER_NAME']."/" : HTTP_TYPE."://".$_SERVER['HTTP_HOST']."/";
    $links[] = array('href' =>'supplier_rank.php?act=list', 'text' => '返回');
   sys_msg('您的余额不足，请先充值', 1, $links);

  }else{
     $price = '-'.$supplier_rank['price'];
     $change_desc = '续费小程序【'.$supplier_rank['rank_name'].'】套餐,'.$supplier_rank['available_days'].'天';
     log_account_change($supplier['user_id'], $price, 0, 0, 0, $change_desc); //扣除用户余额  记录
     $end_time = $supplier['end_time'] + $supplier_rank['available_days'] * 86400;
     $sql = "UPDATE " . $GLOBALS['yp']->table('supplier') .
                   " SET end_time='$end_time' ,system_fee='$price' WHERE supplier_id = '$supplier_id'";
        $GLOBALS['db']->query($sql);
    $end_time = date("Y-m-d H:i:s",$end_time);
    $links[] = array('href' => 'supplier_rank.php?act=list', 'text' => $_LANG['supplier_rank_list']);
    sys_msg('续费成功，使用时间延长至'.$end_time, 0, $links);
  }



}
/*------------------------------------------------------ */
//-- 升级套餐
/*------------------------------------------------------ */
elseif ($_REQUEST['act']== 'upgrade')
{
    /* 检查权限 */
    admin_priv('supplier_manage');
    

    //$sql = "SELECT * FROM " . $yp->table('supplier') . " WHERE supplier_id = '$supplier_id'";//qq1取出商家数据
   // $supplier = $db->getRow($sql);
    $sql = "SELECT s.*,sr.*".
            "FROM " . $GLOBALS['yp']->table("supplier") . " as s left join " . $GLOBALS['yp']->table("supplier_rank") . " as sr on s.rank_id = sr.rank_id
                WHERE s.supplier_id = '$supplier_id' ";
    $supplier = $db->getRow($sql);
    $sql = "select uid from ". $yp->table('supplier_admin_user') ." where user_id = ".$_SESSION['supplier_user_id'];
    $supplier_uid = $db->getOne($sql);

   if ($supplier_uid  != $supplier['user_id']) {//非店铺创始人
       /* 提示信息 */
    $links[] = array('href' => 'supplier_rank.php?act=list', 'text' => $_LANG['supplier_rank_list']);
    sys_msg('您不是店长，无法进行套餐升级操作！！', 1, $links);
   }
  // 取出 套餐等级
    $sql = "select * from ". $yp->table('supplier_rank') ." where rank_id = ".$supplier['rank_id'];
    $supplier_rank = $db->getRow($sql);//取出当前等级
    $max_rank = $db->getRow("SELECT * FROM " .$yp->table('supplier_rank')." ORDER BY price DESC limit 1");//取出最高套餐等级
  if ($supplier_rank['rank_id'] == $max_rank['ranks']) {
       /* 提示信息 */
    $links[] = array('href' => 'up_rank.php?act=upgrade', 'text' => $_LANG['supplier_rank_list']);
    sys_msg($supplier_rank['rank_name'].'套餐,已是最高套餐，无需升级', 1, $links);
   }
    
    
  
    $supplier['end_time'] = date("Y-m-d H:i:s",$supplier['end_time']);

 

    $smarty->assign('ur_here', '变更系统套餐：');

   

    $smarty->assign('form_action', 'update');
    $smarty->assign('supplier', $supplier);
    /* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 Start */
    // 商品等级
    $smarty->assign('rank_id', $supplier['rank_id']);
    $smarty->assign('supplier_rank_list', get_supplier_rank_list($supplier['price']));
    /* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 End */

    assign_query_info();

    $smarty->display('up_rank_info.htm');


}


/*------------------------------------------------------ */
//-- 提交升级
/*------------------------------------------------------ */
elseif ($_REQUEST['act']=='update')
{
   $new_rank_id = trim($_POST['rank_id']);   

      if (empty($new_rank_id)) {

        sys_msg('请选择套餐', 1); 
      }
   
      $new_supplier_info = get_supplier_rank_info($new_rank_id);//升级到的套餐数据


      $sql = "SELECT * FROM " . $yp->table('supplier') . " WHERE supplier_id = '$supplier_id'";//取出商家数据
      $supplier = $db->getRow($sql);

      $old_supplier_info = get_supplier_rank_info($supplier['rank_id']);//老套餐数据

      $time = time();//当前时间
      $sql = "select * from ". $yp->table('users') ." where user_id = ".$supplier['user_id'];
      $user = $db->getRow($sql);
      if ($supplier['end_time'] > $time && ($old_supplier_info['price'] != 0.00)) {//非体验版使用有效期大于当前时间  套餐未使用完毕
        $old_daily_price = get_daily_price($supplier['rank_id']);//计算旧套餐日价格
        $package_balance =round((($supplier['end_time'] - $time)/86400),2)*$old_daily_price;//旧套餐余额
        $pay_money = $new_supplier_info['price'] - $package_balance;

        if ($pay_money > $user['user_money']) {//升级支付套餐价格大于 用户余额   
   
       /* 提示信息 */
        $pc_url = $_SERVER['SERVER_NAME'] ? HTTP_TYPE."://".$_SERVER['SERVER_NAME']."/" : HTTP_TYPE."://".$_SERVER['HTTP_HOST']."/";
    	$links[] = array('href' =>'supplier_rank.php?act=list', 'text' => '返回');
        sys_msg('您的余额不足，请先充值', 1, $links);
        }else{
         $price = '-'.$pay_money;
         $change_desc = '升级小程序为【'.$new_supplier_info['rank_name'].'】套餐,'.$new_supplier_info['available_days'].'天';
         log_account_change($supplier['user_id'], $price, 0, 0, 0, $change_desc); //扣除用户余额  记录
         $end_time = $time  + $new_supplier_info['available_days'] * 86400;
         $sql = "UPDATE " . $GLOBALS['yp']->table('supplier') .
                   " SET end_time='$end_time',start_time='$time',rank_id='$new_rank_id',system_fee='$price' WHERE supplier_id = '$supplier_id'";
         $GLOBALS['db']->query($sql);
         $end_time = date("Y-m-d H:i:s",$end_time);
         $links[] = array('href' => 'supplier_rank.php?act=list', 'text' => $_LANG['supplier_rank_list']);
         sys_msg('升级成功，使用时间至'.$end_time, 0, $links);

        }

      
      }else{
        if ($new_supplier_info['price'] > $user['user_money']) {//升级支付套餐价格大于 用户余额   
   
       /* 提示信息 */
        $pc_url = $_SERVER['SERVER_NAME'] ? HTTP_TYPE."://".$_SERVER['SERVER_NAME']."/" : HTTP_TYPE."://".$_SERVER['HTTP_HOST']."/";
    	$links[] = array('href' =>'supplier_rank.php?act=list', 'text' => '返回');
        sys_msg('您的余额不足，请先充值', 1, $links);
        }
         $price = '-'.$new_supplier_info['price'];
         $change_desc = '升级小程序为【'.$new_supplier_info['rank_name'].'】套餐,'.$new_supplier_info['available_days'].'天';
         log_account_change($supplier['user_id'], $price, 0, 0, 0, $change_desc); //扣除用户余额  记录
         $end_time = $time  + $new_supplier_info['available_days'] * 86400;
         $sql = "UPDATE " . $GLOBALS['yp']->table('supplier') .
                   " SET end_time='$end_time',start_time='$time',rank_id='$new_rank_id' WHERE supplier_id = '$supplier_id'";
         $GLOBALS['db']->query($sql);
         $end_time = date("Y-m-d H:i:s",$end_time);
         $links[] = array('href' => 'supplier_rank.php?act=list', 'text' => $_LANG['supplier_rank_list']);
         sys_msg('升级成功，使用时间至'.$end_time, 0, $links);

     }

 }



   


//异步调用


//异步调用


elseif ($_REQUEST['act'] == 'ajax')
{
  
     include_once(ROOT_PATH . 'includes/cls_json.php');
    
      $new_rank_id = json_str_iconv(trim($_GET['rank_id']));   

      if (empty($new_rank_id)) {

         $keywords = '提示：请选择套餐！！' ; 
         $json = new JSON();
         $result = array('error' => 1, 'message' => '', 'content' => $keywords);  
  
         die($json->encode($result)); 
      }
   
      $new_supplier_info = get_supplier_rank_info($new_rank_id);//升级到的套餐数据


      $sql = "SELECT * FROM " . $yp->table('supplier') . " WHERE supplier_id = '$supplier_id'";//取出商家数据
      $supplier = $db->getRow($sql);

      $old_supplier_info = get_supplier_rank_info($supplier['rank_id']);//老套餐数据

      $time = time();//当前时间

      if ($supplier['end_time'] > $time && ($old_supplier_info['price'] != 0.00)) {//非体验版使用有效期大于当前时间  套餐未使用完毕
        $old_daily_price = get_daily_price($supplier['rank_id']);//计算旧套餐日价格
        $package_balance =round((($supplier['end_time'] - $time)/86400),2)*$old_daily_price;//旧套餐余额

       // $new_daily_price = get_daily_price($new_supplier_info['rank_id']);//计算套餐日价格

        //把老套餐折算为新套餐
       // $new_day = round(($package_balance/$new_daily_price),0);
         $pay_money = $new_supplier_info['price'] - $package_balance;

         $keywords = '提示：您原套餐余额'.$package_balance.'元，升级为【'.$new_supplier_info['rank_name'].'】套餐需花费'.$pay_money.'元。' ;
        //取出店铺创始人资料
        $sql = "select * from ". $yp->table('users') ." where user_id = ".$supplier['user_id'];
        $user = $db->getRow($sql);
        if ($pay_money > $user['user_money']) {//升级支付套餐价格大于 用户余额   
   
       $keywords .= '您的账户余额不足，请先进行充值！！';
        }

      
      }else{

         $keywords = '提示：升级为【'.$new_supplier_info['rank_name'].'】套餐需花费'.$new_supplier_info['price'].'元' ;
     if ($new_supplier_info['price'] > $user['user_money']) {//升级支付套餐价格大于 用户余额   
   
        $keywords .= '您的账户余额不足，请先进行充值！！';

        }

      }

    
      $json = new JSON();
      $result = array('error' => 0, 'message' => '', 'content' => $keywords);  
  
     die($json->encode($result));    
}


/* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 Start */
/**
 *计算套餐日价格
 * @return array 店铺等级列表 id => name
 */
function get_daily_price($rank_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['yp']->table('supplier_rank') . " WHERE rank_id = '$rank_id'";
    $res = $GLOBALS['db']->getRow($sql);
    
    $daily_price =  round(($res['price']/$res['available_days']),2);

    return $daily_price;
}
/* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 Start */
/**
 * 取得某个店铺等级数据
 * @return array 店铺等级列表 id => name
 */
function get_supplier_rank_info($rank_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['yp']->table('supplier_rank') . " WHERE rank_id = '$rank_id'";
    $res = $GLOBALS['db']->getRow($sql);

   

    return $res;
}

/* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 Start */
/**
 * 取得店铺等级列表
 * @return array 店铺等级列表 id => name
 */
function get_supplier_rank_list($price)
{
    $sql = "SELECT rank_id, rank_name FROM " . $GLOBALS['yp']->table('supplier_rank') . "WHERE price > '$price' ORDER BY sort_order";
    $res = $GLOBALS['db']->getAll($sql);

    $rank_list = array();
    foreach ($res AS $row)
    {
        $rank_list[$row['rank_id']] = addslashes($row['rank_name']);
    }

    return $rank_list;
}
/* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 End */
?>