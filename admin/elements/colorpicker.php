<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage elements
 * @file       colorpicker.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

/**
 * display a colorpicker element
 * example usage:
 *    <pre>
 *        <param type="colorpicker" name="color1" label="LABEL COLORPICKER 1" description="DESCR COLORPICKER" />
 *        <param type="colorpicker" name="color2" label="LABEL COLORPICKER 2" description="DESCR COLORPICKER" />
 *    </pre>
 *
 * @return string html code for the colorpicker
 * @since  1.5.1-daniela
 * @author Wolfgang Pinitsch <and_one@aon.at>
 */
class JFormFieldColorpicker extends JFormField
{
	protected $type = 'colorpicker';

	/**
	 * JFormFieldColorpicker::getInput()
	 *
	 * @return
	 */
	function getInput()
	{
		// Add javascript
		// $method = (stripos($node->_attributes['name'] , 'JL_EXT') !== false)? 'extended' : 'params';
		$document = Factory::getDocument();
		$document->addStylesheet(JURI::base() . 'components/com_sportsmanagement/assets/js/js_color_picker_v2/js_color_picker_v2.css');
		$document->addScript(JURI::base() . 'components/com_sportsmanagement/assets/js/js_color_picker_v2/color_functions.js');
		$document->addScript(JURI::base() . 'components/com_sportsmanagement/assets/js/js_color_picker_v2/js_color_picker_v2.js');
		$output = "<input type=\"text\" style=\"background-color: " . $this->value . "\"
					name=\"" . $this->name . "\" value=\"" . $this->value . "\">";
		$output .= "<input type=\"button\" value=\"#\"
					onclick=\"showColorPicker(this, document.getElementsByName('" . $this->name . "')[0])\">";

		return $output;
	}
}

