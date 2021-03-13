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
	'ACL_CAT_REACTIONS'			=> 'Topic/Post Reactions',
));

// Admin Permissions //0.6
$lang = array_merge($lang, array(
	'ACL_A_DELETE_REACTIONS'	=> 'Can Delete Topic/Post Reactions',
));

// Moderator Permissions //0.6
$lang = array_merge($lang, array(
	'ACL_M_DELETE_REACTIONS'	=> 'Can Delete Topic/Post Reactions',
));

// User Permissions	
$lang = array_merge($lang, array(
	'ACL_U_ADD_REACTIONS'				=> 'Can add Reactions',
	'ACL_U_CHANGE_REACTIONS'			=> 'Can change Reactions',	
	'ACL_U_DELETE_REACTIONS'			=> 'Can delete Reactions',
	'ACL_U_DISABLE_REACTIONS'			=> 'Can disable Topic/Post Reactions Extension',
	'ACL_U_DISABLE_REACTION_TYPES'		=> 'Can disable Reaction Types to their Posts',
	'ACL_U_DISABLE_POST_REACTIONS'		=> 'Can disable Reactions to their Posts',
	'ACL_U_DISABLE_TOPIC_REACTIONS'		=> 'Can disable Reactions to Posts with in their Topics',
	'ACL_U_MANAGE_REACTIONS_SETTINGS'	=> 'Can manage UCP Topic/Post Reaction Settings',
	'ACL_U_RESYNC_REACTIONS'			=> 'Can resync Post Reactions',
	'ACL_U_VIEW_REACTIONS'				=> 'Can view Reactions',
	'ACL_U_VIEW_REACTIONS_PAGE'			=> 'Can view Reactions Page',
	'ACL_U_VIEW_POST_REACTIONS_PAGE'	=> 'Can view Post Reactions Page',
));
