<?php
/**
 *
 * Topic/Post Reactions. An extension for the phpBB 3.2.0 Forum Software package.
 * @author Steve <http://www.steven-clark.online/phpBB3-Extensions/>
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace steve\postreactions\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Post Reactions User Event listener.
 */
class user_listener implements EventSubscriberInterface
{
	/** @var \phpbb\auth\auth */
	protected $auth;
	
	/** @var \phpbb\config\config */
	protected $config;
	
	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\event\dispatcher_interface */
	protected $dispatcher;
	
	/** @var \phpbb\user $user */
	protected $user;
	
	/** @var \steve\postreactions\reaction\delete_reactions */
	protected $tpr_delete_reactions;
	
	/** @var \steve\postreactions\reaction\user_reactions */
	protected $tpr_user_operator;
	
	/** @string \steve\postreactions\config\tables */
	protected $reactions_table;
	
	/**
	 * Constructor
	 */
	public function __construct(
		\phpbb\auth\auth $auth, 
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\user $user,
		\steve\postreactions\reaction\delete_reactions $tpr_delete_reactions,
		\steve\postreactions\reaction\user_reactions $tpr_user_operator,
		$reactions_table)
	{		
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->dispatcher = $dispatcher;
		$this->user = $user;
		$this->tpr_delete_reactions = $tpr_delete_reactions;
		$this->tpr_user_operator = $tpr_user_operator;
		$this->reactions_table = $reactions_table;
	}
	
	static public function getSubscribedEvents()
	{
		return array(		
			'core.memberlist_view_profile'		=> 'tpr_view_profile_reactions',
			'core.delete_user_after'			=> 'tpr_delete_user_after',
		);
	}

	public function tpr_view_profile_reactions($event)
	{
		$user_enable_reactions = (!$this->auth->acl_get('u_disable_reactions', $this->user->data['user_id'])) ? true : ($this->user->data['user_enable_reactions'] ? true  : false);	
		$member_enable_reactions = (!$this->auth->acl_get('u_disable_reactions', $event['member']['user_id'])) ? true : ($event['member']['user_enable_reactions'] ? true  : false);		
		
		if (!empty($this->config['reactions_enabled']) && $member_enable_reactions && $user_enable_reactions && $this->auth->acl_get('u_view_reactions'))
		{
			$this->tpr_user_operator->obtain_user_reactions($event['member']['user_id'], $event['member']['user_reactions'], false);
		}
	}

	public function tpr_delete_user_after($event)
	{
 		foreach ($event['user_ids'] as $user_id)
		{
			$sql = 'SELECT p.post_id, p.post_reaction_data, p.poster_id, r.*
				FROM ' . POSTS_TABLE . ' p LEFT JOIN ' . $this->reactions_table . ' r ON p.post_id = r.post_id
				WHERE r.reaction_user_id = ' . (int) $user_id . '
				AND p.poster_id <> ' . (int) $user_id;
			$result = $this->db->sql_query($sql);
			
			//can used in acp_controller also
			$post_data = array();
			if ($row = $this->db->sql_fetchrow($result))
			{
				do
				{			
					if (empty($row))
					{
						continue;
					}

					$post_data[] = $row;
				}
				while ($row = $this->db->sql_fetchrow($result));

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

								if ($value->id == $data['reaction_type_id'] && $value->count == 1)
								{
									unset($json_data[$key]);
								}
								else if ($value->id == $data['reaction_type_id'])
								{	
									$new_value = ($value->count - (int) 1);
									$value->count = strval($new_value);
								}
							}
						}
						
						$json_data = array_map("unserialize", array_unique(array_map("serialize", $json_data)));
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
			$this->db->sql_freeresult($result);
		}
		
		$reactions = $this->tpr_delete_reactions->delete_post_reactions('reaction_user_id', $event['user_ids']);
			
		$this->tpr_delete_reactions->update_reaction_counts($reactions);
			
		$this->tpr_delete_reactions->delete_reactions('reaction_user_id', $event['user_ids']);
			
		$this->tpr_delete_reactions->delete_reactions('poster_id', $event['user_ids']);
	}
}
