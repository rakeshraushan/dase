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

	public function __construct($compile_id = '')
	{
		// make sure E_STRICT is turned off
		$er = error_reporting(E_ALL^E_NOTICE);
		require_once DASE_PATH . '/lib/smarty/libs/Smarty.class.php';
		$this->smarty = new Smarty();
		$this->smarty->compile_dir = CACHE_DIR;
		if (!$compile_id) {
			$compile_id = 'smarty';
		}
		$this->smarty->compile_id = $comple_id;
		$this->smarty->template_dir = DASE_PATH . '/templates';
		$this->smarty->caching = false;
		$this->smarty->security = false;
		$this->smarty->register_block('block', '_smarty_swisdk_process_block');
		$this->smarty->register_function('extends', '_smarty_swisdk_extends');
		$this->smarty->assign_by_ref('_swisdk_smarty_instance', $this);

		$this->smarty->register_modifier('shift', 'array_shift');
		$this->smarty->assign('app_root', APP_ROOT.'/');
		$this->smarty->assign('msg', Dase_Filter::filterGet('msg'));
		$this->smarty->assign('page_hook',Dase_Registry::get('handler').'_'.Dase_Registry::get('action'));

		error_reporting($er);
	}

	public function __call($method, $args)
	{
		$er = error_reporting(E_ALL^E_NOTICE);
		$ret = call_user_func_array(
			array(&$this->smarty, $method),
			$args);
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

	// template inheritance
	public $_blocks = array();
	public $_derived = null;
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
