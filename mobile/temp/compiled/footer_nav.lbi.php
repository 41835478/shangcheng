<style>
.f_mask_wechat_kefu {background-color: #000;opacity: 0.4;height: 100%;width: 100%; position: absolute;top: 0;left: 0;z-index: 111;display: none;}
#wechat_kefu{position:fixed; bottom:0; left:0; height:0px; z-index:99999999; background:#fff; width:100%;}  
.callme {width:100%; margin:auto;overflow-y:scroll;background:#ffffff; height:100%;}
.callme h2{width:100%; text-indent:30px; height:30px; font-size:18px; line-height:30px; color:#fff; font-weight:normal; padding-top:10px; padding-bottom:10px; background:#FF6500}
.callme ul{width:100%; overflow:hidden; padding-top:10px; padding-bottom:50px;}
.tell_me_con{width:95%; overflow:hidden; margin:auto}
.tell_me_con ul{width:95%; overflow:hidden; margin:auto; margin-top:10px;}
.tell_me_con ul li{width:100%; overflow:hidden; margin:auto;}
.goods_shut{width:100%; height:45px; background:#FFF; position:absolute; bottom:0px;}
.shut{display:block; width:100%; height:45px; background:#E71F19; text-align:center; font-size:18px;line-height:45px; color:#FFF;}

</style>


<div style="height:50px; line-height:50px; clear:both;"></div>
<div class="v_nav">
<div class="vf_nav">
<ul>
<li> <a href="./">
    <i class="vf_1"></i>
    <span>首页</span></a></li>
<li><?php if ($this->_var['had_im']): ?><a href="javascript:chat_online();"><?php elseif ($this->_var['wechat']): ?><a href="javascript:show_wechat_kefu();"><?php else: ?><a href="tel:<?php 
$k = array (
  'name' => 'ypmart_tel',
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?>"><?php endif; ?> 
    <i class="vf_2"></i>
    <span>客服</span></a></li>
<li><a href="catalog.php">
    <i class="vf_3"></i>
    <span>分类</span></a></li>
<li>
<a href="flow.php">
   <em class="global-nav__nav-shop-cart-num" id="YP_CARTINFO" style="right:9px;"><?php 
$k = array (
  'name' => 'cart_info',
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?></em>
   <i class="vf_4"></i>
   <span>购物车</span>

   </a></li>
<li><a href="user.php">
    <i class="vf_5"></i>
    <span>我的</span></a></li>
</ul>
</div>
</div>


<section class="f_mask_wechat_kefu" style="display: none;"></section>
<section class="f_block_wechat_kefu" id="wechat_kefu" style="height:0; overflow:hidden;">
<section class="callme">
<h2 style="background:#62b900">微信客服</h2>  

<div id="tell_me_form" >
  <div class="tell_me_con">
 <ul >
      <?php if ($this->_var['wechat'] && $this->_var['wechat_qrcode']): ?><li style="text-align:center;"><img src="<?php echo $this->_var['wechat_qrcode']; ?>" alt="客服微信二维码" title="客服微信二维码" style="width:160px; height:auto;"/></li><?php endif; ?>
 
      <?php $_from = $this->_var['wechat']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'im');if (count($_from)):
    foreach ($_from AS $this->_var['im']):
?> 
      <?php if ($this->_var['im']): ?> 
      <li style="text-align:center;">
          微信客服:<?php echo $this->_var['im']; ?>
      </li>
      <?php endif; ?> 
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
 </ul>
  </div>
</div>
</section>
<div class="goods_shut">
<a href="javascript:void(0)" onclick="close_wechat_kefu();" class="shut" style=" color:#FFF;font-size:18px;">关闭</a>
</div>
</section>

<script>
function show_wechat_kefu(){
	$("#wechat_kefu").animate({height:'80%'},[10000]);
		var total=0,h=$(window).height(),
        top =$('.callme').height()||0,
        con = $('.tell_me_con');
		total = 0.8*h;
		con.height(total-top+'px');

	$(".f_mask_wechat_kefu").show();
}
function close_wechat_kefu(){	
	$(".f_mask_wechat_kefu").hide();
	$('#wechat_kefu').animate({height:'0'},[10000]);
}
</script>
