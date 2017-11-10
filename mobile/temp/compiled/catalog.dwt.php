<!DOCTYPE html >
<html>
<head>
<meta name="Generator" content="JTYP v7" />
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width">
<title>商品分类</title>
<meta name="Keywords" content="<?php echo $this->_var['keywords']; ?>" />
<meta name="Description" content="<?php echo $this->_var['description']; ?>" />
<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
	<link rel="stylesheet" type="text/css" href="themesmobile/prince_jtypmall_mobile/css/public.css"/>
<link rel="stylesheet" type="text/css" href="themesmobile/prince_jtypmall_mobile/css/catalog.css"/>
<script type="text/javascript" src="themesmobile/prince_jtypmall_mobile/js/jquery.js"></script>

<?php echo $this->smarty_insert_scripts(array('files'=>'common.js')); ?>
</head>
<body>

<header id="whiteheader">
<a href="javascript:history.back(-1)" class="new-back" title="返回"></a>
<a href="flow.php" class='user_btn'></a>
<div class="new_white_search_mid"> <a href="searchindex.php" > <em>搜索商品</em> <span><img src="themesmobile/prince_jtypmall_mobile/images/icosousuo.png"></span> </a> </div>
</header>
<?php echo $this->fetch('library/up_menu.lbi'); ?> 
 
<div class="container">    
  <div class="category-box">
    <div class="category1" style="outline: none;" tabindex="5000">
      <ul class="clearfix" style=" padding-top:45px;">
       <?php $_from = $this->_var['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cat');$this->_foreach['name'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['name']['total'] > 0):
    foreach ($_from AS $this->_var['cat']):
        $this->_foreach['name']['iteration']++;
?>
        <li <?php if (($this->_foreach['name']['iteration'] <= 1)): ?>class="cur"<?php endif; ?>><?php echo htmlspecialchars($this->_var['cat']['name']); ?></li>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      </ul>
    </div>
    <div class="category2" style=" outline: none; overflow-y:scroll" tabindex="5001">
    <?php $_from = $this->_var['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cat');$this->_foreach['name'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['name']['total'] > 0):
    foreach ($_from AS $this->_var['cat']):
        $this->_foreach['name']['iteration']++;
?>      
      <dl style="display: none; padding-top:45px;<?php if (($this->_foreach['name']['iteration'] <= 1)): ?>display: block;<?php endif; ?>"> 
        <?php
		 $GLOBALS['smarty']->assign('index_image',get_advlist('分类-'.$GLOBALS['smarty']->_var['cat']['id'].'-促销广告', 1));
	  ?>  <?php if ($this->_var['index_image']): ?>
        <span>
       
         
		<?php $_from = $this->_var['index_image']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'ad');$this->_foreach['index_image'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['index_image']['total'] > 0):
    foreach ($_from AS $this->_var['ad']):
        $this->_foreach['index_image']['iteration']++;
?>
        <a href="<?php echo $this->_var['ad']['url']; ?>">
 <img src="<?php echo $this->_var['ad']['image']; ?>">
  </a>
		<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
       

		</span>  <?php endif; ?>
        <a href="category.php?id=<?php echo $this->_var['cat']['id']; ?>" class="all" style=" color:#FFF">进入<?php echo htmlspecialchars($this->_var['cat']['name']); ?>频道&nbsp;></a>
        <?php $_from = $this->_var['cat']['cat_id']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'child');$this->_foreach['child'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['child']['total'] > 0):
    foreach ($_from AS $this->_var['child']):
        $this->_foreach['child']['iteration']++;
?>   
        <dt><a href="<?php echo $this->_var['child']['url']; ?>" ><?php echo htmlspecialchars($this->_var['child']['name']); ?></a></dt> 
        <dd> 
        <div class="fenimg">
           <?php $_from = $this->_var['child']['cat_id']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'childer');$this->_foreach['cat22'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['cat22']['total'] > 0):
    foreach ($_from AS $this->_var['childer']):
        $this->_foreach['cat22']['iteration']++;
?> 
           <?php if ($this->_var['childer']['img']): ?> 
        <div class="fen_img">     
        <a href="<?php echo $this->_var['childer']['url']; ?>"><span><img alt="" src="/<?php echo $this->_var['childer']['img']; ?>"></span><em><?php echo $this->_var['childer']['name']; ?></em></a> 
        </div>
        <?php else: ?>
        <div class="fen">
        <a href="<?php echo $this->_var['childer']['url']; ?>"><?php echo $this->_var['childer']['name']; ?></a> 
        </div>  
<?php endif; ?>  
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </div>
     
         </dd>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

      </dl>
   <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </div>
  </div>
</div>


<script src="themesmobile/prince_jtypmall_mobile/js/category.js"></script>
<script src="themesmobile/prince_jtypmall_mobile/js/jquery.nicescroll.min.js"></script> 
</body>
</html>