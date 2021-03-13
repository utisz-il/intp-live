<?php
/**
 *
 * Topic/Post Reactions. An extension for the phpBB 3.2.0 Forum Software package.
 * @author Steve <http://www.steven-clark.online/phpBB3-Extensions/>
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 * 
 */

namespace steve\postreactions\ucp;

/**
 * Topic/Post Reactions UCP module.
 */
class main_module
{
	/** @var ContainerBuilder */
	protected $phpbb_container;

	public $page_title;
	public $tpl_name;
	public $u_action;
	
	function main($id, $mode)
	{
		global $phpbb_container;
		$this->phpbb_container = $phpbb_container;
		
		$this->language = $this->phpbb_container->get('language');
		
		$this->tpl_name = 'ucp_reactions_body';
		$this->page_title = $this->language->lang('UCP_REACTIONS_TITLE');

		$ucp_controller = $this->phpbb_container->get('steve.postreactions.ucp_controller');
		$ucp_controller->ucp_reaction_settings($this->u_action);
	}
}
