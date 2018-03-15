<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
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

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );


/**
 * JFormFieldJLGColor
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class JFormFieldJLGColor extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.3
	 */
	protected $type = 'JLGColor';

	
	/**
	 * JFormFieldJLGColor::getInput()
	 * 
	 * @return
	 */
	protected function getInput()
	{
		$app = JFactory::getApplication();
        $document = JFactory::getDocument();
		$document->addScript(JURI::base().'components/com_sportsmanagement/assets/js/301a.js');
		
		// Initialize some field attributes.
		$size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$classes = (string) $this->element['class'];
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' size<br><pre>'.print_r($size,true).'</pre>'),'Notice');

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		$class = $classes ? ' class="' . trim($classes) . '"' : '';
		
		$document->addScriptDeclaration("
				window.addEvent('domready', function() {
					$$('.pickerbutton').addEvent('click', function(){
						var field = this.id.substr(12);
						showColorGrid3(field,'sample_'+field);
						return overlib(document.id('colorpicker301').innerHTML, 
						               CAPTION,'Farbe:', BELOW, RIGHT,FOLLOWMOUSE=false, 
						               CLOSEBTN=true, CLOSEBTNCOLORS=['white', 'white', 'white', 'white'], 
				                   STICKY=false);
					});
					document.id('sample_".$this->id."').style.backgroundColor = document.id('".$this->id."').value;
				});
		");
		
		$html = array();		
		$html[] = '<input class="inputbox" type="text" id="sample_'.$this->id.'" size="1" value="">&nbsp;';		
		$html[] = '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . '/>';
		$html[] = '<input	type="button" id="buttonpicker'.$this->id.'" class="inputbox pickerbutton" value="..."/>';
		$html[] = '<div	id="colorpicker301" class="colorpicker301" style="position:absolute;top:0px;left:0px;z-index:1000;display:none;"></div>';

		return implode("\n",$html);
	}
}
