<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
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

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');
jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * JFormFieldjsmcolorsranking
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class JFormFieldjsmcolorsranking extends JFormField
{
	/**
	 * field type
	 * @var string
	 */
	public $type = 'jsmcolorsranking';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $select_id = JRequest::getVar('id');
        //$this->value = explode(",", $this->value);
        $rankingteams = $this->element['rankingteams'];
        //$targetid = $this->element['targetid'];
        
        
	
    
   // $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' rankingteams<br><pre>'.print_r($rankingteams,true).'</pre>'),'');
    //$mainframe->enqueueMessage(JText::_('JFormFieldseasoncheckbox getInput options<br><pre>'.print_r($options,true).'</pre>'),'');
   


// Initialize variables.
            $html = array();
           
            //$html[] = '<fieldset id="' . $this->id . '"' .  '>';
            //$html[] = '<table>';
            $html[] = '<ul class="config-option-list>';
//            $html[] = '<li>';
//            $html[] = '<table>';
//            $html[] = '<tr>';
//            $html[] = '<th>';
//            $html[] = 'von'; 
//            $html[] = '</th>';
//            $html[] = '<th>';
//            $html[] = 'bis'; 
//            $html[] = '</th>';
//            $html[] = '<th>';
//            $html[] = 'farbe'; 
//            $html[] = '</th>';
//            $html[] = '<th>';
//            $html[] = 'text'; 
//            $html[] = '</th>';
//            $html[] = '</tr>';  
                for($a=1; $a <= $rankingteams ; $a++)
                {
                    
                if ( !is_array($this->value) )
                {
                $this->value[$a]['von'] = '';
                }
                
                $html[] = '<li>';    
//                $html[] = '<tr>';
//                $html[] = '<td>';    
                $html[] = '<input type="text" id="' . $this->id . $i . '" name="' . $this->name . '['. $a .'][von]"' . ' value="' .$this->value[$a]['von']. '" size="5"' . '/>';
//                $html[] = '</td>'; 
//                $html[] = '<td>';    
                $html[] = '<input type="text" id="' . $this->id . $i . '" name="' . $this->name . '['. $a .'][bis]"' . ' value="' .$this->value[$a]['bis']. '" size="5"' . '/>';
//                $html[] = '</td>';  
//                $html[] = '<td>';    
                $html[] = '<input type="text" class="color {hash:true,required:false}" id="' . $this->id . $i . '" name="' . $this->name . '['. $a .'][color]"' . ' value="' .$this->value[$a]['color']. '" size="5"' . '/>';
//                $html[] = '</td>';  
//                $html[] = '<td>';    
                $html[] = '<input type="text" id="' . $this->id . $i . '" name="' . $this->name . '['. $a .'][text]"' . ' value="' .$this->value[$a]['text']. '" size="50"' . '/>';
//                $html[] = '</td>';               
//                $html[] = '</tr>';  
                $html[] = '</li>'; 
                }    
//                $html[] = '</table>';
//                $html[] = '</li>';
                $html[] = '</ul>';  
            //$html[] = '</table>';
           
            //$html[] = '</fieldset>';         
    
            //return $html;
            return implode($html);      
    
    }
}
