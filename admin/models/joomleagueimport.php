<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      joomleagueimport.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

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




///**
// * sportsmanagementModeljoomleagueimport::getImportPositions()
// * 
// * @param string $component
// * @return
// */
//function getImportPositions($component = 'joomleague',$which_table='project_position')
//{
//    $app = Factory::getApplication();
//    $db = Factory::getDbo(); 
//    $option = Factory::getApplication()->input->getCmd('option');
//    
//    // Select some fields
//            $query = $db->getQuery(true);
//            $query->clear();
//		    $query->select('pos.*,pos.id as value, pos.name as text');
//// From table
//		    $query->from('#__'.$component.'_position as pos');
//            
//switch ($component)
//{
//    case 'joomleague':
//    $query->join('INNER', '#__'.$component.'_'.$which_table.' AS pp ON pp.position_id = pos.id');
//    //$query->join('INNER', '#__'.$component.'_person AS pp ON pp.position_id = pos.id');
//    $query->group('pos.id');
//    break;
//}
//            
//    
//    
//    $db->setQuery($query);
//    $result = $db->loadObjectList();
//    return $result;
//}
//




/**
 * sportsmanagementModeljoomleagueimport::newstructurjlimport()
 * 
 * @param mixed $season_id
 * @param mixed $jl_table
 * @param mixed $jsm_table
 * @return void
 */
function newstructurjlimport($season_id,$jl_table,$jsm_table,$project_id)
{
    $app = Factory::getApplication();
    $db = Factory::getDbo(); 
    $option = Factory::getApplication()->input->getCmd('option');
    $starttime = microtime(); 
    $query = $db->getQuery(true);
        
//    $season_id = $app->getUserState( "$option.season_id", '0' );
//    $jl_table = $app->getUserState( "$option.jl_table", '' );
//    $jsm_table = $app->getUserState( "$option.jsm_table", '' );
    
/**
* hier muss auch wieder zwischen den joomla versionen unterschieden werden
* felder für den import auslesen
*/                
if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
$jl_fields = $db->getTableColumns($jl_table);
$jsm_fields = $db->getTableColumns($jsm_table);
$jl_fields[$jl_table] = $jl_fields;
$jsm_fields[$jsm_table] = $jsm_fields;
}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here
$jl_fields = $db->getTableFields($jl_table);
$jsm_fields = $db->getTableFields($jsm_table);
}
         

//    $jl_fields = $db->getTableFields($jl_table);
//    $jsm_fields = $db->getTableFields($jsm_table);

/**
* umsetzung der project teams 
*/     
    if ( preg_match("/project_team/i", $jsm_table) )
    {
$my_text;
$my_text .= '<span style="color:'.sportsmanagementModeljoomleagueimports::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
$my_text .= '<br />';
$my_text .= '<span style="color:'.sportsmanagementModeljoomleagueimports::$existingInDbColor. '"<strong>Daten aus der Tabelle: ( '.$jl_table.' ) werden in die neue Struktur umgesetzt!"!</strong>'.'</span>';
$my_text .= '<br />';

$query->clear();
//$query->select('pt.*');
$query->select('p.name as importprojectname');
// From joomleague table
$query->from($jl_table.' AS pt');
$query->join('INNER','#__sportsmanagement_project AS p ON p.id = pt.project_id');
$query->where('p.id = '.$project_id);
            
if ( $season_id )
{
$query->select('p.season_id');
$query->where('p.season_id = '.$season_id);
}
$db->setQuery($query);
$importprojectname = $db->loadResult();  

$my_text .= '<span style="color:'.sportsmanagementModeljoomleagueimports::$existingInDbColor. '"<strong>Daten aus dem Projekt: ( '.$importprojectname.' ) werden in die neue Struktur umgesetzt!"!</strong>'.'</span>';
$my_text .= '<br />';
//$app->enqueueMessage(Text::_('Daten aus der Tabelle: ( '.$jsm_table.' ) werden in die neue Struktur umgesetzt!'),'Notice');

            // Select some fields
//            $query = $db->getQuery(true);
            $query->clear();
		    $query->select('pt.*');
            $query->select('p.name as importprojectname');
            // From joomleague table
		    $query->from($jl_table.' AS pt');
            $query->join('INNER','#__sportsmanagement_project AS p ON p.id = pt.project_id');
            $query->where('p.id = '.$project_id);
            
            if ( $season_id )
            {
                $query->select('p.season_id');
                $query->where('p.season_id = '.$season_id);
            }
            
            $db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
            $result = $db->loadObjectList();
                        
            foreach ( $result as $row )
            {
                
                $query->clear();
                $query->select('id');
                // From table
                $query->from('#__sportsmanagement_season_team_id');
                $query->where('season_id = '.$row->season_id);
                $query->where('team_id = '.$row->team_id);
                $db->setQuery($query);
                $new_id = $db->loadResult();
                if ( !$new_id )
                {
                // Create and populate an object.
                $temp = new stdClass();
                $temp->season_id = $row->season_id;
                $temp->team_id = $row->team_id;
                // Insert the object into table.
                $result = Factory::getDbo()->insertObject('#__sportsmanagement_season_team_id', $temp);
                if ( $result )
                {
                    $new_id = $db->insertid();
                }
                }
//                else
//                {
//                    // Select some fields
//                    //$query = $db->getQuery(true);
//                    $query->clear();
//		            $query->select('id');
//                    // From table
//                    $query->from('#__sportsmanagement_season_team_id');
//                    $query->where('season_id = '.$row->season_id);
//                    $query->where('team_id = '.$row->team_id);
//                    $db->setQuery($query);
//                    $new_id = $db->loadResult();
//                }
                
//                // Create an object for the record we are going to joomleague update.
//                $object = new stdClass();
//                // Must be a valid primary key value.
//                $object->id = $row->id;
//                $object->import = 1;
//                // Update their details in the users table using id as the primary key.
//                $result = Factory::getDbo()->updateObject($jl_table, $object, 'id'); 
                
                // Create an object for the record we are going to joomleague update.
                $object = new stdClass();
                $jsm_field_array = $jsm_fields[$jsm_table];
                //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.'jsm_field_array<br><pre>'.print_r($jsm_field_array,true).'</pre>'),'');
                foreach ( $jl_fields[$jl_table] as $key2 => $value2 )
                {
                //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.'key<br><pre>'.print_r($key,true).'</pre>'),'');
                //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.'value<br><pre>'.print_r($value,true).'</pre>'),'');
                if (array_key_exists($key2, $jsm_field_array)) 
                {
                    $object->$key2 = $row->$key2;
                }
                }
                // jetzt die neue team_id
                $object->team_id = $new_id;
                // Insert the object into table.
                $result2 = Factory::getDbo()->insertObject($jsm_table, $object);
                
                if ( $result2 )
                {
                    // alles in ordnung
                }
                else
                {
                    // eintrag schon vorhanden, ein update
                    $tblProjectteam = JTable::getInstance('Projectteam', 'sportsmanagementtable');
                    $tblProjectteam->load($row->id);
                    
                    if ( empty($tblProjectteam->team_id) )
                    {
                        $tblProjectteam->team_id = $new_id;
                        if (!$tblProjectteam->store())
				        {
				        $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
				        }
                    }
                    /*
                    // eintrag schon vorhanden, ein update
                    // Create an object for the record we are going to update.
                    $object = new stdClass();
                    // Must be a valid primary key value.
                    $object->id = $row->id;
                    $object->team_id = $new_id;
                    // Update their details in the users table using id as the primary key.
                    $result = Factory::getDbo()->updateObject($jsm_table, $object, 'id');
                    */
                }
                
                
                
                
$my_text .= '<span style="color:'.sportsmanagementModeljoomleagueimports::$storeSuccessColor. '"<strong>Projectteam: ( '.$row->team_id.' ) mit ( '.$new_id.' ) umgesetzt!</strong>'.'</span>';
$my_text .= '<br />';
     
            }
            sportsmanagementModeljoomleagueimports::$_success['Projectteam:'] .= $my_text;
            
            }
            elseif ( preg_match("/team_player/i", $jsm_table) )
    {

/**
* umsetzung der teamplayer 
*/     
    //$team_player = array();
    sportsmanagementModeljoomleagueimports::$team_player[$project_id][0] = 0;
    $my_text;
    $my_text .= '<span style="color:'.sportsmanagementModeljoomleagueimports::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
$my_text .= '<br />';
    
    //$app->enqueueMessage(Text::_('Daten aus der Tabelle: ( '.$jsm_table.' ) werden in die neue Struktur umgesetzt!'),'Notice');    
    
$my_text .= '<span style="color:'.sportsmanagementModeljoomleagueimports::$existingInDbColor. '"<strong>Daten aus der Tabelle: ( '.$jl_table.' ) werden in die neue Struktur umgesetzt!"!</strong>'.'</span>';
$my_text .= '<br />';  

$query->clear();
//$query->select('pt.*');
$query->select('p.name as importprojectname');
$query->from('#__sportsmanagement_project AS p');
$query->where('p.id = '.$project_id);
$db->setQuery($query);            
$importprojectname = $db->loadResult();  

      
$my_text .= '<span style="color:'.sportsmanagementModeljoomleagueimports::$existingInDbColor. '"<strong>team_player Daten aus dem Projekt: ( '.$importprojectname.' ) werden in die neue Struktur umgesetzt!"!</strong>'.'</span>';
$my_text .= '<br />';
        
//        $query = $db->getQuery(true);
        $query->clear();
        $query->select('tp.*,st.team_id');
        $query->select('pers.firstname,pers.lastname');
        $query->from($jl_table.' AS tp');
        $query->join('INNER','#__sportsmanagement_project_team AS pt ON pt.id = tp.projectteam_id');
        $query->join('INNER','#__sportsmanagement_project AS p ON p.id = pt.project_id');
        $query->join('INNER','#__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
        $query->join('INNER','#__sportsmanagement_person as pers ON pers.id = tp.person_id ');
        
        
        $query->where('p.id = '.$project_id);
        
        if ( $season_id )
            {
                $query->select('p.season_id');
                $query->where('p.season_id = '.$season_id);
            }
            
            $db->setQuery($query);
            
            $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
            $result = $db->loadObjectList();
        
        foreach ( $result as $row )
            {
                
                $query->clear();
		        $query->select('id');
                // From table
                $query->from('#__sportsmanagement_season_person_id');
                $query->where('person_id = '.$row->person_id);
                $query->where('season_id = '.$row->season_id);
                $query->where('team_id = '.$row->team_id);
                $query->where('persontype = 1');
                $db->setQuery($query);
                $new_id = $db->loadResult();
                
                if ( !$new_id )
                {
                // als erstes wird der spieler der saison zugeordnet
                // Create and populate an object.
                $temp = new stdClass();
                $temp->person_id = $row->person_id;
                $temp->season_id = $row->season_id;
                $temp->team_id = $row->team_id;
                $temp->picture = $row->picture;
                $temp->persontype = 1;
                // Insert the object into the user profile table.
                $result = Factory::getDbo()->insertObject('#__sportsmanagement_season_person_id', $temp);
                }
                
                // ist der spieler schon in der season team person tabelle ?
                // Select some fields
                //$query = $db->getQuery(true);
                $query->clear();
		        $query->select('id');
                // From table
                $query->from('#__sportsmanagement_season_team_person_id');
                $query->where('person_id = '.$row->person_id);
                $query->where('season_id = '.$row->season_id);
                $query->where('team_id = '.$row->team_id);
                $db->setQuery($query);
                $new_id = $db->loadResult();
                
                if ( !$new_id )
                {
                    // Create and populate an object.
                    $temp = new stdClass();
                    $temp->season_id = $row->season_id;
                    $temp->team_id = $row->team_id;
                    $temp->person_id = $row->person_id;
                    $temp->picture = $row->picture;
                    $temp->project_position_id = $row->project_position_id;
                    $temp->persontype = 1;
                    $temp->active = 1;
                    $temp->published = 1;
                    // Insert the object into the user profile table.
                    $result = Factory::getDbo()->insertObject('#__sportsmanagement_season_team_person_id', $temp);
                    if ( $result )
                    {
                        $new_id = $db->insertid();
                    }
                    else
                    {
                        $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
                    }
$my_text .= '<span style="color:'.sportsmanagementModeljoomleagueimports::$storeSuccessColor. '"<strong>Team PLayer: ['.$row->firstname.' - '.$row->lastname.' ] ( '.$row->person_id.' ) mit ( '.$new_id.' ) umgesetzt!</strong>'.'</span>';
$my_text .= '<br />';                
                }
                else
                {
$my_text .= '<span style="color:'.sportsmanagementModeljoomleagueimports::$existingInDbColor. '"<strong>Team PLayer: ['.$row->firstname.' - '.$row->lastname.' ] ( '.$row->person_id.' ) mit ( '.$new_id.' ) vorhanden!</strong>'.'</span>';
$my_text .= '<br />';
                    
                }
//                // Create an object for the record we are going to joomleague update.
//                $object = new stdClass();
//                // Must be a valid primary key value.
//                $object->id = $row->id;
//                $object->import = $new_id;
//                // Update their details in the users table using id as the primary key.
//                $result_update = Factory::getDbo()->updateObject('#__joomleague_team_player', $object, 'id'); 
                
                // kein update, sondern den datensatz aus der importierten tabelle löschen
                // delete all custom keys for user 1001.
$query->clear();
$conditions = array(
    $db->quoteName('id') . ' = '.$row->id
);
 
$query->delete($db->quoteName($jsm_table));
$query->where($conditions);
$db->setQuery($query);
$result_update = $db->execute();
                
                if ( !$result_update )
                    {
                        $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
                    }
                    else
                    {
                        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' update team_player id: '.$row->id.' mit season_team_person id: '.$new_id),'');
                    }
            


            //$team_player[$row->id] = $new_id;
            sportsmanagementModeljoomleagueimports::$team_player[$project_id][$row->id] = $new_id;
            }
            sportsmanagementModeljoomleagueimports::$_success['Team Player Projekt('.$project_id.'):'] .= $my_text;
            //return $team_player;
            
    }
    elseif ( preg_match("/team_staff/i", $jsm_table) )
    {
/**
* umsetzung der team mitarbeiter
*/        
         
        $my_text;
        $my_text .= '<span style="color:'.sportsmanagementModeljoomleagueimports::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
$my_text .= '<br />';
//        $team_staff = array();
//    $team_staff[0] = 0;
    sportsmanagementModeljoomleagueimports::$team_staff[$project_id][0] = 0;
    
        //$app->enqueueMessage(Text::_('Daten aus der Tabelle: ( '.$jsm_table.' ) werden in die neue Struktur umgesetzt!'),'Notice');
$my_text .= '<span style="color:'.sportsmanagementModeljoomleagueimports::$existingInDbColor. '"<strong>Daten aus der Tabelle: ( '.$jl_table.' ) werden in die neue Struktur umgesetzt!"!</strong>'.'</span>';
$my_text .= '<br />';

$query->clear();
//$query->select('pt.*');
$query->select('p.name as importprojectname');
$query->from('#__sportsmanagement_project AS p');
$query->where('p.id = '.$project_id);
$db->setQuery($query);            
$importprojectname = $db->loadResult();  

      
$my_text .= '<span style="color:'.sportsmanagementModeljoomleagueimports::$existingInDbColor. '"<strong>team_player Daten aus dem Projekt: ( '.$importprojectname.' ) werden in die neue Struktur umgesetzt!"!</strong>'.'</span>';
$my_text .= '<br />';
        
//    $query = $db->getQuery(true);
        $query->clear();
        $query->select('tp.*,st.team_id');
        $query->from($jl_table.' AS tp');
        $query->select('pers.firstname,pers.lastname');
        $query->join('INNER','#__sportsmanagement_project_team AS pt ON pt.id = tp.projectteam_id');
        $query->join('INNER','#__sportsmanagement_project AS p ON p.id = pt.project_id');
        $query->join('INNER','#__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
        $query->join('INNER','#__sportsmanagement_person as pers ON pers.id = tp.person_id ');
        $query->where('p.id = '.$project_id);
        
        if ( $season_id )
            {
                $query->select('p.season_id');
                $query->where('p.season_id = '.$season_id);
            }
            
            $db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
            $result = $db->loadObjectList();
        
        foreach ( $result as $row )
            {
                
                $query->clear();
		        $query->select('id');
                // From table
                $query->from('#__sportsmanagement_season_person_id');
                $query->where('person_id = '.$row->person_id);
                $query->where('season_id = '.$row->season_id);
                $query->where('team_id = '.$row->team_id);
                $query->where('persontype = 2');
                $db->setQuery($query);
                $new_id = $db->loadResult();
                if ( !$new_id )
                {
                // als erstes wird der spieler der saison zugeordnet
                // Create and populate an object.
                $temp = new stdClass();
                $temp->person_id = $row->person_id;
                $temp->season_id = $row->season_id;
                $temp->team_id = $row->team_id;
                $temp->picture = $row->picture;
                $temp->persontype = 2;
                // Insert the object into the user profile table.
                $result = Factory::getDbo()->insertObject('#__sportsmanagement_season_person_id', $temp);
                }
                
                // ist der spieler schon in der season team person tabelle ?
                // Select some fields
                $query = $db->getQuery(true);
                $query->clear();
		        $query->select('id');
                // From table
                $query->from('#__sportsmanagement_season_team_person_id');
                $query->where('person_id = '.$row->person_id);
                $query->where('season_id = '.$row->season_id);
                $query->where('team_id = '.$row->team_id);
                $db->setQuery($query);
                $new_id = $db->loadResult();
                
                if ( !$new_id )
                {
                    // Create and populate an object.
                    $temp = new stdClass();
                    $temp->season_id = $row->season_id;
                    $temp->team_id = $row->team_id;
                    $temp->person_id = $row->person_id;
                    $temp->picture = $row->picture;
                    $temp->project_position_id = $row->project_position_id;
                    $temp->persontype = 2;
                    $temp->active = 1;
                    $temp->published = 1;
                    // Insert the object into the user profile table.
                    $result = Factory::getDbo()->insertObject('#__sportsmanagement_season_team_person_id', $temp);
                    if ( $result )
                    {
                        $new_id = $db->insertid();
                    }
                    else
                    {
                        $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
                    }
$my_text .= '<span style="color:'.sportsmanagementModeljoomleagueimports::$storeSuccessColor. '"<strong>Team Staff: ['.$row->firstname.' - '.$row->lastname.' ] ( '.$row->id.' ) mit ( '.$new_id.' ) umgesetzt!</strong>'.'</span>';
$my_text .= '<br />';                
                }
                else
                {
$my_text .= '<span style="color:'.sportsmanagementModeljoomleagueimports::$existingInDbColor. '"<strong>Team Staff: ['.$row->firstname.' - '.$row->lastname.' ] ( '.$row->id.' ) mit ( '.$new_id.' ) vorhanden!</strong>'.'</span>';
$my_text .= '<br />';                    
                }
                
//                // Create an object for the record we are going to joomleague update.
//                $object = new stdClass();
//                // Must be a valid primary key value.
//                $object->id = $row->id;
//                $object->import = $new_id;
//                // Update their details in the users table using id as the primary key.
//                $result_update = Factory::getDbo()->updateObject('#__joomleague_team_staff', $object, 'id'); 

$query->clear();
$conditions = array(
    $db->quoteName('id') . ' = '.$row->id
);
 
$query->delete($db->quoteName($jsm_table));
$query->where($conditions);
$db->setQuery($query);
$result_update = $db->execute();
                
                if ( !$result_update )
                    {
                        $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
                    }
                    else
                    {
                        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' update team_staff id: '.$row->id.' mit season_team_person id: '.$new_id),'');
                    }

    //$team_staff[$row->id] = $new_id;
    sportsmanagementModeljoomleagueimports::$team_staff[$project_id][$row->id] = $new_id;                
    } 
    sportsmanagementModeljoomleagueimports::$_success['Team Staff ('.$project_id.'):'] .= $my_text; 
    //return $team_staff;
    }      
    elseif ( preg_match("/project_referee/i", $jsm_table) )
    {
/**
* projekt schiedsrichter 
*/        
        $my_text;
        $my_text .= '<span style="color:'.sportsmanagementModeljoomleagueimports::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
$my_text .= '<br />';
//        $project_referee = array();
//    $project_referee[0] = 0;
    sportsmanagementModeljoomleagueimports::$project_referee[$project_id][0] = 0;
        //$app->enqueueMessage(Text::_('Daten aus der Tabelle: ( '.$jsm_table.' ) werden in die neue Struktur umgesetzt!'),'Notice');
$my_text .= '<span style="color:'.sportsmanagementModeljoomleagueimports::$existingInDbColor. '"<strong>Daten aus der Tabelle: ( '.$jsm_table.' ) werden in die neue Struktur umgesetzt!"!</strong>'.'</span>';
$my_text .= '<br />';

$query->clear();
//$query->select('pt.*');
$query->select('p.name as importprojectname');
$query->from('#__sportsmanagement_project AS p');
$query->where('p.id = '.$project_id);
$db->setQuery($query);            
$importprojectname = $db->loadResult();  

      
$my_text .= '<span style="color:'.sportsmanagementModeljoomleagueimports::$existingInDbColor. '"<strong>team_player Daten aus dem Projekt: ( '.$importprojectname.' ) werden in die neue Struktur umgesetzt!"!</strong>'.'</span>';
$my_text .= '<br />';

//$query = $db->getQuery(true);
        $query->clear();
        $query->select('tp.*');
        $query->select('pers.firstname,pers.lastname');
        $query->from($jl_table.' AS tp');
        $query->join('INNER','#__sportsmanagement_project AS p ON p.id = tp.project_id');
        $query->join('INNER','#__sportsmanagement_person as pers ON pers.id = tp.person_id ');
$query->where('p.id = '.$project_id);

if ( $season_id )
            {
                $query->select('p.season_id');
                $query->where('p.season_id = '.$season_id);
            }
            
            $db->setQuery($query);
             $result = $db->loadObjectList();

foreach ( $result as $row )
            {
                
                $query->clear();
		        $query->select('id');
                // From table
                $query->from('#__sportsmanagement_season_person_id');
                $query->where('person_id = '.$row->person_id);
                $query->where('season_id = '.$row->season_id);
                $query->where('team_id = 0');
                $query->where('persontype = 3');
                $db->setQuery($query);
                $new_id = $db->loadResult();
                
                if ( !$new_id )
                {
                // als erstes wird der spieler der saison zugeordnet
                // Create and populate an object.
                $temp = new stdClass();
                $temp->person_id = $row->person_id;
                $temp->season_id = $row->season_id;
                $temp->team_id = 0;
                $temp->picture = $row->picture;
                $temp->persontype = 3;
                $temp->published = 1;
                // Insert the object into the table.
                $result = Factory::getDbo()->insertObject('#__sportsmanagement_season_person_id', $temp);
                }
                
                if ( $result )
                {
                $new_id = $db->insertid();
$my_text .= '<span style="color:'.sportsmanagementModeljoomleagueimports::$storeSuccessColor. '"<strong>Project Referee: ['.$row->firstname.' - '.$row->lastname.' ] person_id: ( '.$row->person_id.' ) mit ( '.$new_id.' ) umgesetzt!</strong>'.'</span>';
$my_text .= '<br />';                 
                }
                else
                {
                $query = $db->getQuery(true);
                $query->clear();
		        $query->select('id');
                // From table
                $query->from('#__sportsmanagement_season_person_id');
                $query->where('person_id = '.$row->person_id);
                $query->where('season_id = '.$row->season_id);
                $query->where('persontype = 3');
                $db->setQuery($query);
                $new_id = $db->loadResult();
$my_text .= '<span style="color:'.sportsmanagementModeljoomleagueimports::$existingInDbColor. '"<strong>Project Referee: ['.$row->firstname.' - '.$row->lastname.' ] person_id: ( '.$row->person_id.' ) mit ( '.$new_id.' ) vorhanden!</strong>'.'</span>';
$my_text .= '<br />'; 
}

//$project_referee[$row->person_id] = $new_id;  
sportsmanagementModeljoomleagueimports::$project_referee[$project_id][$row->person_id] = $new_id;                
}                

//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' __joomleague_project_referee -> <br><pre>'.print_r($project_referee,true).'</pre>'),'');
 
sportsmanagementModeljoomleagueimports::$_success['Project Referee neue Struktur ('.$project_id.'):'] .= $my_text; 
    //return $project_referee;       
}             

}




            
}    

?>