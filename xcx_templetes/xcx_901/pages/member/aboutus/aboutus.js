var server = require('../../../utils/server');

Page({
  data: {
    aboutus:[],
  },

	onLoad: function (options) {
    this.getAboutUs();
	},

  getAboutUs: function () {
    var that = this;

    server.getJSON('/User/getXcxConfig/', function (res) {
      var datas = res.data.result;
      that.setData({
        aboutus: datas
      });

    });
  },


});