
var server = require('./utils/server');
var md5 = require('./utils/md5.js');
var jtypkey = 'prince';   //接口授权码(今天优品提供)



// 授权登录 
App({
	onLaunch: function () {
		var that = this;

		// 设备信息
		wx.getSystemInfo({
			success: function (res) {
				that.screenWidth = res.windowWidth;
				that.pixelRatio = res.pixelRatio;
			}
		});
	},


	getOpenId: function (cb) {
		wx.login({
			success: function (res) {
				if (res.code) {
          server.getJSON("/User/getOpenid", { url: 'https://api.weixin.qq.com/sns/jscode2session?appid=123456789&secret=987654321&js_code=' + res.code + '&grant_type=authorization_code&code=' + res.code},function(res){ // 获取openId
              if (res.data.openid){
                var openId = res.data.openid;
                var session_key = res.data.session_key;
                // TODO 缓存 openId session_key
                var app = getApp();
                var that = app;
                that.globalData.openid = openId;
                that.globalData.session_key = session_key;
              }else{
                console.log(res.data.errmsg);
                wx.showToast({
                  title: res.data.errmsg,
                  duration: 3000
                });
                return false;
              }
							//验证是否关联openid
							typeof cb == "function" && cb()
					});
					//发起网络请求
				}
      }
    });


	},

	register:function(cb){
       var app = this;
       this.getUserInfo(function () {
            console.log('授权成功');
            var openId = app.globalData.openid;
            var userInfo = app.globalData.userInfo;
            var country = userInfo.country;
            var city = userInfo.city;
            var gender = userInfo.gender;
            var nick_name =  userInfo.nickName;
            var province = userInfo.province;
            var avatarUrl = userInfo.avatarUrl;
            var up_uid = app.globalData.up_uid;

            server.getJSON('/User/register?open_id=' + openId + "&country=" + country + "&gender=" + gender + "&nick_name=" + nick_name + "&province=" + province + "&city=" + city + "&head_pic=" + avatarUrl + "&up_uid=" + up_uid,function(res){
app.globalData.userInfo = res.data.res
                
                typeof cb == "function" && cb()
						});

       })
  },
getUserInfo:function(cb){
    var that = this
    if(this.globalData.userInfo){
      typeof cb == "function" && cb(this.globalData.userInfo)
    }else{
      //调用登录接口
      wx.login({
        success: function () {
          wx.getUserInfo({
            success: function (res) {
              that.globalData.userInfo = res.userInfo
              typeof cb == "function" && cb(that.globalData.userInfo)
            },
            fail: function () {
              var app = getApp();
              app.getSetting(function () {
                typeof cb == "function" && cb()
              });
            }
          })
        },

      })
    }
  },

  //强制授权
  getSetting: function (cb) {
    wx.showModal({
      showCancel: false,
      title: '提示',
      content: '小程序需要获取用户信息权限,否则无法正常使用,点击确定前往设置.',
      success: function (res) {
        if (res.confirm) {

          wx.openSetting({
            success: function (data) {
              if (data) {
                console.log(data);
                if (data.authSetting["scope.userInfo"] == true) {
                  wx.getUserInfo({
                    success: function (res) {
                      var app = getApp();
                      var that = app;
                      that.globalData.userInfo = res.userInfo;
                      typeof cb == "function" && cb(that.globalData.userInfo)
                    },
                    fail: function () {
                      console.info("授权失败返回数据");
                    }
                  });
                } else {
                  console.info("再次拒绝");
                  var app = getApp();
                  app.getSetting(function () {
                    typeof cb == "function" && cb()
                  });
                }
              }
            },
            fail: function () {
              console.info("设置失败返回数据");
            }
          });

        } else if (res.cancel) {
          console.log('您已取消')
        }
      }
    })
  },

  getInviteCode: function (options) {
    var app = this;
    if (options.uid != undefined) {
      app.globalData.up_uid = options.uid;
      wx.showToast({
        title: '来自用户:' + options.uid + '的分享',
        icon: 'success',
        duration: 2000
      })
    }
  },

	globalData: {
		'openid': null,
    'session_key': null,
		'userInfo':null,
    'up_uid':0,
		'login':false,
    'jtypkey': jtypkey
	}
})
