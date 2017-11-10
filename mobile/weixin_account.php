<?php


define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}
if(isset($_REQUEST['act']) && $_REQUEST['act'] == 'update')
{
	$wxid = $_GET['wxid'];
	if($wxid)
	{
		$GLOBALS['user']->logout();
		yp_header("Location:user.php?wxid={$wxid}&is_update=1\n");
	 	exit;
	}
	yp_header("Location:./\n");
	exit;
}
if(isset($_REQUEST['act']) && $_REQUEST['act'] == 'jcbd')
{
	include_once (ROOT_PATH . 'includes/cls_json.php');
	$json = new JSON();
	$result = array('error' => 0,'message' => '');
	$wxid = $_GET['wxid'];
	if($wxid)
	{
		 $sql = "SELECT COUNT(*) FROM " . $GLOBALS['yp']->table('users') . 
		 		" WHERE fake_id = '$wxid'";
		 $count = $GLOBALS['db']->getOne($sql);
		 if($count == 0)
		 {
			 $result['error'] = 1;
			 $result['message'] = '该微信号不存在！'; 
		 }
		 else
		 {
			  $sql = "UPDATE " . $GLOBALS['yp']->table('users') . 
			  		 " SET is_bind = 0 WHERE fake_id = '$wxid'";
			  $num = $GLOBALS['db']->query($sql);
			  if($num > 0)
			  {
				  $result['error'] = 0;
				  $result['message'] = '解除绑定成功！';
				  $result['wxid'] = $wxid;
				  $GLOBALS['user']->logout();  
			  }
			  else
			  {
				  $result['error'] = 2;
				  $result['message'] = '解除绑定失败！';    
			  } 
		 }
	} 
	else
	{
		 $result['error'] = 3;
		 $result['message'] = '参数错误！'; 
	}
	die($json->encode($result));
}

if(isset($_GET['wxid']))
{
	 $sql = "SELECT user_id FROM " . 
	 		$GLOBALS['yp']->table('users') . 
			" WHERE fake_id = '" . $_GET['wxid'] . "'";
	 $user_id = $GLOBALS['db']->getOne($sql);
	 if($user_id > 0)
	 {
		 $sql = "SELECT * FROM " . 
		 		$GLOBALS['yp']->table('users') . " WHERE user_id = '$user_id'";
		 $user = $GLOBALS['db']->getRow($sql);
		 $aite_id = explode('_',$user['aite_id']);
		 $tag = $aite_id[0];
		 if(empty($tag))
		 {
		 	if($user['mobile_phone'])
		 	{
			 	$tag = 'phone'; 
		 	} 
		 	else
		 	{
			 	$tag = 'email'; 
		 	}
		 }
		 if($tag)
		 {
		    $smarty->assign('tag',$tag); 
		 }
		 $smarty->assign('user',$user);
	 }
	 else
	 {
		 yp_header("Location:user.php?wxid={$_GET['wxid']}\n");
	 	 exit;  
	 }
}
else
{
	 yp_header("Location:./\n");
	 exit;
}
$smarty->assign('wxid',$_GET['wxid']);
$smarty->display('weixin_account.dwt');

?>