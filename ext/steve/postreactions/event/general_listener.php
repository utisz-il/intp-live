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
 * Post Reactions Event general_listener.
 */
class general_listener implements EventSubscriberInterface
{
	/** @var \phpbb\auth\auth */
	protected $auth;
	
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\event\dispatcher_interface */
	protected $dispatcher;
	
	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\template\template */
	protected $template;
	
	/** @var \phpbb\user */
	protected $user;

	/** @var string phpEx */
	protected $php_ext;
	
	/**
	 * Constructor
	 *
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\language\language $language,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$php_ext)
	{		
		$this->auth = $auth;
		$this->config = $config;
		$this->helper = $helper;
		$this->dispatcher = $dispatcher;
		$this->language = $language;
		$this->template = $template;
		$this->user = $user;
		$this->php_ext = $php_ext;
		$this->user_enable_reactions = (!$this->auth->acl_get('u_disable_reactions', $this->user->data['user_id'])) ? true : ($this->user->data['user_enable_reactions'] ? true  : false);
	}
	
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'							=> 'tpr_load_language_on_setup',
			'core.page_header'							=> 'tpr_add_page_header_link',
			'core.page_footer'							=> 'tpr_add_page_footer_link',			
			'core.viewonline_overwrite_location'		=> 'tpr_viewonline_page',
		);
	}
	
	public function tpr_load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'steve/postreactions',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function tpr_add_page_header_link()
	{
		$reactions_enabled = (!empty($this->config['reactions_enabled']) && !empty($this->config['reactions_page_enabled'])) ? true : false;

		$this->template->assign_vars(array(
			'REACTIONS_ENABLED' => (!empty($this->config['reactions_enabled'])) ? true : false,
			'U_VIEW_REACTIONS'	=> ($this->auth->acl_get('u_view_reactions_pages') && $reactions_enabled && $this->user_enable_reactions) ? $this->helper->route('steve_postreactions_view_reactions_controller_page') : false,
		));
	}
	
	public function tpr_add_page_footer_link()
	{
		if (!empty($this->config['reactions_enabled']))
		{
			$this->template->assign_var('REACTION_COPY', $this->language->lang('REACTION_COPY'));
		}
	}
	
	public function tpr_viewonline_page($event)
	{
		if (empty($this->config['reactions_enabled']) && empty($this->config['reactions_page_enabled']) 
			|| !$this->auth->acl_get('u_view_reactions_pages') || !$this->user_enable_reactions)
		{
			return;
		}

		if ($event['on_page'][1] === 'app' && strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/' . 'reactions') === 0)
		{
			$event['location'] = $this->language->lang('VIEWING_REACTIONS');
			$event['location_url'] = $this->helper->route('steve_postreactions_view_reactions_controller_page');
		}
	}
}
