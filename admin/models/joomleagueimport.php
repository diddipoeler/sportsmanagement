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
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
jimport('joomla.application.component.modellist');

$maxImportTime = 1920;
if ((int)ini_get('max_execution_time') < $maxImportTime){@set_time_limit($maxImportTime);}

/**
 * sportsmanagementModeljoomleagueimport
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModeljoomleagueimport extends JModelList
{




/**
 * sportsmanagementModeljoomleagueimport::newstructur()
 * 
 * @param mixed $step
 * @param integer $count
 * @return void
 */
function newstructur($step,$count=5)
{
    $mainframe = JFactory::getApplication();
    $db = JFactory::getDbo(); 
    $option = JRequest::getCmd('option');
    $starttime = microtime(); 
        
    $season_id = $mainframe->getUserState( "$option.season_id", '0' );
    $jl_table = $mainframe->getUserState( "$option.jl_table", '' );
    $jsm_table = $mainframe->getUserState( "$option.jsm_table", '' );
    
    // felder für den import auslesen
    $jl_fields = $db->getTableFields($jl_table);
    $jsm_fields = $db->getTableFields($jsm_table);
    
    if ( preg_match("/project_team/i", $jsm_table) )
    {

            // Select some fields
            $query = $db->getQuery(true);
            $query->clear();
		    $query->select('pt.*');
            //$query->select('p.season_id');
            // From joomleague table
		    $query->from($jl_table.' AS pt');
            $query->join('INNER','#__sportsmanagement_project AS p ON p.id = pt.project_id');
            $query->where('pt.import = 0');
            
            if ( $season_id )
            {
                $query->select('p.season_id');
                $query->where('p.season_id = '.$season_id);
            }
            
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            
            
            $db->setQuery($query,$step,$count);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
            $result = $db->loadObjectList();
            
            foreach ( $result as $row )
            {
                // Create and populate an object.
                $temp = new stdClass();
                $temp->season_id = $row->season_id;
                $temp->team_id = $row->team_id;
                // Insert the object into the user profile table.
                $result = JFactory::getDbo()->insertObject('#__sportsmanagement_season_team_id', $temp);
                if ( $result )
                {
                    $new_id = $db->insertid();
                }
                else
                {
                    // Select some fields
                    $query = $db->getQuery(true);
                    $query->clear();
		            $query->select('id');
                    // From table
                    $query->from('#__sportsmanagement_season_team_id');
                    $query->where('season_id = '.$row->season_id);
                    $query->where('team_id = '.$row->team_id);
                    $db->setQuery($query);
                    $new_id = $db->loadResult();
                }
                
                // Create an object for the record we are going to joomleague update.
                $object = new stdClass();
                // Must be a valid primary key value.
                $object->id = $row->id;
                $object->import = 1;
                // Update their details in the users table using id as the primary key.
                $result = JFactory::getDbo()->updateObject($jl_table, $object, 'id'); 
                
                // Create an object for the record we are going to joomleague update.
                $object = new stdClass();
                $jsm_field_array = $jsm_fields[$jsm_table];
                //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'jsm_field_array<br><pre>'.print_r($jsm_field_array,true).'</pre>'),'');
                foreach ( $jl_fields[$jl_table] as $key2 => $value2 )
                {
                //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'key<br><pre>'.print_r($key,true).'</pre>'),'');
                //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'value<br><pre>'.print_r($value,true).'</pre>'),'');
                if (array_key_exists($key2, $jsm_field_array)) 
                {
                    $object->$key2 = $row->$key2;
                }
                }
                // jetzt die neue team_id
                $object->team_id = $new_id;
                // Insert the object into the user profile table.
                $result = JFactory::getDbo()->insertObject($jsm_table, $object);
            }
            
            }
            elseif ( preg_match("/team_player/i", $jsm_table) )
    {
        $query = $db->getQuery(true);
        $query->clear();
        $query->select('tp.*');
        $query->from($jl_table.' AS tp');
        $query->join('INNER','#__sportsmanagement_project_team AS pt ON pt.id = tp.projectteam_id');
        $query->join('INNER','#__sportsmanagement_project AS p ON p.id = pt.project_id');
        
        
        $query->where('tp.import = 0');
        
        if ( $season_id )
            {
                $query->select('p.season_id');
                $query->where('p.season_id = '.$season_id);
            }
            
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            
            
            $db->setQuery($query,$step,$count);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
            $result = $db->loadObjectList();
        
        foreach ( $result as $row )
            {
            
            
            
            
            }
    }        
            // danach die alten datensätze löschen
            //$db->truncateTable($jsm_table);
 
       
             

}


            
}    

?>