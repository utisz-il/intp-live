<?php
/**
 *
 * Topic/Post Reactions. An extension for the phpBB 3.2.0 Forum Software package.
 * @author Steve <http://www.steven-clark.online/phpBB3-Extensions/>
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 * 
 */


namespace steve\postreactions\migrations;

use \phpbb\db\migration\container_aware_migration;

/**
* update migration
*/

class mu1_tp_reactions extends container_aware_migration
{
/* 	public function effectively_installed()
	{
		return isset($this->config['reactions_version']) && version_compare($this->config['reactions_version'], '0.5.0', '>=');
	} */
	
	static public function depends_on()
	{
		return array(
			'\steve\postreactions\migrations\m1_tp_reactions',
		);
	}
	
	public function update_data()
	{
		return array(
			array('config.update', array('reactions_version', '0.5.5-dev')),
			array('config.add', array('reactions_resync_enable', true)),
			array('permission.add', array('u_resync_reactions')),
			
			array('custom', array(array($this, 'post_reaction_data'))),
		);
	}
	
	public function update_schema()
	{
		return array(
			'change_columns'    => array(
				$this->table_prefix . 'posts'	=> array(
					'post_reaction_data'	=> array('TEXT', null),
				),
			),
		);
	}
	
	public function post_reaction_data()
	{
		$sql = 'SELECT post_id, post_reaction_data, post_reactions
			FROM ' . POSTS_TABLE . '
			WHERE post_reaction_data <> ""';
		$result = $this->db->sql_query($sql);

		$post_data = array();
		if ($row = $this->db->sql_fetchrow($result))
		{
			do
			{			
				if (empty($row))
				{
					continue;
				}
				$post_data[] = $row;
			}
			while ($row = $this->db->sql_fetchrow($result));
			
			if (!empty($post_data))
			{
				foreach ($post_data as $data)
				{
					$regex = preg_match_all('/(\d+):+[a-zA-Z\.0-9_-]+.(gif|jpg|jpeg|png|svg):+(\d+)/', $data['post_reaction_data']);
					if ($regex && $data['post_reactions'] > 0)
					{
						$post_reaction_data = explode('|', $data['post_reaction_data']);
						
						$json_data = array();
						foreach ($post_reaction_data as $key => $reaction_data)
						{
							list($reaction_id, $reaction_image, $reaction_count) = explode(':', $reaction_data);
							
							$json_data[$key] = array(
								'id'		=> $reaction_id,
								'src'		=> $reaction_image,
								'count'		=> (!empty($reaction_count)) ? $reaction_count : 1,
							);				
						}
						
						$json_data = array_map("unserialize", array_unique(array_map("serialize", $json_data)));
						$post_data = array_values($json_data);
						$post_data = json_encode($json_data);
					}
					else if ($regex)
					{
						$post_data = "";//
					}
					
					$sql = 'UPDATE ' . POSTS_TABLE . "
						SET post_reaction_data = '" . $this->db->sql_escape($post_data) . "'
						WHERE post_id = " . (int) $data['post_id'];
					$this->db->sql_query($sql);
				}
				unset($post_reaction_data, $post_reaction_data);
			}
		}
		$this->db->sql_freeresult($result);
	}
}
