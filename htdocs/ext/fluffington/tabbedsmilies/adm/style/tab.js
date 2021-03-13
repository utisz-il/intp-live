var didSort = false;
var rename_store = {};
var delete_store = [];
var parent_scroll = {};

var langu = {
	"delete": $(".deletebutton").attr("title"),
	"rename": $(".renamebutton").attr("title"),
	"new": "New",
	"newBox": $(".newbutton").attr("title")
};


$( window ).on("beforeunload",function(){
	if(didSort){
		return "Are you sure?";
	}
});

$("#resetButton").on("click", function(){
	didSort = false;
	window.location.reload();
});

$("#fluffington_tabbedsmilies_acp").on("submit", function(event){
	var ob = ['tab-0'].concat($(".outerbox").sortable("toArray"));
	var allTabs = {};
	for(const tab of ob){
		if(tab == "newbox"){
			continue;
		}
		allTabs[tab] = $('#'+tab+' .smileybox').sortable("toArray");
	}
	$('#updated_sorting')[0].value = JSON.stringify(allTabs);
	$('#updated_names')[0].value = JSON.stringify(rename_store);
	$('#deleted_tabs')[0].value = JSON.stringify(delete_store);
	didSort = false;
	return true;
});

outerBoxSettings = {
	items: ".outersort",
	tolerance: "pointer",
	placeholder: "placeholder",
	cursor: "grabbing",
	stop: function(){
		didSort = true;
	}
};

$("#outerbox").sortable(outerBoxSettings);

smileyBoxSettings = {
	connectWith: ".smileybox",
	placeholder: "miniholder",
	items:"> img",
	cursor: "grabbing",
	activate: function( event, ui){
		$(ui['placeholder']).attr("src","../images/spacer.gif");
		$(ui['placeholder']).css("width",$(ui['item']).css("width"));
		$(ui['placeholder']).css("height",$(ui['item']).css("height"));
	},
	start: function(event, ui){
		parent_scroll['top'] = ui.item.parent().scrollTop();
		parent_scroll['left'] = ui.item.parent().scrollLeft();
		$(document).on('mousemove.tplugin',function(e){
			if(e.clientY <= 40){
				window.scrollBy(0,-20)
			}else if($(window).innerHeight() - e.clientY <= 40){
				window.scrollBy(0,20)
			}	
		});
	},
	stop: function(event, ui){
		didSort = true;
		$(document).off('mousemove.tplugin')
	},
	remove(event,ui){
		$(event.target).scrollTop(parent_scroll['top']);
		$(event.target).scrollLeft(parent_scroll['left']);
	}
};

$(".smileybox").sortable(smileyBoxSettings);

function setName(input){
	newName = $(input)[0].value.trim()
	h2box = $(input).parent();
	if(newName != ""){
		h2box.find(".h2_text").text(newName);
		rename_store[$(input).attr('data-id')] = newName;
	}else{
		$(input)[0].value = h2box.find(".h2_text").text();
	}
	h2box.find("input").css("display","none");
	h2box.find("span").css("display","unset");
}

function updateButtons(){
	$(".rename_box").off("keydown");
	$(".rename_box").off("blur");
	$(".deletebutton").off("click");
	$(".renamebutton").off("click");

	$(".renamebutton").on("click",function(){
		$(this).parent().css("display","none");
		$(this).parent().next().css("display","unset");
		$(this).parent().next().focus();
	});
	$(".rename_box").on("keydown",function(event){
		if ( event.which == 13 ) {
			event.preventDefault();
			setName(this);
		}else if (event.which == 27){
			$(this)[0].value = ''
			setName(this);
		}
	});
	$(".rename_box").on("blur",function(){
		setName(this)
	});

	$(".deletebutton").on("click",function(){
		//FIXME Replace with phpBB native confirm box?
		result = window.confirm("Are you sure you want to delete this tab?");
		if(result){
			if(!$(this).attr('data-id').startsWith("newtab")){
				delete_store.push($(this).attr('data-id'));
			}
			$smileybox = $(this).parentsUntil(".outerbox");
			$("#tab-1 .smileybox").append($smileybox.find(".sortsmiley"));
			$smileybox.parentsUntil(".outerbox").remove();
			didSort = true;
		}
	});

	$(".newbutton").on("click",function(){
		var id = $(this).attr("data-id");
		rename_store["newtab-"+id] = langu['new'];
		$("#newbox").replaceWith(`
			<div class="innerbox outersort" id="newtab-${id}">
				<h2>
					<span><span class="h2_text">${langu['new']}</span> <i class="icon fas fa-pencil renamebutton" title="${langu['rename']}"></i> <i class="icon far fa-times deletebutton" data-id="newtab-${id}" title="${langu['delete']}"></i></span>
					<input autocomplete="off" type="text" class="rename_box" data-id="newtab-${id}" value="${langu['new']}" />
				</h2>
				<div class="smileybox">
				</div>
			</div>`);
		id++;
		$("#outerbox").append(`
			<div class="newbox outersort" id="newbox" style="position:relative">
				<h2>${langu['newBox']}</h2>
				<div class="buttonbox">
					<i class="icon fas fa-plus-square newbutton" data-id="${id}" title="${langu['newBox']}"></i>
				</div>
			</div>`);
		$("#outerbox").sortable("refresh");
		$(".smileybox").sortable(smileyBoxSettings);
		didSort = true;
		updateButtons();
	});
}
updateButtons();

phpbb.addAjaxCallback('fluffington.didSort', function(){
	didSort = false;
});
