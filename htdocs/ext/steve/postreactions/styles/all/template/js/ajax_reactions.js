/**
 *
 * Topic/Post Reactions. An extension for the phpBB 3.2.0 Forum Software package.
 * @author Steve
 * @copyright (c) <http://www.steven-clark.online/phpBB3-Extensions/>
 * @license GNU General Public License, version 2 (GPL-2.0)
 * @ strip back create functions
 */
 
(function($) {  // Avoid conflicts with other libraries

'use strict';

$('ul[id^=ltpr-]').click(function(e) {
	e.preventDefault();

	var buttons = $('ul[id^=ltpr-]');
	buttons.parent().css("visibility", "hidden");
	$('.button > .fa-smile-o').addClass('fa-spin').css('color', '#FF0000');

	setTimeout(function() {
		$('.button > .fa-smile-o').removeClass('fa-spin').css('color', '#008000');
		buttons.parent().css("visibility", "visible");
	}, 3000);
});

//need post count 0.6.0
phpbb.addAjaxCallback('add_reaction', function(res) {
	var typesData = JSON.parse(res.TYPE_DATA);
	var postId = res.POST_ID;
	var deleteUrl = res.REACTION_DELETE;
	var viewUrl = res.VIEW_URL;
	var reactionCount = Number($("#reaction-count" + postId).html()) + 1;
	var userReactionCount = Number($("#user-reaction-count" + postId).html()) + 1;
			
 	if (typeof res.success !== 'undefined') {

		$("#reaction-types" + postId).fadeOut(500).empty();

		$.each(typesData, function(index, value) {

			var count = (!tprData.countEnabled) ? '' : '<strong class="badge reaction_count">' + value.count + '</strong>';
			
			$("#reaction-types" + postId).append(count).fadeIn(500);

			var imgSrc = $("<img />").attr({
				id: 'rimage' + value.id + postId,
				src: tprData.url + value.src,
				height: tprData.height,
				width: tprData.width,
				class: "reaction_image",
				alt: tprData.alt
			});
			
			imgSrc.appendTo("#reaction-types" + postId);
			
			var changeClass = (res.NEW_TYPE == value.id) ? "user_alert" : "user_alert_new";
			if (viewUrl) {
				var url = '<a href="' + viewUrl + '?reaction=' + value.id + '" class="' + changeClass + '" onclick="reactions(this.href);return false;" title="' + tprData.urlTitle + '"></a>';
				$("#rimage" + value.id + postId).wrap(url);
			}
		});

		if (deleteUrl) {
			$('#delete-hide' + postId).removeClass('hidden').fadeIn(800);
			$('#delete-reaction' + postId).attr('href', deleteUrl);
		}

		if (!res.updated) {
			$("#reaction-count" + postId).html(parseInt(reactionCount));
			$("#user-reaction-count" + postId).html(parseInt(userReactionCount));
		}
	}
});

phpbb.addAjaxCallback('delete_reaction',  function(res) {
	var typesData = JSON.parse(res.TYPE_DATA);
	var postId = res.POST_ID;
	var viewUrl = res.VIEW_URL;
	var reactionCount = Number($("#reaction-count" + postId).html()) - 1;
	var userReactionCount = Number($("#user-reaction-count" + postId).html()) - 1;

	if (typeof res.success !== 'undefined') {
			
		$("#reaction-count" + postId).html(parseInt(reactionCount));
		$('#delete-hide' + postId).addClass('hidden').fadeOut(1000);
		$("#user-reaction-count" + postId).html(parseInt(userReactionCount));

		if (!typesData) {
			$("#reaction-types" + postId).fadeOut(800).empty();
			
		} else {
			$("#reaction-types" + postId).fadeOut(500).empty();

			$.each(typesData, function(index, value) {

				var count = (!tprData.countEnabled) ? '' : '<strong class="badge reaction_count">' + value.count + '</strong>';
				
				$("#reaction-types" + postId).append(count).fadeIn(500);

				var imgSrc = $("<img />").attr({
					id: 'rimage' + value.id + postId,
					src: tprData.url + value.src,
					height: tprData.height,
					width: tprData.width,
					class: "reaction_image",
					alt: tprData.alt
				});
				imgSrc.appendTo("#reaction-types" + postId);
				
				if (viewUrl) {
					var url = '<a href="' + viewUrl + '?reaction=' + value.id + '" class="user_alert_new" onclick="reactions(this.href);return false;" title="' + tprData.urlTitle + '"></a>';
					$("#rimage" + value.id + postId).wrap(url);
				}
			});
		}
	}
});

$(function() {
	if (tprData.quickReply !== '') {
		var qrForm = $('form#qr_postform');
		
		qrForm.find('input[type=submit][name=post]').click(function() {
				
		var message = $('textarea', '#message-box').val().length;
			if (!message) {
				phpbb.alert(tprData.alertTitle, tprData.alertMsg);
				phpbb.closeDarkenWrapper(5000);
				return false;
			} else {
				$('form#qr_postform').attr('action', function(i, val) {
					return val + '&' + tprData.quickReply;
				});
			}
		});
	};
});

})(jQuery); // Avoid conflicts with other libraries
