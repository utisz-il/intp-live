<?php
/**
 *
 * Topic/Post Reactions. An extension for the phpBB 3.2.0 Forum Software package.
 * @author Steve <http://www.steven-clark.online/phpBB3-Extensions/>
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace steve\postreactions\ucp;

/**
 * Topic/Post Reactions UCP module info.
 */
class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\steve\postreactions\ucp\reactions_module',
			'title'		=> 'UCP_REACTIONS_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'UCP_REACTIONS_SETTING',
					'auth'	=> 'ext_steve/postreactions && acl_u_manage_reactions_settings',
					'cat'	=> array('UCP_REACTIONS_SETTING')
				),
			),
		);
	}
}
