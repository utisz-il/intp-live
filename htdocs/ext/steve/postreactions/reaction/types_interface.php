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
* Interface
*/
interface types_interface
{
	/**
	* 
	* @access public
	*/
	public function obtain_reaction_types($module_controller = false);
	
	public function obtain_reaction_type($type_id);
	
	//public function reation_type_count($type_id);
	
	public function display_reaction_types($reaction_types, $post_id, $user_type_id, $marked);
	
	public function reparse_data(&$post_data);
	
	public function get_reaction_file($reaction_file_name);

	public function reactions_image_path();
	
	public function delete_reaction_types_cache();
	
	public function tpr_common_vars();
}
