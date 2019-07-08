<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      dependsql_jl.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage fields
 */
 
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
FormHelper::loadFieldClass('list');

HTMLHelper::_( 'behavior.framework' );


/**
 * FormFieldDependSQL
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2018
 * @version $Id$
 * @access public
 */
class JFormFieldDependSQL extends FormField
{
	/**
	 * field name
	 *
	 * @access protected
	 * @var string
	 */
	protected $type = 'dependsql';

	/**
	 * FormFieldDependSQL::getInput()
	 * 
	 * @return
	 */
	protected function getInput()
	{
		// elements
		//$required   = $this->element['required'] ? ' required aria-required="true"' : '';
		$required     = $this->required ? ' required aria-required="true"' : '';
		$key 		= ($this->element['key_field'] ? $this->element['key_field'] : 'value');
		$val 		= ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
		$task 		= $this->element['task'];
		$depends 	= $this->element['depends'];
		$ctrl 		= $this->name;
		
		// Attribs
		$attribs	 = ' task="'.$task.'"';
		$attribs	.= $required;
		if ($v = $this->element['size'])
		{
			$attribs .= ' size="'.$v.'"';
		}
		if ($depends)
		{
			$attribs	.= ' depends="'.$depends.'"';
		}
		$attribs	.= ' class="inputbox';
		// Optionally add "depend" to the class attribute
		if ($depends)
		{
			$attribs	.= ' depend"';
		}
		else
		{
			$attribs	.= '"';
		}
		$attribs	.= ' current="'.$this->value.'"';
	
		if ($required=='true') {
			$options = array();
		}
		else {

			$options = array();
		}

		$query = $this->element['query'];
		if ($query!='')
		{
			$db = Factory::getDbo();
			$db->setQuery($query);
			$options = array_merge($options, $db->loadObjectList());
		}
		
		if ($depends)
		{
			$doc = Factory::getDocument();
			$doc->addScript(Uri::base() . 'components/com_sportsmanagement/assets/js/depend.js' );
		}

		return HTMLHelper::_('select.genericlist',  $options, $this->name, trim($attribs), $key, $val, $this->value, $this->id);
		
	}
}
