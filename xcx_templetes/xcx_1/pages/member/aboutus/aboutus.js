var server = require('../../../utils/server');

Page({
  data: {
    aboutus:[],
  },

	onLoad: function (options) {
    this.getAboutUs();
	},

  onShow: function (options) {
    this.getAboutUs();
  },

  getAboutUs: function () {
    var that = this;
    var syskey = getApp().globalData.syskey

    server.getJSON('/User/getXcxConfig/syskey/' + syskey, function (res) {
      var datas = res.data.result;
      that.setData({
        aboutus: datas
      });

    });
  },
  address: function () {
    var that = this;
    var latitude = that.data.aboutus.latitude ? that.data.aboutus.latitude:0;
    var longitude = that.data.aboutus.longitude ? that.data.aboutus.longitude : 0;
    var name = that.data.aboutus.shop_name;
    var address = that.data.aboutus.shop_address;

    wx.openLocation({
      latitude: parseFloat(latitude),
      longitude: parseFloat(longitude),
      name: name,
      address: address,
      scale: 25
    })
  },
  tel: function (e) {

    wx.makePhoneCall({
      phoneNumber: e.currentTarget.id 
    })
  },


});