<?php
/**
 *
 * Topic/Post Reactions. An extension for the phpBB 3.2.0 Forum Software package.
 * @author Steve <http://www.steven-clark.online/phpBB3-Extensions/>
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 * @ redo/split
 */

namespace steve\postreactions\controller;

/**
 * Topic/Post Reactions Pages.
 */
class reaction_pages
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;
	
	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;
	
	/** @var \phpbb\language\language $language */
    protected $language;
	
	/** @var \phpbb\request\request */
	protected $request;
	
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;
	
	/** @var string phpEx */
	protected $php_ext;
	
	/** @var string phpBB root path */	
	protected $root_path;	

	/** @var pagination */
	protected $pagination;
		
	/** @var \steve\postreactions\reaction\reaction */
	protected $type_operator;
	
	/** @ \steve\postreactions\config\Tables */
	protected $reactions_table;

	/**
	 * Constructor
	 *
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\pagination $pagination,
		$php_ext,
		$root_path,
		\steve\postreactions\reaction\reaction_types $type_operator,
		$reactions_table)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->helper = $helper;
		$this->language = $language;		
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->pagination = $pagination;
		$this->php_ext = $php_ext;
		$this->root_path = $root_path;		
		$this->type_operator = $type_operator;
		$this->reactions_table = $reactions_table;
	}

	public function view_reactions()
	{
 		if (!$this->auth->acl_get('u_view_reactions_pages'))
		{
			throw new \phpbb\exception\http_exception(403, 'NOT_AUTHORISED_REACTIONS');
		}
		
		$user_enable_reactions = (!$this->auth->acl_get('u_disable_reactions', $this->user->data['user_id'])) ? true : ($this->user->data['user_enable_reactions'] ? true  : false);
		$reactions_enabled = (!empty($this->config['reactions_enabled']) && !empty($this->config['reactions_page_enabled'])) ? true : false;
 		if (empty($reactions_enabled) || !$user_enable_reactions)
		{
			throw new \phpbb\exception\http_exception(404, 'REACTIONS_DISABLED');
		}

		$reaction_types = $this->type_operator->obtain_reaction_types(false);
				
		$this->type_operator->display_reaction_types($reaction_types, 0, 0, $disabled_types = array());
							
		$query_limit = (int) $this->config['reactions_per_page'];
		$page = $this->request->variable('page', 0);
		$poster_count = (int) 0;
		$default_order = 'DESC';//0.6.0
		$poster_id = $recent_reactions = $reaction_user = array();
		
		$user_enable_reactions = (!$this->auth->acl_get('u_disable_reactions', $this->user->data['user_id'])) ? true : ($this->user->data['user_enable_reactions'] ? true  : false);
		
		$sql = 'SELECT *
			FROM ' . USERS_TABLE . '
			WHERE user_reactions > 0
				AND user_id <> ' . ANONYMOUS . '
			ORDER BY user_reactions ' . $default_order;
		$result = $this->db->sql_query_limit($sql, $query_limit, $page);
		 
		$i = 0;
		while ($rows = $this->db->sql_fetchrow($result))
		{
			$user_enable_reactions = (!$this->auth->acl_get('u_disable_reactions', $rows['user_id'])) ? true : ($rows['user_enable_reactions'] ? true : false);
			if (empty($rows) || !$user_enable_reactions)
			{
				continue;
			}
			
			$reaction_user[$i] = $rows;
			$poster_id[$i] = $rows['user_id'];			
			$i++;
		}
		$this->db->sql_freeresult($result);

		if (sizeof($poster_id))
		{
			$sql = ' SELECT COUNT(DISTINCT poster_id) AS poster_count
				FROM ' . $this->reactions_table;
			$result = $this->db->sql_query($sql);
			$poster_count = (int) $this->db->sql_fetchfield('poster_count');
			$this->db->sql_freeresult($result);
			
			$recent_limit = (int) $this->config['rec_reactions_limit'];
			$recent_sql_limit = $query_limit * $recent_limit;
			
			//sql array (post_enable_reactions
			$sql = 'SELECT r.*, p.post_id, p.forum_id, p.post_visibility, p.post_enable_reactions, f.forum_id, f.forum_enable_reactions
				FROM ' . $this->reactions_table . ' r LEFT JOIN ' . POSTS_TABLE . ' p ON r.post_id = p.post_id 
				LEFT JOIN ' . FORUMS_TABLE . ' f ON f.forum_id = p.forum_id
				WHERE ' . $this->db->sql_in_set('r.poster_id', $poster_id) . '
					AND p.post_visibility = ' . ITEM_APPROVED . '
				ORDER BY r.reaction_time DESC';
			$result = $this->db->sql_query_limit($sql, $recent_sql_limit);

			while ($rows = $this->db->sql_fetchrow($result))
			{
				$recent_reactions[$rows['poster_id']][] = $rows;
			}
			$this->db->sql_freeresult($result);
		}

		for ($i = 0, $end = sizeof($reaction_user); $i < $end; ++$i)
		{
			if (!isset($reaction_user[$i]))
			{
				continue;
			}

			$row = $reaction_user[$i];
			$poster_id = (int) $row['user_id'];
			
			 $user_row = array(
				'USER_AVATAR'		=> get_user_avatar($row['user_avatar'], $row['user_avatar_type'], $row['user_avatar_width'], $row['user_avatar_height']),
				'USER_NAME'			=> get_username_string('full', $poster_id, $row['username'], $row['user_colour']),
				'JOINED'			=> $this->user->format_date($row['user_regdate']),
				'POSTS'				=> $row['user_posts'],
				'RECENT_LIMIT'		=> ($row['user_reactions'] > $recent_limit) ? sprintf($this->language->lang('RECENT_REACTIONS', $recent_limit, $row['user_reactions'])) : sprintf($this->language->lang('USER_REACTIONS', $row['user_reactions'])),
				//'U_VIEW_ALL'		=> $this->helper->route('', array('mode' => 'user', 'id' => $row['poster_id']);
			);
			$this->template->assign_block_vars('reactions', $user_row);
			
			if (!empty($recent_reactions[$poster_id]))
			{
				foreach (array_slice($recent_reactions[$poster_id], 0, $recent_limit) as $recent_reaction)
				{
					$post_id = (int) $recent_reaction['post_id'];
					$u_view_post = ($recent_reaction['post_enable_reactions'] && $this->auth->acl_get('f_read', $recent_reaction['forum_id']) && $recent_reaction['forum_enable_reactions']);
					
					$reaction_types = array(
						'IMAGE_SRC'		=> $this->type_operator->get_reaction_file($recent_reaction['reaction_file_name']),
						'TITLE'			=> $recent_reaction['reaction_type_title'],
						'U_VIEW_POST'	=> ($u_view_post) ? append_sid("{$this->root_path}viewtopic.$this->php_ext?p=$post_id#p$post_id") : '',
					);
					$this->template->assign_block_vars('reactions.recent', $reaction_types);
				}
				unset($recent_reactions[$poster_id]);
			}
		}
		unset($reaction_user);
		
		$view_list_url = $this->helper->route('steve_postreactions_view_reactions_controller_page');
		$this->pagination->generate_template_pagination($view_list_url, 'pagination', 'page', $poster_count, $query_limit, $page);
		
		$welome_reactions = ($this->user->data['user_id'] != ANONYMOUS) ? get_username_string('full', $this->user->data['user_id'], $this->user->data['username'], $this->user->data['user_colour']) : $this->language->lang('GUEST');
					
		$this->template->assign_vars(array(
			'POSTER_COUNT'				=> sprintf($this->language->lang('TOTAL_USERS', $poster_count)),
			'WELCOME_REACTIONS_PAGE'	=> sprintf($this->language->lang('WELCOME_REACTIONS_PAGE', $welome_reactions, $poster_count)),			
		));
		
		$this->type_operator->tpr_common_vars();

		$this->template->assign_block_vars('navlinks', array(
			'U_VIEW_FORUM'		=> $view_list_url,
			'FORUM_NAME'		=> $this->language->lang('REACTIONS'),
		));
		
		$page_title = ($page) ? sprintf($this->language->lang('REACTIONS_TITLES', $this->pagination->get_on_page($query_limit, $page))) : $this->language->lang('REACTIONS_TITLE');
		return $this->helper->render('reactions_body.html', $page_title);
	}

  	public function post_reactions($post_id)
	{
 		if (!$this->auth->acl_get('u_view_post_reactions_page'))
		{
			throw new \phpbb\exception\http_exception(403, 'NOT_AUTHORISED_REACTIONS');
		}
		
		$user_enable_reactions = (!$this->auth->acl_get('u_disable_reactions', $this->user->data['user_id'])) ? true : ($this->user->data['user_enable_reactions'] ? true  : false);
		$reactions_enabled = (!empty($this->config['reactions_enabled']) && !empty($this->config['reactions_posts_page_enabled'])) ? true : false;
 		if (empty($reactions_enabled) || !isset($post_id) || !$user_enable_reactions)
		{
			$error = (!$reactions_enabled || !$user_enable_reactions) ? 'REACTIONS_DISABLED' : 'REACTIONS_NOT_FOUND';
			throw new \phpbb\exception\http_exception(404, $error);
		}

		$query_limit = (int) $this->config['reactions_per_page'];
		$page = $this->request->variable('page', 0);
		$reaction_type_id = $this->request->variable('reaction', '');
		
		$where_and = '';
		$view_list_url = $this->helper->route('steve_postreactions_view_reactions_controller_pages', array('post_id' => $post_id));
		
		if ($reaction_type_id)
		{
			$where_and = ' AND reaction_type_id = ' . (int) $reaction_type_id;
			$view_list_url = $this->helper->route('steve_postreactions_view_reactions_controller_pages', array('post_id' => $post_id, 'reaction' => $reaction_type_id));
		}
		//forum

		$sql = 'SELECT COUNT(post_id) AS item_count
			FROM ' . $this->reactions_table . '
			WHERE post_id = ' . (int) $post_id .
				$where_and;
		$result = $this->db->sql_query($sql);
		$item_count = (int) $this->db->sql_fetchfield('item_count');
		$this->db->sql_freeresult($result);
				
		$select_col = 'r.*, p.forum_id, p.post_id, p.post_visibility, p.post_enable_reactions, ';
		$select_col .= 'u.user_id, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, u.user_enable_reactions';
		
		$sql_arr = array(
			'SELECT'    => $select_col,
			'FROM'		=> array(
				$this->reactions_table  => 'r',
				POSTS_TABLE        		=> 'p'
			),
			'LEFT_JOIN' => array(
				array(
					'FROM'  => array(USERS_TABLE => 'u'),
					'ON'    => 'u.user_id = r.reaction_user_id',
				),
			),				
			'WHERE'		=> 'p.post_id = '. (int) $post_id . '
				AND p.post_enable_reactions = 1
				AND p.post_visibility = ' . ITEM_APPROVED . ' 
				AND r.post_id = ' . (int) $post_id . $where_and,
		);
			
		$sql = $this->db->sql_build_query('SELECT', $sql_arr);
		$result = $this->db->sql_query_limit($sql, $query_limit, $page);
		
		$post_reactions = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (empty($row))
			{
				continue;			
			}
			
			$user_enable_reactions = (!$this->auth->acl_get('u_disable_reactions', $row['user_id'])) ? true : ($row['user_enable_reactions'] ? true : false);
			if (!$user_enable_reactions && $item_count == 1)
			{
				throw new \phpbb\exception\http_exception(404, 'REACTIONS_DISABLED_USER');
			}
			
			$post_reactions[] = $row;
		}
		$this->db->sql_freeresult($result);
		//0.7.0 if sizeof($post_reactions) > || < rebiuld $this->tpr_resync->post_reaction data();
		if(!empty($post_reactions))
		{
			foreach ($post_reactions as $row)
			{
				if (!$this->auth->acl_get('f_read', $row['forum_id']))
				{
					throw new \phpbb\exception\http_exception(403, 'SORRY_AUTH_READ');
				}
				
				$user_enable_reactions = (!$this->auth->acl_get('u_disable_reactions', $row['user_id'])) ? true : ($row['user_enable_reactions'] ? true : false);			
				if (!$user_enable_reactions && $item_count == 1)
				{
					throw new \phpbb\exception\http_exception(404, 'REACTIONS_DISABLED_USER');
				}
				else if (!$user_enable_reactions)
				{
					continue;
				}
				
				if ($reaction_type_id)
				{
					$this->template->assign_vars(array(
						'IMG_SRC'	=> $this->type_operator->get_reaction_file($row['reaction_file_name']),
						'TITLE'		=> $row['reaction_type_title'],
					));
				}

				$this->template->assign_block_vars('reaction', array(
					'IMG_SRC'			=> (!$reaction_type_id) ? $this->type_operator->get_reaction_file($row['reaction_file_name']) : '',//hack
					'NAME'				=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
					'TIME'				=> $this->user->format_date($row['reaction_time']),
					'TITLE'				=> (!$reaction_type_id) ? $row['reaction_type_title'] : '',
					'USER_AVATAR'		=> get_user_avatar($row['user_avatar'], $row['user_avatar_type'], $row['user_avatar_width'], $row['user_avatar_height']),
				));			
			}
			unset($post_reactions);
		}

		$this->pagination->generate_template_pagination($view_list_url, 'pagination', 'page', $item_count, $query_limit, $page);
					
		$this->template->assign_var('COUNT', $item_count);		
		$this->type_operator->tpr_common_vars();
		
		$page_title = ($page) ? sprintf($this->language->lang('REACTIONS_TITLES', $this->pagination->get_on_page($query_limit, $page))) : $this->language->lang('REACTIONS_TITLE');
		return $this->helper->render('reactions_post_body.html', $page_title);
	}
}
