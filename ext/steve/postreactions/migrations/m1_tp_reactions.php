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

class m1_tp_reactions extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['reactions_version']);
	}
	
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v320\v320');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('reactions_version', '0.5.0-dev')),
			//
			array('config.add', array('reactions_enabled', true)),
			array('config.add', array('reactions_page_enabled', true)),
			array('config.add', array('reactions_posts_page_enabled', true)),
			array('config.add', array('post_author_react', false)),
			array('config.add', array('rec_reactions_limit', 20)),			
			array('config.add', array('reaction_image_height', 40)),
			array('config.add', array('reaction_image_width', 40)),
			array('config.add', array('reactions_image_path', 'ext/steve/postreactions/images/emoji')),		
			array('config.add', array('reaction_notifications_enabled', true)),			
			array('config.add', array('reaction_sql_cache', 84600)),
			array('config.add', array('reactions_per_page', 25)),
			array('config.add', array('reaction_type_count_enable', true)),
			array('config.add', array('user_reaction_notifications_options', true)),			

			array('permission.add', array('a_delete_reactions')),	
			array('permission.add', array('m_delete_reactions')),
			array('permission.add', array('u_add_reactions')),
			array('permission.add', array('u_change_reactions')),			
			array('permission.add', array('u_delete_reactions')),		
			array('permission.add', array('u_disable_post_reactions')),
			array('permission.add', array('u_disable_topic_reactions')),
			array('permission.add', array('u_disable_reactions')),
			array('permission.add', array('u_disable_reaction_types')),
			array('permission.add', array('u_manage_reactions_settings')),			
			array('permission.add', array('u_view_reactions')),
			array('permission.add', array('u_view_reactions_pages')),
			array('permission.add', array('u_view_post_reactions_page')),
		);	
	}
	
	public function update_schema()
	{
		return array(
			'add_tables'		=> array(
				$this->table_prefix . 'reactions'	=> array(
					'COLUMNS'		=> array(
						'reaction_id'			=> array('UINT', null, 'auto_increment'),
						'reaction_user_id'		=> array('UINT', 0),
						'poster_id'				=> array('UINT', 0),
						'post_id'				=> array('UINT', 0),
						'topic_id'				=> array('UINT', 0),
						'reaction_type_id'		=> array('UINT', 0),
						'reaction_file_name'	=> array('VCHAR:255', ''),
						'reaction_type_title'	=> array('VCHAR:255', ''),
						'reaction_time'			=> array('TIMESTAMP', 0),				
					),
					'PRIMARY_KEY'	=> 'reaction_id',
				),
				$this->table_prefix . 'reaction_types'	=> array(
					'COLUMNS'		=> array(
						'reaction_type_id'			=> array('UINT', null, 'auto_increment'),
						'reaction_type_order_id'	=> array('UINT', 0),
						'reaction_file_name'		=> array('VCHAR:255', ''),
						'reaction_type_title'		=> array('VCHAR:255', ''),
						'reaction_type_enable'		=> array('BOOL', 1),
					),
					'PRIMARY_KEY'	=> 'reaction_type_id',
				),				
			),
			
			'add_columns'	=> array(
				$this->table_prefix . 'forums'	=> array(
					'forum_enable_reactions'		=> array('BOOL', 1),
				),				
				$this->table_prefix . 'posts'	=> array(
					'post_reactions'				=> array('UINT', 0),
					'post_reaction_data'			=> array('TEXT_UNI', ''),//null
					'post_enable_reactions'			=> array('BOOL', 1),
					'post_disabled_reaction_ids'	=> array('VCHAR:255', ''),
				),
				$this->table_prefix . 'topics'	=> array(
					'topic_enable_reactions'		=> array('BOOL', 1),
				),					
				$this->table_prefix . 'users'	=> array(
					'user_reactions'				=> array('UINT', 0),
					'user_disabled_reaction_ids'	=> array('VCHAR:255', ''),
					'user_enable_reactions'			=> array('BOOL', 1),
					'user_enable_foe_reactions'		=> array('BOOL', 0),
					'user_enable_post_reactions'	=> array('BOOL', 1),
					'user_enable_topic_reactions'	=> array('BOOL', 1),
				),				
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'forums'		=> array(
					'forum_enable_reactions',
				),
				$this->table_prefix . 'posts'		=> array(
					'post_disabled_reaction_ids',
					'post_reactions',
					'post_enable_reactions',
					'post_reaction_data',
				),
				$this->table_prefix . 'topics'		=> array(
					'topic_enable_reactions',
				),			
				$this->table_prefix . 'users'		=> array(
					'user_reactions',
					'user_disabled_reaction_ids',
					'user_enable_reactions',
					'user_enable_foe_reactions',
					'user_enable_post_reactions',
					'user_enable_topic_reactions',
				),				
			),
			
			'drop_tables'		=> array(
				$this->table_prefix . 'reactions',
				$this->table_prefix . 'reaction_types',
			),
		);
	}	
}
