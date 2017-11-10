var server = require('../../utils/server');
var categoryId
var keywords
var cPage = 0;
var gsort = "shop_price";
var asc = "desc";
var store_id;
// 使用function初始化array，相比var initSubMenuDisplay = [] 既避免的引用复制的，同时方式更灵活，将来可以是多种方式实现，个数也不定的
function initSubMenuDisplay() {
	return ['hidden', 'hidden', 'hidden', 'hidden'];
}

//定义初始化数据，用于运行时保存
var initSubMenuHighLight = [
		['highlight','','','',''],
		['',''],
		['','',''],[]
	];

Page({
	data:{
		menu:["highlight","","",""],
		subMenuDisplay:initSubMenuDisplay(),
		subMenuHighLight:initSubMenuHighLight,
    sort: [['shop_price-desc', 'shop_price-asc'], ['click_count-desc', 'click_count-asc'], ['is_new-desc', 'is_new-asc'], 'is_promote-asc'],
		goods: [],
		empty:false
	},
	search:function(e){

        keywords = this.data.keywords;
		cPage = 0;
		this.data.goods = [];
        this.getGoodsByKeywords(keywords,cPage,gsort+"-"+asc);
		
	},
	bindChange: function(e) {
		
		var keywords = e.detail.value;
		
		this.setData({
			keywords: keywords
		});
	},
	onLoad: function(options){
		categoryId = options.categoryId;
		store_id = options.id;
		keywords = options.keywords;
		// 生成Category对象
        //var category = AV.Object.createWithoutData('Category', categoryId);
        //this.category = category;
 
		if(!keywords)
        this.getGoods(categoryId, 0,this.data.sort[0][0]);
		else
		this.getGoodsByKeywords(keywords,0,this.data.sort[0][0]);
	},
	getGoodsByKeywords: function(keywords,page,sort){
		
		var that = this;
		var sortArray = sort.split('-');
		gsort = sortArray[0];
		asc = sortArray[1];


        server.getJSON('/Store/storeGoods',{store_id:store_id,p:page,sort:sortArray[0],sort_asc:sortArray[1],key:keywords},function(res){

// success
			var newgoods = res.data.result.goods_list
         
		    var ms = that.data.goods
            for(var i in newgoods){
               ms.push(newgoods[i]);
            }

    if(ms.length == 0)
	{
		that.setData({
                empty: true
            });
	}
    else 
	that.setData({
                empty: false
            });
      wx.stopPullDownRefresh();

			that.setData({
                goods: ms
            });


		});
        
	},

	getGoods: function(category, pageIndex,sort){
		var that = this;
		var sortArray = sort.split('-');
		gsort = sortArray[0];
		asc = sortArray[1];


        server.getJSON('/Store/storeGoods',{store_id:store_id,p:pageIndex,sort:sortArray[0],sort_asc:sortArray[1]},function(res){

// success
			var newgoods = res.data.result.goods_list
         
		    var ms = that.data.goods
            for(var i in newgoods){
               ms.push(newgoods[i]);
            }

    if(ms.length == 0)
	{
		that.setData({
                empty: true
            });
	}
    else 
	that.setData({
                empty: false
            });
      wx.stopPullDownRefresh();

			that.setData({
                goods: ms
            });


		});

	},


	tapGoods: function(e) {
		var objectId = e.currentTarget.dataset.objectId;
		wx.navigateTo({
			url:"../goods/detail/detail?objectId="+objectId
		});
	},
	tapMainMenu: function(e) {
//		获取当前显示的一级菜单标识
		var index = parseInt(e.currentTarget.dataset.index);
		// 生成数组，全为hidden的，只对当前的进行显示
		var newSubMenuDisplay = initSubMenuDisplay();
//		如果目前是显示则隐藏，反之亦反之。同时要隐藏其他的菜单
		if(this.data.subMenuDisplay[index] == 'hidden') {
			newSubMenuDisplay[index] = 'show';
		} else {
			newSubMenuDisplay[index] = 'hidden';
		}

        var menu = ["","","",""];
        menu[index] = "highlight";

       if(index == 3)
	   {
		   this.setData({
                goods: []
            });
		cPage = 0;
		   if(!keywords)
        this.getGoods(categoryId, 0,this.data.sort[index]);
		else
		this.getGoodsByKeywords(keywords, 0,this.data.sort[index]);
	   }

		// 设置为新的数组
		this.setData({
			menu:menu,
			subMenuDisplay: newSubMenuDisplay
		});
	},
	tapSubMenu: function(e) {
		// 隐藏所有一级菜单
		this.setData({
			subMenuDisplay: initSubMenuDisplay()
		});
		// 处理二级菜单，首先获取当前显示的二级菜单标识
		var indexArray = e.currentTarget.dataset.index.split('-');
		// 初始化状态
		// var newSubMenuHighLight = initSubMenuHighLight;
		for (var i = 0; i < initSubMenuHighLight.length; i++) {
			// 如果点中的是一级菜单，则先清空状态，即非高亮模式，然后再高亮点中的二级菜单；如果不是当前菜单，而不理会。经过这样处理就能保留其他菜单的高亮状态
			//if (indexArray[0] == i) {
				for (var j = 0; j < initSubMenuHighLight[i].length; j++) {
					// 实现清空
					initSubMenuHighLight[i][j] = '';
				}
				// 将当前菜单的二级菜单设置回去
			//}
		}
        this.setData({
                goods: []
            });
		cPage = 0;
		if(!keywords)
        this.getGoods(categoryId, 0,this.data.sort[indexArray[0]][indexArray[1]]);
		else
		this.getGoodsByKeywords(keywords, 0,this.data.sort[indexArray[0]][indexArray[1]]);

		// 与一级菜单不同，这里不需要判断当前状态，只需要点击就给class赋予highlight即可
		initSubMenuHighLight[indexArray[0]][indexArray[1]] = 'highlight';
		// 设置为新的数组
		this.setData({
			subMenuHighLight: initSubMenuHighLight
		});
	},
	onReachBottom: function () {
		if(!keywords)
		this.getGoods(categoryId, ++cPage,gsort+"-"+asc);
		else
		this.getGoodsByKeywords(keywords, ++cPage,gsort+"-"+asc);
		wx.showToast({
		  title: '加载中',
		  icon: 'loading'
		})
	},
	onPullDownRefresh: function () {
		this.setData({
                goods: []
            });
		cPage = 0;
		if(!keywords)
		this.getGoods(categoryId, cPage,gsort+"-"+asc);
		else
		this.getGoodsByKeywords(keywords, cPage,gsort+"-"+asc);
	}
});