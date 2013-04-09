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
 * Renders a Dynamic SQL element
 *
 * in the xml element, the following elements must be defined:
 * - depends: list of elements name this element depends on, separated by comma (e.g: "p, tid")
 * - task: the task used to return the query, using defined depends element names as parameters for query (=> 'index.php?option=com_joomleague&controller=ajax&task=<task>&p=1&tid=34')
 * @package Joomleague
 * @subpackageParameter
 * @since1.5
 */
class JFormFieldMultiDependSQL extends JFormField
{
	/**
	 * Element name
	 *
	 * @accessprotected
	 * @varstring
	 */
	protected $type = 'multidependsql';

	function getInput()
	{
		// TODO: for the moment always require a selection, because when it is set to 0, the multiselection
		// will also select the empty line, next to the real selected ones. This will lead to a longer link
		// (all selected ids (e.g. events or stats) will be included in the link address), so this should
		// be fixed later, so that when nothing is selected, only id=0 will be in the link address.
		//$required = (int) $node->attributes('required');
		$required = 1;
		$key = ($this->element['key_field'] ? $this->element['key_field'] : 'value');
		$val = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
		$task = $this->element['task'];
		$depends = $this->element['depends'];
		$query = $this->element['query'];
		
		$ctrl = $this->name;
		
		// Construct the various argument calls that are supported.
		$attribs	 = ' task="'.$task.'"';
		$attribs	.= ' isrequired="'.$required.'"';
		if ($v = $this->element['size'])
		{
			$attribs	.= 'size="'.$v.'"';
		}

		if ($depends)
		{
			$attribs	.= ' depends="'.$depends.'"';
		}
		$attribs	.= ' class="mdepend inputbox';
		// Optionally add "depend" to the class attribute
		if ($depends)
		{
			$attribs	.= ' depend"';
		}
		else
		{
			$attribs	.= '"';
		}
		
		$value = is_array($this->value) ? $this->value[0] : $this->value; 
		$attribs	.= ' current="'.$value.'"';
		$attribs	.= ' multiple="multiple"';
		
		$selected = explode("|", $value);

		if ($required)
		{
			$options = array();
		}
		else
		{
			$options = array(JHTML::_('select.option', '', JText::_('Select'), $key, $val));
		}

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

		// Render the HTML SELECT list.
		$text = JHTML::_('select.genericlist', $options, 'l'.$ctrl, $attribs, $key, $val, $selected );
		$text .= '<input type="hidden" name="'.$ctrl.'" id="'.$this->id.'" value="'.$value.'"/>';
		return $text;
	}
}
