<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
* @version   1.0.05
* @file      agegroup.php
* @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license   GNU General Public License version 2 or later; see LICENSE.txt
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
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
 * @author Wolfgang Pinitsch <and_one@aon.at>
 * @since  1.5.1-daniela
 * @return string html code for the colorpicker
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
         // add javascript
        //$method = (stripos($node->_attributes['name'] , 'JL_EXT') !== false)? 'extended' : 'params';
        $document = Factory::getDocument();
        $document->addStylesheet(JURI::base().'components/com_sportsmanagement/assets/js/js_color_picker_v2/js_color_picker_v2.css');
        $document->addScript(JURI::base().'components/com_sportsmanagement/assets/js/js_color_picker_v2/color_functions.js');
        $document->addScript(JURI::base().'components/com_sportsmanagement/assets/js/js_color_picker_v2/js_color_picker_v2.js');
        $output  = "<input type=\"text\" style=\"background-color: ".$this->value."\" 
					name=\"".$this->name."\" value=\"".$this->value."\">"; 
        $output .= "<input type=\"button\" value=\"#\" 
					onclick=\"showColorPicker(this, document.getElementsByName('".$this->name."')[0])\">";
        return $output;
    }
}
 
