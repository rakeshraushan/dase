<?php

/*
 *   see http://www.phpinsider.com/smarty-forum/viewtopic.php?t=8944
 *
 *   Copyright (c) 2006, Matthias Kestenholz <mk@spinlock.ch>, Moritz Zumbühl <mail@momoetomo.ch>
 *   Distributed under the GNU General Public License.
 *   Read the entire license text here: http://www.gnu.org/licenses/gpl.html
 *
 *   this class adds Django-style template inheritance to Smarty
 *
 *   adapted for DASe by Peter Keane 4/2008
 *
 */  


class Dase_Template {

	protected $smarty;

	public function __construct($request,$use_module_template_dir=false)
	{
		// make sure E_STRICT is turned off
		$er = error_reporting(E_ALL^E_NOTICE);
		require_once DASE_PATH . '/lib/smarty/libs/Smarty.class.php';
		$this->smarty = new Smarty();
		$this->smarty->compile_dir = CACHE_DIR;
		$this->smarty->compile_id = $request->module ? $request->module : 'smarty';
		if ($use_module_template_dir) {
			$this->smarty->template_dir = DASE_PATH . '/modules/'.$request->module.'/templates';
		} else {
			$this->smarty->template_dir = DASE_PATH . '/templates';
		}
		$this->smarty->caching = false;
		$this->smarty->security = false;
		$this->smarty->register_block('foreachgroup', 'smarty_block_foreachgroup');
		$this->smarty->register_block('block', '_smarty_swisdk_process_block');
		$this->smarty->register_function('extends', '_smarty_swisdk_extends');
		$this->smarty->register_modifier('filter', '_smarty_dase_atom_feed_filter');
		$this->smarty->register_modifier('sortby', '_smarty_dase_atom_feed_sortby');
		$this->smarty->register_modifier('select', '_smarty_dase_atom_entry_select');
		$this->smarty->register_modifier('media', '_smarty_dase_atom_entry_select_media');
		$this->smarty->register_modifier('modifiers','_smarty_documenter_modifiers');
		$this->smarty->register_modifier('params','_smarty_documenter_get_params');
		$this->smarty->assign_by_ref('_swisdk_smarty_instance', $this);

		$this->smarty->register_modifier('shift', 'array_shift');
		$this->smarty->assign('app_root', APP_ROOT.'/');
		if ($request->module) {
			$this->smarty->assign('module_root', APP_ROOT.'/modules/'.$request->module.'/');
		}
		$this->smarty->assign('msg', $request->get('msg'));
		$this->smarty->assign('request', $request);
		$this->smarty->assign('main_title', Dase_Config::get('main_title'));
		error_reporting($er);
	}

	public function __call($method, $args)
	{
		$er = error_reporting(E_ALL^E_NOTICE);
		$ret = call_user_func_array( array(&$this->smarty, $method), $args);
		error_reporting($er);
		return $ret;
	}

	public function __get($var)
	{
		$er = error_reporting(E_ALL^E_NOTICE);
		$ret = $this->smarty->$var;
		error_reporting($er);
		return $ret;
	}

	public function __set($var, $value)
	{
		$er = error_reporting(E_ALL^E_NOTICE);
		$ret = ($this->smarty->$var = $value);
		error_reporting($er);
		return $ret;
	}

	public function display($resource_name)
	{
		echo $this->fetch($resource_name);
	}

	public function fetch($resource_name)
	{
		$ret = $this->smarty->fetch($resource_name);
		while($resource = $this->_derived) {
			$this->_derived = null;
			$ret = $this->smarty->fetch($resource);
		}
		return $ret;
	}

	function __destruct() 
	{
		Dase_Log::debug('finished templating '.Dase_Timer::getElapsed());
	}
	// template inheritance
	public $_blocks = array();
	public $_derived = null;
}

/** free-floating functions! */
function _smarty_dase_atom_feed_filter(Dase_Atom_Feed $feed,$att,$val)
{
	//returns an array of entries that match 
	return $feed->filter($att,$val);
}

function _smarty_dase_atom_feed_sortby(Dase_Atom_Feed $feed,$att)
{
	return $feed->sortBy($att);
}

function _smarty_dase_atom_entry_select(Dase_Atom_Entry $entry,$att)
{
	//returns value of attribute 
	return $entry->select($att);
}

function _smarty_dase_atom_entry_select_media(Dase_Atom_Entry $entry,$size)
{
	//returns media of stated size 
	return $entry->selectMedia($size);
}

function _smarty_documenter_modifiers(Documenter $d,$value)
{
	//used in documentation 
	return $d->getModifiers($value);
}

/** here is a function used in docs */
function _smarty_documenter_get_params(ReflectionParameter $p)
{
	//from no starch oo php book
	$description = "";
	//is it an object?
	$c = $p->getClass();
	if(is_object($c)){
		$description .= $c->getName() . " ";
	}
	$description .= "\$" . $p->getName();
	//check default
	if ($p->isDefaultValueAvailable()){
		$val = $p->getDefaultValue();
		//could be empty string
		if($val == ""){
			$val = "\"\"";
		}
		$description .= " = $val";
	}
	return $description;
}

function _smarty_swisdk_process_block($params, $content, &$smarty, &$repeat)
{
	if($content===null)
		return;
	$name = $params['name'];
	$ss = $smarty->get_template_vars('_swisdk_smarty_instance');
	if(!isset($ss->_blocks[$name]))
		$ss->_blocks[$name] = $content;
	return $ss->_blocks[$name];
}

function _smarty_swisdk_extends($params, &$smarty)
{
	$ss = $smarty->get_template_vars('_swisdk_smarty_instance');
	$ss->_derived = $params['file'];
} 


/** tucker b requested this */
function smarty_block_foreachgroup($params, $content, $smarty, &$repeat)
{
	static $grouparray = array();
	
	//Check required parameters
	if (!isset($params['from']))
		$smarty->trigger_error('foreachgroup: missing "from" parameter.');
	if (!isset($params['group_by']))
		$smarty->trigger_error('foreachgroup: missing "groupby" parameter');
		
	//If this is the first pass, sort into groups
	if (is_null($content))
	{
		$from = &$params['from'];
		foreach ($from as $current)
		{
			$key = eval('return '.$params['group_by'].';');
			if (!isset($grouparray[$key]))
				$grouparray[$key] = array();
			$grouparray[$key][] = $current;
		}
	}
	
	//For all other passes, get the next group
	//and set special variables.
	if (list($key, $item) = each($grouparray))
	{
		$smarty->assign('current_grouping_key', $key);
		$smarty->assign('current_group', $item);
		$repeat = true;
	}
	//If no more groups, stop the cycle.
	else
	{
		$repeat = false;
	}
	
	if (!is_null($content))
		return $content;
}

