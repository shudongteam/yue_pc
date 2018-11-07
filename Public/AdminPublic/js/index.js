// JavaScript Document


	//简洁菜单点击效果
	/*
	$(function(){
		$(".j_menu_list").hide();
		$(".j_a_list").click(function(){
			var len = $('.j_a_list').length;
			var index = $(".j_a_list").index(this);
			for(var i=0;i<len;i++){
				if(i == index){
					$('.j_menu_list').eq(i).slideToggle(300);
					}else{
						$('.j_menu_list').eq(i).slideUp(300);
					}
				}
		});
		$(".j_menu_list>span>i").click(function(){
			$(".j_menu_list").slideUp(300)	
		})
	})*/
	//简洁菜单移动效果
	$(function(){
		$(".j_menu_list").hide();
		$(".j_a_list").hover(function(){
			$(".leftmenu2 ul li").hover(function(){
				$(this).find('.j_menu_list').show();	
			},function(){
				$(this).find('.j_menu_list').hide();
			});
		})
	})
	
	