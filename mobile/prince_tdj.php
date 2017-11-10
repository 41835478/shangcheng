<?php
//今/天/优/品/多/商/户/系/统-采集 mod on 20160808 by prince  qq 1/2/0/0/2/9/1/2/1
	$sql = "SELECT api_data FROM " . $yp->table('sharegoods_module') . " WHERE class = 'taobao'";
	$vo = $db->getOne($sql);
	$vo = unserialize($vo);
	$code_tdj=$vo['code_tdj'];
	$smarty->assign('code_tdj',$code_tdj);
//今/天/优/品/多/商/户/系/统-采集 mod on 20160808 by prince  qq 1/2/0/0/2/9/1/2/1-end

?>