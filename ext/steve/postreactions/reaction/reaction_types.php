<?php
/**
 *
 * Topic/Post Reactions. An extension for the phpBB 3.2.0 Forum Software package.
 * @author Steve <http://www.steven-clark.online/phpBB3-Extensions/>
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 * @core
 */

namespace steve\postreactions\reaction;

/**
* operator
*/
class reaction_types implements types_interface
{
	/** @var \phpbb\auth\auth */
	protected $auth;
		
	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;
	
	/** @var \phpbb\config\config */
	protected $config;
	
	/** @var \phpbb\db\driver\driver */
	protected $db;
		
	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\template\template */	
	protected $template;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @string \steve\postreactions\config\Tables */
	protected $reactions_table;
	protected $reaction_types_table;	
	
	const TP_CACHE_TIME = 86400;
	
	/**
	 * Constructor
	 */
	public function __construct(
		\phpbb\auth\auth $auth,	
		\phpbb\cache\driver\driver_interface $cache,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,		
		\phpbb\controller\helper $helper,
		\phpbb\template\template $template,
		\phpbb\path_helper $path_helper,
		$reactions_table,
		$reaction_types_table)
	{
		$this->auth = $auth;
		$this->cache = $cache;
		$this->config = $config;
		$this->db = $db;
		$this->helper = $helper;
		$this->template = $template;
		$this->path_helper = $path_helper;
		$this->reactions_table = $reactions_table;
		$this->reaction_types_table = $reaction_types_table;
	}

	public function obtain_reaction_types($module_controller = false)
	{
		$cache_time = (isset($this->config['reaction_sql_cache'])) ? (int) $this->config['reaction_sql_cache'] : TP_CACHE_TIME;
		$reaction_types = array();	
		$reaction_types = $this->cache->get('_reaction_types');
		//also for acp
		if ($reaction_types === false)
		{
			$sql_where = ($module_controller) ? "" : "WHERE reaction_type_enable = 1";
			
			$sql = "SELECT *
				FROM " . $this->reaction_types_table . "
				$sql_where
					ORDER BY reaction_type_order_id ASC";
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				if (empty($row))
				{
					continue;
				}				
				$reaction_types[] = $row;
			}
			$this->db->sql_freeresult($result);	
			
			$this->cache->put('_reaction_types', $reaction_types, $cache_time);
		}
		
		return $reaction_types;	
	}

	public function obtain_reaction_type($type_id)
	{
		if (!isset($type_id))
		{
			return false;
		}
		
		$sql = 'SELECT *
			FROM ' . $this->reaction_types_table . '
			WHERE reaction_type_id = ' . (int) $type_id;
		$result = $this->db->sql_query($sql);
		$reaction_type = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		
		return $reaction_type;
	}
	
/* 	public function reation_type_count($type_id)
	{
		if (!isset($type_id))
		{
			return false;
		}
		
		$sql = 'SELECT COUNT(reaction_type_id) AS count
			FROM ' . $this->reactions_table . '
			WHERE reaction_type_id = ' . (int) $type_id;
		$result = $this->db->sql_query($sql);
		$type_count = (int) $this->db->sql_fetchfield('count');
		$this->db->sql_freeresult($result);
		
		return sizeof($type_count);
	} */
	
	public function display_reaction_types($reaction_types, $post_id, $user_type_id, $disabled_types)
	{
		if (empty($reaction_types))
		{
			return false;
		}
		
		if (!is_array($disabled_types))
		{
			$disabled_types = array($disabled_types);
		}

		foreach ($reaction_types as $reaction_type)
		{
			if (empty($reaction_type['reaction_file_name']))
			{
				continue;
			}
			
			$disabled_type = (in_array($reaction_type['reaction_type_id'], $disabled_types, true)) ? true : false;
			
			$type_row = array(
				'CHECKED'			=> $disabled_type,	
				'ID'				=> $reaction_type['reaction_type_id'],
				'IMAGE_SRC'			=> (isset($reaction_type['reaction_file_name'])) ? $this->get_reaction_file($reaction_type['reaction_file_name']) : '',//false			
				'TITLE'				=> (isset($reaction_type['reaction_type_title'])) ? $reaction_type['reaction_type_title'] : '',		
			);
			
			if (!empty($post_id))
			{
				$reaction_add_url = $this->helper->route('steve_postreactions_add_reaction_controller', array('post_id' => $post_id, 'type_id' => $reaction_type['reaction_type_id'], 'hash' => generate_link_hash('add_reaction')));
				$type_row += array(	
					'U_REACTED'			=> ($user_type_id == $reaction_type['reaction_type_id']) ? true : false,
					'U_REACTION_ADD'	=> ($this->auth->acl_get('u_add_reactions')) ? $reaction_add_url : false,//
				);
			}

			$switch_vars = (empty($post_id)) ? 'reaction_types' : 'postrow.reaction_types';
			$this->template->assign_block_vars($switch_vars, $type_row);
		}
		
		return $reaction_types;
		unset($reaction_types);
	}
	
	public function reparse_data(&$post_data)
	{
		if (preg_match_all('/(\d+):+[a-zA-Z\.0-9_-]+.(gif|jpg|jpeg|png|svg):+(\d+)/', $post_data))
		{
			$post_reaction_data = explode('|', $post_data);
			
			$json_data = array();
			foreach ($post_reaction_data as $key => $reaction_data)
			{
				list($reaction_id, $reaction_image, $reaction_count) = explode(':', $reaction_data);
				
				$json_data[$key] = array(
					'id'		=> $reaction_id,
					'src'		=> $reaction_image,
					'count'		=> $reaction_count,
				);				
			}
			
			$json_data = array_map("unserialize", array_unique(array_map("serialize", $json_data)));
			$post_data = array_values($json_data);
			$post_data = json_encode($json_data);
			
			return $post_data;
			unset($post_reaction_data);
		}			
	}

	public function get_reaction_file($reaction_file_name)
	{
		if (!preg_match('/.(gif|jpg|jpeg|png|svg)/', strtolower($reaction_file_name)) || !$reaction_file_name)
		{
			return false;
		}
		
		return $this->reactions_image_path() . '/' . $reaction_file_name;
	}

	public function reactions_image_path()
	{
		return $this->path_helper->get_web_root_path() . $this->config['reactions_image_path'];
	}
	
	public function delete_reaction_types_cache()
	{
		$this->cache->destroy('_reaction_types');
	}

	public function tpr_common_vars()
	{
		//$count
		return $this->template->assign_vars(array(
			'REACTION_IMAGE_HEIGHT'		=> (isset($this->config['reaction_image_height'])) ? $this->config['reaction_image_height'] : (int) 0,
			'REACTION_IMAGE_WIDTH'		=> (isset($this->config['reaction_image_width'])) ? $this->config['reaction_image_width'] : (int) 0,
			'REACTION_URL'				=> $this->reactions_image_path() . '/',
			'REACTIONS_ENABLED'			=> (!empty($this->config['reactions_enabled'])) ? true : false,
			//'COUNT'						=> $count,
		));
	}
}
