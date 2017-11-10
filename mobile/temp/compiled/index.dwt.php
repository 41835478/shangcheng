<!DOCTYPE html >
<html>
<head>
<meta name="Generator" content="JTYP v7" />
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width">
<title><?php echo $this->_var['page_title']; ?></title>
<meta name="Keywords" content="<?php echo $this->_var['keywords']; ?>" />
<meta name="Description" content="<?php echo $this->_var['description']; ?>" />
<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
<link rel="stylesheet" type="text/css" href="themesmobile/prince_jtypmall_mobile/css/public.css"/>
<link rel="stylesheet" type="text/css" href="themesmobile/prince_jtypmall_mobile/css/index.css"/>
<script type="text/javascript" src="themesmobile/prince_jtypmall_mobile/js/TouchSlide.1.1.js"></script>
</head>
<body>
<?php 
$k = array (
  'name' => 'share',
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?>
<div class="body_bj">


<div id="show_guide_qrcode" onclick="closeReg();" style="display:none;text-align: center;position:fixed; top:0;  left:0; width:100%; height:100%;background:rgba(0, 0, 0, 0.7); color:#FFF;z-index:2000001;  ">
<img src="<?php echo $this->_var['guide_qrcode']; ?>" style="width: 250px;height: 250px; margin-top:15%;z-index:20001;" />
<br /><br />识别图中二维码关注我们
</div>



<header id="newheader"> <?php echo $this->fetch('library/page_header_index.lbi'); ?> </header>
 
<?php echo $this->fetch('library/index_ad.lbi'); ?> 
 

<?php echo $this->fetch('library/index_icon.lbi'); ?> 


<div class="hot">
<h3></h3>
<ul id="mq" onmouseover="iScrollAmount=0"onmouseout="iScrollAmount=1">
  
<?php $this->assign('articles',$this->_var['articles_16']); ?><?php $this->assign('articles_cat',$this->_var['articles_cat_16']); ?><?php echo $this->fetch('library/cat_articles.lbi'); ?>

</ul>
</div>
<script>
var oMarquee = document.getElementById("mq"); //滚动对象
var iLineHeight = 30; //单行高度，像素
var iLineCount = 7; //实际行数
var iScrollAmount = 1; //每次滚动高度，像素
function run() {
oMarquee.scrollTop += iScrollAmount;
if ( oMarquee.scrollTop == iLineCount * iLineHeight )
oMarquee.scrollTop = 0;
if ( oMarquee.scrollTop % iLineHeight == 0 ) {
window.setTimeout( "run()", 2000 );
} else {
window.setTimeout( "run()", 50 );
}
}
oMarquee.innerHTML += oMarquee.innerHTML;
window.setTimeout( "run()", 2000 );
</script>

<div class="floor_img">
<h2>
 
<?php $this->assign('ads_id','14'); ?><?php $this->assign('ads_num','1'); ?><?php echo $this->fetch('library/ad_position.lbi'); ?>

</h2>
<dl>
    <dt> 
<?php $this->assign('ads_id','17'); ?><?php $this->assign('ads_num','1'); ?><?php echo $this->fetch('library/ad_position.lbi'); ?>
 </dt>
    <dd> 
    <span class="Edge"> 
<?php $this->assign('ads_id','18'); ?><?php $this->assign('ads_num','1'); ?><?php echo $this->fetch('library/ad_position.lbi'); ?>
 </span> 
<span>

<?php $this->assign('ads_id','19'); ?><?php $this->assign('ads_num','1'); ?><?php echo $this->fetch('library/ad_position.lbi'); ?>
 </span> </dd>
  </dl>
<ul>
<li class="brom">
 
<?php $this->assign('ads_id','23'); ?><?php $this->assign('ads_num','1'); ?><?php echo $this->fetch('library/ad_position.lbi'); ?>
 
</li>
<li>
 
<?php $this->assign('ads_id','22'); ?><?php $this->assign('ads_num','1'); ?><?php echo $this->fetch('library/ad_position.lbi'); ?>
 
</li>
</ul>
<ul>
<li class="brom">
 
<?php $this->assign('ads_id','21'); ?><?php $this->assign('ads_num','1'); ?><?php echo $this->fetch('library/ad_position.lbi'); ?>
 
</li>
<li>
 
<?php $this->assign('ads_id','20'); ?><?php $this->assign('ads_num','1'); ?><?php echo $this->fetch('library/ad_position.lbi'); ?>
 
</li>
</ul>
</div>






<?php echo $this->fetch('library/supplier_city.lbi'); ?>
<?php echo $this->fetch('library/recommend_promotion.lbi'); ?>
<?php $this->assign('ads_id','25'); ?><?php $this->assign('ads_num','1'); ?><?php echo $this->fetch('library/ad_position.lbi'); ?>
 

 

<?php echo $this->fetch('library/recommend_new.lbi'); ?>
<?php echo $this->fetch('library/recommend_hot.lbi'); ?>
 
 
<div class="index_img">
 
 
</div>
 

<?php $this->assign('cat_goods',$this->_var['cat_goods_5']); ?><?php $this->assign('goods_cat',$this->_var['goods_cat_5']); ?><?php echo $this->fetch('library/cat_goods.lbi'); ?>
 
 
<div id="index_banner" class="index_banner">
<div class="bd">
<ul>
			<?php $_from = $this->_var['wap_index_img']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'ad');$this->_foreach['wap_index_img'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['wap_index_img']['total'] > 0):
    foreach ($_from AS $this->_var['ad']):
        $this->_foreach['wap_index_img']['iteration']++;
?>
          <li><a href="<?php echo $this->_var['ad']['url']; ?>"><img src="<?php echo $this->_var['ad']['image']; ?>" width="100%" /></a></li>
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
					</ul>
</div>
<div class="hd">
					<ul></ul>
				</div>
</div>
<script type="text/javascript">
				TouchSlide({ 
					slideCell:"#index_banner",
					titCell:".hd ul", //开启自动分页 autoPage:true ，此时设置 titCell 为导航元素包裹层
					mainCell:".bd ul", 
					effect:"leftLoop", 
					autoPage:true,//自动分页
					autoPlay:true //自动播放
				});
			</script>
   


 <div class="floor_body2" >
    <h2>————&nbsp;<?php echo $this->_var['lang']['best_goods']; ?>&nbsp;————</h2>
    <div id="J_ItemList">
      <ul class="product single_item info">
      </ul>
      <a href="javascript:;" class="get_more"> </a> 
      </div>
  </div>

<?php echo $this->fetch('library/footer_nav.lbi'); ?> 

<script type="text/javascript" src="themesmobile/prince_jtypmall_mobile/js/jquery.js"></script>
<?php echo $this->smarty_insert_scripts(array('files'=>'jquery.json.js,transport.js')); ?>
<script type="text/javascript" src="themesmobile/prince_jtypmall_mobile/js/touchslider.dev.js"></script>
<script type="text/javascript" src="themesmobile/prince_jtypmall_mobile/js/jquery.more.js"></script>
<?php echo $this->smarty_insert_scripts(array('files'=>'common.js')); ?>
<script type="text/javascript">
var url = 'index_bestgoods.php?act=ajax';
$(function(){
	$('#J_ItemList').more({'address': url});
});


</script> 
<script>
function goTop(){
	$('html,body').animate({'scrollTop':0},600);
}
</script>
<a href="javascript:goTop();" class="gotop"><img src="themesmobile/prince_jtypmall_mobile/images/topup.png"></a> 
<script type="Text/Javascript" language="JavaScript">


function selectPage(sel)
{
   sel.form.submit();
}


</script>
<script type="text/javascript">
<?php $_from = $this->_var['lang']['compare_js']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
<?php if ($this->_var['key'] != 'button_compare'): ?>
var <?php echo $this->_var['key']; ?> = "<?php echo $this->_var['item']; ?>";
<?php else: ?>
var button_compare = "";
<?php endif; ?>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
var compare_no_goods = "<?php echo $this->_var['lang']['compare_no_goods']; ?>";
var btn_buy = "<?php echo $this->_var['lang']['btn_buy']; ?>";
var is_cancel = "<?php echo $this->_var['lang']['is_cancel']; ?>";
var select_spe = "<?php echo $this->_var['lang']['select_spe']; ?>";
</script>
</div> 
</body>
</html>