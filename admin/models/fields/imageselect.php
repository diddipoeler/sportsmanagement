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

defined('_JEXEC') or die('Restricted access');

/**
 * JFormFieldImageSelect
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class JFormFieldImageSelect extends JFormField
{
	protected $type = 'imageselect';

	/**
	 * JFormFieldImageSelect::getInput()
	 * 
	 * @return
	 */
	function getInput() 
    {
        $app	= JFactory::getApplication();
		$option = $app->input->getCmd('option');
        
		$default = $this->value;
		$arrPathes = explode('/', $default);
		$filename = array_pop($arrPathes);
		//$targetfolder = array_pop($arrPathes);
		$targetfolder = $this->element['targetfolder'];
        
        //$app->enqueueMessage(JText::_('JFormFieldImageSelect targetfolder<br><pre>'.print_r($targetfolder,true).'</pre>'),'Notice');
        
		
// 		echo 'this->value -> '.$this->value.'<br>';
// 		echo 'this->name -> '.$this->name.'<br>';
// 		echo 'filename -> '.$filename.'<br>';
// 		echo 'targetfolder -> '.$targetfolder.'<br>';
		
		$output  = ImageSelectSM::getSelector($this->name, $this->name.'_preview', $targetfolder, $this->value, $default, $this->name, $this->id);
		$output .= '<img class="imagepreview" src="'.JURI::root(true).'/media/com_sportsmanagement/jl_images/spinner.gif" '; 
		$output .= ' name="'.$this->name.'_preview" id="'.$this->name.'_preview" border="3" alt="Preview" title="Preview" />';
		$output .= '<input type="hidden" id="'.$this->id.'" name="'.$this->name.'" value="'.$this->value.'" />';
		return $output;
	}
}
