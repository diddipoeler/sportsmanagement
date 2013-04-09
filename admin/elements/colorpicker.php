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
 * 
 * display a colorpicker element
 * example usage:
 *	<pre>
 *		<param type="colorpicker" name="color1" label="LABEL COLORPICKER 1" description="DESCR COLORPICKER" />
 *		<param type="colorpicker" name="color2" label="LABEL COLORPICKER 2" description="DESCR COLORPICKER" />
 *	</pre>
 * 
 * @author Wolfgang Pinitsch <and_one@aon.at>
 * @since 1.5.1-daniela
 * @return string html code for the colorpicker
 */
class JFormFieldColorpicker extends JFormField
{

	protected $type = 'colorpicker';
	
	function getInput() {
			// add javascript
		//$method = (stripos($node->_attributes['name'] , 'JL_EXT') !== false)? 'extended' : 'params';
		$document = JFactory::getDocument();
		$document->addStylesheet(JURI::base().'components/com_joomleague/assets/js/js_color_picker_v2/js_color_picker_v2.css');
		$document->addScript(JURI::base().'components/com_joomleague/assets/js/js_color_picker_v2/color_functions.js');
		$document->addScript(JURI::base().'components/com_joomleague/assets/js/js_color_picker_v2/js_color_picker_v2.js');
		$output  = "<input type=\"text\" style=\"background-color: ".$this->value."\" 
					name=\"".$this->name."\" value=\"".$this->value."\">"; 
		$output .= "<input type=\"button\" value=\"#\" 
					onclick=\"showColorPicker(this, document.getElementsByName('".$this->name."')[0])\">";
		return $output;
	}
}
 