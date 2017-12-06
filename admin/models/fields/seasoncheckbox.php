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
 * JFormFieldseasoncheckbox
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class JFormFieldseasoncheckbox extends JFormField
{
	/**
	 * field type
	 * @var string
	 */
	public $type = 'checkboxes';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        $select_id = JFactory::getApplication()->input->getVar('id');
        $this->value = explode(",", $this->value);
        $targettable = $this->element['targettable'];
        $targetid = $this->element['targetid'];
        
        
        //$app->enqueueMessage(JText::_('JFormFieldseasoncheckbox getInput targettable<br><pre>'.print_r($targettable,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_('JFormFieldseasoncheckbox getInput targetid<br><pre>'.print_r($targetid,true).'</pre>'),'');
    
    
        // Initialize variables.
		//$options = array();
    
    //$db = JFactory::getDbo();
			$query = JFactory::getDbo()->getQuery(true);
			// saisons selektieren
			$query->select('id AS value, name AS text');
			$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season');
			$query->order('name');
            
            $starttime = microtime(); 
			JFactory::getDbo()->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
			$options = JFactory::getDbo()->loadObjectList();
    
    // teilnehmende saisons selektieren
    if ( $select_id )
    {
    $query = JFactory::getDbo()->getQuery(true);
			// saisons selektieren
			$query->select('season_id');
			$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_'.$targettable);
			$query->where($targetid.'='.$select_id);
            $query->group('season_id');
            
            $starttime = microtime(); 
			JFactory::getDbo()->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
			$this->value = JFactory::getDbo()->loadColumn();
    }
    else
    {
        $this->value = '';
    }
    
    //$app->enqueueMessage(JText::_('JFormFieldseasoncheckbox getInput query<br><pre>'.print_r($query,true).'</pre>'),'');
    //$app->enqueueMessage(JText::_('JFormFieldseasoncheckbox getInput value<br><pre>'.print_r($this->value,true).'</pre>'),'');
    //$app->enqueueMessage(JText::_('JFormFieldseasoncheckbox getInput options<br><pre>'.print_r($options,true).'</pre>'),'');
   


// Initialize variables.
            $html = array();
    
            // Initialize some field attributes.
            $class = $this->element['class'] ? ' class="checkboxes ' . (string) $this->element['class'] . '"' : ' class="checkboxes"';
    
            // Start the checkbox field output.
            $html[] = '<fieldset id="' . $this->id . '"' . $class . '>';
    
            // Get the field options.
            //$options = $options;
    
            // Build the checkbox field output.
            $html[] = '<ul>';
            foreach ($options as $i => $option)
            {
    
                // Initialize some option attributes.
                $checked = (in_array((string) $option->value, (array) $this->value) ? ' checked="checked"' : '');
                $class = !empty($option->class) ? ' class="' . $option->class . '"' : '';
                $disabled = !empty($option->disable) ? ' disabled="disabled"' : '';
    
                // Initialize some JavaScript option attributes.
                $onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
    
                $html[] = '<li>';
                $html[] = '<input type="checkbox" id="' . $this->id . $i . '" name="' . $this->name . '[]"' . ' value="'
                    . htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $onclick . $disabled . '/>';
    
                $html[] = '<label for="' . $this->id . $i . '"' . $class . '>' . JText::_($option->text) . '</label>';
                $html[] = '</li>';
            }
            $html[] = '</ul>';
    
            // End the checkbox field output.
            $html[] = '</fieldset>';
    
            return implode($html);    
    
    }
}
