<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
<title>HTML填充信息窗口内容</title>
<style type="text/css">
*{
    margin:0px;
    padding:0px;
}
body, button, input, select, textarea {
    font: 12px/16px Verdana, Helvetica, Arial, sans-serif;
}
#info {
    margin-top: 10px;
}
#container{
	min-width:600px;
	min-height:767px;
}
</style>
<script charset="utf-8" src="http://map.qq.com/api/js?v=2.exp"></script>
<script>
var init = function() {
    var center = new qq.maps.LatLng(23.053177,113.408417);
    var map = new qq.maps.Map(document.getElementById('container'),{
        center: center,
        zoom: 13
    });
    var infoWin = new qq.maps.InfoWindow({
        map: map
    });
    infoWin.open();
    //tips  自定义内容
    infoWin.setContent('<div style="width:200px;padding-top:10px;">'+
        '<img style="float:left;" src="img/infowindow-img.jpg"/> '+
        '我是个可爱的小孩子</div>');
    infoWin.setPosition(center);
}
</script>
</head>
<body onload="init()">
    <div id="container"></div>
    <div id="info">
    <p>调用open方法打开一个信息窗，内容为一张图片和一段文字。</p>
</div>
</body>
</html>