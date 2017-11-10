var server = require('../../utils/server');
var QQMapWX = require('../../utils/qqmap-wx-jssdk.js');
var server = require('../../utils/server');
var seat;
var isLoc = false;
Page({
	data: {
		"address":"定位中",
    title: "欢迎光临",
    desc: "这里的宝贝不错噢",
		banner: [],
		goods: [],
		bannerHeight: Math.ceil(290.0 / 750.0 * getApp().screenWidth)
	},

	showMenu: function (e) {
    var url = e.currentTarget.dataset.url;
    var is_tabbar = e.currentTarget.dataset.is_tabbar;
    if (is_tabbar ==1 )
      wx.switchTab({
        url: "../" + url
      });
    else{
      wx.navigateTo({
        url: "../" + url
      });
    }
	},
	search: function (e) {
		wx.navigateTo({
			url: "../search/index"
		});
	},

	onLoad: function (options) {
		//seat = options.seat;
		//wx.showToast({title:seat+"seat"});
		//
		//this.loadMainGoods();

		var app = getApp();
    app.getInviteCode(options);
		app.getOpenId(function () {

			var openId = getApp().globalData.openid;

            server.getJSON("/User/validateOpenid",{openid:openId},function(res){

				if (res.data.code == 200) {
						getApp().globalData.userInfo = res.data.data;
						getApp().globalData.login = true;
						//wx.switchTab({
						//url: '/pages/index/index'
						//});
					}
					else{
						if (res.data.code == '400') {
						console.log("need register");
						app.register(function () {
               getApp().globalData.login = true;
						});
					  }

					}

			});

		});	
	},

  onPullDownRefresh: function () {
    this.loadBanner();
  },

	
	loadBanner: function () {
		var that = this;
   var city = that.data.address;
	 city = encodeURI(city);
        server.getJSON("/Index/home",{city:that.data.address},function(res){
        if (res.data.status == 1) {
            var banner = res.data.result.ad;
            var goods = res.data.result.goods;
            var ad = res.data.ad;
            var menu_list = res.data.result.menu_list;
            var xcxconfig = res.data.result.xcxconfig;
            wx.stopPullDownRefresh();
            that.setData({
              banner: banner,
              goods: goods,
              ad: ad,
              menu_list: menu_list,
              xcxconfig: xcxconfig,
              title: xcxconfig.title,
              desc: xcxconfig.brief,
              copyright: xcxconfig.copyright
            
            });
        }else{
          wx.showToast({
            title: res.data.msg,
            duration: 3000
          });
        }
        });

		
		
	},
	loadMainGoods: function () {
		var that = this;
		var query = new AV.Query('Goods');
		query.equalTo('isHot', true);
		query.find().then(function (goodsObjects) {
			that.setData({
				goods: goodsObjects
			});
		});
	},
	onShow:function(){
    var app = getApp();
		var self = this;

    /*if (getApp().globalData.login==false) {
      console.log("need register");
      app.register(function () {
        getApp().globalData.login = true;
      });
    }*/

		if (isLoc) {
      var address = getApp().globalData.city;
      this.setData({address:address});
      self.loadBanner();
      return;
		}
		wx.getLocation({
			type: 'gcj02',
			success: function (res) {
				var latitude = res.latitude;
				var longitude = res.longitude;

				app.globalData.lat = latitude;
				app.globalData.lng = longitude;

				// 实例划API核心类
				var map = new QQMapWX({
					key: 'LAWBZ-2CHCD-MCK4X-PSTUA-NJZJJ-IHFQ2' // 必填
				});
				////address: res.result.address_component.city
				// 调用接口
				map.reverseGeocoder({
					location: {
						latitude: latitude,
						longitude: longitude
					},
					success: function (res) {
						console.log(res);

						if (res.result.ad_info.city != undefined) {
							self.setData({

								address: res.result.ad_info.city
							});
              getApp().globalData.city = res.result.ad_info.city;
							isLoc = true;
							self.loadBanner();
						}
					},
					fail: function (res) {
						console.log(res);
            
					},
					complete: function (res) {
						console.log(res);
					}
				});
			},
      fail: function (res) {
        self.loadBanner();
      }

		})



	},
	clickBanner: function (e) {

    var url = e.currentTarget.dataset.url;
		wx.navigateTo({
      url: "../" + url
		});
	},

	showDetail: function (e) {
		var goodsId = e.currentTarget.dataset.goodsId;
		wx.navigateTo({
			url: "../goods/detail/detail?objectId=" + goodsId
		});
	},



  onShareAppMessage: function (res) {
    var that = this;
    var user_id = getApp().globalData.userInfo.user_id;
    var open_id = getApp().globalData.userInfo.open_id;

    return {
      title: that.data.title,
      desc: that.data.desc,
      path: '/pages/index/index?uid=' + user_id,
      success: function (res) {
        server.postJSON('/Share/getShare/user_id/' + user_id + '/type/Index', { user_id:user_id,open_id: open_id}, function (res) {
          var result = res.data.result;
        
          wx.showModal({
            title: '提示',
            showCancel: false,
            confirmText: '我知道了',
            content: result,
          })
        })
      },
      fail: function (res) {
        wx.showModal({
          title: '提示',
          showCancel: false,
          confirmText: '我知道了',
          content: '您取消了分享！',

        })
        // 转发失败
      }
		}
	},
	select:function(){
		wx.navigateTo({
			url: '../switchcity/switchcity',
			success: function(res){
				// success
			},
			fail: function(res) {
				// fail
			},
			complete: function(res) {
				// complete
			}
		})
	}
})