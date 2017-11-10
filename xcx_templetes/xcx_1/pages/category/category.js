var server = require('../../utils/server');
Page({
    data: {
        topCategories: [],
        subCategories: [],
        highlight: ['highlight', '', '', '', '', '', '', '', '', '', '', ''],
        banner: '',
        parent_id:'',
        cat_name:''
    },
    onLoad: function () {
        this.getTopCategory();
       
    },
    onPullDownRefresh: function () {
      this.getTopCategory();
    },

    tapTopCategory: function (e) {
        // 拿到objectId，作为访问子类的参数
        var objectId = e.currentTarget.dataset.id;
        var banner_name = e.currentTarget.dataset.banner;
        
        var index = parseInt(e.currentTarget.dataset.index);
        this.setHighlight(index);
        
        this.getCategory(objectId);
        this.getBanner(objectId, banner_name);

    },
    
    clickBanner: function (e) {

      var url = e.currentTarget.dataset.url;
		wx.navigateTo({
      url: "../" + url
		});
	},
    getTopCategory: function (parent) {
        var that = this;
        server.getJSON("/Goods/goodsCategoryList", { syskey: getApp().globalData.syskey },function(res){
          var categorys = res.data.result;
          wx.stopPullDownRefresh();
          that.setData({
            topCategories: categorys
          });
          if (categorys){
            that.getCategory(categorys[0].id);
            that.getBanner(categorys[0].id, categorys[0].mobile_name);
          }
        });
    },
    getCategory: function (parent) {
        var that = this;


        server.getJSON('/Goods/goodsCategoryList/parent_id/' + parent, { syskey: getApp().globalData.syskey },function(res){
var categorys = res.data.result;
                that.setData({
                    subCategories: categorys,
                    parent_id: parent
                });
       });
    },
    setHighlight: function (index) {
        var highlight = [];
        for (var i = 0; i < this.data.topCategories; i++) {
            highlight[i] = '';
        }
        highlight[index] = 'highlight';
        this.setData({
            highlight: highlight
        });
    },
    avatarTap: function (e) {
        // 拿到objectId，作为访问子类的参数
        var objectId = e.currentTarget.dataset.objectId;
        wx.navigateTo({
            url: "../../../../goods/list/list?categoryId=" + objectId
        });
    },
    getBanner: function (cat_id,banner_name) {

        
      var that = this;
      that.setData({
        cat_name: banner_name
      });
      banner_name = encodeURI(banner_name);

      server.getJSON('/goods/categoryBanner/cat_id/' + cat_id,function(res){
        var got_banner = res.data.status;//prince 20180807
        var banner = res.data.banner;
                that.setData({
                    banner: banner,
                    got_banner: got_banner  //prince 20180807
                });
                //console.log(res);
       });
    }
})
