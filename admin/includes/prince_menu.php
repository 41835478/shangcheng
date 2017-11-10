<?php

/**
 * //采集 mod on 20160808 by PRINCE  qq 1 2 0 0 2 9 1 2 1

*/

if (!defined('IN_PRINCE'))
{
    die('Hacking attempt');
}
/*------------------------------------------------------ */
//-- 菜单项
/*------------------------------------------------------ */

//淘宝天猫
$modules['02_taobao']['17_3setting']                = 'prince_shops.php?act=tb_setting';
//$modules['02_taobao']['17_1others']               = 'prince_shops.php?act=others';
$modules['02_taobao']['17_1prince_tbk_api']    		= 'prince_shops.php?act=prince_tbk_api_colect_view';
$modules['02_taobao']['17_2oalmm']                 	= 'prince_shops.php?act=shopdata';
$modules['02_taobao']['17_20oalmm']               	= 'prince_shops.php?act=batchco';
$modules['02_taobao']['17_3onekey']                 = 'prince_shops.php?act=getAllgoods';
$modules['02_taobao']['17_3talmm']               	= 'prince_shops.php?act=tools';
$modules['02_taobao']['17_4princeinfo']                = '../plugin.php';
/*------------------------------------------------------ */
//-- 权限控制
/*------------------------------------------------------ */

//淘宝数据
$purview['17_3setting']       						= '17_3setting';
$purview['17_1others']       						= '17_1others';
$purview['17_2oalmm']       						= '17_2oalmm';
$purview['17_20oalmm']       						= '17_20oalmm';
$purview['17_3onekey']       						= '17_3onekey';
$purview['17_1prince_tbk_api']      					= '17_1prince_tbk_api';
$purview['17_3talmm']       						= '17_3talmm';
$purview['17_4princeinfo']       						= '17_4princeinfo';


//今/天/优/品/多/商/户/系/统-采集 mod on 20160808 by prince  qq 1/2/0/0/2/9/1/2/1-end


?>
