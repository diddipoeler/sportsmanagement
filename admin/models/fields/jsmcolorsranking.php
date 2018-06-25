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

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');
jimport('joomla.html.html');
jimport('joomla.form.formfield');
// Get the pane and slider class
jimport('joomla.html.pane');

/**
 * JFormFieldjsmcolorsranking
 * http://docs.joomla.org/Creating_a_modal_form_field
 * http://docs.joomla.org/Creating_a_custom_form_field_type
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
	public function getInput()
	{
		$app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        $select_id = JFactory::getApplication()->input->getVar('id');
        //$this->value = explode(",", $this->value);
        $rankingteams = $this->element['rankingteams'];
        $templatename = $this->element['templatename'];
        $templatefield = $this->element['name'];
        // Initialize variables.
        $html = array();
        
        //build the html options for extratime
		$select_ranking[] = JHtmlSelect::option('0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'));
        for($a=1; $a <= $rankingteams ; $a++)
                {
                $select_ranking[] = JHtmlSelect::option($a,$a);    
                    
                }    

        $select_Options = sportsmanagementHelper::getExtraSelectOptions($templatename,$templatefield,TRUE);
        
        
        if ( $select_Options )
        {
            $select_text[] = JHtmlSelect::option('',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'));
            foreach ( $select_Options as $row )
            {
                $select_text[] = JHtmlSelect::option($row->value,$row->text); 
            }
        }

            $html[] = '<table>';
            $html[] = '<tr>';
            $html[] = '<th>';
            $html[] = 'von'; 
            $html[] = '</th>';
            $html[] = '<th>';
            $html[] = 'bis'; 
            $html[] = '</th>';
            $html[] = '<th>';
            $html[] = 'farbe'; 
            $html[] = '</th>';
            $html[] = '<th>';
            $html[] = 'text'; 
            $html[] = '</th>';
            $html[] = '</tr>';  
                
                for($a=1; $a <= $rankingteams ; $a++)
                {
                    
                if ( !isset($this->value[$a]) )
                {
                $this->value[$a]['von'] = '';
		$this->value[$a]['bis'] = '';
		$this->value[$a]['text'] = '';
		$this->value[$a]['color'] = '';
                }
 
               $html[] = '<tr>';
                $html[] = '<td>';    
                $html[] = JHtml::_(	'select.genericlist',$select_ranking,
													$this->name . '['. $a .'][von]"','class="inputbox" size="1"','value','text',
													$this->value[$a]['von']);
                $html[] = '</td>'; 
                $html[] = '<td>';    
                $html[] = JHtml::_(	'select.genericlist',$select_ranking,
													$this->name . '['. $a .'][bis]"','class="inputbox" size="1"','value','text',
													$this->value[$a]['bis']);
                
                $html[] = '</td>';  
                $html[] = '<td>';    
                $html[] = '<input type="text" class="color {hash:true,required:false}" id="' . $this->id . '" name="' . $this->name . '['. $a .'][color]"' . ' value="' .$this->value[$a]['color']. '" size="5"' . '/>';
                $html[] = '</td>';  
                $html[] = '<td>'; 
                if ( $select_Options )
        {
            $html[] = JHtml::_(	'select.genericlist',$select_text,
													$this->name . '['. $a .'][text]"','class="inputbox" size="1"','value','text',
													$this->value[$a]['text']);
            }
            else
            {
                $html[] = '<input type="text" class="inputbox" id="' . $this->id . '" name="' . $this->name . '['. $a .'][text]"' . ' value="' .$this->value[$a]['text']. '" size="40"' . '/>';
            }
                
                $html[] = '</td>';               
                $html[] = '</tr>';  

                }    
            $html[] = '</table>';
                    
        

            return implode($html);     
    
    }
}
