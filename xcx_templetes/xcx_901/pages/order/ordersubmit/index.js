// pages/order/ordersubmit/index.js
var server = require('../../../utils/server');
var tp;
var pay_points;
var points_rate;
Page({
  data: { use_money: 0, use_point: 0, check: ['true', ''], "coupon": [], cv: '请选择优惠劵', cpos: -1, "couponCode": '' },
  addressSelect: function () {
    wx.navigateTo({
      url: '../../address/select/index'
    });
  },
  bindChange: function (e) {

    var use_money = e.detail.value;

    this.setData({
      use_money: use_money,
    });
  },
  bindChangeOfcoupon: function (e) {
    var couponCode = e.detail.value;

    this.setData({
      couponCode: couponCode,
    });
  },
  bindChangeOfPoint: function (e) {
    var use_point = e.detail.value;
    this.setData({
      use_point: use_point,
    });
  },
  bindPickerChange: function (e) {
    var value = e.detail.value;
    var cv = this.data.coupon[value];
    this.setData({ cv: cv, cpos: value });



    this.useCoupon();


  },
  useCoupon: function () {
    if (this.data.cpos == -1)
      return;
    var money = this.data.couponList[this.data.cpos].money;
    var totalObj = this.data.totalPrice;
    totalObj.total_fee = totalObj.total_fee - money
    if (totalObj.total_fee < 0)
      totalObj.total_fee = 0;
    this.setData({ totalPrice: totalObj });
  },
  use: function () {
    //totalPrice:
    var user_money = getApp().globalData.userInfo.user_money;
    var use_money = this.data.use_money;
    if (use_money == "0" || use_money == 0 || use_money == "" || use_money == undefined) {
      wx.showToast({
        title: '请输入余额',
        duration: 1000
      });
      return;
    }
    user_money = parseFloat(user_money)
    use_money = parseFloat(use_money)

    var use_point = this.data.use_point;
    use_point = parseInt(use_point)




    if (use_money < 0) {
      wx.showToast({
        title: '请输入大于0元的金额',
        duration: 1000
      });
      return;
      return;
    }
    if (isNaN(use_money)) {
      wx.showToast({
        title: '输入余额有误',
        duration: 1000
      });
      return;
    }
    if (use_point < 0) {
      use_point = 0;
    }
    if (isNaN(use_point)) {
      use_point = 0;
    }

    use_point = use_point - use_point % parseInt(points_rate);
    var user_point = pay_points;
    if (parseInt(user_point) < use_point) {
      use_point = 0;
    }


    if (user_money < use_money) {
      var totalObj = this.data.totalPrice;





      use_point = use_point - use_point % parseInt(points_rate);
      var m = tp - use_point / parseInt(points_rate)
      totalObj.total_fee = m
      this.setData({ totalPrice: totalObj });

      this.useCoupon();

      this.setData({ use_money: 0 });
      wx.showToast({
        title: '请输入小余当前余额',
        duration: 1000
      });
      return;
    }

    use_point = use_point - use_point % parseInt(points_rate);
    var m = tp - use_point / parseInt(points_rate)

    var totalPrice = m - use_money;
    if (totalPrice < 0)
      totalPrice = 0;
    var totalObj = this.data.totalPrice;
    totalObj.total_fee = totalPrice
    this.setData({ totalPrice: totalObj });

    this.useCoupon();
  },
  use_point: function () {
    //totalPrice:
    var user_point = pay_points;
    var use_point = this.data.use_point;

    if (use_point == "0" || use_point == 0 || use_point == "" || use_point == undefined) {
      wx.showToast({
        title: '请输入积分',
        duration: 1000
      });
      return;
    }


    use_point = parseInt(use_point)

    if (use_point < 0) {
      wx.showToast({
        title: '请输入大于0元的积分',
        duration: 1000
      });
      return;
      return;
    }
    if (isNaN(use_point)) {
      wx.showToast({
        title: '输入积分有误',
        duration: 1000
      });
      return;
    }

    var use_money = this.data.use_money;
    use_money = parseInt(use_money);
    if (use_money < 0) {
      use_money = 0;
    }
    if (isNaN(use_money)) {
      use_money = 0;
    }
    var user_money = getApp().globalData.userInfo.user_money;
    if (user_money < use_money) {
      use_money = 0;
    }

    
    if (parseInt(user_point) < use_point) {

      var totalObj = this.data.totalPrice;
      var m = tp - use_money;
      totalObj.total_fee = m
      this.setData({ totalPrice: totalObj });

      this.setData({ use_point: 0 });
      this.useCoupon();
      wx.showToast({
        title: '请输入小余当前积分',
        duration: 1000
      });
      return;
    }

    use_point = use_point - use_point % parseInt(points_rate);
    var m = tp - use_money;
    var totalPrice = m - (use_point / parseInt(points_rate));
    if (totalPrice < 0)
      totalPrice = 0;
    var totalObj = this.data.totalPrice;
    totalObj.total_fee = totalPrice
    this.setData({ totalPrice: totalObj });
    this.useCoupon();
  },
  onShow: function () {
    var app = getApp();
    var cartIds = app.globalData.cartIds;
    var amount = app.globalData.amount;
    this.setData({ cartIds: cartIds, amount: amount });



    this.getCarts(cartIds);
    // 页面初始化 options为页面跳转所带来的参数
  },
  initData: function () {
    var app = getApp();
    pay_points = app.globalData.userInfo.pay_points;
    var user_money = app.globalData.userInfo.user_money;
    this.setData({ freemoney: user_money, pay_points: pay_points });
  },
  formSubmit: function (e) {
    // user 
    var address_id = this.data.address.address_id
    var user_id = getApp().globalData.userInfo.user_id
    var use_money = this.data.use_money
    var pay_points = this.data.use_point
    var that = this;
    var app = getApp();
    var couponTypeSelect = this.data.check[0] == "true" ? 1 : 2;
    var coupon_id = 0;
    if (this.data.cpos != -1) {
      coupon_id = this.data.couponList[this.data.cpos].id;
    }
    var couponCode = this.data.couponCode;

    server.getJSON('/Cart/cart3/act/submit_order/user_id/' + user_id + "/address_id/" + address_id + "/user_money/" + use_money + "/pay_points/" + pay_points + "/couponTypeSelect/" + couponTypeSelect + "/coupon_id/" + coupon_id + "/couponCode/" + couponCode, function (res) {


      if (res.data.status != 1) {
        wx.showToast({
          title: res.data.msg,
          duration: 2000
        });
        return;
      }

      var result = res.data.result
      app.globalData.wxdata = res.data.data
      app.globalData.order = res.data.order
      if (res.data.status == 1) {


        wx.showToast({
          title: '提交成功',
          duration: 1000
        });



        setTimeout(function () {

          if (res.data.order.pay_status == 1) {
            wx.switchTab({
              url: "../../member/index/index"
            });
            return;
          }

          wx.navigateTo({
            url: '../payment/payment?order_id=' + result
          });
        }, 1000);

      }


    });
  },

  getCarts: function (cartIds) {
    var user_id = getApp().globalData.userInfo.user_id
    var that = this
    var app = getApp()

    server.getJSON('/Cart/cart2/user_id/' + user_id, function (res) {
      if(res.data.status == -2){
        wx.navigateBack({
          delta: 1, // 回退前 delta(默认为1) 页面
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
        return;
      }
      var user_data = app.globalData.userInfo;
      user_data.user_money = res.data.result.userInfo.user_money;
      user_data.pay_points = res.data.result.userInfo.pay_points;
      app.globalData.userInfo = user_data
      var address = res.data.result.addressList
      var cartList = res.data.result.cartList
      var enable_coupon = res.data.result.enable_coupon
      var enable_balance = res.data.result.enable_balance
      var enable_point = res.data.result.enable_point

      var userInfo = res.data.result.userInfo
      var totalPrice = res.data.result.totalPrice
      tp = totalPrice.total_fee
      points_rate = res.data.result.points
      that.setData({ address: address, cartList: cartList, userInfo: userInfo, totalPrice: totalPrice, enable_coupon: enable_coupon, enable_balance: enable_balance, enable_point: enable_point  });

      var couponList = res.data.result.couponList
      var ms = that.data.coupon
      for (var i in couponList) {
        ms.push(couponList[i].name);
      }
      that.setData({ coupon: ms, couponList: couponList });
      that.initData();
    })
  },
  check1: function () {
    this.setData({ check: ['true', ''] });
  },
  check2: function () {
    this.setData({ check: ['', 'true'] });
  },
  onReady: function () {
    // 页面渲染完成
  },

  onHide: function () {
    // 页面隐藏
  },
  onUnload: function () {
    // 页面关闭
  }
})