<?php 
/**
* @copyright	Copyright (C) 2007-2013 JoomLeague.net. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); // Check to ensure this file is included in Joomla!

JHTML::_( 'behavior.mootools' );

/**
 * Renders a Dynamic SQL field
 *
 * in the xml field, the following fields must be defined:
 * - depends: list of fields name this field depends on, separated by comma (e.g: "p, tid")
 * - task: the task used to return the query, using defined depends field names as parameters for query (=> 'index.php?option=com_joomleague&controller=ajax&task=<task>&p=1&tid=34')
 * @package Joomleague
 * @subpackageParameter
 * @since1.5
 */
class JFormFieldDependSQL extends JFormField
{
	/**
	 * field name
	 *
	 * @access protected
	 * @var string
	 */
	protected $type = 'dependsql';

	function getInput()
	{
		$required = $this->element['required'] == "true" ? 'true' : 'false';
		$key = ($this->element['key_field'] ? $this->element['key_field'] : 'value');
		$val = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
		$task = $this->element['task'];
		$depends = $this->element['depends'];

		$ctrl = $this->name;
		
		// Construct the various argument calls that are supported.
		$attribs	 = ' task="'.$task.'"';
		$attribs	.= ' required="'.$required.'"';
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
		
		$lang = JFactory::getLanguage();
		$lang->load("com_joomleague", JPATH_ADMINISTRATOR);
		if ($required) {
			$options = array();
		}
		else {
			$options = array(JHTML::_('select.option', '', JText::_('COM_JOOMLEAGUE_GLOBAL_SELECT'), $key, JText::_($val)));
		}

		$query = $this->element['query'];
		if ($query!='')
		{
			$db = JFactory::getDBO();
			$db->setQuery($query);
			$options = array_merge($options, $db->loadObjectList());
		}
		
		if ($depends)
		{
			$doc = JFactory::getDocument();
			$doc->addScript(JURI::base() . 'components/com_joomleague/assets/js/depend.js' );
		}

		return JHTML::_('select.genericlist',  $options, $ctrl, $attribs, $key, $val, $this->value, $this->id);
	}
}
