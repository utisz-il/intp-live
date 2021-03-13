<?php
/**
 *
 * Topic/Post Reactions. An extension for the phpBB 3.2.0 Forum Software package.
 * @author Steve <http://www.steven-clark.online/phpBB3-Extensions/>
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace steve\postreactions\controller;

/**
 * Post Reactions acp controller.
 */
class acp_controller implements acp_interface
{
	/** @var \phpbb\config\config */
	protected $config;
	
	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\language\language */
    protected $language;

	/** @var \phpbb\log\log */
	protected $log;
	
	/** @var \phpbb\notification\manager */
	protected $notification_manager;
	
	/** @var pagination */
	protected $pagination;
	
	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;
	
	/** @var string phpBB root path */
	protected $root_path;
	
	protected $tpr_delete_reactions;
	
	/** @var \steve\postreactions\reaction\reaction_types */
	protected $type_operator;
			
	/** @string \steve\postreactions\config\tables */
	protected $reactions_table;
	protected $reaction_types_table;	

	/**
	 * Constructor
	 *
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\language\language $language,
		\phpbb\log\log $log,
		\phpbb\notification\manager $notification_manager,		
		\phpbb\pagination $pagination,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		\steve\postreactions\reaction\delete_reactions $tpr_delete_reactions,
		\steve\postreactions\reaction\reaction_types $type_operator,
		$reactions_table,
		$reaction_types_table)
	{
		$this->config = $config;
		$this->db = $db;
		$this->language = $language;
		$this->log = $log;
		$this->notification_manager = $notification_manager;		
		$this->pagination = $pagination;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->tpr_delete_reactions = $tpr_delete_reactions;
		$this->type_operator = $type_operator;
		$this->reactions_table = $reactions_table;
		$this->reaction_types_table = $reaction_types_table;
	}

	public function reaction_settings($submit, $u_action)
	{
		$image_path = $this->request->variable('reactions_image_path', '', true);
		
		if ($submit && !@is_dir($this->root_path . $image_path) || (!$submit && !@is_dir($this->type_operator->reactions_image_path())))
		{	
			$error = sprintf($this->language->lang('UPLOAD_NOT_DIR'), ($submit) ? $image_path : $this->config['reactions_image_path']);
		}
			
		if ($submit && !isset($error))
		{
			$image_path = trim($image_path, "/");

			$this->config->set('reactions_enabled', $this->request->variable('reactions_enabled', false));
			$this->config->set('reactions_page_enabled', $this->request->variable('reactions_page_enabled', false));
			$this->config->set('reactions_posts_page_enabled', $this->request->variable('reactions_posts_page_enabled', false));
			$this->config->set('reaction_type_count_enable', $this->request->variable('reaction_type_count_enable', false));
			$this->config->set('post_author_react', $this->request->variable('post_author_react', false));
			$this->config->set('reactions_resync_enable', $this->request->variable('reactions_resync_enable', false));
			$this->config->set('reactions_image_path', $image_path);
			$this->config->set('reaction_image_height', $this->request->variable('reaction_image_height', 0));
			$this->config->set('reaction_image_width', $this->request->variable('reaction_image_width', 0));
			$this->config->set('reactions_per_page', $this->request->variable('reactions_per_page', 0));
			$this->config->set('reaction_sql_cache', $this->request->variable('reaction_sql_cache', 0));
			$this->config->set('rec_reactions_limit', $this->request->variable('rec_reactions_limit', 0));
			
			trigger_error($this->language->lang('ACP_REACTIONS_SETTING_SAVED') . adm_back_link($u_action), E_USER_NOTICE);
		}

		$this->template->assign_vars(array(
			'SETTINGS_MODE'					=> true,
			'U_ACTION'						=> $u_action,
			'S_ERROR'						=> (isset($error)) ? $error : '',				
			'REACTIONS_ENABLE_PAGES'		=> $this->config['reactions_page_enabled'],
			'REACTIONS_ENABLE_POST_PAGES'	=> $this->config['reactions_posts_page_enabled'],
			'REACTIONS_ENABLE'				=> $this->config['reactions_enabled'],
			'REACTIONS_RESYNC_ENABLE'		=> $this->config['reactions_resync_enable'],
			'REACTION_TYPE_COUNT_ENABLE'	=> $this->config['reaction_type_count_enable'],
			'POST_AUTHOR_REACT'				=> $this->config['post_author_react'],
			'REACTION_PATH'					=> $this->config['reactions_image_path'],
			'REACTION_HEIGHT'				=> $this->config['reaction_image_height'],	
			'REACTION_WIDTH'				=> $this->config['reaction_image_width'],					
			'REACTIONS_PER_PAGE'			=> $this->config['reactions_per_page'],
			'REC_REACTIONS_LIMIT'			=> $this->config['rec_reactions_limit'],
			'REACTIONS_IMAGE_CACHE'			=> $this->config['reaction_sql_cache'],
		));
	}
	
	public function edit_add($submit, $reaction_type_id, $action, $u_action)
	{
		if($action == 'edit' && !$reaction_type_id)
		{
			trigger_error($this->language->lang('REACTION_TYPE_ID_EMPTY') . adm_back_link($u_action), E_USER_WARNING);
		}

		$data = array(
			'reaction_file_name' 	=> $this->request->variable('reaction_file_name', '', true),
			'reaction_type_enable'	=> $this->request->variable('reaction_type_enable', false),
			'reaction_type_title'	=> $this->request->variable('reaction_type_title', '', true),
		);
			
		if ($submit)
		{
			if (empty($data['reaction_file_name']))
			{
				$error = $this->language->lang('ACP_NO_REACTION_IMAGE_SELECTED');
			}
			
			if (!isset($error))
			{
				$sql_ary = array(
					'reaction_file_name'		=> (string) $data['reaction_file_name'],
					'reaction_type_enable'		=> (bool) $data['reaction_type_enable'],
					'reaction_type_title'		=> (string) $data['reaction_type_title'],
				);
				
				if($action == 'add')
				{
					$sql = 'INSERT INTO ' .  $this->reaction_types_table . $this->db->sql_build_array('INSERT', $sql_ary);
					$this->db->sql_query($sql);
				}
				else if($action == 'edit')
				{
					$sql = 'UPDATE ' .  $this->reaction_types_table . ' 
						SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE reaction_type_id = ' . (int) $reaction_type_id;
					$this->db->sql_query($sql);
				}
				
				$log_lang = ($action == 'add') ? 'LOG_ACP_REACTION_ADDED' : 'LOG_ACP_REACTION_EDITED';		
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $log_lang, false, array($data['reaction_type_title']));
				
				$this->type_operator->delete_reaction_types_cache();
				
				$msg_lang = ($action == 'add') ? 'ACP_REACTION_ADDED' : 'ACP_REACTION_UPDATED';
				trigger_error($this->language->lang($msg_lang) . adm_back_link($u_action), E_USER_NOTICE);
			}
		}
		
		if($action == 'edit')
		{
			$reaction_type = $this->type_operator->obtain_reaction_type($reaction_type_id);
		}
		
		$reaction_image = (isset($reaction_type['reaction_file_name'])) ? $reaction_type['reaction_file_name'] : '';
		$reactions_image_path = $this->type_operator->reactions_image_path() . '/';

		$this->template->assign_vars(array(
			'U_ADD_REACTION'	=> true,
			'U_ACTION'			=> ($action == 'add') ? $u_action . '&amp;action=add' : $u_action . '&amp;action=edit&amp;reaction_type_id=' . $reaction_type_id,
			'S_ERROR'			=> (isset($error)) ? $error : '',
			'S_FILENAME_LIST'	=> $this->select_reaction_image($reactions_image_path, $reaction_image),
			'REACTION_IMAGE'	=> $reactions_image_path . $reaction_image,
			'REACTION_PATH'		=> $reactions_image_path,
			'REACTION_ENABLE'	=> ($action == 'add') ? true : ((!empty($reaction_type['reaction_type_enable'])) ? true : false),
			'TITLE'				=> (isset($reaction_type['reaction_type_title'])) ? $reaction_type['reaction_type_title'] : '',
		));		
	}
	
	public function delete_type($reaction_type_id, $mode, $u_action)
	{

		if (!check_link_hash($this->request->variable('hash', ''), 'acp_reactions'))
		{
			trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($u_action), E_USER_WARNING);
		}

		$reaction_type = $this->type_operator->obtain_reaction_type($reaction_type_id);
		$type = $reaction_type['reaction_type_id'] . ':' . $reaction_type['reaction_file_name'];

		if (!$reaction_type || !$reaction_type_id)
		{
			trigger_error($this->language->lang('REACTION_TYPE_ID_EMPTY') . adm_back_link($u_action), E_USER_WARNING);
		}

		//$type_count = $this->request->variable('count', 0);
		/*
		if (sizeof($posts) //$type_count >= 200)
		{
			$chunk_ary = array_chunk($posts, 200);
			foreach($chunk_ary as $chunk)
		}
		*/

		$sql = 'SELECT reaction_id, post_id, poster_id, reaction_type_id
			FROM ' . $this->reactions_table . "
			WHERE reaction_type_id = " . (int) $reaction_type_id;
		$result = $this->db->sql_query($sql);
		
		$post_ids = $poster_ids = $posts = $reaction_ids = array();
		if ($row = $this->db->sql_fetchrow($result))
		{
			do
			{
				$posts[] = (int) $row['post_id'];
				$poster_ids[$row['poster_id']] = (!empty($poster_ids[$row['poster_id']])) ? $poster_ids[$row['poster_id']] + 1 : 1;
				$post_ids[$row['post_id']] = (!empty($post_ids[$row['post_id']])) ? $post_ids[$row['post_id']] + 1 : 1;
				$reaction_ids[] = (int) $row['reaction_id'];
			}
			while ($row = $this->db->sql_fetchrow($result));
					
			$this->db->sql_transaction('begin');

			if (!empty($poster_ids))
			{
				foreach ($poster_ids as $poster_id => $count)
				{
					$sql = 'UPDATE ' . USERS_TABLE . '
						SET user_reactions = 0
						WHERE user_id = ' . (int) $poster_id . '
						AND user_reactions < ' . (int) $count;
					$this->db->sql_query($sql);

					$sql = 'UPDATE ' . USERS_TABLE . '
						SET user_reactions = user_reactions - ' . (int) $count . '
						WHERE user_id = ' . (int) $poster_id . '
						AND user_reactions >= ' . (int) $count;
					$this->db->sql_query($sql);
				}
				unset($poster_ids);
			}
			
			if (!empty($post_ids))
			{
				foreach ($post_ids as $post_id => $count)
				{
					$sql = 'UPDATE ' . POSTS_TABLE . '
						SET post_reactions = 0
						WHERE post_id = ' . (int) $post_id . '
						AND post_reactions < ' . (int) $count;
					$this->db->sql_query($sql);

					$sql = 'UPDATE ' . POSTS_TABLE . '
						SET post_reactions = post_reactions - ' . (int) $count . '
						WHERE post_id = ' . (int) $post_id . '
						AND post_reactions >= ' . (int) $count;
					$this->db->sql_query($sql);
				}
				unset($post_ids);	
			}
			
			if (!empty($posts))
			{		
				$sql1 = 'SELECT post_id, post_reaction_data
					FROM ' . POSTS_TABLE . "
					WHERE " . $this->db->sql_in_set('post_id', $posts);
				$results = $this->db->sql_query($sql1);
				
				$post_data = array();
				while ($row = $this->db->sql_fetchrow($results))
				{
					if (empty($row))
					{
						continue;
					}
					$post_data[] = $row;
				}
				$this->db->sql_freeresult($results);
				
				if (!empty($post_data))
				{
					foreach ($post_data as $data)
					{
						$json_data = json_decode($data['post_reaction_data']);
						
						if (!empty($json_data))
						{
							foreach ($json_data as $key => $value)
							{
								if (empty($value))
								{
									continue;
								}

								if ($value->id == $reaction_type_id)
								{
									unset($json_data[$key]);
								}
							}
						}
						
						$post_data = array_values($json_data);
						$post_data = json_encode($post_data);

						$sql = 'UPDATE ' . POSTS_TABLE . "
							SET post_reaction_data = '" . $this->db->sql_escape($post_data) . "'
							WHERE post_id = " . (int) $data['post_id'];
						$this->db->sql_query($sql);	
					}
					unset($post_data, $json_data);
				}
			}

			if (!empty($reaction_ids))
			{
				$this->notification_manager->delete_notifications('steve.postreactions.notification.type.reaction', $reaction_ids);
			}
			
			$sql = 'DELETE FROM ' . $this->reactions_table . ' 
				WHERE reaction_type_id = ' . (int) $reaction_type_id . '
					AND ' . $this->db->sql_in_set('post_id', $posts);
			$this->db->sql_query($sql);
			
			$this->db->sql_transaction('commit');
			
			unset($posts);
		}
		$this->db->sql_freeresult($result);
		
		//
		$sql = 'DELETE FROM ' . $this->reaction_types_table . ' 
			WHERE reaction_type_id = ' . (int) $reaction_type_id;
		$this->db->sql_query($sql);
		
		$this->type_operator->delete_reaction_types_cache();
			
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_REACTION_DELETED');
		
		$this->template->assign_vars(array(
			'U_RETURN'		=> $u_action,
		));		
	}

	public function move_up_down($reaction_type_id, $action, $u_action)
	{
		if (!check_link_hash($this->request->variable('hash', ''), 'acp_reactions') || !$reaction_type_id)
		{
			trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($u_action), E_USER_WARNING);
		} 
		// Get current order id...
		$sql = 'SELECT reaction_type_order_id as current_order
			FROM ' . $this->reaction_types_table . '
			WHERE reaction_type_id = ' . (int) $reaction_type_id;
		$result = $this->db->sql_query($sql);
		$current_order = (int) $this->db->sql_fetchfield('current_order');
		$this->db->sql_freeresult($result);

		if ($current_order == 0 && $action == 'move_up')
		{
			return;
		}
		// on move_down, switch position with next order_id...
		// on move_up, switch position with previous order_id...
		$switch_order_id = ($action == 'move_down') ? $current_order + 1 : $current_order - 1;

		$sql = 'UPDATE ' . $this->reaction_types_table . '
			SET reaction_type_order_id = ' . (int) $current_order . '
			WHERE reaction_type_order_id = ' . (int) $switch_order_id . '
				AND reaction_type_id <> ' . (int) $reaction_type_id;
		$this->db->sql_query($sql);
		
		$move_executed = (bool) $this->db->sql_affectedrows();

		// Only update the other entry too if the previous entry got updated
		if ($move_executed)
		{
			$sql = 'UPDATE ' . $this->reaction_types_table . '
				SET reaction_type_order_id = ' . (int) $switch_order_id . '
				WHERE reaction_type_order_id = ' . (int) $current_order . '
					AND reaction_type_id = ' . (int)  $reaction_type_id;
			$this->db->sql_query($sql);
			
			$this->type_operator->delete_reaction_types_cache();
		}

		if ($this->request->is_ajax())
		{
			$json_response = new \phpbb\json_response;
			$json_response->send(array('success' => $move_executed));
		}
	}
	
	public function activate_deactivate($reaction_type_id, $action, $u_action)
	{			
		if (!check_link_hash($this->request->variable('hash', ''), 'acp_reactions') || !$reaction_type_id)
		{
			trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($u_action), E_USER_WARNING);
		}
		
		$activate_deactivate = ($action == 'activate') ? 1 : 0;
		
		$sql = 'UPDATE ' . $this->reaction_types_table . '
			SET reaction_type_enable = ' . $activate_deactivate . '
			WHERE reaction_type_id = ' . (int) $reaction_type_id;
		$this->db->sql_query($sql);

		$this->type_operator->delete_reaction_types_cache();
		
		if ($this->request->is_ajax())
		{
			$json_response = new \phpbb\json_response;
			$json_response->send(array('text' => ($action == 'activate') ? $this->language->lang('ENABLED') : $this->language->lang('DISABLED')));
		}						
	}
	
	public function select_reaction_image($reactions_image_path, $image)
	{
		$imglist = filelist($reactions_image_path);
		$filename_list = '<option value="">' . $this->language->lang('ACP_SELECT_REACTION_IMAGE') . '</option>' . PHP_EOL;
		
		foreach ($imglist as $path => $img_ary)
		{
			sort($img_ary);
			foreach ($img_ary as $img)
			{
				$img = $path . $img;	
				$selected = ($img == $image) ? ' selected="selected"' : '';

				if (strlen($img) > (int) 255 || $img === 'delete/274e.png')//config it
				{
					continue;
				}
				$filename_list .= '<option value="' . htmlspecialchars($img) . '"' . $selected . '>' . $img . '</option>' . PHP_EOL;
			}
		}
		unset($imglist, $img_ary);
		
		return $filename_list;
	}
	
	public function sort_reaction_order()
	{
		$sql = 'SELECT reaction_type_id AS order_id, reaction_type_order_id AS fields_order
			FROM ' .  $this->reaction_types_table . '
				ORDER BY reaction_type_order_id';
		$result = $this->db->sql_query($sql);

		if ($row = $this->db->sql_fetchrow($result))
		{
			$order = 0;
			do
			{
				++$order;
				if ($row['fields_order'] != $order)
				{
					$this->db->sql_query('UPDATE ' . $this->reaction_types_table . '
						SET reaction_type_order_id = ' . $order . '
						WHERE reaction_type_id = ' . (int) $row['order_id']);
				}
			}
			while ($row = $this->db->sql_fetchrow($result));
		}
		$this->db->sql_freeresult($result);			
	}
	
	public function acp_reactions_main($u_action)
	{
		$query_limit = (int) $this->config['reactions_per_page'];
		$start = $this->request->variable('start', 0);
		
		$sql = 'SELECT *
			FROM ' . $this->reaction_types_table . '
			ORDER BY reaction_type_order_id ASC';
		$result = $this->db->sql_query_limit($sql, (int) $query_limit, $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$active_lang = (!$row['reaction_type_enable']) ? $this->language->lang('DISABLED') : $this->language->lang('ENABLED');
			$active_value = (!$row['reaction_type_enable']) ? 'activate' : 'deactivate';

			$this->template->assign_block_vars('reaction_types', array(
				'U_ACTIVATE_DEACTIVATE'	=> $u_action . '&amp;action=' . $active_value . '&amp;reaction_type_id=' . $row['reaction_type_id'] . '&amp;hash=' . generate_link_hash('acp_reactions'),
				'L_ACTIVATE_DEACTIVATE'	=> $active_lang,
				'U_DELETE'			=> $u_action . '&amp;action=delete&amp;reaction_type_id=' . $row['reaction_type_id'] . '&amp;hash=' . generate_link_hash('acp_reactions'),
				'U_EDIT'			=> $u_action . '&amp;action=edit&amp;reaction_type_id=' . $row['reaction_type_id'],
				'U_MOVE_UP'			=> $u_action . '&amp;action=move_up&amp;reaction_type_id=' . $row['reaction_type_id'] . '&amp;hash=' . generate_link_hash('acp_reactions'),
				'U_MOVE_DOWN'		=> $u_action . '&amp;action=move_down&amp;reaction_type_id=' . $row['reaction_type_id'] . '&amp;hash=' . generate_link_hash('acp_reactions'),
				'TITLE'				=> $row['reaction_type_title'],
				'IMAGE_SRC'			=> $this->type_operator->reactions_image_path() . '/' . $row['reaction_file_name'],
			));			
		}
		$this->db->sql_freeresult($result);
		
		$sql = 'SELECT COUNT(reaction_type_id) AS reaction_count 
			FROM ' . $this->reaction_types_table;
		$result = $this->db->sql_query($sql);
		$reaction_count = (int) $this->db->sql_fetchfield('reaction_count');
		$this->db->sql_freeresult($result);
		
		$this->pagination->generate_template_pagination($u_action, 'pagination', 'start', $reaction_count, $query_limit, $start);

		$this->template->assign_vars(array(
			'REACTIONS_COUNT'       => $reaction_count,
			'U_ACTION'				=> $u_action,
			'U_ADD'					=> $u_action . '&amp;action=add',					
		));
		
		$this->type_operator->tpr_common_vars();
	}
		
	public function reation_type_count($type_id)
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
		
		return (int) $type_count;
	}	
}
