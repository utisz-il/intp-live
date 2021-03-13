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
class delete_reactions implements delete_interface
{
	/** @var \phpbb\db\driver\driver */
	protected $db;

	protected $type_operator;
	
	/** @string \steve\postreactions\config\Tables */
	protected $reactions_table;
	
	/**
	 * Constructor
	 */
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\notification\manager $notification_manager,
		\steve\postreactions\reaction\reaction_types $type_operator,
		$reactions_table)
	{
		$this->db = $db;
		$this->notification_manager = $notification_manager;
		$this->type_operator = $type_operator;	
		$this->reactions_table = $reactions_table;
	}
	//rename
	public function delete_post_reactions($in_set, $ids)
	{
		$this->check_array_ids($in_set, $ids);
		
		$sql = 'SELECT *
			FROM ' . $this->reactions_table . "
			WHERE " . $this->db->sql_in_set($in_set, $ids);
		$result = $this->db->sql_query($sql);
		
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
		
		return $reactions;
	}
	
	public function update_reaction_counts($reactions)
	{
		if (!empty($reactions))
		{
			foreach ($reactions as $reaction => $key)
			{	
				$sql = 'UPDATE ' . POSTS_TABLE . '
					SET post_reactions = post_reactions - 1
					WHERE post_id = ' . (int) $key['post_id'];
				$this->db->sql_query($sql);

				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_reactions = user_reactions - 1
					WHERE user_id = ' . (int) $key['poster_id'];
				$this->db->sql_query($sql);
				
				$this->notification_manager->delete_notifications('steve.postreactions.notification.type.reaction', $key['reaction_id']);
			}
			unset($reactions);
		}
		return (bool) $this->db->sql_affectedrows();
	}		

	public function delete_reactions($in_set = '', $ids)
	{
		$this->check_array_ids($in_set, $ids);
		//could be and in set
		$sql = 'DELETE FROM ' . $this->reactions_table . "
			WHERE " . $this->db->sql_in_set($in_set, $ids);
		$this->db->sql_query($sql);

		return (bool) $this->db->sql_affectedrows();
	}

	public function check_array_ids($in_set = '', $ids)
	{
		if (empty($ids) || empty($in_set))
		{
			return false;
		}
		
		if (!is_array($ids))
		{
			$ids = array($ids);
		}
		
		return $ids;
	}
}
