<?php

/**
 * QQ120029121 证书反查文件
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: prince $
 * $Id: certi.php 16075 2009-05-22 02:19:40Z prince $
*/

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');

/*------------------------------------------------------ */
//-- 证书反查
/*------------------------------------------------------ */
$session_id = empty($_POST['session_id']) ? '' : trim($_POST['session_id']);

if (!empty($session_id))
{

    $sql = "SELECT sesskey FROM " . $yp->table('sessions') . " WHERE sesskey = '" . $session_id . "' ";
    $sesskey = $db->getOne($sql);
    if ($sesskey != '')
    {
        exit('{"res":"succ","msg":"","info":""}');
    }
    else
    {
        exit('{"res":"fail","msg":"error:000002","info":""}');
    }
}
else
{
    exit('{"res":"fail","msg":"error:000001","info":""}');
}

?>