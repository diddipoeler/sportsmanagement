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


//function gettotals()
//{
//    $mainframe = JFactory::getApplication();
//        $db = JFactory::getDbo(); 
//        $option = JRequest::getCmd('option');
//        //$post = JRequest::get('post');
//        //$exportfields = array();
//        //$cid = $post['cid'];
//        //$jl = $post['jl'];
//        //$jsm = $post['jsm'];
//        
//        //$cid = JRequest::get('cid');
//        //$jl = JRequest::get('jl');
//        //$jsm = JRequest::get('jsm');
//        
//        //$jsm_table = JRequest::get('jsm_table');
//        
//        // retrieve the value of the state variable. First see if the variable has been passed
//        // in the request. Otherwise retrieve the stored value. If none of these are specified,
//        // the specified default value will be returned
//        // function syntax is getUserStateFromRequest( $key, $request, $default );
//        
//        $jsm_table = $mainframe->getUserStateFromRequest( "$option.jsm_table", 'jsm_table', '' );
//        $jl_table = $mainframe->getUserStateFromRequest( "$option.jl_table", 'jl_table', '' );
//
//        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'');
//        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' cid<br><pre>'.print_r($cid,true).'</pre>'),'');
//        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jl<br><pre>'.print_r($jl,true).'</pre>'),'');
//        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsm<br><pre>'.print_r($jsm,true).'</pre>'),'');
//        
//        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsm_table<br><pre>'.print_r($jsm_table,true).'</pre>'),'');
//        
//        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' REQUEST<br><pre>'.print_r($_REQUEST,true).'</pre>'),'');
//        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' POST<br><pre>'.print_r($_POST,true).'</pre>'),'');
//    
//    //foreach ( $cid as $key => $value )
//        //{
//        //$jsm_table = $jsm[$value];
//        // Das "i" nach der Suchmuster-Begrenzung kennzeichnet eine Suche ohne
//            // Berücksichtigung von Groß- und Kleinschreibung
//            if (preg_match("/project_team/i", $jsm_table)) 
//            {
//            
//            $query = $db->getQuery(true);
//            $query->clear();
//            $query->select('COUNT(id) AS total');
//            $query->from($jsm_table);
//            $query->where('import = 0');
//            
//            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
//            
//            $db->setQuery($query);
//            $total = $db->loadResult();
//            
//            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'total<br><pre>'.print_r($total,true).'</pre>'),'');
//            
//            return $total;
//            }
//        //}
//
//}        





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
//        $post = JRequest::get('post');
//        $exportfields = array();
//        $cid = $post['cid'];
//        $jl = $post['jl'];
//        $jsm = $post['jsm'];
    

            // Select some fields
            $query = $db->getQuery(true);
            $query->clear();
		    $query->select('pt.id,pt.project_id,pt.team_id');
            $query->select('p.season_id');
            // From table
		    $query->from('#__sportsmanagement_project_team AS pt');
            $query->join('INNER','#__sportsmanagement_project AS p ON p.id = pt.project_id');
            $query->where('pt.import = 0');
            //$query->setLimit($a,1);
            
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
                    $new_id = $db->loadResult();
                }
                
                // Create an object for the record we are going to update.
                $object = new stdClass();
                // Must be a valid primary key value.
                $object->id = $row->id;
                $object->team_id = $new_id;
                $object->import = 1;
                // Update their details in the users table using id as the primary key.
                $result = JFactory::getDbo()->updateObject('#__sportsmanagement_project_team', $object, 'id'); 
                
                
            }
            
            
            // danach die alten datensätze löschen
            //$db->truncateTable($jsm_table);
 
            
            
             

}


            
}    

?>