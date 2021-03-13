//Todo fix the bottom margin on the smiley box when box is set to left/right

function tabHeightFix(){
	//Fixes weird height issues. Tries to make the smilies box the same size as the message box.
	//Fuction is called when tabs are created or switched and when the window is resized
	tabHeight = Object.values(
		$("#mytabs ul").css([
			"height",
			"margin-top",
			"margin-bottom",
			"border-top-width",
			"border-bottom-width",
			"padding-top",
			"padding-bottom"
		])
	);
	tabHeight = tabHeight.concat(
		Object.values(
			$("#mytabs div").css([
				"margin-top",
				"margin-bottom",
				"border-top-width",
				"border-bottom-width",
				"padding-top",
				"padding-bottom"
			])
		)
	);
	tabBoxHeight = 0;
	//Add the height of the smiley box excluding the inner smiley box
	for(const value of tabHeight){
		tabBoxHeight += parseFloat(value);
	}
	
	/*
	tabLayout values
		undefined: tab box is above or below the message box
		2 or 3: tab box is to the left or right of the message box receptively 
	*/
	
	if(typeof(tabLayout) == "undefined"){
		//Ignore tab's calculated height
		$("#mytabs div").css("height", "unset")
	}else{
		//Ignore small windows
		if($(window).width() >= 750){
			//Calculate height left over for the inner smiley box
			inner_height = $("#message-box").height() - tabBoxHeight
			if(inner_height < 150){
				inner_height = 150;
			}
			$("#mytabs div").css("max-height", inner_height);
		}
		//Ignore tab's calculated height
		$("#mytabs div").css("height", "unset");
	}
}

//Create tabs
$("#mytabs").tabs({classes: {
	"ui-tabs": "tabs",
	"ui-tabs-tab": "tab",
	"ui-tabs-active": "activetab",
	"ui-tabs-panel": "panel bg3"},
	heightStyle:"fill",
	collapsible: true,
	activate: function(event,ui){
		ui['newTab'].removeClass("ui-tabs-tab ui-tab ui-state-hover ui-state-focus ui-tabs-active ui-state-active");
		tabHeightFix();
	}
});

//Remove jquery-ui classes and replace with prosilver classes
$("#mytabs").removeClass("ui-widget ui-widget-content");
$("#mytabs ul").removeClass();
$("li.tab").removeClass("ui-state-active ui-state-default");
$(".ui-tabs-panel").removeClass("ui-tabs-panel ui-widget-content");

//Start box in the closed position if enabled
if(tabStartClosed){
	$("#ui-id-1").click();
}

//Tab box should be ready-ish. Remove placeholder.
$(".loadingtab").remove();
$(".tabbox").removeClass("hidden");

tabHeightFix();
//Adjust height of smiley box to match message box
if(typeof(tabLayout) != "undefined"){
	if($("#message").height() < $(".tabbox").height()){
		$("#message").height($(".tabbox").height())
	}
}

//Quick fix for tab panel background in PM
$("#mytabs .bg3").css("background-color", $(".bg3").css("background-color"))


//Recalculate tab height if window or message box is resized
new ResizeObserver(tabHeightFix).observe($("#mytabs ul")[0])
new ResizeObserver(tabHeightFix).observe($("#message")[0])