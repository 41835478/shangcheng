<?php

/**
 * QQ120029121 广告处理文件
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: affiche.php 17217 2017-04-01 06:29:08Z PRINCE $
*/

define('IN_PRINCE', true);
define('INIT_NO_SMARTY', true);
require(dirname(__FILE__) . '/includes/init.php');

/* 是否指定广告的id及跳转地址 */

// safety_20150629 change_start

if (!empty($_GET['ad_id']) && preg_match('/^-?[1-9]\d*$/', $_REQUEST['ad_id']))
{
	$ad_id = intval($_GET['ad_id']);
}
else
{
    yp_header("Location: index.php\n");
    exit;
}

// safety_20150629 change_end

/* act 操作项的初始化*/
$_GET['act'] = !empty($_GET['act']) ? trim($_GET['act']) : '';

if ($_GET['act'] == 'js')
{
    /* 编码转换 */
    if (empty($_GET['charset']))
    {
        $_GET['charset'] = 'UTF8';
    }

    header('Content-type: application/x-javascript; charset=' . ($_GET['charset'] == 'UTF8' ? 'utf-8' : $_GET['charset']));

    $url = $yp->url();
    $str = "";

    /* 取得广告的信息 */
    $sql = 'SELECT ad.ad_id, ad.ad_name, ad.ad_link, ad.ad_code '.
           'FROM ' . $yp->table('ad') . ' AS ad ' .
           'LEFT JOIN ' . $yp->table('ad_position') . ' AS p ON ad.position_id = p.position_id '.
           "WHERE ad.ad_id = '$ad_id' and " . gmtime() . " >= ad.start_time and " . gmtime() . "<= ad.end_time";

    $ad_info = $db->getRow($sql);

    if (!empty($ad_info))
    {
        /* 转换编码 */
        if ($_GET['charset'] != 'UTF8')
        {
            $ad_info['ad_name'] = yp_iconv('UTF8', $_GET['charset'], $ad_info['ad_name']);
            $ad_info['ad_code'] = yp_iconv('UTF8', $_GET['charset'], $ad_info['ad_code']);
        }

        /* 初始化广告的类型和来源 */
        $_GET['type'] = !empty($_GET['type']) ? intval($_GET['type'])    : 0;
        $_GET['from'] = !empty($_GET['from']) ? urlencode($_GET['from']) : '';

        $str = '';
        switch ($_GET['type'])
        {
            case '0':
                /* 图片广告 */
                $src = (strpos($ad_info['ad_code'], 'http://') === false && strpos($ad_info['ad_code'], 'https://') === false) ? $url . DATA_DIR . "/afficheimg/$ad_info[ad_code]" : $ad_info['ad_code'];
                $str = '<a href="' .$url. 'affiche.php?ad_id=' .$ad_info['ad_id']. '&from=' .$_GET['from']. '&uri=' .urlencode($ad_info['ad_link']). '" target="_blank">' .
                        '<img src="' . $src . '" border="0" alt="' . $ad_info['ad_name'] . '" /></a>';
                break;

            case '1':
                /* Falsh广告 */
                $src = (strpos($ad_info['ad_code'], 'http://') === false && strpos($ad_info['ad_code'], 'https://') === false) ? $url . DATA_DIR . '/afficheimg/' . $ad_info['ad_code'] : $ad_info['ad_code'];
                $str = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0"> <param name="movie" value="'.$src.'"><param name="quality" value="high"><embed src="'.$src.'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed></object>';
                break;

            case '2':
                /* 代码广告 */
                $str = $ad_info['ad_code'];
                break;

            case 3:
                /* 文字广告 */
                $str = '<a href="' .$url. 'affiche.php?ad_id=' .$ad_info['ad_id']. '&from=' .$_GET['from']. '&uri=' .urlencode($ad_info['ad_link']). '" target="_blank">' . nl2br(htmlspecialchars(addslashes($ad_info['ad_code']))). '</a>';
                break;
        }
    }
    echo "document.writeln('$str');";
}
else
{
    /* 获取投放站点的名称 */
    $site_name = !empty($_GET['from']) ? addslashes($_GET['from']) : addslashes($_LANG['self_site']);

    /* 商品的ID */
    $goods_id = !empty($_GET['goods_id']) ? intval($_GET['goods_id']) : 0;

    /* 存入SESSION中,购物后一起存到订单数据表里 */
    $_SESSION['from_ad'] = $ad_id;
    $_SESSION['referer'] = stripslashes($site_name);

    /* 如果是商品的站外JS */
    if ($ad_id == '-1')
    {
        $sql = "SELECT count(*) FROM " . $yp->table('adsense') . " WHERE from_ad = '-1' AND referer = '" . $site_name . "'";
        if($db->getOne($sql) > 0)
        {
            $sql = "UPDATE " . $yp->table('adsense') . " SET clicks = clicks + 1 WHERE from_ad = '-1' AND referer = '" . $site_name . "'";
        }
        else
        {
            $sql = "INSERT INTO " . $yp->table('adsense') . "(from_ad, referer, clicks) VALUES ('-1', '" . $site_name . "', '1')";
        }
        $db->query($sql);

        $sql = "SELECT goods_name FROM " .$yp->table('goods'). " WHERE goods_id = $goods_id";
        $res = $db->query($sql);

        $row = $db->fetchRow($res);

        $uri = build_uri('goods', array('gid' => $goods_id), $row['goods_name']);

        yp_header("Location: $uri\n");

        exit;
    }
    else
    {
        /* 更新站内广告的点击次数 */
        $db->query('UPDATE ' . $yp->table('ad') . " SET click_count = click_count + 1 WHERE ad_id = '$ad_id'");

        $sql = "SELECT count(*) FROM " . $yp->table('adsense') . " WHERE from_ad = '" . $ad_id . "' AND referer = '" . $site_name . "'";
        if($db->getOne($sql) > 0)
        {
            $sql = "UPDATE " . $yp->table('adsense') . " SET clicks = clicks + 1 WHERE from_ad = '" . $ad_id . "' AND referer = '" . $site_name . "'";
        }
        else
        {
            $sql = "INSERT INTO " . $yp->table('adsense') . "(from_ad, referer, clicks) VALUES ('" . $ad_id . "', '" . $site_name . "', '1')";
        }
        $db->query($sql);

        /* 跳转到广告的链接页面 */
        if (!empty($_GET['uri']))
        {
            $uri = (strpos($_GET['uri'], 'http://') === false && strpos($_GET['uri'], 'https://') === false) ? $yp->http() . urldecode($_GET['uri']) : urldecode($_GET['uri']);
        }
        else
        {
            $uri = $yp->url();
        }

        yp_header("Location: $uri\n");
        exit;
    }
}

?>