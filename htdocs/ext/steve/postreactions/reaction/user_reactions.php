<?php
/**
 *
 * Topic/Post Reactions. An extension for the phpBB 3.2.0 Forum Software package.
 * @author Steve <http://www.steven-clark.online/phpBB3-Extensions/>
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace steve\postreactions\reaction;

/**
* operator
*/
class user_reactions implements user_interface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;
	
	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\template\template */	
	protected $template;
	
	/** @var \phpbb\user */
	protected $user;

	/** @var string phpEx */	
	protected $php_ext;
	
	/** @var string phpBB root path */	
	protected $root_path;
	
	protected $type_operator;
	
	/** @string \steve\postreactions\config\Tables */
	protected $reactions_table;
	
	/**
	 * Constructor
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$php_ext,
		$root_path,
		\steve\postreactions\reaction\reaction_types $type_operator,
		$reactions_table)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->template = $template;
		$this->user = $user;
		$this->php_ext = $php_ext;
		$this->root_path = $root_path;
		$this->type_operator = $type_operator;	
		$this->reactions_table = $reactions_table;
	}

	public function obtain_user_reactions($user_id, $reaction_count, $search_reactions = false)
	{
		//bots
		if (!isset($user_id) || $user_id == ANONYMOUS || empty($reaction_count))
		{
			return false;
		}
		
		$recent_limit = (int) $this->config['rec_reactions_limit'];

		$sql = 'SELECT r.*, p.post_id, p.forum_id, p.post_visibility, p.post_enable_reactions
			FROM ' . $this->reactions_table . " r LEFT JOIN " . POSTS_TABLE . " p ON r.post_id = p.post_id
			WHERE r.poster_id = " . (int) $user_id . "
				AND p.post_visibility = " . ITEM_APPROVED . "
			ORDER BY r.reaction_time DESC";
		$result = $this->db->sql_query_limit($sql, $recent_limit);
		
		$reactions = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (empty($row))
			{
				continue;
			}
			$reactions[] = $row;
		}
		$this->db->sql_freeresult($result);
	
		if (!empty($reactions))
		{
			foreach ($reactions as $reaction)
			{
				$post_id = (int) $reaction['post_id'];

				$reactions = array(
					'IMAGE_SRC'		=> $this->type_operator->get_reaction_file($reaction['reaction_file_name']),
					'TITLE'			=> $reaction['reaction_type_title'],
					'U_VIEW_POST'	=> ($this->auth->acl_get('f_read', $reaction['forum_id']) && $reaction['post_enable_reactions']) ? append_sid("{$this->root_path}viewtopic.{$this->php_ext}?p=$post_id#p$post_id") : '',			
				);
				$this->template->assign_block_vars('reactions', $reactions);
			}
			unset($reactions);
			
			$this->template->assign_var('REACTION_COUNT', $reaction_count);

			$this->type_operator->tpr_common_vars();
		}
		
		return isset($user_id);	
	}
}
