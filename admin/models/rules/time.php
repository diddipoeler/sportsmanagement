<?php

defined('_JEXEC') or die('Restricted access');
 
// import Joomla formrule library
jimport('joomla.form.formrule');
 
/**
 * Form Rule class for the Joomla Framework.
 */
class JFormRuleTime extends JFormRule
{
	/**
	 * The regular expression.
	 *
	 * @access	protected
	 * @var		string
	 * @since	2.5
	 */
	protected $regex = '^[0-9]{1,2}:[0-9]{1,2}$';
	
	public function test(SimpleXMLElement &$element, $value, $group = null, &$input = null, &$form = null)
	{
		if ($value == null or $value == '') {
			return true;
		}
		return parent::test($element, $value, $group, $input, $form);
	}
}