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
	'REACTIONS_VERSION'					=> '0.5.5-dev',
	'UCP_ENABLE_REACTIONS'				=> 'Enable Topic/Post Reactions',
	'UCP_ENABLE_REACTIONS_EXPLAIN'		=> 'Selecting No, Will remove your ability to React to Posts, Users ability to react to your posts, Your Reactions page, counts &amp; notifications',
	'UCP_DEFAULT_POST_SETTINGS'			=> 'Edit Default Topic/Post Reactions settings',
	'SELECT_REACTION_TYPES'				=> 'Select Reactions Users Can’t use to react to your Posts',
	'UCP_REACTIONS_SAVED'				=> 'Topic/Post Reactions settings have been saved successfully!',
	'UCP_REACTIONS_SETTING'				=> 'Settings',
	'UCP_REACTIONS_TITLE'				=> 'Topic/Post Reactions',		
	'UCP_FOE_REACTIONS_ENABLE'			=> 'Enable Foe reactions',
	'UCP_FOE_REACTIONS_EXPLAIN'			=> 'Allow Users who currently have you on their Foes list to react to your Posts?',	
	'UCP_POST_REACTIONS_ENABLE'			=> 'Enable Post Reactions by default',
	'UCP_POST_REACTIONS_EXPLAIN'		=> 'Allow Users to react to your Posts?',
	'UCP_TOPIC_REACTIONS_ENABLE'		=> 'Enable Topic Reactions by default',	
	'UCP_TOPIC_REACTIONS_EXPLAIN'		=> 'Allow Reactions in your Topics?',
));
