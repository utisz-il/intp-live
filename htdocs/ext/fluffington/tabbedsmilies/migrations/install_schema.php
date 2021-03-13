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

class install_schema extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'smilies_tabs');
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v320\v320'];
	}

	public function update_schema()
	{
		//FIXME Remove icon column later.
		return [
			'add_tables' => [
				$this->table_prefix . 'smilies_tabs' => [
					'COLUMNS' => [
						'id'		=> ['UINT',null,'auto_increment'],
						'name'		=> ['VCHAR:50',"New"],
						'icon'		=> ['VCHAR:50',null],
						'tab_order' => ['UINT',null],
					],
					'PRIMARY_KEY'	=> 'id',
				],
			],
			'add_columns' => [
				$this->table_prefix . 'smilies' => [
					'tab_order'	=> ['UINT',0],
					'tab_id'	=> ['UINT',1],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'smilies_tabs',
			],
			'drop_columns' => [
				$this->table_prefix . 'smilies' => [
					'tab_order',
					'tab_id',
				],
			],
		];
	}

	public function update_data()
	{
		return [
			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_FLUFFINGTON_TITLE',
			]],
			['module.add', [
				'acp',
				'ACP_FLUFFINGTON_TITLE',
				[
					'module_basename'	=> '\fluffington\tabbedsmilies\acp\main_module',
					'modes'				=> ['list'],
				],
			]],
			['custom', [
				[$this, 'initialize_table'],
			]],
		];
	}

	public function initialize_table()
	{
		//FIXME Is there a way to access language variables for the name?
		$insert = $this->db->sql_build_array("INSERT",[
			'name'	=> 'Unsorted',
		]);
		$sql = 'INSERT INTO ' . $this->table_prefix . 'smilies_tabs ' . $insert;
		$this->db->sql_query($sql);
		return true;
	}
}
