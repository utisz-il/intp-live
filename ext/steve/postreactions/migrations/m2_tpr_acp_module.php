<?php
/**
 *
 * Topic/Post Reactions. An extension for the phpBB 3.2.0 Forum Software package.
 * @author Steve <http://www.steven-clark.online/phpBB3-Extensions/>
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace steve\postreactions\migrations;

class m2_tpr_acp_module extends \phpbb\db\migration\migration
{	
	static public function depends_on()
	{
		return array('\steve\postreactions\migrations\m1_tp_reactions');
	}

	public function update_data()
	{
		return array(
			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_REACTIONS_TITLE'
			)),
			array('module.add', array(
				'acp',
				'ACP_REACTIONS_TITLE',
				array(
					'module_basename'	=> '\steve\postreactions\acp\main_module',
					'modes'				=> array('settings', 'reactions'),
				),
			)),
		);
	}
}
