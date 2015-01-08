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

jimport( 'joomla.application.component.model' );


/**
 * sportsmanagementModelAjax
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelAjax extends JModelLegacy
{
        /**
         * sportsmanagementModelAjax::addGlobalSelectElement()
         * 
         * @param mixed $elements
         * @param bool $required
         * @return
         */
        public static function addGlobalSelectElement($elements, $required=false) 
        {
            // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' elements<br><pre>'.print_r($elements,true).'</pre>'),'Notice');
        
                if(!$required) 
                {
                $mitems = array(JHTML::_('select.option', '', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));
                
                //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' mitems<br><pre>'.print_r($mitems,true).'</pre>'),'Notice');
                
                return array_merge($mitems, $elements);
                }
                else
                {
                $mitems = array(JHTML::_('select.option', '', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));
                
                //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' mitems<br><pre>'.print_r($mitems,true).'</pre>'),'Notice');
                
                if ( $elements )
                {
                return array_merge($mitems, $elements);
                }
                else
                {
                return $mitems;    
                }
                
                }
                //return $elements;
        }
        
        
        /**
         * sportsmanagementModelAjax::getProjectRoundOptions()
         * 
         * @param mixed $sports_type_id
         * @param bool $required
         * @return
         */
        public static function getProjectRoundOptions($project_id, $required = false, $ordering = 'ASC' , $round_ids = NULL, $slug = false, $dabse = false)
        {
            // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
       
       // Get a db connection.
        if ( !$dabse )
        {
            $db = JFactory::getDBO();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        
       $query = $db->getQuery(true);
        $query->select('id as value');
        $query->select('name AS text');
        $query->select('id, name, round_date_first, round_date_last, roundcode');
        
        $query->from('#__sportsmanagement_round');
        if ( $project_id )
        {
        $query->where('project_id = '.$project_id);
        }
        else
        {
            $query->where('project_id = 0');
        }
        $query->where('published = 1');
        
        if ( $round_ids )
    {
    $query->where('id IN (' . implode(',', $round_ids) . ')');   
    }
        
        $query->order('roundcode '.$ordering);  
       $db->setQuery($query);    
            
        $result = $db->loadObjectList();
        
//        foreach ($result as $row)
//        {
//            $row->name = JText::_($row->name);
//        }
        
        return self::addGlobalSelectElement($result, $required);
       }
       
        /**
         * sportsmanagementModelAjax::getpersonpositionoptions()
         * 
         * @param mixed $sports_type_id
         * @param bool $required
         * @return
         */
        public static function getpersonpositionoptions($sports_type_id, $required = false, $slug = false, $dbase = false)
        {
            $option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();

        // Get a db connection.
        if ( !$dbase )
        {
            $db = JFactory::getDBO();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        
        $query->select('pos.id AS value, pos.name AS text');
			$query->from('#__sportsmanagement_position as pos');
			$query->where('pos.sports_type_id = '.$sports_type_id);
            $query->order('pos.name');  
			$db->setQuery($query);    
            
        $result = $db->loadObjectList();
        
//        foreach ($result as $row)
//        {
//            $row->name = JText::_($row->name);
//        }
        
        return self::addGlobalSelectElement($result, $required);
        }
        
        /**
         * sportsmanagementModelAjax::getpersonagegroupoptions()
         * 
         * @param mixed $sports_type_id
         * @param bool $required
         * @return
         */
        public static function getpersonagegroupoptions($sports_type_id, $required = false, $slug = false, $dabse = false, $project_id = 0 )
        {
            $option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();

        // Get a db connection.
        if ( $dabse )
        {
            $db = JFactory::getDBO();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,FALSE);
        }
        $query = $db->getQuery(true);
        
         $query->select('a.id AS value, concat(a.name, \' von: \',a.age_from,\' bis: \',a.age_to,\' Stichtag: \',a.deadline_day) AS text');
			$query->from('#__sportsmanagement_agegroup as a');
            
            if ( $project_id )
            {
            $query->join('LEFT', '#__sportsmanagement_league AS l ON l.country = a.country'); 
            $query->join('LEFT', '#__sportsmanagement_project AS p ON p.league_id = l.id');    
            $query->where('p.id = '.$project_id);
            }

            $query->where('a.sportstype_id = '.$sports_type_id);
			$query->order('a.name');    
                    
        $db->setQuery($query);
        
        $result = $db->loadObjectList();
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($query->dump(),true).'</pre>' ),'');
        
//        foreach ($result as $row)
//        {
//            $row->name = JText::_($row->name);
//        }
           
        return self::addGlobalSelectElement($result, $required);
        }
        
        
        /**
         * sportsmanagementModelAjax::getpersonlistoptions()
         * 
         * @param mixed $person_art
         * @param bool $required
         * @return
         */
        static function getpersonlistoptions($person_art, $required = false, $slug = false, $dbase = false)
        {
            $option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
       // Get a db connection.
        if ( !$dbase )
        {
            $db = JFactory::getDBO();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        if ( $person_art == 2 )
        {
        $query->select("id AS value, concat(lastname,' - ',firstname,'' ) AS text");
			$query->from('#__sportsmanagement_person ');
			$query->order('lastname');
			$db->setQuery($query);    
            
        }
        
        return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }
        
        /**
         * sportsmanagementModelAjax::getProjectsBySportsTypesOptions()
         * 
         * @param mixed $sports_type_id
         * @param bool $required
         * @return
         */
        function getProjectsBySportsTypesOptions($sports_type_id, $required = false, $slug = false, $dbase = false)
        {
            $option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
       // Get a db connection.
        if ( !$dbase )
        {
            $db = JFactory::getDBO();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        // Select some fields
        $query->select('CONCAT_WS(\':\', p.id, p.alias) AS value,p.name AS text');
        // From 
		$query->from('#__sportsmanagement_project AS p');
        $query->join('INNER',' #__sportsmanagement_sports_type AS st ON st.id = p.sports_type_id ');
        // Where
        $query->where('p.sports_type_id = ' . $db->Quote($sports_type_id) );
        // order
        $query->order('p.name');

                $db->setQuery( $query );
                                                           
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }
        
         /**
         * sportsmanagementModelAjax::getAgeGroupsBySportsTypesOptions()
         * 
         * @param mixed $sports_type_id
         * @param bool $required
         * @return
         */
        function getAgeGroupsBySportsTypesOptions($sports_type_id, $required = false, $slug = false, $dbase = false)
        {
            // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
       
       //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' sports_type_id<br><pre>'.print_r($sports_type_id,true).'</pre>'),'');
       
       // Get a db connection.
        if ( !$dbase )
        {
            $db = JFactory::getDBO();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        // Select some fields
        $query->select('CONCAT_WS(\':\', a.id, a.alias) AS value,a.name AS text');
        // From 
		$query->from('#__sportsmanagement_agegroup AS a');
        $query->join('INNER',' #__sportsmanagement_sports_type AS st ON st.id = a.sportstype_id ');
        // Where
        if ( $sports_type_id )
        {
        $query->where('a.sports_type_id = ' . $sports_type_id );
        }
        // order
        $query->order('a.name');

                $db->setQuery( $query );
                                                           
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }

        /**
         * sportsmanagementModelAjax::getProjectDivisionsOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        public static function getProjectDivisionsOptions($project_id, $required = false, $slug = false, $dabse = false)
        {
            $option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
        // Get a db connection.
        if ( !$dabse )
        {
            $db = JFactory::getDBO();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        
        // Select some fields
        if ( $slug )
        {
        $query->select('CONCAT_WS(\':\', d.id, d.alias) AS value,d.name AS text');
        }
        else
        {
        $query->select('d.id AS value,d.name AS text');    
        }
        // From 
		$query->from('#__sportsmanagement_division AS d');
        $query->join('INNER',' #__sportsmanagement_project p ON p.id = d.project_id ');
        // Where
        $query->where('d.project_id = ' . $db->Quote($project_id) );
        // group
        $query->group('d.id');
        // order
        $query->order('d.name');

                $db->setQuery( $query );
                
                
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }

        /**
         * sportsmanagementModelAjax::getProjectTeamsByDivisionOptions()
         * 
         * @param mixed $project_id
         * @param integer $division_id
         * @param bool $required
         * @return
         */
        public static function getProjectTeamsByDivisionOptions($project_id, $division_id=0, $required=false, $slug = false, $dbase = false)
        {
            $option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
       // Get a db connection.
        if ( !$dbase )
        {
            $db = JFactory::getDBO();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        // Select some fields
        $query->select('CONCAT_WS(\':\', t.id, t.alias) AS value,t.name AS text');
        // From 
		$query->from('#__sportsmanagement_project_team as pt');
        $query->join('INNER',' #__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
        $query->join('INNER',' #__sportsmanagement_team t ON t.id = st.team_id ');
        $query->join('INNER',' #__sportsmanagement_project p ON p.id = pt.project_id ');
                                
                // Where
        $query->where('pt.project_id = ' . $db->Quote($project_id) );
                                
                if($division_id>0) {
                    // Where
        $query->where('pt.division_id = ' . $db->Quote($division_id) );
                        
                }
                
                // order
        $query->order('t.name');
                
                
                $db->setQuery($query);
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }
        
        /**
         * sportsmanagementModelAjax::getProjectsByClubOptions()
         * 
         * @param mixed $club_id
         * @param bool $required
         * @return
         */
        public static function getProjectsByClubOptions($club_id, $required=false, $slug = false, $dbase = false)
        {
            $option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
      // Get a db connection.
        if ( !$dbase )
        {
            $db = JFactory::getDBO();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        
                if($club_id == 0) 
                {
                        $projects = (array) self::getProjects();
                        return self::addGlobalSelectElement($projects, $required);
                } 
                else 
                {
                    // Select some fields
        $query->select('CONCAT_WS(\':\', p.id, p.alias) AS value,p.name AS text');
        // From 
		$query->from('#__sportsmanagement_project_team as pt');
        $query->join('INNER',' #__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
        $query->join('INNER',' #__sportsmanagement_team t ON t.id = st.team_id ');
        $query->join('INNER',' #__sportsmanagement_project p ON p.id = pt.project_id ');
                                
                // Where
        $query->where('t.club_id = ' . $db->Quote($club_id) );
        
                    
                    
                        
                        
                        $db->setQuery($query);
                        return self::addGlobalSelectElement($db->loadObjectList(), $required);
                }
        }
        
        /**
         * sportsmanagementModelAjax::getProjects()
         * 
         * @return
         */
        public static function getProjects($season_id = 0, $required = false, $slug = false, $dabse = false)
        {
            $option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
       // Get a db connection.
        if ( !$dabse )
        {
            $db = JFactory::getDBO();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        //$db = JFactory::getDbo();
        $query = $db->getQuery(true);
        // Select some fields
        if ( $slug )
        {
        $query->select('CONCAT_WS(\':\', p.id, p.alias) AS value,p.name AS text');
        }
        else
        {
        $query->select('p.id AS value,p.name AS text');    
        }
        // From 
		$query->from('#__sportsmanagement_project as p');
        // Where
        if ( $season_id )
        {
        $query->where('p.season_id = ' . $season_id );
        } 
        else
        {
        $query->where('p.season_id = 0');    
        }                       
                // Where
        $query->order('p.name');
        
                $db->setQuery($query);
                //return $db->loadObjectList();
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }
        
        /**
         * sportsmanagementModelAjax::getProjectTeamOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        public static function getProjectTeamOptions($project_id, $required = false, $slug = false, $dabse = false)
        {
            $option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
       // Get a db connection.
        if ( !$dabse )
        {
            $db = JFactory::getDBO();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        
        // Select some fields
        if ( $slug )
        {
        $query->select('CONCAT_WS(\':\', t.id, t.alias) AS value,t.name AS text');
        }
        else
        {
        $query->select('t.id AS value,t.name AS text');    
        }
        // From 
		$query->from('#__sportsmanagement_project_team as pt');
        $query->join('INNER',' #__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
        $query->join('INNER',' #__sportsmanagement_team t ON t.id = st.team_id ');
        $query->join('INNER',' #__sportsmanagement_project p ON p.id = pt.project_id ');
                                
                // Where
        if ( $project_id )
        {
        $query->where('pt.project_id = ' . $project_id );
        }
        else
        {
            $query->where('pt.project_id = 0' );
        }
        // order
        $query->order('t.name');
        
                
                
                $db->setQuery($query);
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }

        /**
         * sportsmanagementModelAjax::getProjectTeamPtidOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        function getProjectTeamPtidOptions($project_id, $required = false, $slug = false, $dbase = false)
        {
            $option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
      // Get a db connection.
        if ( !$dbase )
        {
            $db = JFactory::getDBO();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        // Select some fields
        if ( $slug )
        {
        $query->select('CONCAT_WS(\':\', pt.id, t.alias) AS value,t.name AS text');
        }
        else
        {
        $query->select('pt.id AS value,t.name AS text');    
        }
        // From 
		$query->from('#__sportsmanagement_project_team as pt');
        $query->join('INNER',' #__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
        $query->join('INNER',' #__sportsmanagement_team t ON t.id = st.team_id ');
        $query->join('INNER',' #__sportsmanagement_project p ON p.id = pt.project_id ');
                                
                // Where
        $query->where('pt.project_id = ' . $db->Quote($project_id) );
        // order
        $query->order('t.name');
        
                
                $db->setQuery($query);
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }

        /**
         * sportsmanagementModelAjax::getProjectPlayerOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        public static function getProjectPlayerOptions($project_id, $required = false, $slug = false, $dbase = false)
        {
            $option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
      // Get a db connection.
        if ( !$dbase )
        {
            $db = JFactory::getDBO();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
                
        // Select some fields
        $query->select("CONCAT_WS(':', p.id, p.alias) AS value,CONCAT(p.lastname, ', ', p.firstname, ' (', p.birthday, ')') AS text");
        // From 
		$query->from('#__sportsmanagement_person AS p');
        $query->join('INNER',' #__sportsmanagement_season_team_person_id AS stp ON stp.person_id = p.id ');
        $query->join('INNER',' #__sportsmanagement_season_team_id AS st ON st.team_id = stp.team_id ');
        $query->join('INNER',' #__sportsmanagement_project_team pt ON pt.team_id = st.id ');
        // Where
        $query->where('pt.project_id = ' . $project_id);
        $query->where('p.published = 1');
        $query->where('stp.persontype = 1');
        // group
        $query->group('p.id');
        // order
        $query->order('text');        
                        
                $db->setQuery($query);
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }

        /**
         * sportsmanagementModelAjax::getProjectStaffOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        public static function getProjectStaffOptions($project_id, $required = false, $slug = false, $dbase = false)
        {
            $option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
      // Get a db connection.
        if ( !$dbase )
        {
            $db = JFactory::getDBO();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        // Select some fields
        $query->select("CONCAT_WS(':', p.id, p.alias) AS value,CONCAT(p.lastname, ', ', p.firstname, ' (', p.birthday, ')') AS text");
        // From 
		$query->from('#__sportsmanagement_person AS p');
        $query->join('INNER',' #__sportsmanagement_season_team_person_id AS stp ON stp.person_id = p.id ');
        $query->join('INNER',' #__sportsmanagement_season_team_id AS st ON st.team_id = stp.team_id ');
        $query->join('INNER',' #__sportsmanagement_project_team pt ON pt.team_id = st.id ');
        // Where
        $query->where('pt.project_id = ' . $db->Quote($project_id));
        $query->where('p.published = 1');
        $query->where('stp.persontype = 2');
        // group
        $query->group('p.id');
        // order
        $query->order('text');
                 
                $db->setQuery($query);
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }

        /**
         * sportsmanagementModelAjax::getProjectClubOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        public static function getProjectClubOptions($project_id, $required = false, $slug = false, $dbase = false)
        {
            $option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
      // Get a db connection.
        if ( !$dbase )
        {
            $db = JFactory::getDBO();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        // Select some fields
        $query->select('CONCAT_WS(\':\', c.id, c.alias) AS value,c.name AS text');
        // From 
		$query->from('#__sportsmanagement_project_team as pt');
        $query->join('INNER',' #__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
        $query->join('INNER',' #__sportsmanagement_team t ON t.id = st.team_id ');
        $query->join('INNER',' #__sportsmanagement_club AS c ON c.id = t.club_id ');
        $query->join('INNER',' #__sportsmanagement_project p ON p.id = pt.project_id ');
                                
                // Where
        if ( $project_id )
        {
        $query->where('pt.project_id = ' . $project_id );
        }
        else
        {
        $query->where('pt.project_id = 0');    
        }
        // group
        $query->group('c.id');
        // order
        $query->order('c.name');
                                                                
$db->setQuery($query);                                                                        
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }

        /**
         * sportsmanagementModelAjax::getProjectEventsOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        public static function getProjectEventsOptions($project_id, $required = false, $slug = false, $dbase = false)
        {
            $option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
      // Get a db connection.
        if ( !$dbase )
        {
            $db = JFactory::getDBO();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        
        // Select some fields
        $query->select('CONCAT_WS(\':\', et.id, et.alias) AS value,et.name AS text');
        // From 
		$query->from('#__sportsmanagement_eventtype as et');
        $query->join('INNER',' #__sportsmanagement_match_event as me ON et.id = me.event_type_id ');
        $query->join('INNER',' #__sportsmanagement_match as m ON m.id = me.match_id');
        $query->join('INNER',' #__sportsmanagement_round as r ON m.round_id = r.id ');
        // Where
        $query->where('r.project_id = ' . $db->Quote($project_id));
        // group
        $query->group('et.id');
        // order
        $query->order('et.ordering');

                $db->setQuery( $query );
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }


        /**
         * sportsmanagementModelAjax::getProjectStatOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        public static function getProjectStatOptions($project_id, $required=false, $slug = false, $dbase = false)
        {
            $option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
      // Get a db connection.
        if ( !$dbase )
        {
            $db = JFactory::getDBO();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        
        // Select some fields
        $query->select('CONCAT_WS(\':\', s.id, s.alias) AS value,s.name AS text');
        // From 
		$query->from('#__sportsmanagement_project_position AS ppos');
        $query->join('INNER',' #__sportsmanagement_position_statistic AS ps ON ps.position_id = ppos.position_id ');
        $query->join('INNER',' #__sportsmanagement_statistic AS s ON s.id = ps.statistic_id ');
        $query->join('INNER',' #__sportsmanagement_project p ON p.id = ppos.project_id ');
        // Where
        $query->where('ppos.project_id = ' . $db->Quote($project_id));
        // group
        $query->group('s.id');
        // order
        $query->order('s.name');
        
        $db->setQuery($query);

                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }

        /**
         * sportsmanagementModelAjax::getMatchesOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        public static function getMatchesOptions($project_id, $required=false, $slug = false, $dbase = false)
        {
            $option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
      // Get a db connection.
        if ( !$dbase )
        {
            $db = JFactory::getDBO();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        
        // Select some fields
        $query->select("m.id AS value,CONCAT('(', m.match_date, ') - ', t1.middle_name, ' - ', t2.middle_name) AS text");
        // From 
		$query->from('#__sportsmanagement_match AS m');
        $query->join('INNER',' #__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id ');
        $query->join('INNER',' #__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id ');
        
        $query->join('INNER',' #__sportsmanagement_season_team_id as st1 ON st1.id = pt1.team_id ');
        $query->join('INNER',' #__sportsmanagement_season_team_id as st2 ON st2.id = pt2.team_id ');
        
        $query->join('INNER',' #__sportsmanagement_team AS t1 ON st1.team_id = t1.id ');
        $query->join('INNER',' #__sportsmanagement_team AS t2 ON st2.team_id = t2.id ');
        
                                
                // Where
        $query->where('pt1.project_id = ' . $db->Quote($project_id) );
        
        // order
        $query->order('m.match_date, t1.short_name');

                                        
                $db->setQuery($query);
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }

        /**
         * sportsmanagementModelAjax::getRefereesOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        function getRefereesOptions($project_id, $required = false, $slug = false, $dbase = false)
        {
            $option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
      // Get a db connection.
        if ( !$dbase )
        {
            $db = JFactory::getDBO();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        // Select some fields
        $query->select("p.id AS value,CONCAT(p.firstname, ' ', p.lastname) AS text");
        // From 
		$query->from('#__sportsmanagement_person AS p');
        $query->join('INNER',' #__sportsmanagement_season_person_id AS sp ON sp.person_id = p.id ');
        $query->join('INNER',' #__sportsmanagement_project_referee AS pr ON pr.person_id = sp.id ');
        // Where
        $query->where('pr.project_id = ' . $db->Quote($project_id));
        $query->where('p.published = 1');
        // order
        $query->order('text');
                    
                
                $db->setQuery($query);
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }

        /**
         * sportsmanagementModelAjax::getProjectTreenodeOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        function getProjectTreenodeOptions($project_id, $required = false, $slug = false, $dbase = false)
        {
            $option = JRequest::getCmd('option');
	   $app = JFactory::getApplication();
      // Get a db connection.
        if ( !$dbase )
        {
            $db = JFactory::getDBO();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        
        // Select some fields
        $query->select('tt.id AS value,tt.id AS text');
        // From 
		$query->from('#__sportsmanagement_treeto AS tt');
        $query->join('INNER',' #__sportsmanagement_project p ON p.id = tt.project_id ');
        // Where
        $query->where('tt.project_id = ' . $db->Quote($project_id));
        // order
        $query->order('tt.id');
        
        $db->setQuery($query);

                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }


}
?>
