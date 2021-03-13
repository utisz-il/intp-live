<?php
/**
 *
 * Topic/Post Reactions. An extension for the phpBB 3.2.0 Forum Software package.
 * @author Steve <http://www.steven-clark.online/phpBB3-Extensions/>
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 * stolen *** from phpbb rules
 */


namespace steve\postreactions\migrations;

use \phpbb\db\migration\container_aware_migration;

/**
* Migration 
*/
class m4_tp_reactions_data extends container_aware_migration
{
	/**
	*
	* @return bool True if data exists, false otherwise
	* @access public
	*/
	public function effectively_installed()
	{
		$sql = 'SELECT * FROM ' . $this->table_prefix . 'reaction_types';
		$result = $this->db->sql_query_limit($sql, (int) 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row !== false;
	}

	/**
	* Assign migration file dependencies for this migration
	*
	* @return array Array of migration files
	* @static
	* @access public
	*/
	static public function depends_on()
	{
		return array('\steve\postreactions\migrations\m1_tp_reactions');
	}

	/**
	* Add or update data in the database
	*
	* @return array Array of table data
	* @access public
	*/
	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'insert_tp_reactions'))),
		);
	}

	/**
	* Custom function
	*
	* @return void
	* @access public
	*/
	public function insert_tp_reactions()
	{
		//$user = $this->container->get('user');
		$lang = $this->container->get('language');
		$lang->add_lang('common', 'steve/postreactions');

		$reaction_type = array(
			array(	
				'reaction_type_order_id'	=> 1,
				'reaction_file_name'		=> '1f44d.png',
				'reaction_type_title'		=> $lang->lang('REACTION_LIKE'),
				'reaction_type_enable'		=> true,
			),
			array(	
				'reaction_type_order_id'	=> 2,
				'reaction_file_name'		=> '1f44e.png',
				'reaction_type_title'		=> $lang->lang('REACTION_DISLIKE'),
				'reaction_type_enable'		=> true,
			),
			array(	
				'reaction_type_order_id'	=> 3,
				'reaction_file_name'		=> '1f642.png',
				'reaction_type_title'		=> $lang->lang('REACTION_HAPPY'),
				'reaction_type_enable'		=> true,
			),
			array(	
				'reaction_type_order_id'	=> 4,
				'reaction_file_name'		=> '1f60d.png',
				'reaction_type_title'		=> $lang->lang('REACTION_LOVE'),
				'reaction_type_enable'		=> true,
			),			
			array(	
				'reaction_type_order_id'	=> 5,
				'reaction_file_name'		=> '1f602.png',
				'reaction_type_title'		=> $lang->lang('REACTION_FUNNY'),
				'reaction_type_enable'		=> true,
			),
			array(	
				'reaction_type_order_id'	=> 6,
				'reaction_file_name'		=> '1f611.png',
				'reaction_type_title'		=> $lang->lang('REACTION_NEUTRAL'),
				'reaction_type_enable'		=> true,
			),
			array(	
				'reaction_type_order_id'	=> 7,
				'reaction_file_name'		=> '1f641.png',
				'reaction_type_title'		=> $lang->lang('REACTION_UNHAPPY'),
				'reaction_type_enable'		=> true,
			),
			array(	
				'reaction_type_order_id'	=> 8,
				'reaction_file_name'		=> '1f62f.png',
				'reaction_type_title'		=> $lang->lang('REACTION_SURPRISED'),
				'reaction_type_enable'		=> true,
			),
			array(	
				'reaction_type_order_id'	=> 9,
				'reaction_file_name'		=> '1f62d.png',
				'reaction_type_title'		=> $lang->lang('REACTION_CRY'),
				'reaction_type_enable'		=> true,
			),
			array(	
				'reaction_type_order_id'	=> 10,
				'reaction_file_name'		=> '1f621.png',
				'reaction_type_title'		=> $lang->lang('REACTION_MAD'),
				'reaction_type_enable'		=> true,
			),			
		);

		$this->db->sql_multi_insert($this->table_prefix . 'reaction_types', $reaction_type);
	}
}
