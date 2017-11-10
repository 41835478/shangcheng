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
require(dirname(__FILE__) . '/includes/lib_getdata.php');

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

//点赞
if($_REQUEST['act'] == 'zan')
{ 
    $article_id = $_REQUEST['article_id'];
    $user_ip = real_ip();
    $ablesql = "SELECT COUNT(*) FROM " . $GLOBALS['yp']->table('article_zan') .
                " WHERE user_ip='$user_ip' AND article_id='$article_id'";
    $able = $GLOBALS['db']->getOne($ablesql);
    if($able > 0){
        echo "<script>alert('请勿重复点赞！');window.history.back();</script>";
    }else{
        $username = empty($_SESSION['user_name']) ? '' : $_SESSION['user_name'];
    $sql = "INSERT INTO " . $GLOBALS['yp']->table('article_zan') .
        " (`article_id`,`user_ip`,`user_name`,`zan_status`) VALUES ('$article_id','$user_ip','$username','1')";
    $GLOBALS['db']->query($sql);
    $url = $_SERVER['HTTP_REFERER'];
    header('Location: ' . $url);
    //echo $url
    }
    exit;
}
//发表评论
if($_REQUEST['act'] == 'comment')
{ 
    if(! isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0)
    {
        echo "<script>alert('请先登录！');location.href='user.php';</script>";
    }else{
        $article_id = $_REQUEST['article_id'];
        $user_name = $_SESSION['user_name'];
        $content = $_REQUEST['content'];
        $add_time = time();
        $ip_address = real_ip();
        $user_id = $_SESSION['user_id'];
        $sql = "INSERT INTO " . $GLOBALS['yp']->table('comment') .
                " (`comment_type`,`id_value`,`user_name`,`content`,`add_time`,`ip_address`,`user_id`,`status`) VALUES ('1','$article_id','$user_name','$content','$add_time','$ip_address','$user_id','1')";
        $GLOBALS['db']->query($sql);
        $url = $_SERVER['HTTP_REFERER'];
        header('Location: ' . $url);
    }
        exit;
}

/* 更新点击次数 */
$db->query('UPDATE ' . $yp->table('article') . " SET click_count = click_count + 1 WHERE article_id = '$article_id'");

$cache_id = sprintf('%X', crc32($_REQUEST['id'] . '-' . $_CFG['lang']));

if (!$smarty->is_cached('article.dwt', $cache_id))
{
    /* 文章详情 */
    $article = get_article_info($article_id);
    /*文章评论*/
    $comments = get_article_comments($article_id);

    if (empty($article))
    {
        yp_header("Location: ./\n");
        exit;
    }

    if (!empty($article['link']) && $article['link'] != 'http://' && $article['link'] != 'https://')
    {
        yp_header("location:$article[link]\n");
        exit;
    }

    $smarty->assign('article_categories',   article_categories_tree($article_id)); //文章分类树
    $smarty->assign('categories',       get_categories_tree());  // 分类树
    $smarty->assign('helps',            get_shop_help()); // 网店帮助
    $smarty->assign('top_goods',        get_top10());    // 销售排行
    $smarty->assign('best_goods',       get_recommend_goods('best'));       // 推荐商品
    $smarty->assign('new_goods',        get_recommend_goods('new'));        // 最新商品
    $smarty->assign('hot_goods',        get_recommend_goods('hot'));        // 热点文章
    $smarty->assign('promotion_goods',  get_promote_goods());    // 特价商品
    $smarty->assign('related_goods',    article_related_goods($_REQUEST['id']));  // 特价商品
    $smarty->assign('id',               $article_id);
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

    /* 检查是否已赞 */
    $user_ip = real_ip();
    $sql = "SELECT COUNT(*) FROM " .$GLOBALS['yp']->table('article_zan') .
        " WHERE user_ip='$user_ip' AND article_id = '$article_id'";
    $able = $GLOBALS['db']->GetOne($sql);
    if ($able > 0)
    {
        $article['is_zan'] = 1;
    }
    else
    {
        $article['is_zan'] = 0;
    }
    //点赞数
    $sql1 = "SELECT COUNT(*) FROM " .$GLOBALS['yp']->table('article_zan') .
        " WHERE article_id = '$article_id'";
    $article['zan_num'] = $GLOBALS['db']->GetOne($sql1);

    $smarty->assign('article',      $article);
    $smarty->assign('comments',     $comments);
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

    /* 相关商品 */
    $sql = "SELECT a.goods_id, g.goods_name " .
            "FROM " . $yp->table('goods_article') . " AS a, " . $yp->table('goods') . " AS g " .
            "WHERE a.goods_id = g.goods_id " .
            "AND a.article_id = '$_REQUEST[id]' ";
    $smarty->assign('goods_list', $db->getAll($sql));

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
    $smarty->assign( 'article_link', get_article_new(array($article[cat_id]),'art_cat',5) );//获取相关文章
}

if(isset($article) && $article['cat_id'] > 2)
{
    $smarty->display('article.dwt', $cache_id);
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

//获得文章评论
function get_article_comments($article_id){
    $sql = "SELECT * FROM " . $GLOBALS['yp']->table('comment') 
        . " WHERE comment_type=1 AND parent_id=0 AND status=1 AND id_value='" . $article_id 
        . "' ORDER BY add_time";
    $comments = $GLOBALS['db']->getAll($sql);
    foreach ($comments as $key => $value) {
        $comments[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'],$comments[$key]['add_time']);
        $childsql = "SELECT * FROM " . $GLOBALS['yp']->table('comment') 
                    . " WHERE comment_type=1 AND status=1 AND parent_id='".$value['comment_id']."' AND id_value='" . $article_id 
                    . "' ORDER BY add_time"; 
        $comments[$key]['child'] = $GLOBALS['db']->getAll($childsql);
    }
    return $comments;
}

?>