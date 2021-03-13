<?php
/**
 *
 * Tabbed Smilies. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, Lupe Fluffingtopn
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace fluffington\tabbedsmilies\controller;

use phpbb\config\config;
use phpbb\language\language;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\db\driver\driver_interface;

class acp_controller
{
	protected $config;
	protected $language;
	protected $request;
	protected $template;
	protected $db;
	protected $u_action;

	public $table;
	public $smilies;

	private $errors;

	public function __construct(config $config, language $language, request $request, template $template, driver_interface $db)
	{
		$this->config = $config;
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
		$this->db = $db;
		//Why isn't the table prefix part of the db class?
		global $table_prefix;
		$this->table = $table_prefix . 'smilies_tabs';
		$this->smilies = $table_prefix . 'smilies';
		//FIXME Real error messages
		$this->errors = False;
	}

	public function save_settings()
	{
		//Todo:s add settings for max/min width/height
		if(!check_form_key('fluffington_tabbedsmilies'))
		{
			trigger_error($this->language->lang('FORM_INVALID'),E_USER_WARNING);
		}
		
		$load	= $this->request->variable('tab_loading',1);
		$close	= $this->request->variable('tab_closed',0);
		$text	= $this->request->variable('tab_text',1);
		$icon	= $this->request->variable('tab_icon',1);
		$layout	= $this->request->variable('tab_layout',0);
		
		foreach ([$load,$close,$text,$icon] as $var)
		{
			if ($var == 0 || $var == 1)
			{
				continue;
			}else
			{
				$this->errors = True;
				break;
			}
		}
		if($layout < 0 or $layout > 3)
		{
			$this->errors = True;
		}
		
		$json = html_entity_decode($this->request->variable('sorting','',true));
		$sorted = json_decode($json,true);

		$json = html_entity_decode($this->request->variable('rename','',true));
		$renamed = json_decode($json,true);

		$json = html_entity_decode($this->request->variable('delete','',true));
		$deleted = json_decode($json,true);
		
		foreach ($renamed as $str)
		{
			if (strlen($str) > 50)
			{
				$this->errors = True;
				break;
			}
		}

		//We will start a sql transaction here. If we find errors later on we will rollback the whole thing.
		$this->db->sql_transaction('begin');

		//FIXME NEVER FORGET TO ESCAPE USER INPUT!!
		//FIXME Add sql error catching?
		
		if ($deleted !== NULL && !$this->errors)
		{
			foreach ($deleted as $value)
			{
				$tab_id = explode('-',$value);
				if ($this->id_check($tab_id))
				{
					$tab_id[1] = intval($tab_id[1]);

					if ($tab_id[1] != 1)
					{
						$this->db->sql_query("DELETE FROM $this->table WHERE id = $tab_id[1]");
						
						$update = $this->db->sql_build_array('UPDATE',[
							'tab_id' => 1,
							'tab_order' => 0,
						]);
						$this->db->sql_query("UPDATE $this->smilies SET $update WHERE tab_id = $tab_id[1]");
					}
					else
					{
						$this->errors = True;
					}
				}
			}
		}
		else
		{
			$this->errors = True;
		}

		if ($sorted !== NULL && $renamed !== NULL && !$this->errors)
		{
			$tab_postion = 0;
			foreach($sorted as $key => $value)
			{
				$tab_id = explode('-',$key);
				if ($this->id_check($tab_id))
				{
					$tab_id[1] = intval($tab_id[1]);

					if($tab_id[0] == "newtab")
					{
						if(!isset($renamed[$key]))
						{
							$renamed[$key] = "New";
						}
						$update = $this->db->sql_build_array("INSERT", [
							"name" => $renamed[$key],
							"tab_order" => $tab_postion,
						]);
						$this->db->sql_query("INSERT INTO $this->table $update");
						$tab_id[1] = $this->db->sql_nextid();
					}
					elseif($tab_id[0] == "tab")
					{
						if(isset($renamed[$key]))
						{
							$update = $this->db->sql_build_array("UPDATE", [
								"tab_order" => $tab_postion,
								"name" => $renamed[$key],
							]);
						}
						else
						{
							$update = $this->db->sql_build_array("UPDATE", [
								"tab_order" => $tab_postion,
							]);
						}
						$this->db->sql_query("UPDATE $this->table SET $update WHERE id = $tab_id[1]");
					}
					else
					{
						//We shouldn't be here unless someone is messing with the data. Even then, shouldn't the id_check() catch it?
						$this->errors = True;
					}

					$tab_postion++;
					$smiley_postion = 0;

					foreach($value as $smiley)
					{
						$smiley_id = explode('-',$smiley);
						if ($this->id_check($smiley_id))
						{
							$smiley_id[1] = intval($smiley_id[1]);

							$update = $this->db->sql_build_array("UPDATE",[
								"tab_order" => $smiley_postion,
								"tab_id" => $tab_id[1],
							]);
							$this->db->sql_query("UPDATE $this->smilies SET $update WHERE smiley_id = $smiley_id[1]");
							$smiley_postion++;
						}
					}
				}
			}
		}
		else{
			$this->errors = True;
		}

		//If there were any errors at all, rollback back everything.
		//Todo: Check for any sql errors and rolllback if found
		if($this->errors)
		{
			$this->db->sql_transaction('rollback');
			trigger_error($this->language->lang('ACP_TABBEDSMILIES_INVALID') . adm_back_link($this->u_action),E_USER_WARNING);
		}
		else
		{
			$this->db->sql_transaction('commit');
			$this->config->set('tabbed_smilies_loading',$load);
			$this->config->set('tabbed_smilies_closed',$close);
			$this->config->set('tabbed_smilies_text',$text);
			$this->config->set('tabbed_smilies_icon',$icon);
			$this->config->set('tabbed_smilies_layout',$layout);
			trigger_error($this->language->lang('ACP_TABBEDSMILIES_UPDATED') . adm_back_link($this->u_action),E_USER_NOTICE);
		}
	}

	public function display_tabs()
	{
		$this->language->add_lang('common', 'fluffington/tabbedsmilies');
		$errors = [];
		add_form_key('fluffington_tabbedsmilies');

		$tabs = [];
		$smilies = [];

		$this->template->assign_block_vars('tab_list',[
			'name'	=> $this->language->lang('TABBEDSMILIES_HIDDEN'),
			'id'	=> 0,
		]);

		$result = $this->db->sql_query('select * from ' . $this->table . ' order by tab_order');
		while($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('tab_list',[
				'name' => $row['name'],
				'id' => $row['id'],
			]);
		}

		$result = $this->db->sql_query('select * from ' . $this->smilies . ' order by tab_id,tab_order');
		while($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('smiley_list', [
				'code'		=> $row['code'],
				'emotion'	=> $row['emotion'],
				'height'	=> $row['smiley_height'],
				'id'		=> $row['smiley_id'],
				'tab_id'	=> $row['tab_id'],
				'url'		=> $row['smiley_url'],
				'width'		=> $row['smiley_width'],
			]);
		}
		
		$this->template->assign_vars([
			'tab_loading'	=> $this->config['tabbed_smilies_loading'],
			'tab_closed' 	=> $this->config['tabbed_smilies_closed'],
			'tab_text'		=> $this->config['tabbed_smilies_text'],
			'tab_icon'		=> $this->config['tabbed_smilies_icon'],
			'tab_layout'	=> $this->config['tabbed_smilies_layout'],
		]);

		// Set output variables for display in the template
		$this->template->assign_vars([
			'S_ERROR'		=> $s_errors,
			'ERROR_MSG'		=> $s_errors ? implode('<br />', $errors) : '',

			'U_ACTION'		=> $this->u_action,
		]);
	}

	public function id_check($id)
	{
		/*
		* The tab or smiley IDs should always match the same patteren. If not I call hacks.
		* Returns true is no errors or false if there was a problem.
		*/
		
		$err = False;

		if (!is_array($id))
		{
			$this->errors = True;
			return False;
		}

		if (sizeof($id) != 2)
		{
			$err = True;
		}

		if (!in_array($id[0], ["tab","newtab","smiley"],true))
		{
			$err = True;
		}

		if (!is_numeric($id[1]))
		{
			$err = True;
		}
		
		if (intval($id[1]) < 0)
		{
			$err = True;
		}

		if ($err)
		{
			$this->errors = True;
			return False;
		}
		else
		{
			return True;
		}
	}
				
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
