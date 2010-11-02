/*
Functions to use on Editor Page.
*/

function Show_Syntax(elm){
	var st=$("#syntax_explain");
	if(st.hasClass("open")){
		st.removeClass("open").addClass("close").fadeOut();
		$(elm).text("▼記法について");
		//location.hash="";
	}else{
		st.removeClass("close").addClass("open").fadeIn();
		$(elm).text("▲記法について");
		//location.hash="#syntax_explain";
	}
}
var editor={
	result:"",
	link:function(){
		var url=prompt("URLを貼りつけてください","");
		if(!url){
			return false;
		}
		var title=prompt("タイトルを入力(空も可能)","");
		this.HatenaSyntaxLine("link",url,title);
	},
	HatenaSyntaxLine:function(){
		var val=$(".contentTextarea:first").val();
		var type=arguments[0];
		switch(type){
			case "link":
				var url=arguments[1];
				var title=arguments[2];
				if(!title){
					this.result=("["+url+"]");
				}else{
					this.result=("["+url+":title="+title+"]");
				}
				break;
			case "image":
				var path=arguments[1];
				this.result=("[image:"+path+"]")
				break;
		}
		$(".contentTextarea:first").val(val+this.result);
	}
}