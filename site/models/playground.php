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
    var $playgroundid = 0;
    var $playground = null;
    var $projectid = 0;
    
    static $cfg_which_database = 0;

    /**
     * sportsmanagementModelPlayground::__construct()
     * 
     * @return
     */
    function __construct( )
    {
        

        $this->projectid = JRequest::getInt( "p", 0 );
        $this->playgroundid = JRequest::getInt( "pgid", 0 );
        sportsmanagementModelProject::$projectid = $this->projectid;
        self::$cfg_which_database = JRequest::getInt('cfg_which_database',0);
        
        parent::__construct( ); 
    }

//    /**
//     * sportsmanagementModelPlayground::getPlayground()
//     * 
//     * @return
//     */
//    function getPlayground( )
//    {
//        if ( is_null( $this->playground ) )
//        {
//            $pgid = JRequest::getInt( "pgid", 0 );
//            if ( $pgid > 0 )
//            {
//                $this->playground = $this->getTable( 'Playground', 'sportsmanagementTable' );
//                $this->playground->load( $pgid );
//            }
//        }
//        return $this->playground;
//    }

    
    

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
            
//            $query = "SELECT id, team_id, project_id
//                      FROM #__joomleague_project_team
//                      WHERE standard_playground = ".(int)$playground->id;
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

//                $query = "SELECT name, short_name, notes
//                          FROM #__joomleague_team
//                          WHERE id=".(int)$row->team_id;
                          
                $db->setQuery( $query );
                $teams[ $row->id ]->teaminfo[] = $db->loadObjectList();
                $query->clear();
                $query->select('name');
                $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project ');
                $query->where('id = '. (int)$row->project_id);

//                $query= "SELECT name
//                         FROM #__joomleague_project
//                         WHERE id=".(int)$row->project_id;
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
            
//        $query = "
//            SELECT c.logo_small,
//                   c.country
//            FROM #__joomleague_team t
//            LEFT JOIN #__joomleague_club c ON c.id = t.club_id
//            WHERE t.id = ".$team_id."
//        ";

        $db->setQuery( $query );
        $result = $db->loadObjectList();

        return $result;
    }

//    /**
//     * sportsmanagementModelPlayground::getTeamsFromMatches()
//     * 
//     * @param mixed $games
//     * @return
//     */
//    function getTeamsFromMatches( & $games )
//    {
//        $option = JRequest::getCmd('option');
//	    $app = JFactory::getApplication();
//        // Get a db connection.
//        $db = JFactory::getDbo();
//        $query = $db->getQuery(true);
//        
//        $teams = Array();
//
//        if ( !count( $games ) )
//        {
//            return $teams;
//        }
//
//        foreach ( $games as $m )
//        {
//            $teamsId[] = $m->team1;
//            $teamsId[] = $m->team2;
//        }
//        $listTeamId = implode( ",", array_unique( $teamsId ) );
//
//        $query = "SELECT t.id, t.name
//                 FROM #__joomleague_team t
//                 WHERE t.id IN (".$listTeamId.")";
//        $db->setQuery( $query );
//        $result = $db->loadObjectList();
//
//        foreach ( $result as $r )
//        {
//            $teams[$r->id] = $r;
//        }
//
//        return $teams;
//    }

//    function getGoogleApiKey( )
//    {
//    	$params =& JComponentHelper::getParams('com_joomleague');
//    	$apikey=$params->get('cfg_google_api_key');
//
//      return $apikey;
//    }



//    function getGoogleMap( $mapconfig, $address_string = "" )
//    {
//        $gm = null;
//
//        $google_api_key = $this->getGoogleApiKey();
//        if ( ( trim( $google_api_key ) != "" ) &&
//             ( trim( $address_string ) != "" ) )
//        {
//            $gm = new EasyGoogleMap( $google_api_key, "jl_pg_map" );
//
//            $width = ( is_int( $mapconfig['width'] ) ) ? $mapconfig['width'].'px' : $mapconfig['width'];
//
//            $gm->SetMapWidth( $mapconfig['width'] );
//            $gm->SetMapHeight( $mapconfig['height'] );
//            $gm->SetMapControl( $mapconfig['map_control'] );
//            $gm->SetMapDefaultType( $mapconfig['default_map_type'] );
//
//            if ( intval( $mapconfig['map_zoom'] ) > 0 )
//            {
//                $gm->SetMapZoom( intval( $mapconfig['map_zoom'] ) );
//            }
//
//            $gm->mScale = ( intval( $mapconfig['map_scale'] ) > 0 ) ? TRUE : FALSE;
//            $gm->mMapType = ( intval( $mapconfig['map_type_select']) > 0 ) ? TRUE : FALSE;
//            $gm->mContinuousZoom = ( intval( $mapconfig['cont_zoom']) > 0 ) ? TRUE : FALSE;
//            $gm->mDoubleClickZoom = ( intval( $mapconfig['dblclick_zoom']) > 0 ) ? TRUE : FALSE;
//            $gm->mInset = ( intval( $mapconfig['map_inset'] ) > 0 ) ? TRUE : FALSE;
//            $gm->mShowMarker = ( intval( $mapconfig['show_marker'] ) > 0 ) ? TRUE : FALSE;
//            $gm->SetMarkerIconStyle( $mapconfig['map_icon_style'] );
//            $gm->SetMarkerIconColor( $mapconfig['map_icon_color'] );
//            $gm->SetAddress( $address_string );
//        }
//        return $gm;
//    }

}
?>