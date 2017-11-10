<?php

/**
 * sql
*/
	define('IN_PRINCE', true);
	require('../includes/init.php');

    $sql="SELECT 
		*
	FROM ".$yp->table('account_log')."";
	$goods=$db ->getAll($sql);
	print_r($goods);

?>

