var server = require('../../../utils/server');
var cPage = 0;
var ctype = "NO";
Page({
	tabClick:function(e){
        var index = e.currentTarget.dataset.index
        var types= ["NO","WAITPAY","WAITSEND","WAITRECEIVE","FINISH"]
         
         
		var classs= ["text-normal","text-normal","text-normal","text-normal","text-normal","text-normal"]
		classs[index] = "text-select"
		this.setData({tabClasss:classs,tab:index})
        cPage = 0;
		ctype = types[index];
		this.data.orders = [];
		this.getOrderLists(types[index],cPage);
	},
	pay:function(e){
		var index = e.currentTarget.dataset.index;
		var order = this.data.orders[index];
		var app = getApp();
		app.globalData.order = order
		wx.navigateTo({
			url: '../orderpay/payment?order_id=' + 1
		});
	},
	cancel:function(e)
	{
		var index = e.currentTarget.dataset.index;
		var order = this.data.orders[index];
        var that = this;
        wx.showModal({
  title: '提示',
  showCancel:true,
  content: '确定取消订单吗？',
  success: function(res) {
    
    if (res.confirm) {

		 
		var user_id = getApp().globalData.userInfo.user_id
		
		server.getJSON('/User/cancelOrder/user_id/' + user_id +"/order_id/" + order['order_id'],function(res){
wx.showToast({ title: res.data.msg, icon: 'success', duration: 2000 })
				cPage = 0;
	      that.data.orders = [];
          that.getOrderLists(ctype,0);
		});

		
    }
  }
})



	},



	confirm:function(e)
	{
		var index = e.currentTarget.dataset.index;
		var order = this.data.orders[index];
        var that = this;
        wx.showModal({
  title: '提示',
  showCancel:true,
  content: '确定已收货吗？',
  success: function(res) {
    
    if (res.confirm) {

		 
		var user_id = getApp().globalData.userInfo.user_id
		
		server.getJSON('/User/orderConfirm/user_id/' + user_id +"/order_id/" + order['order_id'],function(res){
wx.showToast({ title: res.data.msg, icon: 'success', duration: 2000 })
				cPage = 0;
				
	      that.data.orders = [];
          that.getOrderLists(ctype,0);
		});
		
    }
  }
})



	},



	details:function(e){
		var index = e.currentTarget.dataset.index;
		var goods = this.data.orders[index];
wx.navigateTo({
			url: '../details/index?order_id='+goods['order_id']
		});
	},
  kuaidi: function (e) {
    var index = e.currentTarget.dataset.index;
    var goods = this.data.orders[index];
    wx.navigateTo({
      url: '../kuaidi/index?order_id=' + goods['order_id']
    });
  },
	onReachBottom: function () {
		this.getOrderLists(ctype,++cPage);
		wx.showToast({
		  title: '加载中',
		  icon: 'loading'
		})
	},
	onPullDownRefresh: function () {
    cPage = 0;
    this.data.orders = [];
		this.getOrderLists(ctype,0);
	},
	data: {
		orders: [],
        tabClasss:["text-select","text-normal","text-normal","text-normal","text-normal"],
	},
	getOrderLists:function(ctype,page){
        var that = this;
		var user_id = getApp().globalData.userInfo.user_id
	
	    server.getJSON('/User/getOrderList/user_id/' + user_id + "/type/" + ctype + "/page/" + page,function(res){
var datas = res.data.result;
            
			var ms = that.data.orders
      for(var i in datas){
   ms.push(datas[i]);
}
wx.stopPullDownRefresh();
			that.setData({
						orders: ms
					});
		});
	},
	onShow:function(){
        this.data.orders = [];//prince
        this.getOrderLists(ctype,0);//prince
		
  },
	onLoad: function (option) {
		 // 页面显示
if (option.cid == "WAITPAY") {//未付款
  var tabClasss = ["text-normal", "text-select", "text-normal", "text-normal", "text-normal"];
  this.setData({ tabClasss: tabClasss });
}     
if(option.cid == "WAITSEND"){//待发货
	var  tabClasss = ["text-normal","text-normal","text-select","text-normal","text-normal"];
	this.setData({tabClasss:tabClasss});
}
if(option.cid == "FINISH"){//已完成
	var  tabClasss = ["text-normal","text-normal","text-normal","text-normal","text-select"];
	this.setData({tabClasss:tabClasss});
}

	cPage = 0;
	ctype = option.cid;
	this.data.orders = [];
	}
});