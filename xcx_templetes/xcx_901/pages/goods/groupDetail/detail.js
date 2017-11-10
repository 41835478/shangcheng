var server = require('../../../utils/server');
Page({
	data: {
		goods: {},
		current: 0,
		tabStates:[true,false,false],
		tabClasss:["text-select","text-normal","text-normal"],
		galleryHeight: getApp().screenWidth,
		tab:0,
		goods_num:1,
		textStates:["view-btns-text-normal","view-btns-text-select"],
	},
	propClick: function(e) {
		var pos = e.currentTarget.dataset.pos;
		var index = e.currentTarget.dataset.index;
		var goods = this.data.goods
		for(var i= 0 ; i < goods.goods.goods_spec_list[index].length;i++)
		{
              
			  if(i == pos)
			  goods.goods.goods_spec_list[index][pos].isClick = 1;
			  else
			  goods.goods.goods_spec_list[index][i].isClick = 0;
		}

		this.setData({goods:goods});
		this.checkPrice();
	},
	addCollect:function(e){
		var goods_id = e.currentTarget.dataset.id;
		console.log(goods_id);

        var user_id = getApp().globalData.userInfo.user_id
		var ctype = 0;
		

        server.getJSON('/Goods/collectGoods/user_id/' + user_id +"/goods_id/" + goods_id + "/type/" + ctype,function(res){
			wx.showToast({ title: res.data.msg, icon: 'success', duration: 2000 })
		});

		

	},
	bindMinus: function(e) {
		
		var num = this.data.goods_num;
		if (num > 1) {
			num --;
		}
		
		this.setData({goods_num:num});
	},
	bindManual: function(e) {
		var index = parseInt(e.currentTarget.dataset.index);
		var num = e.detail.value;
		this.setData({goods_num:num});
	},
	bindPlus:function(e){
        var num = this.data.goods_num;
		
		num++;
		
		this.setData({goods_num:num});
	},
	onLoad: function(options) {
		
		var goodsId = options.objectId;
		this.getGoodsById(goodsId);
	},
	tabClick:function(e){
        var index = e.currentTarget.dataset.index
		var classs= ["text-normal","text-normal","text-normal"]
		classs[index] = "text-select"
		this.setData({tabClasss:classs,tab:index})
	},
	getGoodsById: function(goodsId) {
		
        var that = this

        server.getJSON('/Activity/group/id/' + goodsId,function(res){
            var goodsInfo = res.data.result;
			that.setData({
				groupInfo:res.data.groupInfo,
				goods: goodsInfo
			});
			that.checkPrice();
		});
	},
	checkPrice:function(){
		this.setData({price:this.data.groupInfo.price});
		return ;
		var goods = this.data.goods;
        var spec = ""
        for(var i= 0 ; i < goods.goods.goods_spec_list.length;i++)
		{
              
			  for(var j= 0 ; j < goods.goods.goods_spec_list[i].length;j++)
			  {
				  if(goods.goods.goods_spec_list[i][j].isClick == 1)
				  {
if(spec == "")
spec = goods.goods.goods_spec_list[i][j].item_id
else
spec = spec + "_" + goods.goods.goods_spec_list[i][j].item_id
				  }
			  }
		}
        console.log(spec);
        
		var specs = spec.split("_");
		for(var i = 0 ; i < specs.length;i++)
		{
           specs[i] = parseInt(specs[i])
		}
        specs.sort(function(a,b){return a - b});
		spec = ""
		for(var i = 0 ; i < specs.length;i++)
		{
if(spec == "")
spec = specs[i]
else
spec = spec + "_" + specs[i]
		}
		console.log(spec);
		var price = goods['spec_goods_price'][spec].price;
		console.log(price);
		this.setData({price:price});
	},
	bug: function() {
        var goods = this.data.goods;
		var spec = ""
		if(goods.goods.goods_spec_list !=null)
        for(var i= 0 ; i < goods.goods.goods_spec_list.length;i++)
		{
              
			  for(var j= 0 ; j < goods.goods.goods_spec_list[i].length;j++)
			  {
				  if(goods.goods.goods_spec_list[i][j].isClick == 1)
				  {
if(spec == "")
spec = goods.goods.goods_spec_list[i][j].item_id
else
spec = spec + "_" + goods.goods.goods_spec_list[i][j].item_id
				  }
			  }
		}

        
		var app = getApp()
		var that = this;
        var goods_id = that.data.goods.goods.goods_id;
        var goods_spec = spec;
		var session_id = app.globalData.openid//that.data.goods.goods.spec_goods_price
		var goods_num = that.data.goods_num;

        var user_id = "0"
        if(app.globalData.login)
		    user_id = app.globalData.userInfo.user_id

       server.getJSON('/Cart/addCart',{goods_id:goods_id,goods_spec:goods_spec,session_id:session_id,goods_num:goods_num,user_id:user_id},function(res){
if(res.data.status == 1)
		   {
           wx.showToast({
			title: '已加入购物车',
			icon: 'success',
			duration: 2000
		});
		wx.switchTab({
			url: '../../cart/cart'
		});
		   }
		else
		wx.showToast({
			title: res.data.msg,
			icon: 'error',
			duration: 2000
		});

	   });

       

	},
	addCart: function() {

		var goods = this.data.goods;
		var spec = ""

		if(goods.goods.goods_spec_list !=null)
        for(var i= 0 ; i < goods.goods.goods_spec_list.length;i++)
		{
              
			  for(var j= 0 ; j < goods.goods.goods_spec_list[i].length;j++)
			  {
				  if(goods.goods.goods_spec_list[i][j].isClick == 1)
				  {
if(spec == "")
spec = goods.goods.goods_spec_list[i][j].item_id
else
spec = spec + "_" + goods.goods.goods_spec_list[i][j].item_id
				  }
			  }
		}




		var app = getApp()
		var that = this;
        var goods_id = that.data.goods.goods.goods_id;
        var goods_spec = spec;
		var session_id = app.globalData.openid//that.data.goods.goods.spec_goods_price
		var goods_num = that.data.goods_num;
		var user_id = "0"
        if(app.globalData.login)
		    user_id = app.globalData.userInfo.user_id

       server.getJSON('/Cart/addCart',{goods_id:goods_id,goods_spec:goods_spec,session_id:session_id,goods_num:goods_num,user_id:user_id},function(res){
if(res.data.status == 1)
           wx.showToast({
			title: '已加入购物车',
			icon: 'success',
			duration: 1000
		});
		else
		wx.showToast({
			title: res.data.msg,
			icon: 'error',
			duration: 1000
		});
	   });
       

	},
	showCartToast: function () {
		wx.showToast({
			title: '已加入购物车',
			icon: 'success',
			duration: 1000
		});
		wx.navigateTo({
			url: '../../../../../../cart/cart'
		});

	},
	previewImage: function (e) {
		wx.previewImage({
			//从<image>的data-current取到current，得到String类型的url路径
			current: this.data.goods.get('images')[parseInt(e.currentTarget.dataset.current)],
			urls: this.data.goods.get('images') // 需要预览的图片http链接列表
		})
	}
});