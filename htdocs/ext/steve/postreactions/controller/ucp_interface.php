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
 * ucp Post Reactions.
 */

interface ucp_interface
{
	/**
	* @access public
	*/	
	public function ucp_reaction_settings($u_action);
}
