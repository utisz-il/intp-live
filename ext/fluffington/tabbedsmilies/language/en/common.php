<?php
/**
 *
 * Tabbed Smilies. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, Lupe Fluffington
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//



$lang = array_merge($lang, [
	'ACP_FLUFFINGTON_TITLE'		=> 'Fluffington Extensions',
	'ACP_TABBEDSMILIES'			=> 'Tabbed Smilies',
	'ACP_TABBEDSMILIES_INVALID'	=> 'Submitted data is invalid. Please reload page and try again',
	'ACP_TABBEDSMILIES_UPDATED'	=> 'Smiley tabs updated',

	'TABBEDSMILIES_UNSORTED'	=> 'Unsorted',
	'TABBEDSMILIES_NEW'			=> 'NEW',
	'TABBEDSMILIES_NEW_BOX'		=> 'Add a new tab',
	'TABBEDSMILIES_RESET'		=> 'Reset',
	'TABBEDSMILIES_SAVE'		=> 'Save changes',
	'TABBEDSMILIES_RENAME'		=> 'Rename tab',
	'TABBEDSMILIES_DELETE'		=> 'Delete tab',

	'TABBEDSMILIES_HIDDEN'			=> 'Hidden',
	'TABBEDSMILIES_HIDDEN_EXPLAIN'	=> 'Smilies inside this box will not be shown. It can not be moved or deleted.',
	'TABBEDSMILIES_DEFAULT_EXPLAIN'	=> 'This is the default tab and can not be deleted. Smilies from deleted tabs will land here',
	
	'TABBEDSMILIES_LAZY'		=> 'Lazy loading',
	'TABBEDSMILIES_LAZY_EXPLAIN'=> 'Defers image loading for unviewed smilies.',
	'TABBEDSMILIES_CLOSED'		=> 'Close tabs on startup',
	'TABBEDSMILIES_TEXT'		=> 'Display tab titles',
	'TABBEDSMILIES_ICON'		=> 'Display tab icon',
	'TABBEDSMILIES_LAYOUT'		=> 'Location of tabs',
	'TABBEDSMILIES_ABOVE'		=> 'Above message box',
	'TABBEDSMILIES_BELOW'		=> 'Below message box',
	'TABBEDSMILIES_LEFT'		=> 'Left of message box',
	'TABBEDSMILIES_RIGHT'		=> 'Right of message box',

]);
