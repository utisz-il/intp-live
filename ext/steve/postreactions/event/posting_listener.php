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
 * Post Reactions Event listener.
 */
class posting_listener implements EventSubscriberInterface
{
	
	/** @var \phpbb\auth\auth */
	protected $auth;
	
	/** @var \phpbb\config\config */
	protected $config;
	
	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\event\dispatcher_interface */
	protected $dispatcher;

	/** @var \phpbb\notification\manager */
	protected $notification_manager;
	
	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;
	
	/** @var \phpbb\user */
	protected $user;
	
	/** @var \steve\postreactions\reaction\reaction */
	protected $tpr_delete_reactions;
	
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
		\phpbb\event\dispatcher_interface $dispatcher,//
		\phpbb\notification\manager $notification_manager,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\steve\postreactions\reaction\delete_reactions $tpr_delete_reactions,
		\steve\postreactions\reaction\reaction_types $type_operator,
		$reactions_table)
	{		
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->dispatcher = $dispatcher;
		$this->notification_manager = $notification_manager;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->tpr_delete_reactions = $tpr_delete_reactions;
		$this->type_operator = $type_operator;
		$this->reactions_table = $reactions_table;
	}
	
	static public function getSubscribedEvents()
	{
		return array(
			'core.posting_modify_message_text'			=> 'tpr_modify_message_text',
			'core.posting_modify_submit_post_before'	=> 'tpr_submit_post_before',
			'core.posting_modify_template_vars'			=> 'tpr_modify_template_vars',
			'core.submit_post_modify_sql_data'			=> 'tpr_modify_sql_data',
			'core.delete_posts_in_transaction'			=> 'tpr_reaction_delete_posts',
		);
	}

	public function tpr_modify_message_text($event)
	{
 		$post_data = $event['post_data'];
		
		$marked_ary	= array_keys($this->request->variable('reaction_type_id', array(0)));
		$marked = implode('|', $marked_ary);
		$s_new_message = ($event['mode'] == 'post' || ($event['mode'] == 'edit' && isset($post_data['topic_first_post_id']) == $event['post_id']));
		
		$post_data = array_merge($post_data, array(
			'post_enable_reactions' 		=> $this->request->variable('enable_post_reactions', false),
			'topic_enable_reactions' 		=> ($s_new_message) ? $this->request->variable('enable_topic_reactions', false) : $event['post_data']['topic_enable_reactions'],
			'post_disabled_reaction_ids'	=> $marked,
		));
		
		$event['post_data'] = $post_data;
	}

	public function tpr_submit_post_before($event)
	{
 		$data = $event['data'];
		
		$data = array_merge($data, array(
			'enable_post_reactions' 		=> (bool) $event['post_data']['post_enable_reactions'],
			'enable_topic_reactions' 		=> (bool) $event['post_data']['topic_enable_reactions'],
			'reaction_type_id' 				=> (string) $event['post_data']['post_disabled_reaction_ids'],
		));
		
		$event['data'] = $data;
	}

	public function tpr_modify_template_vars($event)
	{
 		$page_data = $event['page_data'];
		$post_data = $event['post_data'];
		$mode = $event['mode'];
		
		$reactions_enabled = (!empty($this->config['reactions_enabled'])) ? true : false;			
		$forum_enable_reactions = (!empty($post_data['forum_enable_reactions'])) ? true : false;
		$user_enable_reactions = (!$this->auth->acl_get('u_disable_reactions', $this->user->data['user_id'])) ? true : ($this->user->data['user_enable_reactions'] ? true  : false);
		
		$qr_action = $this->request->variable('qr_action', '');

		if (!$reactions_enabled || !$forum_enable_reactions || !$user_enable_reactions)
		{
			return;
		}
		
		$refresh = $event['refresh'];
		$preview = $event['preview'];
		$submit	= $event['submit'];
		
		$topic_checked = (isset($post_data['topic_enable_reactions'])) ? $post_data['topic_enable_reactions'] : false;
 		if (empty($topic_checked) && !in_array($mode, array('edit_topic', 'edit_first_post')) && isset($post_data['topic_first_post_id']) != $event['post_id'])
		{
			return;
		}
		
		if ($mode == 'post' && !$topic_checked && !$preview && !$submit)
		{
			$topic_checked = (!empty($this->user->data['user_enable_topic_reactions'])) ? true : false;
		}

		$qr_preview = ($preview && $qr_action == 'full') ? $preview : !$preview;
		$post_checked = (isset($post_data['post_enable_reactions'])) ? $post_data['post_enable_reactions'] : false;
		if (($mode == 'post' || $mode == 'reply') && $qr_preview && !$post_checked && !$submit)
		{
			$post_checked = (!empty($this->user->data['user_enable_post_reactions'])) ? true : false;
		}
		
		$u_disable_reaction_types = ($this->auth->acl_get('u_disable_reaction_types')) ? true : false;

		$reactions_checked = (!isset($post_data['post_disabled_reaction_ids']) || $qr_preview && $mode != 'edit') ? $this->user->data['user_disabled_reaction_ids'] : $post_data['post_disabled_reaction_ids'];
		$marked = explode('|', $reactions_checked);

		if ($refresh && $preview && $submit)
		{
			$marked = $page_data['post_disabled_reaction_ids'];
		}

		$reaction_types = $this->type_operator->obtain_reaction_types();
		
		$this->type_operator->display_reaction_types($reaction_types, 0, 0, $marked);
					
		$this->type_operator->tpr_common_vars();

		$page_data = array_merge($page_data, array(
			'S_FORUM_ENABLE_REACTIONS'		=> $forum_enable_reactions,
			'S_ENABLE_POST_REACTIONS'		=> ($post_checked) ? ' checked="checked"' : '',
			'S_ENABLE_TOPIC_REACTIONS'		=> ($topic_checked) ? ' checked="checked"' : '',
			'U_DISABLE_REACTION_TYPES'		=> $u_disable_reaction_types,
			'U_DISABLE_POST_REACTIONS'		=> ($this->auth->acl_get('u_disable_post_reactions')) ? true : false,
			'U_DISABLE_TOPIC_REACTIONS'		=> ($this->auth->acl_get('u_disable_topic_reactions')) ? true : false,				
		));
		
 		$event['page_data'] = $page_data;
		$event['post_data'] = $post_data;
		$event['mode'] = $mode;
	}

	public function tpr_modify_sql_data($event)
	{
		$data = $event['data'];
		$sql_data = $event['sql_data'];
		$qr_action = $this->request->variable('qr_action', '');
			
		if (in_array($event['post_mode'], array('post', 'edit_topic', 'edit_first_post')))
		{
			$enable_topic = (!empty($data['enable_topic_reactions'])) ? $data['enable_topic_reactions'] : (!empty($this->user->data['user_enable_topic_reactions']) ? true : false);
			$sql_data[TOPICS_TABLE]['sql']['topic_enable_reactions'] = (!$this->auth->acl_get('u_disable_topic_reactions')) ? true : $enable_topic;
		}
		//No Mode!!
		$enable_post = (isset($data['enable_post_reactions']) && $qr_action != 'submit') ? $data['enable_post_reactions'] : ((!empty($this->user->data['user_enable_post_reactions']) || !$this->auth->acl_get('u_disable_post_reactions')) ? true : false);
		$enable_types =	(isset($data['reaction_type_id']) && $qr_action != 'submit') ? $data['reaction_type_id'] : (isset($this->user->data['user_disabled_reaction_ids']) ? $this->user->data['user_disabled_reaction_ids'] : '');	
		
		$sql_data[POSTS_TABLE]['sql'] = array_merge($sql_data[POSTS_TABLE]['sql'], array(
			'post_enable_reactions' 		=> (!$this->auth->acl_get('u_disable_topic_reactions')) ? true : $enable_post,
			'post_disabled_reaction_ids'  	=> (!$this->auth->acl_get('u_disable_reaction_types')) ? '' : $enable_types,
		));	
		
		$event['data'] = $data;
		$event['sql_data'] = $sql_data;
	}
	
	public function tpr_reaction_delete_posts($event)
	{
		//updating user counts
		//leave for now								
		//delete_posts('poster_id', $user_id);
		foreach ($event['poster_ids'] as $poster_id)
		{
			//select count summit likie poster_id
			$sql = ' SELECT COUNT(*) AS count_id
				FROM ' . $this->reactions_table . '
				WHERE poster_id = ' . (int) $poster_id . '
					AND ' . $this->db->sql_in_set('post_id', $event['post_ids']);
			$result = $this->db->sql_query($sql);
			$count_id = (int) $this->db->sql_fetchfield('count_id');
			$this->db->sql_freeresult($result);
			//int
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_reactions = 0
				WHERE user_id = ' . (int) $poster_id . '
					AND user_reactions < ' . $count_id;
			$this->db->sql_query($sql);
			
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_reactions = user_reactions - ' . $count_id . '
				WHERE user_id = ' . (int) $poster_id . '
					AND user_reactions >= ' . $count_id;
			$this->db->sql_query($sql);
		}

		$reactions = $this->tpr_delete_reactions->delete_post_reactions('post_id', $event['post_ids']);
		
		if (!empty($reactions))
		{
			foreach ($reactions as $reactions => $key)//
			{
				$this->notification_manager->delete_notifications('steve.postreactions.notification.type.reaction', $key['reaction_id']);
			}
			unset($reactions);			
		}
		
		foreach ($event['post_ids'] as $post_id)
		{	
			$this->tpr_delete_reactions->delete_reactions('post_id', $post_id);
		}
	}
}
