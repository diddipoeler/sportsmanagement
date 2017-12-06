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



/**
 * JFormFieldprojectpositionlist
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class JFormFieldprojectpositionlist extends JFormFieldList
{
    
	/**
	 * field type
	 * @var string
	 */
	public $type = 'projectpositionlist';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// Reference global application object
        $this->jsmapp = JFactory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        
        $team_id = $this->jsmapp->getUserState( "$this->jsmoption.team_id", '0' );
        $persontype = $this->jsmapp->getUserState( "$this->jsmoption.persontype", '0' );
        $project_team_id = $this->jsmapp->getUserState( "$this->jsmoption.project_team_id", '0' );
        $pid = $this->jsmapp->getUserState( "$this->jsmoption.pid", '0' );
        
        // Initialize variables.
		$options = array();
//    $vartable = (string) $this->element['targettable'];
		$select_id = JFactory::getApplication()->input->getVar('id');
        $db = JFactory::getDbo();
			$query = $db->getQuery(true);
			
			$query->select('pp.id AS value, pos.name AS text');
            $query->from('#__sportsmanagement_position as pos');
            $query->join('INNER', '#__sportsmanagement_project_position AS pp ON pp.position_id = pos.id');
            
			$query->join('INNER', '#__sportsmanagement_sports_type AS s ON s.id = pos.sports_type_id');
            $query->join('INNER', '#__sportsmanagement_person_project_position AS ppp ON pp.project_id = ppp.project_id');
            
            //$query->where('ppp.project_position_id = '.$select_id);
            $query->where('pp.project_id = '.$pid);
			$query->order('pos.ordering,pos.name');
            $query->group('pos.id');
			$db->setQuery($query);
            
        if ( JComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend') )
        {
		$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        }
            
            try { 
			$options = $db->loadObjectList();
            }
catch (Exception $e) {
//    // catch any database errors.
//    $db->transactionRollback();
//    JErrorPage::render($e);
JFactory::getApplication()->enqueueMessage($db->getErrorMsg());
}
            foreach ( $options as $row )
            {
                $row->text = JText::_($row->text);
            }
    
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}
