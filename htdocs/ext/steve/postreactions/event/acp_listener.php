<?php
/**
 *
 * Topic/Post Reactions. An extension for the phpBB 3.2.0 Forum Software package.
 * @author Steve <http://www.steven-clark.online/phpBB3-Extensions/>
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 * @ a_,m_ permissions todo
 */

namespace steve\postreactions\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Post Reactions Event acp_listener.
 */
class acp_listener implements EventSubscriberInterface
{
	/** @var \phpbb\event\dispatcher_interface */
	protected $dispatcher;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/**
	 * Constructor
	 *
	 */
	public function __construct(
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\request\request $request,
		\phpbb\template\template $template)
	{
		$this->dispatcher = $dispatcher;
		$this->request = $request;
		$this->template = $template;
	}
	
	static public function getSubscribedEvents()
	{
		return array(
			'core.permissions'							=> 'tpr_add_permission',
			'core.acp_manage_forums_request_data'		=> 'tpr_forums_request_data',
			'core.acp_manage_forums_initialise_data'	=> 'tpr_forums_initialise_data',
			'core.acp_manage_forums_display_form'		=> 'tpr_forums_display_form',
		);
	}

	public function tpr_add_permission($event)
	{
		$event['categories'] = array_merge($event['categories'], array(
			'reactions'				=> 'ACL_CAT_REACTIONS',
		));

		$new_permissions = array(
			//'m_delete_reactions' 			=> array('lang' => 'ACL_M_DELETE_REACTIONS',			'cat' => ''),
			'u_add_reactions' 				=> array('lang' => 'ACL_U_ADD_REACTIONS', 				'cat' => 'reactions'),
			'u_change_reactions' 			=> array('lang' => 'ACL_U_CHANGE_REACTIONS', 			'cat' => 'reactions'),			
			'u_delete_reactions'	 		=> array('lang' => 'ACL_U_DELETE_REACTIONS', 			'cat' => 'reactions'),
			'u_disable_post_reactions' 		=> array('lang' => 'ACL_U_DISABLE_POST_REACTIONS', 		'cat' => 'reactions'),
			'u_disable_reactions'			=> array('lang' => 'ACL_U_DISABLE_REACTIONS', 			'cat' => 'reactions'),
			'u_disable_reaction_types'		=> array('lang' => 'ACL_U_DISABLE_REACTION_TYPES', 		'cat' => 'reactions'),
			'u_disable_topic_reactions' 	=> array('lang' => 'ACL_U_DISABLE_TOPIC_REACTIONS', 	'cat' => 'reactions'),
			'u_manage_reactions_settings'	=> array('lang' => 'ACL_U_MANAGE_REACTIONS_SETTINGS',	'cat' => 'reactions'),
			'u_resync_reactions'			=> array('lang' => 'ACL_U_RESYNC_REACTIONS',			'cat' => 'reactions'),
			'u_view_reactions' 				=> array('lang' => 'ACL_U_VIEW_REACTIONS', 				'cat' => 'reactions'),
			'u_view_reactions_pages' 		=> array('lang' => 'ACL_U_VIEW_REACTIONS_PAGE', 		'cat' => 'reactions'),
			'u_view_post_reactions_page'	=> array('lang' => 'ACL_U_VIEW_POST_REACTIONS_PAGE',	'cat' => 'reactions'),
		);

		$event['permissions'] = array_merge($event['permissions'], $new_permissions);
	}

	public function tpr_forums_request_data($event)
	{
		$forum_data = $event['forum_data'];
		$forum_data['forum_enable_reactions'] = $this->request->variable('forum_enable_reactions', false);
		$event['forum_data'] = $forum_data;
	}

	public function tpr_forums_initialise_data($event)
	{
 		$forum_data = $event['forum_data'];
		if ($event['action'] == 'add' && !$event['update'])
		{
			$forum_data['forum_enable_reactions'] = true;
		}
		$event['forum_data'] = $forum_data;
	}

	public function tpr_forums_display_form($event)
	{
		$template_data = $event['template_data'];
		$template_data = array_merge($template_data, array(
			'S_ENABLE_REACTIONS'	=> $event['forum_data']['forum_enable_reactions'],
		));
		$event['template_data'] = $template_data;
	}
}
