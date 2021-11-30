//入力・修正時の入力チェック
function chk(){
   	if(document.form2.shu1.value == ''){
   		if(document.form2.shu2.value.length == 0){
			window.confirm('トレーニング種目を入力してください。');
			return false;
   		}
	}
	if(document.form2.weight.value.length == 0){
		window.confirm('重量を入力してください。');
		return false;
	}else if(document.form2.weight.value.replace(/[0123456789.]/g,'') != ''){
		window.confirm('重量は半角数字で入力してください。');
		return false;
	}
   	if(document.form2.rep.value.length == 0){
		window.confirm('回数を入力してください。');
		return false;
	}else if(document.form2.rep.value.replace(/[0123456789]/g,'') != ''){
		window.confirm('回数は半角数字で入力してください。');
		return false;
	}
	if(document.form2.rep2.value.replace(/[0123456789]/g,'') != ''){
		window.confirm('補回数は半角数字で入力してください。');
		return false;
	}
   	if(document.form2.sets.value.length == 0){
		window.confirm('セット数を入力してください。');
		return false;
	}else if(document.form2.sets.value.replace(/[0123456789]/g,'') != ''){
		window.confirm('セット数は半角数字で入力してください。');
		return false;
	}
	
	return true;
}
function chk2(){
   	if(document.form3.shu1.value == ''){
   		if(document.form3.shu2.value.length == 0){
			window.confirm('トレーニング種目を入力してください。');
			return false;
   		}
	}
	if(document.form3.weight.value.length == 0){
		window.confirm('重量を入力してください。');
		return false;
	}else if(document.form3.weight.value.replace(/[0123456789.]/g,'') != ''){
		window.confirm('重量は半角数字で入力してください。');
		return false;
	}
   	if(document.form3.rep.value.length == 0){
		window.confirm('回数を入力してください。');
		return false;
	}else if(document.form3.rep.value.replace(/[0123456789]/g,'') != ''){
		window.confirm('回数は半角数字で入力してください。');
		return false;
	}
   	if(document.form3.sets.value.length == 0){
		window.confirm('セット数を入力してください。');
		return false;
	}else if(document.form3.sets.value.replace(/[0123456789]/g,'') != ''){
		window.confirm('セット数は半角数字で入力してください。');
		return false;
	}
	
	return true;
}

function chk3(){
	if(window.confirm('削除していいですか？')){
		return true;
	}else{
		return false;
	}
}

//modal
$(function(){
	
	$("#modal-open").click(function(){
	
		$("body").append('<div id="modal-bg"></div>');
		
		modalResize();

		$("#modal-bg").fadeIn("slow");
		$("#modal-main").fadeIn("slow");
		$("#modal-bg").click(function(){
			$("#modal-main,#modal-bg").fadeOut("slow",function(){
				$('#modal-bg').remove() ;
			});
	
		});

		$(window).resize(modalResize);
		
		function modalResize(){
	
			var w = $(window).width();
			var h = $(window).height();
			
			var cw = $("#modal-main").outerWidth();
			var ch = $("#modal-main").outerHeight();
	
			$("#modal-main").css({"left": ((w - cw)/2) + "px"});

		}
		
	});
});

jQuery(function($){
	$('#running').on('click', function () {
		$("#usanso-edit").slideToggle();

		modalResize();
		$(window).resize(modalResize);

		function modalResize(){
			var w = $(window).width();
			var h = $(window).height();
			var cw = $("#usanso-edit").outerWidth();
			var ch = $("#usanso-edit").outerHeight();
			$("#usanso-edit").css({"left": ((w - cw)/2) + "px"});
		}
	});
	
	$('#taisosiki').on('click', function () {
		$("#taisosiki-edit").slideToggle();

		modalResize();
		$(window).resize(modalResize);

		function modalResize(){
			var w = $(window).width();
			var h = $(window).height();
			var cw = $("#taisosiki-edit").outerWidth();
			var ch = $("#taisosiki-edit").outerHeight();
			$("#taisosiki-edit").css({"left": ((w - cw)/2) + "px"});
		}
	});

	$('#weight').on('click', function () {
		$("#wt-edit").slideToggle();

		modalResize();
		$(window).resize(modalResize);

		function modalResize(){
			var w = $(window).width();
			var h = $(window).height();
			var cw = $("#wt-edit").outerWidth();
			var ch = $("#wt-edit").outerHeight();
			$("#wt-edit").css({"left": ((w - cw)/2) + "px"});
		}
	});
});
