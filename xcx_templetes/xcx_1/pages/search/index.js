var server = require('../../utils/server');
Page({
	
	search:function(e){
        var that = this
        var keywords = that.data.keywords;
        //console.log('search:' + keywords);	
        if (keywords == undefined || keywords == '') {
          wx.showToast({
            title: '请输入关键词',
            duration: 1000
          });
          return false;
        }
        wx.navigateTo({
            url: "../../../../goods/list/list?keywords=" + keywords
        });
		//nihao

	},
	bindChange: function(e) {
    var that = this
		var keywords = e.detail.value;
    //console.log('bindChange:'+keywords);	
    that.setData({
			keywords: keywords
		});
	},
	onLoad:function(option)
	{
		var that = this
    var syskey = getApp().globalData.syskey;

    server.getJSON("/Goods/getHotKeywords/syskey/" + syskey,function(res){
        var keyword = res.data.data;
		    that.setData({keyword:keyword});

		});

		
	},
	click:function(e)
	{
        var keywords = e.currentTarget.dataset.word;
        wx.navigateTo({
            url: "../../../../goods/list/list?keywords=" + keywords
        });
	}
})