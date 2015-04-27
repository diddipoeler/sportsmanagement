<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

/**
 * sportsmanagementModelPlayground
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPlayground extends JModelLegacy
{
    static $playgroundid = 0;
    var $playground = null;
    static $projectid = 0;
    
    static $cfg_which_database = 0;

    /**
     * sportsmanagementModelPlayground::__construct()
     * 
     * @return
     */
    function __construct( )
    {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;

        self::$projectid = $jinput->getInt( "p", 0 );
        self::$playgroundid = $jinput->getInt( "pgid", 0 );
        sportsmanagementModelProject::$projectid = self::$projectid;
        self::$cfg_which_database = $jinput->getInt('cfg_which_database',0);
        
        parent::__construct( ); 
    }

    /**
     * sportsmanagementModelPlayground::getTeams()
     * 
     * @return
     */
    function getTeams( )
    {
        $option = JRequest::getCmd('option');
	    $app = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $teams = array();

        $playground = $this->getPlayground( );
        if ( $playground->id > 0 )
        {
            $query->select('id, team_id, project_id');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team ');
            $query->where('standard_playground = '. (int)$playground->id);

            $db->setQuery( $query );
            $rows = $db->loadObjectList();
			
            foreach ( $rows as $row )
            {
                $teams[$row->id]->project_team[] = $row;
                $query->clear();
                $query->select('t.name, t.short_name, t.notes');
                $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team as t ');
                $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st ON st.team_id = t.id ');
                $query->where('st.id = '. (int)$row->team_id);
                          
                $db->setQuery( $query );
                $teams[ $row->id ]->teaminfo[] = $db->loadObjectList();
                $query->clear();
                $query->select('name');
                $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project ');
                $query->where('id = '. (int)$row->project_id);

                $db->setQuery( $query );
            	$teams[ $row->id ]->project = $db->loadResult();
            }
        }
        return $teams;
    }
 


    /**
     * sportsmanagementModelPlayground::getTeamLogo()
     * 
     * @param mixed $team_id
     * @return
     */
    function getTeamLogo($team_id)
    {
        $option = JRequest::getCmd('option');
	    $app = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $query->select('c.logo_small,c.country');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ');
        $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_club AS c ON c.id = t.club_id  ');
        $query->where('t.id = '. $team_id);

        $db->setQuery( $query );
        $result = $db->loadObjectList();

        return $result;
    }

}
?>