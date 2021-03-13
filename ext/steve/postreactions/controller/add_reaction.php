<?php
/**
 *
 * Topic/Post Reactions. An extension for the phpBB 3.2.0 Forum Software package.
 * @author Steve <http://www.steven-clark.online/phpBB3-Extensions/>
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 * @split add/update
 */

namespace steve\postreactions\controller;

/**
 * Post Reactions add controller.
 */
class add_reaction implements add_interface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;
	
	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\controller\helper $controller_helper */
	protected $helper;

	/** @var \phpbb\notification\manager */
	protected $notification_manager;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\user */
	protected $user;

	/** @var \steve\postreactions\reaction\reaction_types */
	protected $type_operator;
	
	/** @string \steve\postreactions\config\tables */
	protected $reactions_table;

	/**
	 * Constructor
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		\phpbb\notification\manager $notification_manager,
		\phpbb\request\request $request,
		\phpbb\user $user,
		\steve\postreactions\reaction\reaction_types $type_operator,
		$reactions_table)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->helper = $helper;
		$this->db = $db;
		$this->notification_manager = $notification_manager;
		$this->request = $request;
		$this->user = $user;
		$this->type_operator = $type_operator;
		$this->reactions_table = $reactions_table;
	}

	/**
	 * controller
	 */
	public function add($post_id, $type_id)
	{
		if (empty($this->config['reactions_enabled']))
		{
			throw new \phpbb\exception\http_exception(404, 'REACTIONS_DISABLED');
		}

		$sql = 'SELECT post_id, poster_id, post_subject, topic_id, post_reaction_data
			FROM ' . POSTS_TABLE . '
			WHERE post_id = ' . (int) $post_id;
		$result = $this->db->sql_query($sql);
		$post_row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$post_data = $post_row['post_reaction_data'];
		//new file
		$this->type_operator->reparse_data($post_data);
		
 		if (!isset($post_row['post_id']) || !isset($post_id) || !isset($type_id))
		{
			throw new \phpbb\exception\http_exception(404, 'REACTION_ERROR');
		}

		$user_enable_reactions = (!$this->auth->acl_get('u_disable_reactions', $this->user->data['user_id'])) ? true : ($this->user->data['user_enable_reactions'] ? true  : false);
		if (!$user_enable_reactions || !$this->auth->acl_get('u_add_reactions') || !$this->request->is_ajax()
			|| !check_link_hash($this->request->variable('hash', ''), 'add_reaction'))
		{
			throw new \phpbb\exception\http_exception(403, 'NO_AUTH_OPERATION');
		}
		
		$sql = 'SELECT *
			FROM ' . $this->reactions_table . '
			WHERE reaction_user_id = ' . (int) $this->user->data['user_id'] . ' 
				AND post_id = ' . (int) $post_id;
		$result = $this->db->sql_query($sql);
		$user_row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$reaction = $this->type_operator->obtain_reaction_type($type_id);
		
 		if (empty($reaction))
		{
			throw new \phpbb\exception\http_exception(404, 'REACTION_ERROR');
		}
		
 		$sql = 'SELECT COUNT(reaction_type_id) AS reaction_count
			FROM ' . $this->reactions_table . '
			WHERE reaction_type_id = ' . (int) $type_id . '
				AND post_id = ' . (int) $post_id;
		$result = $this->db->sql_query($sql);
		$new_type_counted = (int) $this->db->sql_fetchfield('reaction_count');
		$this->db->sql_freeresult($result);
		
		$delete_url = ($this->auth->acl_get('u_delete_reactions')) ? $this->helper->route('steve_postreactions_delete_reaction_controller', array('post_id' => $post_id, 'user_id' => $this->user->data['user_id'], 'hash' => generate_link_hash('delete_reaction'))) : '';
		$view_url = ($this->auth->acl_get('u_view_post_reactions_page') && !empty($this->config['reactions_posts_page_enabled'])) ? $this->helper->route('steve_postreactions_view_reactions_controller_pages', array('post_id' => $post_id)) : '';

		$new_type = $reaction['reaction_file_name'];
		$add_reaction = $update_reaction = false;
		
		//split action
 		if ($user_row['reaction_user_id'] != $this->user->data['user_id'])
		{
			//try ($this->type_operator->ajust_type_add($type_id, $new_type_counted, false) catch
			$json_data = json_decode($post_data);
			if (!empty($json_data))
			{
				foreach ($json_data as $key => $value)
				{
					if (empty($value))
					{
						continue;
					}
					
					if ($value->id == $type_id)
					{
						$new_value = ($new_type_counted + (int) 1);
						$value->count = strval($new_value);
					} 
				}
			}
			
			if (empty($post_data) || empty($new_type_counted))
			{
				$json_data[] = array('id' => "$type_id", 'src' => "$new_type", 'count' => "1");
			}
			
			$json_data = array_map("unserialize", array_unique(array_map("serialize", $json_data)));
			$post_data = array_values($json_data);
			unset($json_data);
			
			$post_data = json_encode($post_data);
			
			$this->db->sql_transaction('begin');
			
			$sql = 'UPDATE ' . POSTS_TABLE . "
				SET post_reaction_data = '" . $this->db->sql_escape($post_data) . "', post_reactions = post_reactions + 1
				WHERE post_id = " . (int) $post_id;
			$this->db->sql_query($sql); 
			
			$sql_ary = array(
				'post_id'				=> (int) $post_id,
				'poster_id'				=> (int) $post_row['poster_id'],
				'reaction_user_id'		=> (int) $this->user->data['user_id'],
				'reaction_type_id'		=> (int) $type_id,
				'reaction_file_name'	=> (string) $reaction['reaction_file_name'],
				'reaction_type_title'	=> (string) $reaction['reaction_type_title'],
				'reaction_time'			=> time(),
				'topic_id'				=> (int) $post_row['topic_id'],
			);
			
			$sql = 'INSERT INTO ' . $this->reactions_table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
			$this->db->sql_query($sql); 
			$reaction_nextid = (int) $this->db->sql_nextid();
			
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_reactions = user_reactions + 1
				WHERE user_id = ' . (int) $post_row['poster_id'];
			$this->db->sql_query($sql); 			

			if ($post_row['poster_id'] != $this->user->data['user_id'])
			{
				$notification_data = array(
					'reaction_id'			=> $reaction_nextid,			
					'post_id'				=> $post_id,
					'poster_id'				=> $post_row['poster_id'],
					'post_subject'			=> $post_row['post_subject'],
					'reaction_file_name'	=> $reaction['reaction_file_name'],
					'reaction_type_title'	=> $reaction['reaction_type_title'],
					'user_id' 				=> $this->user->data['user_id'],
				);
				$this->notification_manager->add_notifications('steve.postreactions.notification.type.reaction', $notification_data);
			}
			
			$this->db->sql_transaction('commit');
			$add_reaction = true;
		}
		else
		{
			if (!check_link_hash($this->request->variable('hash', ''), 'add_reaction') || !$this->auth->acl_get('u_change_reactions'))
			{
				throw new \phpbb\exception\http_exception(403, 'NO_AUTH_OPERATION');
			}
			
			if ($type_id == $user_row['reaction_type_id'])
			{
				throw new \phpbb\exception\http_exception(403, 'REACTION_TYPE_DUPLICATE');//
			}

			$sql = 'SELECT COUNT(reaction_type_id) AS count
				FROM ' . $this->reactions_table . '
				WHERE reaction_type_id = ' . (int) $user_row['reaction_type_id'] . '
					AND post_id = ' . (int) $post_id;
			$result = $this->db->sql_query($sql);
			$old_type_counted = (int) $this->db->sql_fetchfield('count');
			$this->db->sql_freeresult($result);

			$json_data = json_decode($post_data);
			if (!empty($json_data))
			{
				foreach ($json_data as $key => $value)
				{
					if (empty($value))
					{
						continue;
					}
					//is_numeric 
					if ($value->id == $user_row['reaction_type_id'] && $old_type_counted <= 1)
					{
						unset($json_data[$key]);
					}
					else if ($value->id == $user_row['reaction_type_id'])
					{	
						$new_value = ($old_type_counted - (int) 1);
						$value->count = strval($new_value);
					}
					
					if ($value->id == $type_id)
					{
						$new_value = ($new_type_counted + (int) 1);
						$value->count = strval($new_value);
					} 
				}
			}
			
			if (!$new_type_counted)
			{
				$json_data[] = array('id' => "$type_id", 'src' => "$new_type", 'count' => "1");
			}
			
			$json_data = array_map("unserialize", array_unique(array_map("serialize", $json_data)));
			$post_data = array_values($json_data);
			unset($json_data);
			
			$post_data = json_encode($post_data);
			
			$this->db->sql_transaction('begin');
			
			$sql = 'UPDATE ' . POSTS_TABLE . "
				SET post_reaction_data = '" . $this->db->sql_escape($post_data) . "'  
				WHERE post_id = " . (int) $post_id;
			$this->db->sql_query($sql); 
			
			$sql_ary = array(
				'reaction_file_name'	=> (string) $reaction['reaction_file_name'],
				'reaction_type_id'		=> (int) $type_id,
				'reaction_type_title'	=> (string) $reaction['reaction_type_title'],
				'reaction_time'			=> time(),
				'topic_id'				=> (int) $post_row['topic_id'],
			);
						
			$sql = 'UPDATE ' . $this->reactions_table . '
				SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE post_id = ' . (int) $post_id . ' 
					AND reaction_user_id = ' . (int) $this->user->data['user_id'];
			$this->db->sql_query($sql);
			
			if ($post_row['poster_id'] != $this->user->data['user_id'])
			{
				$notification_data = array(
					'reaction_id'			=> $user_row['reaction_id'],
					'item_id'				=> $user_row['reaction_id'],
					'item_parent_id'		=> $post_id,
					'post_id'				=> $post_id,
					'poster_id'				=> $post_row['poster_id'],
					'post_subject'			=> $post_row['post_subject'],
					'reaction_file_name'	=> $reaction['reaction_file_name'],
					'reaction_type_title'	=> $reaction['reaction_type_title'],
					'user_id' 				=> $this->user->data['user_id'],
				);
				$this->notification_manager->update_notifications('steve.postreactions.notification.type.reaction', $notification_data);
			}
			
			$this->db->sql_transaction('commit');
			$update_reaction = true;
		}
		//send post count 0.6.0
		if ($this->request->is_ajax() && ($add_reaction || $update_reaction))
		{
			$json_response = new \phpbb\json_response;
			$data_send = array(
				'success' 			=> true,
				'POST_ID' 			=> $post_id,
				'NEW_TYPE'			=> $type_id,
				'REACTION_DELETE'	=> $delete_url,
				'TYPE_DATA' 		=> $post_data,
				'VIEW_URL'			=> $view_url,
			);
			
			if (!empty($update_reaction))
			{
				$data_send += array('updated' => true);
			}
			return $json_response->send($data_send);
		}
		
		throw new \phpbb\exception\http_exception(500, 'GENERAL_ERROR');
	}
}
