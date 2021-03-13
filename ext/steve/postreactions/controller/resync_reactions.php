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
 * Post Reactions resync controller.
 */
class resync_reactions //implements add_interface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;
	
	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\controller\helper $controller_helper */
	protected $helper;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\user */
	protected $user;
	
	/** @var string phpEx */
	protected $php_ext;
	
	/** @var string phpBB root path */	
	protected $root_path;	

	/** @string \steve\postreactions\config\tables */
	protected $reactions_table;

	/**
	 * Constructor
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,		
		\phpbb\db\driver\driver_interface $db,
		\phpbb\request\request $request,
		\phpbb\user $user,
		$php_ext,
		$root_path,
		$reactions_table)
	{
		$this->auth = $auth;
		$this->config = $config;		
		$this->db = $db;
		$this->request = $request;
		$this->user = $user;
		$this->php_ext = $php_ext;
		$this->root_path = $root_path;		
		$this->reactions_table = $reactions_table;
	}

	/**
	 * controller
	 */
	public function resync($post_id, $user_id)
	{
		if (empty($this->config['reactions_enabled']))
		{
			throw new \phpbb\exception\http_exception(404, 'REACTIONS_DISABLED');
		}
		
 		if (!isset($post_id) || !isset($user_id))
		{
			throw new \phpbb\exception\http_exception(404, 'REACTION_ERROR');
		}
		
		$user_enable_reactions = (!$this->auth->acl_get('u_disable_reactions', $this->user->data['user_id'])) ? true : ($this->user->data['user_enable_reactions'] ? true  : false);
		if (!check_link_hash($this->request->variable('hash', ''), 'resync_reaction') || !$user_enable_reactions || !$this->auth->acl_get('u_resync_reactions') 
			&& ($user_id != $this->user->data['user_id'] || !$this->auth->acl_get('a_', 'm_')))
		{
			throw new \phpbb\exception\http_exception(403, 'NO_AUTH_OPERATION');
		}

		if (empty($this->config['reactions_resync_enable']))
		{
			throw new \phpbb\exception\http_exception(404, 'RESYNC_DISABLED');
		}
		
		$sql = 'SELECT COUNT(post_id) AS count
			FROM ' . $this->reactions_table . '
			WHERE post_id = ' . (int) $post_id;
		$result = $this->db->sql_query($sql);
		$post_count = (int) $this->db->sql_fetchfield('count');
		$this->db->sql_freeresult($result);
		
		//0.6.0 remove disabled ids
		$sql = 'SELECT reaction_type_id, reaction_file_name, post_id
			FROM ' . $this->reactions_table . '
			WHERE post_id = ' . (int) $post_id;
		$result = $this->db->sql_query($sql);
		
		$ids = $reactions = $json_data = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ids[] = (int) $row['reaction_type_id'];
			$reactions[] = $row['reaction_type_id'] . ' ' . $row['reaction_file_name'];
		}
		$this->db->sql_freeresult($result);	
		
		$count = array_count_values($ids);

		$reaction_data = array_unique($reactions);
		foreach ($reaction_data as $data)
		{	
			list($id, $src)  = explode(' ', $data);
			$json_data[] = array(
				'id'		=> $id,
				'src'		=> $src,
				'count'		=> strval($count[$id]),
			);
		}
		unset($reaction_data);
		
		$post_data = json_encode($json_data);

		$sql = 'UPDATE ' . POSTS_TABLE . "
			SET post_reaction_data = '" . $this->db->sql_escape($post_data) . "', post_reactions = $post_count
			WHERE post_id = " . (int) $post_id;
		$this->db->sql_query($sql);
		
		redirect(append_sid("{$this->root_path}viewtopic.{$this->php_ext}?p=$post_id#post-reactions$post_id"));
		
		return true;
		
		throw new \phpbb\exception\http_exception(500, 'GENERAL_ERROR');
	}
}
	