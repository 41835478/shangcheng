<?php

/**
 * QQ120029121 文章内容
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: prince $
 * $Id: article.php 17217 2017-04-01 06:29:08Z prince $
*/

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}




/*------------------------------------------------------ */
//-- INPUT
/*------------------------------------------------------ */

$_REQUEST['id'] = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$article_id     = $_REQUEST['id'];
if(isset($_REQUEST['cat_id']) && $_REQUEST['cat_id'] < 0)
{
    $article_id = $db->getOne("SELECT article_id FROM " . $yp->table('article') . " WHERE cat_id = '".intval($_REQUEST['cat_id'])."' ");
}

/*------------------------------------------------------ */
//-- PROCESSOR
/*------------------------------------------------------ */

//用户广告植入
$ad = $GLOBALS['db']->getRow ( "SELECT * FROM " . $GLOBALS['yp']->table('weixin_ad_config') . " WHERE `id` = 1" );//广告页面设置

$smarty->assign('ad',    $ad);

$user_id = $_SESSION[user_id];

$sql = "SELECT * FROM " . $GLOBALS['yp']->table('users') . " WHERE user_id = '$user_id'";

$user_info = $GLOBALS['db']->getRow($sql);//获取用户资料

$is_ad_user = is_ad($user_id);

$article_text = $db->getOne("SELECT title FROM " . $yp->table('article') . " WHERE article_id = $article_id ");

if(!empty($_POST['adid'])){
		
		if($is_ad_user == 1){
		
		  if($ad['is_deduct_money'] == 1){
		  
		   if($user_info['user_money'] >= $ad['deduct_money'] && $user_info['pay_points'] >= $ad['deduct_point'] ){
		  
            $adid=trim($_POST['adid']);//广告位id
	
	        $ifweizhi=trim($_POST['adweizhi']);//广告位置
	
	        $_REQUEST['id'] = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	
            $article_id     = $_REQUEST['id'];//文章id
	 
	        $adtime=date('Y-m-d H:i:s');//时间
	 
	      $sql = "insert into " . $GLOBALS['yp']->table('ypmart_article_ad_info') . "  values (0,'".$adid."','".$user_id."','". $article_id."','". $article_text."',0,'".$adtime."','".$ifweizhi."')";
	
	      $db -> query($sql);

	       $ad_id = $db -> insert_id();
		   
		   $info = date('Y-m-d H:i:s')."文章植入广告扣除".$ad['deduct_money'].'元,和'.$ad['deduct_point'].'积分';
		   
		   log_account_change($_SESSION['user_id'], '-'.$ad['deduct_money'], 0, 0, '-'.$ad['deduct_point'], $info);
	
	        echo "<script>alert('发布成功！！扣除".$ad['deduct_money']."元，和".$ad['deduct_point']."积分！！');window.location.href=\"article_detail.php?id=".$article_id."&de_adid={$ad_id}\";</script>";
			
			 exit;
             
			   }
			 
			 else
		
		       {
			   
		      echo "<script>alert('您的余额不足以支付本次费用，请先重充值！！');window.location.href=\"user.php?act=account_deposit\";</script>";
			  
	          exit;
			  
	          }
			  
			  }
			 
			 else
		
		       {
			   
		       $adid=trim($_POST['adid']);//广告位id
	
	
	           $ifweizhi=trim($_POST['adweizhi']);//广告位置
	
	
	          $_REQUEST['id'] = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	
              $article_id     = $_REQUEST['id'];//文章id
	 
	          $adtime=date('Y-m-d H:i:s');//时间
	 
	          $sql = "insert into " . $GLOBALS['yp']->table('ypmart_article_ad_info') . "  values (0,'".$adid."','".$user_id."','". $article_id."','". $article_text."',0,'".$adtime."','".$ifweizhi."')";
	
	          $db -> query($sql);
			  
	         $ad_id = $db -> insert_id();
	
	          echo "<script>alert('\u63d0\u4ea4\u6210\u529f\uff01');window.location.href=\"article_detail.php?id=".$article_id."&de_adid={$ad_id}\";</script>";
             
			  
	          exit;
			  
	          }  
			  
			  
		   }
		else
		
		{
		echo "<script>alert('您的等级不足，暂时不能发布植入广告！！');window.location.href=\"article_detail.php?id=".$article_id."\";</script>";
	    exit;
	}

}

/*===================打赏=====================*/

//$reward_money ='1';
$key = ($article_id+999999)*2;
$smarty->assign('key',               $key);
if($_GET['act']=='reward'){

    if($ad['zan_money']){
	    //if($_POST['reward_money'] < $ad['zan_money']){
		
		  $reward_money = $_POST['reward_money']?$_POST['reward_money']*100:$ad['zan_money']*100;
         
	  // }
	
	}else{
	
	$reward_money = $_POST['reward_money']?$_POST['reward_money']*100:100;
	
	}
	$reward_meg = $_POST['reward_meg'];
	$anonymous_reward = $_POST['anonymous_reward'];
	$timeStamp = time();
	$order_sn = $timeStamp.mt_rand(100000, 999999); 
	$wxuser =  $GLOBALS['db']->getRow("SELECT * FROM " . $GLOBALS['yp']->table('users')." where user_id='".$_SESSION["user_id"]."'");
	$openid =  $wxuser['fake_id'];
	$ecuid =  $wxuser['user_id'];
	$rewardtimeymd = date('Y-m-d H:i:s');
	$wxname = $wxuser['nickname'];
	$headimg = $wxuser['headimg'];
	$re_money = $reward_money/100;
	$title = $rewardtimeymd.'-文章打赏';
	$delsql="delete from " . $GLOBALS['yp']->table('weixin_reward_log')." where `ecuid`='$ecuid' and  `status` = 0 ";
	$GLOBALS['db'] -> query($delsql);//删除用户未支付订单
	$sql = "insert into " . $GLOBALS['yp']->table('weixin_reward_log')."  (0,'".$article_id."','".$ecuid."','".$order_sn."','".$re_money."','".$reward_meg."','".$anonymous_reward."','".$timeStamp."','".$rewardtimeymd."','".$wxname."','".$headimg."',0)";
	$GLOBALS['db'] -> query($sql);//插入打赏日志订单
	
	include_once(dirname(__FILE__) . '/wxzf/weixin_pay.php');//引入微信支付
	
	echo $jsApiParameters;
    exit;
}else{
    $reward_money ='100';
	include_once(dirname(__FILE__) . '/wxzf/weixin_pay.php');//引入微信支付
}



$cache_id = sprintf('%X', crc32($_REQUEST['id'] . '-' . $_CFG['lang']));


if (!$smarty->is_cached('article_detail.dwt'))


{
    /* 文章详情 */
    $article = get_article_info($article_id);

    if (empty($article))
    {
        yp_header("Location: ./\n");
        exit;
    }

    if (!empty($article['link']) && $article['link'] != 'http://' && $article['link'] != 'https://')
    {
        yp_header("location:$article[link]\n");
        exit;


    }//外部链接
	
/*=================获取页面广告内容======================*/

//$infoid=trim($_GET['ad_id']);



$infoid = trim($_REQUEST['de_adid'])?trim($_REQUEST['de_adid']):trim($_GET['dead_id']) ;

$add = $GLOBALS['db'] -> getOne("SELECT ad_id FROM " . $GLOBALS['yp']->table('ypmart_article_ad_info') . "  WHERE `id` = '$infoid'");

$sql = "SELECT user_id FROM " . $GLOBALS['yp']->table('ypmart_article_ad_user') . " WHERE id = '$add'";

$ad_user = $GLOBALS['db']->getOne($sql);//获取广告发布者ecid

$sql = "SELECT * FROM " . $GLOBALS['yp']->table('users') . " WHERE user_id = '$ad_user'";

$ad_info = $GLOBALS['db']->getRow($sql);//获取广告发布者用户资料


if($ad['is_ad_reward'] == 1){

   if($ad_user != $user_id){

      if($ad_info['user_money'] < $ad['ad_reward_money'] || $ad_info['pay_points'] < $ad['ad_reward_point'] ){//当页面广告发布者的余额或积分不足以支付用户查看广告奖励时下架广告

      $infoid = ' ';

      }

    }
}
if (is_numeric($infoid)){
    
    $ret = $db -> getRow("SELECT * FROM " . $GLOBALS['yp']->table('ypmart_article_ad_info') . "  WHERE `id` = '$infoid'");

	 $user_adid = $ret['ad_id'];//取得页面广告id
	 
	 $if_weizhi = $ret['ifweizhi'];//广告位置
	 
	if( $article_id == $ret['article_id']  ){
	
	$sql = "select * from " . $GLOBALS['yp']->table('ypmart_article_ad_user') . "  where id = '".$user_adid ."'";//取得广告内容

  // $query=mysql_query($sql);
	$query=mysqli_query($sql);

  // $row=mysql_fetch_array($query);
	$row=mysqli_fetch_array($query);


      }
  


	$sql="update " . $GLOBALS['yp']->table('ypmart_article_ad_info') . "  set acount=acount+1 where id= '$infoid'";

  // mysql_query($sql);//文章浏览量
	mysqli_query($sql);//文章浏览量



	
	$smarty->assign('row',    $row); 
    $smarty->assign('ret',    $ret); 
	
	
	
	
}


/*======================end==============================*/

if($_GET['act']=='payok'){
  $dokey = trim($_REQUEST['key'])?trim($_REQUEST['key']):trim($_GET['key']) ;
  $articleid = $dokey/2-999999;
  if($dokey == $key && $articleid == $article_id){
  $user_id = $_SESSION[user_id];
  $reward_id = $db -> getOne("SELECT `reward_id` FROM " . $GLOBALS['yp']->table('weixin_reward_log')."  WHERE `ecuid`='$user_id' and `article_id` = '$articleid' and `status` = 0 ");
   
   $db->query("UPDATE " . $GLOBALS['yp']->table('weixin_reward_log')."  SET `status`='1' WHERE `reward_id`= '$reward_id'");
   
    if($ad['is_reward'] == 1){
	$info = date('Y-m-d H:i:s')."文章打赏,获得".$ad['reward_rank_point'].'成长值,'.$ad['reward_point'].'消费积分';
	log_account_change($_SESSION['user_id'], 0, 0, $ad['reward_rank_point'], $ad['reward_point'], $info);
     echo "<script>alert('打赏成功！！您获得".$ad['reward_point']."消费积分，和".$ad['reward_rank_point']."成长值！！');window.location.href=\"article_detail.php?id=".$article_id."&de_adid={$infoid}\";</script>";
	 
	 }else{
	  echo "<script>alert('打赏成功！');window.location.href=\"article_detail.php?id=".$article_id."&de_adid={$infoid}\";</script>";
	 }
	 
   }else{
   echo "<script>alert('非法操作！！');window.location.href=\"article_detail.php?id=".$article_id."&de_adid={$infoid}\";</script>";
   exit;
   }
}	




/*=======================用户点击广告事件处理================================*/

$tourl = trim($_REQUEST['tourl'])?trim($_REQUEST['tourl']):trim($_GET['tourl']) ;

if($tourl){
    
	 $nowtime = time();
				
     $yestime = strtotime(date('Y-m-d', time()));
	
	if($ad['is_ad_reward'] == 1){

       if($ad_user != $user_id){//自己点击自己的不扣积分 不奖励

            if($ad_info['user_money'] <= $ad['ad_reward_money'] || $ad_info['pay_points'] >= $ad['ad_reward_point'] ){//发布者足以支付此次点击费用
			
			    $count = $db->getOne("select count(*) from ".$yp->table('weixin_ad_log')." where  type='1' and user_id=".$_SESSION['user_id']." and create_time > '$yestime'");//统计当日点击收益次数
                if($count <= $ad['earnings_times'] ){
				
                $info = date('Y-m-d H:i:s')."用户点击文章广告扣除".$ad['ad_reward_money'].'元,和'.$ad['ad_reward_point'].'积分';
		   
		        log_account_change($ad_user, '-'.$ad['ad_reward_money'], 0, 0, '-'.$ad['ad_reward_point'], $info);//先扣除广告发布者用户点击所需费用
	  
	           $info = date('Y-m-d H:i:s')."点击文章广告收益".$ad['ad_reward_money'].'元,和'.$ad['ad_reward_point'].'积分';
		   
		       log_account_change($user_id, $ad['ad_reward_money'], 0, 0, $ad['ad_reward_point'], $info);//奖励给点击用户
			   
			  $GLOBALS['db']->query("insert into ".$yp->table('weixin_ad_log')." (`user_id`,`ad_id`,`type`,`create_time`) values ('$_SESSION[user_id]','$infoid','1','$nowtime') ");//插入用户广告点击日志  
			  echo "<script>alert('点击广告收益提示：您获得".$ad['ad_reward_money']."元，和".$ad['ad_reward_point']."积分！！感谢您的支持！！');window.location.href='{$tourl}';</script>";
		     exit;
	             }else{
		  
		           $GLOBALS['db']->query("insert into ".$yp->table('weixin_ad_log')." (`user_id`,`ad_id`,`type`,`create_time`) values ('$_SESSION[user_id]','$infoid','0','$nowtime') ");//插入用户广告点击日志  
		  
	           echo "<script>alert('点击广告收益提示：您今天的收益次数已超出".$ad['earnings_times']."次，此次点击不再赠送，感谢您的支持！');window.location.href='{$tourl}';</script>";
		     exit;
	        }
	   
	           }else{
		  
		        $GLOBALS['db']->query("insert into ".$yp->table('weixin_ad_log')." (`user_id`,`ad_id`,`type`,`create_time`) values ('$_SESSION[user_id]','$infoid','0','$nowtime') ");//插入用户广告点击日志  
		  
	           echo "<script>alert('无法完成收益到账！');window.location.href=\"article_detail.php?id=".$article_id."&de_adid={$infoid}\";</script>";
		     exit;
	        }
		 
		 
		 

      }


  }
   $GLOBALS['db']->query("insert into ".$yp->table('weixin_ad_log')." (`user_id`,`ad_id`,`type`,`create_time`) values ('$_SESSION[user_id]','$infoid','0','$nowtime') ");//插入用户广告点击日志   
  header("Location: $tourl");
  
  exit;

}
	
/*=============文章详情页添加植入用户广告==================*/
$contents = array();  
$sql = "select * from " . $GLOBALS['yp']->table('ypmart_article_ad_user') . "  where user_id='".$_SESSION['user_id']."' and ad_status=1 ";//查询出所需要的条数    
$res = $GLOBALS['db']->query($sql);
while ($row = $GLOBALS['db']->fetchRow($res)){   
        $contents[] = $row;
}
	
$smarty->assign('contents',    $contents); 

    $smarty->assign('article_categories',   article_categories_tree($article_id)); //文章分类树
    $smarty->assign('categories',       get_categories_tree());  // 分类树
    $smarty->assign('helps',            get_shop_help()); // 网店帮助
    $smarty->assign('top_goods',        get_top10());    // 销售排行
    $smarty->assign('best_goods',       get_recommend_goods('best'));       // 推荐商品
    $smarty->assign('new_goods',        get_recommend_goods('new'));        // 最新商品
    $smarty->assign('hot_goods',        get_recommend_goods('hot'));        // 热点文章
    $smarty->assign('promotion_goods',  get_promote_goods());    // 特价商品
    $smarty->assign('related_goods',    article_related_goods($_REQUEST['id']));  // 特价商品
    $smarty->assign('extpintuan_goods',   index_get_extpintuan());      // 拼团商品 QQ 120029121
    
    $smarty->assign('id',               $article_id);
	$smarty->assign('ad_id',               $infoid);
	
    $smarty->assign('username',         $_SESSION['user_name']);
    $smarty->assign('email',            $_SESSION['email']);
    $smarty->assign('type',            '1');
    $smarty->assign('promotion_info', get_promotion_info());

    /* 验证码相关设置 */
    if ((intval($_CFG['captcha']) & CAPTCHA_COMMENT) && gd_version() > 0)
    {
        $smarty->assign('enabled_captcha', 1);
        $smarty->assign('rand',            mt_rand());
    }

    $smarty->assign('article',      $article);
    $smarty->assign('keywords',     htmlspecialchars($article['keywords']));
    $smarty->assign('description', htmlspecialchars($article['description']));

    $catlist = array();
    foreach(get_article_parent_cats($article['cat_id']) as $k=>$v)
    {
        $catlist[] = $v['cat_id'];
    }

    assign_template('a', $catlist);

    $position = assign_ur_here($article['cat_id'], $article['title']);
    $smarty->assign('page_title',   $position['title']);    // 页面标题
    $smarty->assign('ur_here',      $position['ur_here']);  // 当前位置
    $smarty->assign('comment_type', 1);
    $smarty->assign('jsApiParameters',    $jsApiParameters);
    /* 相关商品 */
    $sql = "SELECT a.goods_id, g.goods_name " .
            "FROM " . $yp->table('goods_article') . " AS a, " . $yp->table('goods') . " AS g " .
            "WHERE a.goods_id = g.goods_id " .
            "AND a.article_id = '$_REQUEST[id]' ";
    $smarty->assign('goods_list', $db->getAll($sql));

  /*统计文章打赏次数*/
    // $total = mysql_fetch_array(mysql_query("select count(*) from " . $GLOBALS['yp']->table('weixin_reward_log')."  where article_id = '$article_id' and `status` = 1 "));
    $total = mysqli_fetch_array(mysqli_query("select count(*) from " . $GLOBALS['yp']->table('weixin_reward_log')."   where article_id = '$article_id' and `status` = 1 "));
    $Total = $total[0];
    $smarty->assign('Total',    $Total);
	
	/*查询单次打赏金额最多*/
	 $shafa = $db -> getRow("SELECT * FROM " . $GLOBALS['yp']->table('weixin_reward_log')."  WHERE article_id = '$article_id' and `status` = 1  and `anonymous_reward` = 0 order by `reward_money` desc limit 1 ");
	$smarty->assign('shafa',    $shafa);
	$shafaid = $shafa['reward_id'];
	/*查询出其他打赏*/
	  $bandeng = array(); 
	  $sql = "select * from " . $GLOBALS['yp']->table('weixin_reward_log')."  where `article_id` = '$article_id' and `status` = 1  and `reward_id` != '$shafaid'  and `anonymous_reward` = 0 order by `reward_id` limit 0,6";//查询出所需要的条数   

          $res = $GLOBALS['db']->query($sql);
          while ($rew = $GLOBALS['db']->fetchRow($res)){   
        $bandeng[] = $rew;
           }

          $smarty->assign('bandeng',    $bandeng); 

/*浏览量*/
    $db -> query("update ".$yp->table('article')." set click_count=click_count + 1 where article_id = '$article_id'");
    $count = $db -> getOne("select click_count from  ".$yp->table('article')." where article_id = '$article_id'");  
    $smarty -> assign('count',$count);

    /* 上一篇下一篇文章 */
    $next_article = $db->getRow("SELECT article_id, title FROM " .$yp->table('article'). " WHERE article_id > $article_id AND cat_id=$article[cat_id] AND is_open=1 LIMIT 1");
    if (!empty($next_article))
    {
        $next_article['url'] = build_uri('article', array('aid'=>$next_article['article_id']), $next_article['title']);
        $smarty->assign('next_article', $next_article);
    }

    $prev_aid = $db->getOne("SELECT max(article_id) FROM " . $yp->table('article') . " WHERE article_id < $article_id AND cat_id=$article[cat_id] AND is_open=1");
    if (!empty($prev_aid))
    {
        $prev_article = $db->getRow("SELECT article_id, title FROM " .$yp->table('article'). " WHERE article_id = $prev_aid");
        $prev_article['url'] = build_uri('article', array('aid'=>$prev_article['article_id']), $prev_article['title']);
        $smarty->assign('prev_article', $prev_article);
    }

    assign_dynamic('article');
}
if(isset($article) && $article['cat_id'] > 2)
{


    $smarty->display('article_detail.dwt');


}
else
{
    $smarty->display('article_pro.dwt', $cache_id);
}

/*------------------------------------------------------ */
//-- PRIVATE FUNCTION
/*------------------------------------------------------ */

/**
 * 获得指定的文章的详细信息
 *
 * @access  private
 * @param   integer     $article_id
 * @return  array
 */
function get_article_info($article_id)
{
    /* 获得文章的信息 */
    $sql = "SELECT a.*, IFNULL(AVG(r.comment_rank), 0) AS comment_rank ".
            "FROM " .$GLOBALS['yp']->table('article'). " AS a ".
            "LEFT JOIN " .$GLOBALS['yp']->table('comment'). " AS r ON r.id_value = a.article_id AND comment_type = 1 ".
            "WHERE a.is_open = 1 AND a.article_id = '$article_id' GROUP BY a.article_id";
    $row = $GLOBALS['db']->getRow($sql);

    if ($row !== false)
    {
        $row['comment_rank'] = ceil($row['comment_rank']);                              // 用户评论级别取整
        $row['add_time']     = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']); // 修正添加时间显示

        /* 作者信息如果为空，则用网站名称替换 */
        if (empty($row['author']) || $row['author'] == '_SHOPHELP')
        {
            $row['author'] = $GLOBALS['_CFG']['shop_name'];
        }
    }

    return $row;
}

/**
 * 获得文章关联的商品
 *
 * @access  public
 * @param   integer $id
 * @return  array
 */
function article_related_goods($id)
{
    $sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb, g.goods_img, g.shop_price AS org_price, ' .
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, ".
                'g.market_price, g.promote_price, g.promote_start_date, g.promote_end_date ' .
            'FROM ' . $GLOBALS['yp']->table('goods_article') . ' ga ' .
            'LEFT JOIN ' . $GLOBALS['yp']->table('goods') . ' AS g ON g.goods_id = ga.goods_id ' .
            "LEFT JOIN " . $GLOBALS['yp']->table('member_price') . " AS mp ".
                    "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ".
            "WHERE ga.article_id = '$id' AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0";
    $res = $GLOBALS['db']->query($sql);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $arr[$row['goods_id']]['goods_id']      = $row['goods_id'];
        $arr[$row['goods_id']]['goods_name']    = $row['goods_name'];
        $arr[$row['goods_id']]['short_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
            sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
        $arr[$row['goods_id']]['goods_thumb']   = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr[$row['goods_id']]['goods_img']     = get_image_path($row['goods_id'], $row['goods_img']);
        $arr[$row['goods_id']]['market_price']  = price_format($row['market_price']);
        $arr[$row['goods_id']]['shop_price']    = price_format($row['shop_price']);
        $arr[$row['goods_id']]['url']           = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);

        if ($row['promote_price'] > 0)
        {
            $arr[$row['goods_id']]['promote_price'] = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
            $arr[$row['goods_id']]['formated_promote_price'] = price_format($arr[$row['goods_id']]['promote_price']);
        }
        else
        {
            $arr[$row['goods_id']]['promote_price'] = 0;
        }
    }

    return $arr;
}
function index_get_extpintuan()
{
    $time = gmtime();
    $limit = get_library_number('extpintuan', 'index');

    $extpintuan_list = array();
    if ($limit > 0)
    {
        $sql = 'SELECT gb.*,g.goods_id, g.goods_name, g.shop_price, g.goods_brief, g.goods_thumb, goods_img, g.shop_price ,g.market_price ' .
                'FROM ' . $GLOBALS['yp']->table('goods_activity') . ' AS gb, ' .
                    $GLOBALS['yp']->table('goods') . ' AS g ' .
                "WHERE gb.act_type = '" . GAT_EXTPINTUAN . "' " .
                "AND g.goods_id = gb.goods_id " .
                "AND gb.start_time <= '" . $time . "' " .
                "AND gb.end_time >= '" . $time . "' " .
                "AND g.is_delete = 0 " .
                "ORDER BY gb.act_id DESC " .
                "LIMIT $limit" ;
        $res = $GLOBALS['db']->query($sql);
		
		$userid=$_SESSION['user_id'];//  QQ  120029121 

        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            /* 如果缩略图为空，使用默认图片 */
            $row['goods_img'] = get_pc_url().'/'. get_image_path($row['goods_id'], $row['goods_img']);
            $row['thumb'] =  get_pc_url().'/'. get_image_path($row['goods_id'], $row['goods_thumb'], true);

            /* 根据价格阶梯，计算最低价 */
            $ext_info = unserialize($row['ext_info']);
            $price_ladder = $ext_info['price_ladder'];
            foreach ($price_ladder AS $amount_price){
                 $final_price = $amount_price['price'];
				 break;
            }
			$market_price=$ext_info['market_price']?$ext_info['market_price']:$row['market_price'];
            $row['final_price'] = price_format($final_price);
            $row['market_price'] = price_format($market_price);
            $row['url'] = 'extpintuan.php?act=view&act_id='.$row['act_id'].'&u='.$userid; // QQ  120029121 
            $row['name']   = $row['act_name']?$row['act_name']:$row['goods_name'];
            $extpintuan_list[] = $row;
        }
    }

    return $extpintuan_list;
}

//判断会员是否可以发布广告
function is_ad($user_id)
{
   
	 //判断是否可以发布广告
	$sql = "SELECT user_rank FROM " . $GLOBALS['yp']->table('weixin_ad_config') . " WHERE id = 1 ";
	$distrib_rank = $GLOBALS['db']->getOne($sql);
	
	if($distrib_rank == -1)
	{
		 //所有注册会员都是分销商
		$GLOBALS['db']->query("UPDATE " . $GLOBALS['yp']->table('users') . " SET is_ad_user = 1 WHERE is_ad_user = 0");
	}
	else
	{
		 $rank = explode(',',$distrib_rank);
		 $ex_where = '';
		 $fx_where = '';
		 for($i = 0; $i < count($rank); $i++)
		 {
			 $sql = "SELECT min_points, max_points, special_rank FROM ".$GLOBALS['yp']->table('user_rank')." WHERE rank_id = '" . $rank[$i] . "'";
             $row = $GLOBALS['db']->getRow($sql);
			 if($i != 0)
			 {
				 $ex_where .= " or ";
				 $fx_where .= " or ";
			 }
             $ex_where .= " (rank_points >= " . intval($row['min_points']) . " AND rank_points < " . intval($row['max_points']) . ")";
			 $fx_where .= " (rank_points < " . intval($row['min_points']) . " OR rank_points >= " . intval($row['max_points']) . ")";
			 if($row['special_rank'] > 0)
			 {
				 $ex_where .= " or user_rank = '" . $rank[$i] . "'";
			 }
         }
		 $GLOBALS['db']->query("UPDATE " . $GLOBALS['yp']->table('users') . " SET is_ad_user = 0 WHERE is_ad_user = 1 AND " . "(".$fx_where.")");
		 //达到条件的所有会员晋级为广告会员
		 $GLOBALS['db']->query("UPDATE " . $GLOBALS['yp']->table('users') . " SET is_ad_user = 1 WHERE is_ad_user = 0 AND " . "(".$ex_where.")");	
	}
	$sql = "SELECT is_ad_user FROM " . $GLOBALS['yp']->table('users') . " WHERE user_id = '$user_id'";
	return $GLOBALS['db']->getOne($sql);
}



?>