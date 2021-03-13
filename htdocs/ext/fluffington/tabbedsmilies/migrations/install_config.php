<?php
/**
 *
 * Tabbed Smilies. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, Lupe Fluffington
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace fluffington\tabbedsmilies\migrations;

class install_config extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\fluffington\tabbedsmilies\migrations\install_schema'];
	}

	public function update_data()
	{
		return [
			['config.add', ['tabbed_smilies_loading',1]],
			['config.add', ['tabbed_smilies_closed',0]],
			['config.add', ['tabbed_smilies_text',1]],
			['config.add', ['tabbed_smilies_icon',1]],
			['config.add', ['tabbed_smilies_layout',0]],
		];
	}
}
