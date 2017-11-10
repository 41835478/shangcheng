<?php

/**
 * QQ120029121 文章及文章分类相关函数库
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: prince $
 * $Id: lib_article.php 17217 2017-04-01 06:29:08Z prince $
*/

if (!defined('IN_PRINCE'))
{
    die('Hacking attempt');
}

//取得文章里面的图片
function GetImageSrc2($body) {
   if( !isset($body) ) {
        return '';
   }
   else {
        preg_match_all ("/<(img|IMG)(.*)(src|SRC)=[\"|'|]{0,}([h|\/].*(jpg|JPG|gif|GIF|png|PNG))[\"|'|\s]{0,}/isU",$body,$out);
        return $out[4];
   }
}
/**
 * 获得文章分类下的文章列表
 *
 * @access  public
 * @param   integer     $cat_id
 * @param   integer     $page
 * @param   integer     $size
 *
 * @return  array
 */
function get_cat_articles($cat_id, $page = 1, $size = 20 ,$requirement='')
{
    //取出所有非0的文章
    if ($cat_id == '-1')
    {
        $cat_str = 'cat_id > 0';
    }
    else
    {
        $cat_str = get_article_children($cat_id);
    }
    //增加搜索条件，如果有搜索内容就进行搜索    
    if ($requirement != '')
    {
        $sql = 'SELECT article_id, title, author, add_time, file_url, open_type' .
               ' FROM ' .$GLOBALS['yp']->table('article') .
               ' WHERE is_open = 1 AND title like \'%' . $requirement . '%\' ' .
              // ' ORDER BY article_type DESC, article_id DESC';
			  ' ORDER BY rand()';//2016.06.27   寒冰   		QQ  309485552    修改文章排序为随机
    }
    else 
    {
        
        $sql = 'SELECT article_id, title, author, add_time, file_url, open_type' .
               ' FROM ' .$GLOBALS['yp']->table('article') .
               ' WHERE is_open = 1 AND ' . $cat_str .
              // ' ORDER BY article_type DESC, article_id DESC';
			  ' ORDER BY rand()';
    }

    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page-1) * $size);

    $arr = array();
    if ($res)
    {
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $article_id = $row['article_id'];

            $arr[$article_id]['id']          = $article_id;
            $arr[$article_id]['title']       = $row['title'];
            $arr[$article_id]['short_title'] = $GLOBALS['_CFG']['article_title_length'] > 0 ? sub_str($row['title'], $GLOBALS['_CFG']['article_title_length']) : $row['title'];
            $arr[$article_id]['author']      = empty($row['author']) || $row['author'] == '_SHOPHELP' ? $GLOBALS['_CFG']['shop_name'] : $row['author'];
            $arr[$article_id]['url']         = $row['open_type'] != 1 ? build_uri('article', array('aid'=>$article_id), $row['title']) : trim($row['file_url']);
            $arr[$article_id]['add_time']    = date($GLOBALS['_CFG']['date_format'], $row['add_time']);
            $imgsrc                          = GetImageSrc2($row['content']);
            $arr[$article_id]['img']         = $imgsrc;
            $arr[$article_id]['description']    = $row['description']?$row['description']:$row['title'];
        }
    }

    return $arr;
}

/**
 * 获得指定分类下的文章总数
 *
 * @param   integer     $cat_id
 *
 * @return  integer
 */
function get_article_count($cat_id ,$requirement='')
{
    global $db, $yp;
    if ($requirement != '')
    {
        $count = $db->getOne('SELECT COUNT(*) FROM ' . $yp->table('article') . ' WHERE ' . get_article_children($cat_id) . ' AND  title like \'%' . $requirement . '%\'  AND is_open = 1');
    }
    else
    {
        $count = $db->getOne("SELECT COUNT(*) FROM " . $yp->table('article') . " WHERE " . get_article_children($cat_id) . " AND is_open = 1");
    }
    return $count;
}



function getads($cat,$num)
{
    $time = gmtime();
    $sql = "SELECT * FROM " . $GLOBALS['yp']->table('ad') . " where position_id=".$cat." and start_time <= '" . $time . "' AND end_time >= '" . $time . "' ORDER BY ad_id desc limit ".$num;
    $res = $GLOBALS['db']->getAll($sql);
    $arr = array();
    foreach ($res AS $idx => $row)
    {
    $arr[$idx]['id'] = $row['ad_id'];
    $arr[$idx]['title'] = $row['ad_name'];
    $arr[$idx]['ad_link'] = $row['ad_link'];
    $arr[$idx]['ad_code'] = "/../data/afficheimg/".$row['ad_code'];
    }
    return $arr;
}
?>