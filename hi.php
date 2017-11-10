<?php



echo "http://img.alicdn.com/imgextra/i1/598803955/TB2rw9GdbXlpuFjy1zbXXb_qpXa-598803955.jpg";
echo str_replace("http://img.alicdn","https://img.alicdn","http://img.alicdn.com/imgextra/i1/598803955/TB2rw9GdbXlpuFjy1zbXXb_qpXa-598803955.jpg");
exit;


echo strpos($s1,$s2).'<br />';



//var_dump($_SERVER);


$s1='1234567890';
$s2='2';
$s3='1234567890';
$s4='p';
$s5='1';
echo strpos($s1,$s2).'<br />';
echo strpos($s1,$s3).'<br />';
echo strpos($s1,$s4).'<br />';
echo strpos($s1,$s5).'<br />';
echo strpos("You love php, I love php too!","php");

if(strpos($s1,$s5)===0){
	echo 'ok';
}else{
		echo 'not ok';

}

 echo $_SERVER['HTTPS'];
 echo $_SERVER['SERVER_PORT'] ;



$str = "baidu.com/";
$var = trim($str);
$len = strlen($var)-1;
echo $len;
echo $var{$len};
if($var{$len}=='/'){
	echo substr("baidu.com/",0,-1);
}


echo '<br />时间'.date('ymdhis');


echo '<br />'.date("Y-y-m-d-H-i-s");
$d =  explode('-', date("Y-y-m-d-H-i-s"));
echo $d[0];
$d = split('-', date("Y-y-m-d-H-i-s"));
var_dump($d[0]);


if(strpos("0.jpg","php")!==false){
	echo 'hzj';
}



?>