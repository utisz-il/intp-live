<?php
/**
 *
 * Topic/Post Reactions. An extension for the phpBB 3.2.0 Forum Software package.
 * @author Steve <http://www.steven-clark.online/phpBB3-Extensions/>
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace steve\postreactions\acp;

/**
 * Topic/Post Reactions ACP module info.
 */
class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\steve\postreactions\acp\main_module',
			'title'		=> 'ACP_REACTIONS_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'		=> 'ACP_REACTIONS_SETTINGS',
					'auth'		=> 'ext_steve/postreactions && acl_a_board',
					'cat'		=> array('ACP_REACTIONS_TITLE')),
				'reactions'	=> array(
					'title'	 	=> 'ACP_REACTION_TYPES', 
					'auth' 		=> 'ext_steve/postreactions && acl_a_board',
					'cat' 		=> array('ACP_REACTIONS_TITLE')),
			),
		);
	}
}
