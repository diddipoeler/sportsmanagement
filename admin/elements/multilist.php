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

defined('_JEXEC') or die('Restricted access');

/**
 * Renders a multiple item select element
 *
 */
 
class JFormFieldMultiList extends JFormField
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	protected $type = 'MultiList';
 
	function getInput() {
		// Base name of the HTML control.
		$ctrl	= $control_name .'['. $name .']';
 
		// Construct an array of the HTML OPTION statements.
		$options = array ();
		foreach ($node->children() as $option)
		{
			$val	= $option->attributes('value');
			$text	= $option->data();
			$options[] = JHTML::_('select.option', $val, JText::_($text));
		}
 
		// Construct the various argument calls that are supported.
		$attribs	= ' ';
		if ($v = $node->attributes( 'size' )) {
			$attribs	.= 'size="'.$v.'"';
		}
		if ($v = $node->attributes( 'class' )) {
			$attribs	.= 'class="'.$v.'"';
		} else {
			$attribs	.= 'class="inputbox"';
		}
		if ($m = $node->attributes( 'multiple' ))
		{
			$attribs	.= ' multiple="multiple"';
			$ctrl		.= '[]';
		}
 
		// Render the HTML SELECT list.
		//return JHTML::_('select.genericlist', $options, $ctrl, $attribs, 'value', 'text', $value, $control_name.$name );
		return JHTML::_('select.genericlist', $options, $ctrl, $attribs, 'value', 'text', array_map('trim', explode(',', $value)), $control_name.$name );
	}
}
