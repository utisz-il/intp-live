<?php
/**
 *
 * Topic/Post Reactions. An extension for the phpBB 3.2.0 Forum Software package.
 * @author Steve <http://www.steven-clark.online/phpBB3-Extensions/>
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 * @version 0.5.5-dev
 */
 
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'REACTIONS_VERSION'				=> '0.5.5-dev',
	'REACTIONS'						=> 'Reactions',	
	'REACTION_COPY'					=> '<a href="http://www.steven-clark.online/" onclick="window.open(this.href);return false;">phpBB post Reactions</a> | Emoji art By: <a href="http://emojione.com/" onclick="window.open(this.href);return false;">EmojiOne</a>',
	'REACTIONS_TITLE'				=> 'Topic/Post Reactions',
	'REACTIONS_TITLES'				=> 'Topic/Post Reactions &bull; page %d',
	//
//Actions
	'ENABLE_POST_REACTIONS'			=> 'Enable Reactions to this Post',
	'ENABLE_TOPIC_REACTIONS'		=> 'Enable Reactions in this Topic',
	'EXPLAIN_REACTIONS_POSTING'		=> 'Here you can select options for Topic and Post Reactions',
	
	'LOG_ACP_REACTION_ADDED'		=> '<strong>Added new Topic/Post Reaction %1$s</strong>',
	'LOG_ACP_REACTION_EDITED'		=> '<strong>Edited Topic/Post Reaction %1$s',
	'LOG_ACP_REACTION_DELETED'		=> '<strong>Deleted Topic/Post Reaction</strong>',

	'ADD_REACTION'					=> 'React to Post',
	'DELETE_REACTION'				=> 'Delete Reaction',	
	'REACTION_ADDED'				=> 'Reaction Added',
	'REACTION_DELETED'				=> 'Reaction Deleted',
	'REACTION_DUPLICATE'			=> 'You have already Reacted to this Post',
	'REACTIONS_LIST_VIEW'			=> 'View All',		
	'REACTION_NOTIFICATION'			=> '<strong>New Reaction</strong> <img src="%1$s" class="reaction-notification" alt="%2$s" /> from: %3$s In post:<br /> “%4$s”',	
	'REACTION_TYPES'				=> 'Reaction Types',
	'REACTION_TYPE_DUPLICATE'		=> 'Reaction duplicate',
	'REACTION_UPDATED'				=> 'Reaction Updated',
	'RESYNC_REACTIONS'				=> 'Re-sync Reactions',
	'SELECT_REACTION_TYPES'			=> 'Select Reactions Users Can’t use to react to your Posts',	
	'UPDATE_REACTION'				=> 'Update Reaction',

//Errors
	'NOT_AUTHORISED_REACTIONS'		=> 'You are not authorised to view Topic/Post Reactions.',
	'NOTIFICATION_TYPE_STEVE_REACTION' 		=> 'Someone Reacts to your Topic/Post',	
	'REACTIONS_DISABLED'			=> 'This Topic/Post Reactions page is currently disabled',
	'REACTIONS_DISABLED_USER'		=> 'This Topic/Post Reaction can not be displayed as the user may have disabled reactions or no longer has permissions.',	
	'REACTIONS_NOT_FOUND'			=> 'An <strong>Error</strong> has occured',//?
	'REACTION_ERROR'				=> 'An <strong>Error</strong> has occurred please refresh the page and try again',
	'RESYNC_DISABLED'				=> 'Re-syncing Reactions is currently disabled',	
	//

	'TOO_FEW_CHARS'					=> 'Your message contains too few characters.',
/* 	
	'USER_REACTION'	=> array(
		1 => 'Reaction',
		2 => 'Reactions',
	),
*/	
	'HR_RECENT_REACTIONS'			=> 'Recent Reactions',
	'RECENT_REACTIONS'				=> 'Showing %d Reactions of %2d',
	'REACTION_COUNT_TOTAL'			=> 'Total Post Reactions',	
	'REACTIONS_TOTAL'				=> 'Total Reactions',
	
	'USER_REACTION'					=> 'Reaction %d',
	'USER_REACTIONS'				=> 'Reactions %d',
	'VIEW_REACTIONS'				=> 'View Reactions',	
	'VIEWING_REACTIONS'				=> 'Viewing Topic/Post Reactions page',
	'WELCOME_REACTIONS_PAGE'		=> 'Welcome %1$s, <br /> A Total of %2$s Members have received reactions to their Posts, You can click on the Reaction Image to view the Post they received the reaction.',

//pre populated reactions
	'REACTION_CRY'					=> 'Cry',
	'REACTION_DISLIKE'				=> 'Dislike',
	'REACTION_FUNNY'				=> 'Funny',
	'REACTION_HAPPY'				=> 'Happy',
	'REACTION_LIKE'					=> 'Like',
	'REACTION_LOVE'					=> 'Love',
	'REACTION_MAD'					=> 'Mad',
	'REACTION_NEUTRAL'				=> 'Neutral',
	'REACTION_SAD'					=> 'Sad',
	'REACTION_SURPRISED'			=> 'Surprised',
	'REACTION_UNHAPPY'				=> 'Unhappy',
));
