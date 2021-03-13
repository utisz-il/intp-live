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
interface delete_interface
{
	/**
	* 
	* @access public
	*/
	public function update_reaction_counts($user_id);

	public function delete_post_reactions($in_set = '', $ids);
	
	public function check_array_ids($in_set = '', $ids);
}
