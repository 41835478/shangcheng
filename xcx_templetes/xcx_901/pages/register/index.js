const AV = require('../../utils/av-weapp.js')
var app = getApp()
var maxTime = 60 
var interval = null 
var currentTime = -1 //倒计时的事件（单位：s）  

Page({
	
	data: {
		login:false,
    time:'获取验证码'
	},
	onLoad: function (options) {
		var login = app.globalData.login;
    var that = this;
		this.setData({login:login});
    wx.getSystemInfo({
  success: function(res) {
    that.setData({height:res.windowHeight})
    
  }
})
	},
	navigateToAddress: function () {
		wx.navigateTo({
			url: '../../address/list/list'
		});
	},
	logout: function () {
		if (AV.User.current()) {
			AV.User.logOut();
			wx.showToast({
				'title': '退出成功'
			});
		} else {
			wx.showToast({
				'title': '请先登录'
			});
		}
	},
	onShow: function () {
		var that = this;
		// 获得当前登录用户
		const user = AV.User.current();
		// 调用小程序 API，得到用户信息
		wx.getUserInfo({
			success: ({userInfo}) => {
				that.setData({
					userInfo: userInfo
				});
			}
		});
	},
	chooseImage: function () {
		var that = this;
		wx.chooseImage({
			count: 1, // 默认9
			sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
			sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
			success: function (res) {
				// 返回选定照片的本地文件路径列表，tempFilePath可以作为img标签的src属性显示图片
				var tempFilePath = res.tempFilePaths[0];
				new AV.File('file-name', {
					blob: {
						uri: tempFilePath,
					},
				}).save().then(
				// file => console.log(file.url())
					function(file) {
						// 上传成功后，将所上传的头像设置更新到页面<image>中
						var userInfo = that.data.userInfo;
						userInfo.avatarUrl = file.url();
						that.setData({
							userInfo, userInfo
						});
					}
				).catch(console.error);
			}
		})
	},
	navigateToAddressAboutus: function () {
		wx.navigateTo({
			url: '/pages/member/aboutus/aboutus'
		});
	},


	turnTologin: function (e) {
    //转为登录
    this.setData({
      ifLogup: false
    });
    this.data.email = '';
    this.data.name = '';
    this.data.password = '';
    this.data.passwordSure = '';
  },
  turnTologup: function (e) {
    this.setData({
      ifphone: false,
      ifLogup: true,
      num: '',
    });
    this.data.name = '';
    this.data.email = '';
    this.data.phoneNum = '';
    this.data.password = '';
  },
  turnto_phone: function (e) {
    this.setData({
      ifphone: true,
    })
  },

  tap_logups(e) {





    wx.switchTab({
      url: '/pages/index/index'
    });
  },
  tap_logup(e) {


    if (this.data.email == "") {
      wx.showToast({
        title: "请输入您的邮箱",
        duration: 1200,
        icon: "loading",
      });
      //this.data.placeholder.
    } else if (this.data.email.slice(-4) != ".com" || this.data.email.indexOf('@') < 0) {
      wx.showToast({
        title: "您输入的邮箱不合法",
        duration: 1200,
        icon: "loading"
      });
      this.setData({
        warn: {
          warn_email: "color:rgb(241,1,25);",
        },
      });
    } else if (this.data.password == "") {
      wx.showToast({
        title: "请设置登陆密码",
        duration: 1200,
        icon: "loading",
      })
    } else if (this.data.password == this.data.passwordSure) {
      //将inform上传至数据库
      var user = new AV.User();
      var that = this;
      user.setUsername(this.data.name);
      user.setEmail(this.data.email);
      user.setPassword(this.data.password);
      user.signUp().then(function (loginedUser) {
        app.iflogup = true;
        wx.showToast({
          title: '',
          icon: 'loading'
        });
        wx.redirectTo({
          url: '../main/main?usrid=' + loginedUser.id
        })
      }, function (error) {
        switch (error.code) {
          case 203:
            wx.showToast({
              title: "您已注册过，请登录",
              icon: "loading",
            });
            that.turnTologin();
            break;
          case 202:
            wx.showToast({
              title: "此用户名已被注册",
              icon: "loading",
            });
            that.setData({
              warn: {
                warn_name: "color:rgb(241,1,25);",
              },
            });
            break;
          case 214:
            wx.showToast({
              title: "您的手机已注册过请登录",
              icon: "loading",
            });
            break;
        };
      });
    } else {
      wx.showToast({
        title: "两次输入的密码不一致",
        duration: 1200,
        icon: "loading",
      });
      this.setData({
        warn: {
          warn_passwordSure: "color:rgb(241,1,25);",
        },
      });
    }
  },
  tap_login: function () {
    var user_login = new AV.User();
    var that = this;
    if (this.data.name == '') {
      wx.showToast({
        title: "请输入注册邮箱",
        duration: 1500,
        icon: "loading"
      });
    } else if (this.data.password == '') {
      wx.showToast({
        title: "请输入密码",
        duration: 1500,
        icon: "loading"
      })
    }
    /*邮箱+密码登陆不行 leancloud没有提供这个接口
     else if(this.data.num.indexOf(".com",0)>0){
       //user_login.setEmail(this.data.num);
       user_login.set("email",this.data.num);
       user_login.setPassword(this.data.password);
       user_login.logIn().then(
         function(loginedUser){
           console.log("success_mail");console.log(loginedUser);
           var getname=loginedUser.getUseName();
           console.log(getname);
           var getemail=loginedUser.getEmail();
           console.log(getemail);
         },
         function(error){
           console.log(error);
           if(error.code=='205'){
             wx.showToast({
               img:'../../images/remind.png', 
               title:"查无此用户",
               duration:1500,
               titleClassName:'my_toast_title'
             });
           }else if(error.code=='216'){
             AV.User.requestEmailVerfiy('abc@xyz.com').then(
               function (result) {
                 console.log(JSON.stringify(result));
                 }, 
                 function (error) {
                   console.log(JSON.stringify(error));
             });
             wx.showToast({
               img:'../../images/remind.png', 
               title:"请先验证邮箱",
               duration:1500,
               titleClassName:'my_toast_title'
             });
           }else if(error.code=='210'){
             wx.showToast({
               img:'../../images/remind.png', 
               title:"密码错误",
               duration:1500,
               titleClassName:'my_toast_title'
             });
             that.setData({
               warn:{
                 warn_passwordSure:"color:rgb(241,1,25);",
               },
             });
           };
         })
     }
   */
    else {
      user_login.setUsername(this.data.name);
      user_login.setPassword(this.data.password);
      user_login.logIn().then(
        function (loginedUser) {
          wx.showToast({
            title: '',
            icon: 'loading'
          });
          var userid = loginedUser.id;
          //匹配成功后跳转界面
          wx.redirectTo({
            url: '../main/main?usrid=' + userid,
          })
        },
        function (error) {
          console.log('error.code'); console.log(error.code);
          if (error.code == '210') {
            wx.showToast({
              title: "密码错误",
              duration: 1500,
              icon: "loading",
            });
            that.setData({
              warn: {
                warn_passwordSure: "color:rgb(241,1,25);",
              },
            });
          } else if (error.code == '211') {
            wx.showToast({
              title: "该邮箱还未注册，请先注册",
              duration: 2200,
              icon: "loading"
            });
            that.setData({
              warn: {
                warn_name: "color:rgb(241,1,25);",
              },
              ifLogup: true,
            })
          } else if (error.code == '216') {
            wx.showToast({
              icon: "loading",
              title: "请先验证邮箱",
              duration: 2000,
            });
            //往邮箱中发送验证邮件
            AV.User.requestEmailVerify(that.data.email).then(
              function (result) {
              },
              function (error) {
                if (error.code == '1') {
                  wx.showToast({
                    title: "今日往此邮箱发送的邮件数已超上限",
                    duration: 2000,
                    icon: "loading"
                  });
                }
              });
          } else if (error.code == '219') {
            that.setData({
              warn: {
                warn_passwordSure: "color:rgb(241,1,25);",
              },
            });
            wx.showToast({
              title: "登录失败次数超过限制，请稍候再试，或通过忘记密码重设密码",
              duration: 4000,
              icon: "loading",
            });
          }
        })
    }
  },
  getnum: function (e) {
    var that = this;
    
    if (parseInt(that.data.phoneNum).toString().length == 11) {
      
      that.reSendPhoneNum();
      return ;
      wx.request({
        url: 'https://newdemo.coolhong.com/index.php/Api/Shipper/getCaptcha.html',
        data: { phone: that.data.phoneNum },
        method: 'POST', // OPTIONS, GET, HEAD, POST, PUT, DELETE, TRACE, CONNECT
        // header: {}, // 设置请求的 header
        success: function (res) {
          // success
          
          wx.showToast({
            title: res.data.msg,
            icon: 'success',
          });
        },
        fail: function () {
          // fail
        },
        complete: function () {
          // complete
        }
      })
    } else {
      wx.showToast({
        title: "请输入正确的手机号",
        icon: "loading"
      })
    }
  },
  inputNum: function (e) {
    this.data.num = e.detail.value;
  },
  quick_register_phone: function (e) {
    
    var that = this;
    if (parseInt(this.data.num).toString().length == 4) {
      
      console.log('https://newdemo.coolhong.com/index.php/Api/User/register?phone=' + this.data.phoneNum + "&num=" + this.data.num + "&openid=" + app.globalData.openid + "&pass=" + this.data.pass + "&remindpass=" + this.data.remindpass + "&nickName=" + app.globalData.nickName);
      wx.request({
        url: 'https://newdemo.coolhong.com/index.php/Api/User/register?phone=' + this.data.phoneNum + "&num=" + this.data.num + "&openid=" + app.globalData.openid + "&pass=" + this.data.pass + "&remindpass=" + this.data.remindpass + "&nickName=" + app.globalData.nickName,
        method: 'GET', // OPTIONS, GET, HEAD, POST, PUT, DELETE, TRACE, CONNECT
        // header: {}, // 设置请求的 header
        success: function (res) {
    
    //
          if (res.data.code == 200) {
            app.globalData.login = true;
            app.globalData.userInfo = res.data.res;
            that.setData({login:true});
        
            wx.showToast({
              title: res.data.msg,
              icon: 'success',
            });

            timeout = setTimeout(function doHandler()
       {
            wx.switchTab({
              url: '/pages/index/index'
            });
			
        },2000);//使用字符串执行方法



          }
          else
            wx.showToast({
              title: res.data.msg,
              icon: 'error',
            });

        },
        fail: function () {
          // fail
        },
        complete: function () {
          // complete
        }
      })

    } else {
      wx.showToast({
        title: "无效的验证码",
        duration: 1500,
        icon: "loading"
      })
    }


  },
  //短信验证码验证
  quick_login_phone: function (e) {
    var that = this;
    if (parseInt(this.data.num).toString().length == 4) {
      
      console.log('https://newdemo.coolhong.com/index.php/Api/User/validate?phone=' + this.data.phoneNum + "&num=" + this.data.num + "&openid=" + app.globalData.openid);
      wx.request({
        url: 'https://newdemo.coolhong.com/index.php/Api/User/validate?phone=' + this.data.phoneNum + "&num=" + this.data.num + "&openid=" + app.globalData.openid,
        data: { 'phone': this.data.phoneNum, 'num': this.data.num },
        method: 'GET', // OPTIONS, GET, HEAD, POST, PUT, DELETE, TRACE, CONNECT
        // header: {}, // 设置请求的 header
        success: function (res) {

          if (res.data.code == 200) {
            app.globalData.login = true;
            that.setData({login:true});
            wx.switchTab({
              url: '/pages/index/index'
            });

            wx.showToast({
              title: res.data.msg,
              icon: 'success',
            });
          }
          else
            wx.showToast({
              title: res.data.msg,
              icon: 'error',
            });

        },
        fail: function () {
          // fail
        },
        complete: function () {
          // complete
        }
      })

    } else {
      wx.showToast({
        title: "无效的验证码",
        duration: 1500,
        icon: "loading"
      })
    }
  },
  
  getPassword: function (e) {
    this.setData({
      password: e.detail.value,
      warn: {
        warn_passwordSure: '',
      }
    })
    this.data.password = e.detail.value;
  },
  getEmail: function (e) {
    this.data.email = e.detail.value;
    this.data.name = e.detail.value;
    this.setData({
      warn: {
        warn_email: "",
      },
    });
  },
  passwordSure: function (e) {
    if (e.detail.value === this.data.password)
      this.data.passwordSure = e.detail.value;
    this.setData({
      warn: {
        warn_passwordSure: "",
      },
    });
  },
  getPhoneNum: function (e) {
    this.setData({
      phoneNum: e.detail.value,
    });
  },
  inputRemindPass: function (e) {
    this.setData({
      remindpass: e.detail.value,
    });
  },
  inputPass: function (e) {
    this.setData({
      pass: e.detail.value,
    });
  },

  input_num: function (e) {
    this.data.num = e.detail.value;
  },
  //重置密码";
  forgetPassword: function (e) {
    var that = this;
    AV.User.requestPasswordReset(this.data.email).then(
      function (success) {
        wx.showToast({
          title: '密码重置邮件已发送，请在邮件中重置密码',
          icon: 'success',
          duration: 5000,
        });
      },
      function (error) {
        console.log(error); console.log(error.code);
        if (error.code == '1') {
          wx.showToast({
            title: "今日往此邮箱发送的邮件数已超上限",
            duration: 2000,
            icon: "loading",
          });
        } else if (error.code == '204') {
          wx.showToast({
            title: "请先输入注册邮箱",
            duration: 1200,
            icon: "loading",
          });
        } else if (error.code == '205') {
          wx.showToast({
            title: "您还没注册哦",
            duration: 1200,
            icon: "loading",
          });
        }
      });
  },
  reSendPhoneNum: function(){  
        if(currentTime < 0){  
            var that = this  
            currentTime = maxTime  
            interval = setInterval(function(){  
                currentTime--  
                that.setData({  
                    time : currentTime+"s"  
                })  
  
                if(currentTime <= 0){  
                    currentTime = -1  
                    clearInterval(interval)
                    that.setData({  
                    time : '获取验证码'  
                })  
                }  
            }, 1000)  
        }else{  
            wx.showToast({  
                title: '短信已发到您的手机，请稍后重试!',  
                icon : 'loading',  
                duration : 700  
            })  
        }  
    }  

})



