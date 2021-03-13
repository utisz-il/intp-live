<?php
/**
 *
 * Topic/Post Reactions. An extension for the phpBB 3.2.0 Forum Software package.
 * @author Steve <http://www.steven-clark.online/phpBB3-Extensions/>
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace steve\postreactions\controller;

/**
 * Post Reactions.
 */

interface acp_interface
{
	/**
	* @access public
	*/	
	public function reaction_settings($submit, $u_action);
	
	/**
	* @access public
	*/	
	public function edit_add($submit, $reaction_type_id, $action, $u_action);
	
	/**
	* @access public
	*/
	public function delete_type($reaction_type_id, $mode, $u_action);
	
	/**
	* @access public
	*/	
	public function move_up_down($reaction_type_id, $action, $u_action);
	
	/**
	* @access public
	*/	
	public function activate_deactivate($reaction_type_id, $action, $u_action);
	
	/**
	* @access public
	*/
	public function select_reaction_image($reactions_image_path, $image);
	
	/**
	* @access public
	*/
	public function sort_reaction_order();
	
	/**
	* @access public
	*/	
	public function acp_reactions_main($u_action);
	public function reation_type_count($type_id);
}
