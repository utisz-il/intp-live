<?php
/**
 *
 * Topic/Post Reactions. An extension for the phpBB 3.2.0 Forum Software package.
 * @author Steve <http://www.steven-clark.online/phpBB3-Extensions/>
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace steve\postreactions\acp;

/**
 * Post Reactions ACP module.
 */
class main_module
{
	/** @var ContainerBuilder */
	protected $phpbb_container;

	public $page_title;
	public $tpl_name;
	public $u_action;
	
	public function main($id, $mode)
	{
		global $phpbb_container, $request, $template;

		$this->phpbb_container = $phpbb_container;
		$this->request = $request;
		$this->template = $template;
		
		$this->tpl_name = 'acp_reactions_body';
		
		$action = $this->request->variable('action', '');
		$submit = $this->request->is_set_post('submit');
		$reaction_type_id = $this->request->variable('reaction_type_id', 0);
			
		$acp_controller = $this->phpbb_container->get('steve.postreactions.acp_controller');
	
		add_form_key('reactions');
		if ($submit && !check_form_key('reactions'))
		{
			trigger_error('FORM_INVALID' . adm_back_link($this->u_action), E_USER_WARNING);
		}

		switch ($mode)
		{	
			case 'settings':
				$this->page_title = 'ACP_REACTIONS_TITLE';
				$acp_controller->reaction_settings($submit, $this->u_action);				
			break;
		
			case 'reactions':

				switch ($action)
				{
					case 'add':
					case 'edit':
					
						$this->page_title = ($action == 'add') ? 'ACP_REACTION_ADD' : 'ACP_REACTION_EDIT';
						$acp_controller->edit_add($submit, $reaction_type_id, $action, $this->u_action);
						
						return;
					break;
					
					case 'delete_data':
						$this->tpl_name = 'acp_reactions_type_delete';
						$acp_controller->delete_type($reaction_type_id, $mode, $this->u_action);
					break;

					case 'delete':
					
						if (!$reaction_type_id)
						{
							trigger_error('REACTION_TYPE_ID_EMPTY' . adm_back_link($this->u_action), E_USER_WARNING);
						}
						
						$this->tpl_name = 'acp_reactions_type_delete';
						
						$count = $acp_controller->reation_type_count($reaction_type_id);
						
						$this->template->assign_vars(array(
							'DELETE'		=> true,
							'U_DELETE'		=> $this->u_action . '&amp;action=delete_data&amp;reaction_type_id=' . $reaction_type_id . '&amp;count=' . $count . '&amp;hash=' . generate_link_hash('acp_reactions'),
						));
						
					break;
					
					case 'move_up':
					case 'move_down':
						$acp_controller->move_up_down($reaction_type_id, $action, $this->u_action);
					break;
					
					case 'activate':
					case 'deactivate':
						$acp_controller->activate_deactivate($reaction_type_id, $action, $this->u_action);
					break;
				}
			
				$this->page_title = 'ACP_REACTION_TYPES';
				$acp_controller->sort_reaction_order();			
				$acp_controller->acp_reactions_main($this->u_action);
				
			break;
			default;
		}
	}	
}
