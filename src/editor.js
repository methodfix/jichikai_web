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