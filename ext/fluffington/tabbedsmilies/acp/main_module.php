<?php
/**
 *
 * Tabbed Smilies. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, Lupe Fluffington
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace fluffington\tabbedsmilies\acp;

/**
 * Tabbed Smilies ACP module.
 */
class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Main ACP module
	 *
	 * @param int    $id   The module ID
	 * @param string $mode The module mode (for example: manage or settings)
	 * @throws \Exception
	 */
	public function main($id, $mode)
	{
		global $phpbb_container;

		$acp_controller = $phpbb_container->get('fluffington.tabbedsmilies.controller.acp');
		$language = $phpbb_container->get('language');

		$request = $phpbb_container->get('request');
		$action = $request->variable("action","");
		switch($action)
		{
			//FIXME You know what? We can probably move this to the the main controller function. Would make error reporting easier.
			case "save":
				$acp_controller->set_page_url($this->u_action);
				$acp_controller->save_settings();
			break;

			default:
				$this->tpl_name = 'acp_tabbedsmilies_body';
				$this->page_title = $language->lang('ACP_TABBEDSMILIES');
				$acp_controller->set_page_url($this->u_action);
				$acp_controller->display_tabs();
			break;
		}
	}
}
