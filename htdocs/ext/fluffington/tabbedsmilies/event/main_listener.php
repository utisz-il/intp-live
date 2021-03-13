<?php
/**
 *
 * Tabbed Smilies. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, Lupe Fluffington
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace fluffington\tabbedsmilies\event;

use phpbb\db\driver\driver;
use phpbb\db\driver\driver_interface;
use phpbb\notification\manager;
use phpbb\template\template;
use phpbb\config\config;
use phpbb\path_helper;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup'					=> 'load_language_on_setup',
			'core.ucp_pm_compose_template'		=> 'prep_tabs_pm',
			'core.posting_modify_template_vars'	=> 'prep_tabs_posting',
			'dmzx.mchat.render_page_after'		=> 'prep_tabs_mchat',
		];
	}

	protected $db;
	protected $config;
	protected $table;

	public function __construct(template $template, driver_interface $db, config $config, path_helper $path_helper)
	{
		$this->template = $template;
		$this->db = $db;
		$this->config = $config;
		global $table_prefix;
		$this->table = $table_prefix . "smilies_tabs";
		$this->smilies = $table_prefix . "smilies";
		$this->smiley_path = $path_helper->get_web_root_path() . $config['smilies_path'] . '/';
		$this->smilies_allowed = False;
	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'fluffington/tabbedsmilies',
			'lang_set' => 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}
	
	public function prep_tabs_pm($event)
	{
		if($event['template_ary']['S_SMILIES_ALLOWED'])
		{
			$template = $event['template_ary'];
			$template['S_SMILIES_ALLOWED'] = 0;
			$event['template_ary'] = $template;
			$this->generate_tabs();
		}
	}
	
	public function prep_tabs_posting($event)
	{
		if($event['page_data']['S_SMILIES_ALLOWED'])
		{
			$template = $event['page_data'];
			$template['S_SMILIES_ALLOWED'] = 0;
			$event['page_data'] = $template;
			$this->generate_tabs();
		}
	}
	
	public function prep_tabs_mchat($event)
	{
		if($event['template_data']['MCHAT_ALLOW_SMILES'])
		{
			$template = $event['template_data'];
			$template['MCHAT_ALLOW_SMILES'] = 0;
			$event['template_data'] = $template;
			$this->generate_tabs();
		}
	}

	private function generate_tabs()
	{
		$this->template->assign_var('tab_smilies_allowed', 1);

		$tabs = [];
		$query = "select * from $this->table order by tab_order";
		$result = $this->db->sql_query($query);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$tabs[$row['id']] = [
					'name'		=> $row['name'],
					'id'		=> $row['id'],
					'smilies'	=> [],
				];
		}
		
		$this->template->assign_vars([
			'tab_loading'	=> $this->config['tabbed_smilies_loading'],
			'tab_closed' 	=> $this->config['tabbed_smilies_closed'],
			'tab_text'		=> $this->config['tabbed_smilies_text'],
			'tab_icon'		=> $this->config['tabbed_smilies_icon'],
			'tab_layout'	=> $this->config['tabbed_smilies_layout'],
		]);

		$result = $this->db->sql_query("select * from $this->smilies where tab_id != 0 order by tab_id,tab_order");
		global $root_path,$config;
		while($row = $this->db->sql_fetchrow($result))
		{
			$row['smiley_url'] = $this->smiley_path . $row['smiley_url'];
			$tabs[$row["tab_id"]]['smilies'][] = $row;
		}
		$this->template->assign_var("tabs",$tabs);
	}
}
