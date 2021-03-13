<?php
/**
 *
 * Tabbed Smilies. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, Lupe Fluffington
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace fluffington\tabbedsmilies\acp;

/**
 * Tabbed Smilies ACP module info.
 */
class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\fluffington\tabbedsmilies\acp\main_module',
			'title'		=> 'ACP_TABBEDSMILIES',
			'modes'		=> [
				'list'	=> [
					'title'	=> 'ACP_TABBEDSMILIES',
					'auth'	=> 'ext_fluffington/tabbedsmilies && acl_a_board',
					'cat'	=> ['ACP_FLUFFINGTON_TITLE'],
				],
			],
		];
	}
}
