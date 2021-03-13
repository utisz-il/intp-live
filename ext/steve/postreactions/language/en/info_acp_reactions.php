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
//ACP MAIN abc..
	'REACTIONS_VERSION'				=> 'Version: 0.5.5-dev',
	'ACP_TPR_DONATE'				=> 'Donate',
	'ACP_REACTIONS_EXPLAIN'			=> 'Here you can Add, Edit, Delete, Enable, Diasable and re-order reaction Types.',
	'ACP_REACTIONS_SETTINGS_EXPLAIN'=> 'Here you can adjust general settings for Topic/Post Reactions Extension.',
	'ACP_REACTIONS_TITLE'			=> 'Topic/Post Reactions',
	'ACP_REACTIONS_SETTINGS'		=> 'Settings',
	'ACP_REACTIONS_ENABLE'			=> 'Enable Topic/Post Reactions',
	'ACP_REACTION_ENABLE_PAGE'		=> 'Enable View Reactions Page',
	'ACP_REACTION_ENABLE_PAGES'		=> 'Enable View Post Reactions Page',
	'ACP_REACTIONS_RESYNC_ENABLE'	=> 'Enable Post Resactions re-sync',
	'ACP_REACTIONS_RESYNC_ENABLE_EXPLAIN'	=> 'If enabled will re-sync reaction types, count',
	'ACP_REACTIONS_SETTING_SAVED'	=> 'Settings have been saved successfully!',

	'ACP_CDN_REACTION_URL'			=> 'Content Delivery Network URL',
	'ACP_REACTION_ADD'				=> 'Add New Topic/Post Reaction',
	'ACP_REACTION_ADDED'			=> 'New Topic/Post Reaction Added',
	'ACP_REACTIONS_CACHE'			=> 'Reactions Image Cache Time',
	'ACP_REACTIONS_CACHE_EXPLAIN'	=> 'Min: <strong>300</strong> Seconds (5 minutes) Max: <strong>86400</strong> Seconds (1 Day)',
	'ACP_REACTION_DELETED_CONFIRM'	=> 'Are you sure that you wish to delete the data associated with this Reaction Type? <br /><br />This removes all of its data and settings and cannot be undone!',	
	'ACP_REACTION_TYPE_DELETED'		=> 'Topic/Post Reaction Type Deleted',
	'ACP_REACTION_EDIT'				=> 'Edit Topic/Post Reaction',
	'ACP_REACTION_ENABLE'			=> 'Enable Topic/Post Reaction',
	'ACP_REACTIONS_HEIGHT'			=> 'Reaction Image height',
	'ACP_REACTION_IMAGE'			=> 'Topic/Post Reaction Image',

	'ACP_REACTIONS_PER_PAGE'		=> 'Reactions per Page',
	'ACP_REACTION_PATH'				=> 'Reactions Image storage path',
	'ACP_REACTION_PATH_EXPLAIN'		=> 'Path under your phpBB root directory, e.g. images/emoji',
	'ACP_REACTION_TITLE'			=> 'Topic/Post Reaction Title',
	'ACP_REACTION_TYPE'				=> 'Topic/Post Type',
	'ACP_REACTION_TYPES'			=> 'Reaction Types',
	'REACTION_TYPE_ID_EMPTY'		=> 'The requested Reaction Type does not exist',
	'ACP_REACTION_TYPE_COUNT_ENABLE'=> 'Enable Reaction Type count in Posts',
	'ACP_REACTION_UPDATED'			=> 'Topic/Post Reaction Updated',
	'ACP_REACTIONS_WIDTH'			=> 'Reaction Image width',
	'ACP_REACTIONS_REC_LIMIT'		=> 'Recent Reactions limit',
	'ACP_REACTIONS_REC_LIMIT_EXP'	=> 'Number of recent Reactions to display in users profile,View Reactions pages',
	//Working on it... 
	'ACP_WORKING_ON_IT'				=> 'Working on it ... Deleteing %1s Reactions, %2s remaining...<br /> Please do not leave or refresh this page.',
	
	//
	'ACP_SELECT_REACTION_IMAGE'		=> 'Select Reaction Image',
	'ACP_SELECT_REACTION_IMAGE_ALT' => 'Image Preview',
	'ACP_NO_REACTION_IMAGE_SELECTED'=> 'No Image selected for Topic/Post Reaction.',
	'ACP_POST_AUTHOR_REACT'			=> 'Allow Post Authors to react to their Posts',//
	'CAT_REACTION_IMAGE'			=> 'Image',
	'CAT_REACTION_URL'				=> 'URL',
	'CAT_REACTION_TITLE'			=> 'Title',
	'CAT_REACTION_ENABLED'			=> 'Enabled',
	'UPLOAD_NOT_DIR'				=> 'The Image storage path you specified does not appear to be a directory. <br /> %1s',	
));
