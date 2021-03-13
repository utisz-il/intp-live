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
 *  Delete Post Reactionscontroller.
 */
class delete_reaction implements delete_interface
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

	/** @var \steve\postreactions\reaction\reaction */
	protected $type_operator;
		
	/** @string \steve\postreactions\config\tables */
	protected $reactions;

	/**
	 * Constructor
	 *
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
	 *
	 */
	public function delete($post_id, $user_id)
	{
		if (empty($this->config['reactions_enabled']))
		{
			throw new \phpbb\exception\http_exception(404, 'REACTIONS_DISABLED');
		}
		
		$sql = 'SELECT p.post_id, p.post_reaction_data, p.poster_id, r.*
			FROM ' . POSTS_TABLE . ' p LEFT JOIN ' . $this->reactions_table . ' r ON p.post_id = r.post_id
			WHERE p.post_id = ' . (int) $post_id . '
				AND r.reaction_user_id = ' . (int) $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);
		$post_row = $this->db->sql_fetchrow($result); 
		$this->db->sql_freeresult($result);
		
		if (!isset($post_row['post_id']) || !isset($post_id) || !isset($user_id) || empty($post_row['post_reaction_data']))
		{
			throw new \phpbb\exception\http_exception(404, 'REACTION_ERROR');
		}

		if ($post_row['reaction_user_id'] != $user_id || !$this->auth->acl_get('u_delete_reactions') 
			|| !check_link_hash($this->request->variable('hash', ''), 'delete_reaction') || !$this->request->is_ajax())
		{
			throw new \phpbb\exception\http_exception(403, 'NO_AUTH_OPERATION');
		}

 		$sql = 'SELECT COUNT(reaction_type_id) AS type_counted
			FROM ' . $this->reactions_table . '
			WHERE reaction_type_id = ' . (int) $post_row['reaction_type_id'] . '
				AND post_id = ' . (int) $post_id;
		$result = $this->db->sql_query($sql);
		$type_counted = (int) $this->db->sql_fetchfield('type_counted');
		$this->db->sql_freeresult($result);

		$post_data = $post_row['post_reaction_data'];
		
		$this->type_operator->reparse_data($post_data);
		
		$json_data = json_decode($post_data);

		foreach ($json_data as $key => $value)
		{
			if (empty($value))
			{
				continue;
			}

			if ($value->id == $post_row['reaction_type_id'] && $type_counted == 1)
			{
				unset($json_data[$key]);
			}
			else if ($value->id == $post_row['reaction_type_id'])
			{	
				$new_value = ($type_counted - (int) 1);
				$value->count = strval($new_value);
			}
		}

		$json_data = array_map("unserialize", array_unique(array_map("serialize", $json_data)));
	
		$post_data = array_values($json_data);
		unset($json_data);
		
		$post_data = json_encode($post_data);//[]
		
		$this->db->sql_transaction('begin');
		
		$sql = 'UPDATE ' . POSTS_TABLE . "
			SET post_reaction_data = '" . $this->db->sql_escape($post_data) . "', post_reactions = post_reactions - 1
			WHERE post_id = " . (int) $post_id;
		$this->db->sql_query($sql);

		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_reactions = user_reactions - 1
			WHERE user_id = ' . (int) $post_row['poster_id'];
		$this->db->sql_query($sql);
		
		$sql = 'DELETE FROM ' . $this->reactions_table . ' 
			WHERE post_id  = ' . (int) $post_row['post_id'] . ' 
			AND reaction_user_id = ' . (int) $post_row['reaction_user_id'];
		$this->db->sql_query($sql);

		$notification_data = array(
			'item_id'			=> $post_row['reaction_id'],
			'item_parent_id'	=> $post_row['post_id'],
		);
		$this->notification_manager->delete_notifications('steve.postreactions.notification.type.reaction', $notification_data);
		
		$this->db->sql_transaction('commit');
		
		$view_url = ($this->auth->acl_get('u_view_post_reactions_page') && !empty($this->config['reactions_posts_page_enabled'])) ? $this->helper->route('steve_postreactions_view_reactions_controller_pages', array('post_id' => $post_id)) : '';

		if ($this->request->is_ajax())
		{
			$json_response = new \phpbb\json_response;
			$data_send = array(
				'success' 			=> true,
				'POST_ID'			=> $post_id,
				'TYPE_DATA' 		=> $post_data,
				'VIEW_URL'			=> $view_url,
			);
			return $json_response->send($data_send);
		}

		throw new \phpbb\exception\http_exception(500, 'GENERAL_ERROR');
	}
}
