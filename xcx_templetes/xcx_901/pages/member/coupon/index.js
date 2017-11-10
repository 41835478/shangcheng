var server = require('../../../utils/server');
var cPage = 0;
var ctype = "0";
Page({

	tabClick:function(e){
        var index = e.currentTarget.dataset.index
		var classs= ["text-normal","text-normal","text-normal","text-normal","text-normal","text-normal"]
		classs[index] = "text-select"
		this.setData({tabClasss:classs,tab:index})

		var types= ["0","1","2"]

		cPage = 0;
		ctype = types[index];
		this.data.coupons = [];
		this.getEvaluateLists(ctype,cPage);
    this.setData({types:ctype});
	},
	
	onReachBottom: function () {
		this.getEvaluateLists(ctype,++cPage);
		wx.showToast({
		  title: '加载中',
		  icon: 'loading'
		})
	},
	onPullDownRefresh: function () {
    
	cPage = 0;
	this.data.orders = [];
        this.getEvaluateLists(ctype,cPage);
	},


	data: {
    types:0,
		coupons: [],
        tabClasss:["text-select","text-normal","text-normal","text-normal","text-normal"],

	},
    onLoad:function(option){
    this.getEvaluateLists(0,0);
	},
    getEvaluateLists:function(cType,page){
        var that = this;
		var user_id = getApp().globalData.userInfo.user_id
	


        server.getJSON('/User/getCouponList/user_id/' + user_id + "/page/" + page + "/type/" + cType,function(res){
            var datas = res.data.result;
			var ms = that.data.coupons
            for(var i in datas){
            ms.push(datas[i]);
            }
            wx.stopPullDownRefresh();
			that.setData({
						coupons: ms
					});
		});

	    

	},
});