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

//  $db->__destruct();

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
jimport('joomla.application.component.modellist');

$maxImportTime = 1920;
if ((int)ini_get('max_execution_time') < $maxImportTime){@set_time_limit($maxImportTime);}

/*
update #__sportsmanagement_project set name = replace(name, 'Kreisligen', 'Kreisliga')";
*/

/*
umsetzung der spieler in den paarungen

########################################################################################
1.) einfügen der startaufstellung 
----------------------------------------------------------------------------------------
INSERT INTO j25_sportsmanagement_match_player (id,match_id,teamplayer_id,came_in,in_for,in_out_time) 

select
mp.id,  
mp.match_id, 
stp.id as teamplayer_id,
mp.came_in,
mp.in_for,
mp.in_out_time

from j25_joomleague_match_player as mp
inner join j25_joomleague_team_player as tp on tp.id = mp.teamplayer_id
inner join j25_joomleague_project_team as pt on pt.id = tp.projectteam_id
inner join j25_joomleague_project as p on p.id = pt.project_id

left join j25_sportsmanagement_season_team_person_id as stp on 
stp.person_id = tp.person_id
and stp.team_id = pt.team_id
and stp.season_id = p.season_id
where mp.came_in = 0
----------------------------------------------------------------------------------------

########################################################################################
2.) danach kommt der update 
UPDATE `j25_sportsmanagement_match_player` SET  `out` =  '1'
WHERE `j25_sportsmanagement_match_player`.`in_out_time` IS NOT NULL
AND `j25_sportsmanagement_match_player`.`came_in` = '0'
----------------------------------------------------------------------------------------

########################################################################################
3.) dann die einwechselungen 

INSERT INTO j25_sportsmanagement_match_player (id,match_id,teamplayer_id,came_in,in_for,in_out_time)
select
mp.id,  
mp.match_id, 
stp.id as teamplayer_id,
mp.came_in,
(
select stp.id
from j25_joomleague_team_player as tp
inner join j25_joomleague_project_team as pt on pt.id = tp.projectteam_id
inner join j25_joomleague_project as p on p.id = pt.project_id

left join j25_sportsmanagement_season_team_person_id as stp on 
stp.person_id = tp.person_id
and stp.team_id = pt.team_id
and stp.season_id = p.season_id
where tp.id = mp.in_for
)
as in_for,
mp.in_out_time

from j25_joomleague_match_player as mp
inner join j25_joomleague_team_player as tp on tp.id = mp.teamplayer_id
inner join j25_joomleague_project_team as pt on pt.id = tp.projectteam_id
inner join j25_joomleague_project as p on p.id = pt.project_id

left join j25_sportsmanagement_season_team_person_id as stp on 
stp.person_id = tp.person_id
and stp.team_id = pt.team_id
and stp.season_id = p.season_id
where mp.came_in = 1

limit 0,5

----------------------------------------------------------------------------------------

########################################################################################
4.) jetzt kommen die schiedsrichter in die season_person tabelle 

INSERT INTO j25_sportsmanagement_season_person_id (person_id,season_id,persontype)

select
mp.person_id as person_id,
p.season_id,
3 as persontype


from j25_joomleague_project_referee as mp
inner join j25_joomleague_project as p on p.id = mp.project_id

left join j25_sportsmanagement_season_person_id as stp on 
stp.person_id = mp.person_id
and stp.season_id = p.season_id

where stp.id IS NULL
group by mp.person_id,p.season_id

limit 0,5
----------------------------------------------------------------------------------------
########################################################################################
5.) jetzt kommen die schiedsrichter in die schiedsrichter projekt tabelle 

INSERT INTO j25_sportsmanagement_project_referee (id,project_id,person_id,published)

select
mp.id,  
mp.project_id, 
stp.id as person_id,
1 as published


from j25_joomleague_project_referee as mp
inner join j25_joomleague_project as p on p.id = mp.project_id

left join j25_sportsmanagement_season_person_id as stp on 
stp.person_id = mp.person_id
and stp.season_id = p.season_id

where stp.persontype = 3

limit 0,5

########################################################################################
6.) jetzt kommen die schiedsrichter in die schiedsrichter spiel tabelle 

INSERT INTO j25_sportsmanagement_match_referee (id,match_id,project_referee_id,published)

select
mp.id,  
mp.match_id, 
mp.project_referee_id,
1 as published


from j25_joomleague_match_referee as mp

########################################################################################
7.) jetzt kommen die ereignisse pro spiel 

INSERT INTO j25_sportsmanagement_match_event (id,match_id,projectteam_id,teamplayer_id,teamplayer_id2,event_time,event_type_id,event_sum,notice,notes)
select
me.id,
me.match_id,
me.projectteam_id,
stp.id as teamplayer_id,
me.teamplayer_id2,
me.event_time,
me.event_type_id,
me.event_sum,
me.notice,
me.notes
from j25_joomleague_match_event as me
inner join j25_joomleague_team_player as tp on tp.id = me.teamplayer_id
inner join j25_joomleague_project_team as pt on pt.id = tp.projectteam_id
inner join j25_joomleague_project as p on p.id = pt.project_id

left join j25_sportsmanagement_season_team_person_id as stp on 
stp.person_id = tp.person_id
and stp.team_id = pt.team_id
and stp.season_id = p.season_id

UPDATE  `j25_sportsmanagement_match_event` SET  `event_type_id` =  '8' WHERE  `j25_sportsmanagement_match_event`.`event_type_id` =1;
UPDATE  `j25_sportsmanagement_match_event` SET  `event_type_id` =  '1' WHERE  `j25_sportsmanagement_match_event`.`event_type_id` =2;
UPDATE  `j25_sportsmanagement_match_event` SET  `event_type_id` =  '2' WHERE  `j25_sportsmanagement_match_event`.`event_type_id` =3;
UPDATE  `j25_sportsmanagement_match_event` SET  `event_type_id` =  '3' WHERE  `j25_sportsmanagement_match_event`.`event_type_id` =4;

1=tor
2=gelbe karte
3= gelb/rote karte
4=rote karte
5=minuten
6=eigentor

mp.teamplayer_id, 
mp.project_position_id,
tp.projectteam_id,
tp.person_id,
m.round_id,
r.project_id,
p.season_id,
pt.team_id,


INSERT INTO j25_sportsmanagement_rosterposition (name,alias,country,extended,short_name,picture)
select name,alias,country,extended,short_name,picture
from j25_joomleague_rosterposition


*/

/**
 * sportsmanagementModeljoomleagueimports
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModeljoomleagueimports extends JModelList
{

static $db_num_rows = 0;

static $storeFailedColor = 'red';
static $storeSuccessColor = 'green';
static $existingInDbColor = 'orange';
static $storeInfo = 'black';

static $_success = array();

static $team_player = array();
static $project_referee = array();
static $team_staff = array();


/**
 * sportsmanagementModeljoomleagueimports::check_database()
 * 
 * @return void
 */
function check_database()
{
$conf = JFactory::getConfig();
$app = JFactory::getApplication();
$params = JComponentHelper::getParams( 'com_sportsmanagement' );   
// welche joomla version ?
            if(version_compare(JVERSION,'3.0.0','ge')) 
        { 
$debug = $conf->get('config.debug');
}
else
{
$debug = $conf->getValue('config.debug');    
}

$option = array(); //prevent problems
$option['driver']   = $params->get( 'jl_dbtype' );            // Database driver name
$option['host']     = $params->get( 'jl_host' );    // Database host name
$option['user']     = $params->get( 'jl_user' );       // User for database authentication
$option['password'] = $params->get( 'jl_password' );   // Password for database authentication
$option['database'] = $params->get( 'jl_db' );      // Database name
$option['prefix']   = $params->get( 'jl_dbprefix' );             // Database prefix (may be empty)
 
// zuerst noch überprüfen, ob der user
// überhaupt den zugriff auf die datenbank hat.
$jl_access = JDatabase::getInstance( $option );    


//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jl_access<br><pre>'.print_r($jl_access,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getErrorMsg<br><pre>'.print_r($jl_access->getErrorMsg(),true).'</pre>'),'Error');


/*
if ( JError::isError($jl_access) ) {
			header('HTTP/1.1 500 Internal Server Error');
			jexit('Database Error: ' . $jl_access->toString() );
		}

		if ($jl_access->getErrorNum() > 0) {
			JError::raiseError(500 , 'JDatabase::getInstance: Could not connect to database ' . 'joomla.library:'.$jl_access->getErrorNum().' - '.$jl_access->getErrorMsg() );
		}

		$jl_access->debug( $debug );
*/            
}

    
/**
 * sportsmanagementModeljoomleagueimports::updateplayerproposition()
 * 
 * @return void
 */
function updateplayerproposition()
{
$app = JFactory::getApplication();
    $db = JFactory::getDbo(); 
    $query = $db->getQuery(true);
    $option = JRequest::getCmd('option');    
    

// Select some fields
            $query = $db->getQuery(true);
            $query->clear();
		    $query->select('tp.project_position_id,tp.import');
            // From joomleague table
		    $query->from('#__joomleague_team_player AS tp');
            $query->where('tp.import != 0');
            $query->group('tp.import');
$db->setQuery($query);
$result = $db->loadObjectList();

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'),'');

foreach ( $result as $row )
{
// Create an object for the record we are going to joomleague update.
                $object = new stdClass();
                // Must be a valid primary key value.
                $object->id = $row->import;
                $object->project_position_id = $row->project_position_id;
                // Update their details in the users table using id as the primary key.
                $result = JFactory::getDbo()->updateObject('#__sportsmanagement_season_team_person_id', $object, 'id');
    
}


}


/**
 * sportsmanagementModeljoomleagueimports::updatestaffproposition()
 * 
 * @return void
 */
function updatestaffproposition()
{
$app = JFactory::getApplication();
    $db = JFactory::getDbo(); 
    $query = $db->getQuery(true);
    $option = JRequest::getCmd('option');    
    

// Select some fields
            $query = $db->getQuery(true);
            $query->clear();
		    $query->select('tp.project_position_id,tp.import');
            // From joomleague table
		    $query->from('#__joomleague_team_staff AS tp');
            $query->where('tp.import != 0');
            $query->group('tp.import');
$db->setQuery($query);
$result = $db->loadObjectList();

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'),'');

foreach ( $result as $row )
{
// Create an object for the record we are going to joomleague update.
                $object = new stdClass();
                // Must be a valid primary key value.
                $object->id = $row->import;
                $object->project_position_id = $row->project_position_id;
                // Update their details in the users table using id as the primary key.
                $result = JFactory::getDbo()->updateObject('#__sportsmanagement_season_team_person_id', $object, 'id');
    
}


}



/**
 * sportsmanagementModeljoomleagueimports::updatepositions()
 * 
 * @return void
 */
function updatepositions()
{
$app = JFactory::getApplication();
    $db = JFactory::getDbo(); 
    $query = $db->getQuery(true);
    $option = JRequest::getCmd('option');
    
$post = JRequest::get('post');
        
        $pks = JRequest::getVar('cid', null, 'post', 'array');
        
        for ($x=0; $x < count($pks); $x++)
		{
            $position = $post['position'.$pks[$x]];
            $position_old = $pks[$x];

// Fields to update.
$fields = array(
    $db->quoteName('position_id') . ' = ' . $db->quote($position),
    $db->quoteName('jl_update') . ' = 1'
);
 
// Conditions for which records should be updated.
$conditions = array(
    $db->quoteName('position_id') . ' = ' . $db->quote($position_old),
    $db->quoteName('jl_update') . ' = 0'
);


$query->clear(); 
$query->update($db->quoteName('#__sportsmanagement_project_position'))->set($fields)->where($conditions);
$db->setQuery($query);
sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
$app->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_N_ITEMS_PUBLISHED',self::$db_num_rows),'');

$query->clear(); 
$query->update($db->quoteName('#__sportsmanagement_person'))->set($fields)->where($conditions);
$db->setQuery($query);
sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
$app->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_N_ITEMS_PUBLISHED',self::$db_num_rows),'');

		}
        
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'');    
    
        
}

/**
 * sportsmanagementModeljoomleagueimports::gettotals()
 * 
 * @return
 */
function gettotals()
{
    $app = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $option = JRequest::getCmd('option');
        
        // retrieve the value of the state variable. First see if the variable has been passed
        // in the request. Otherwise retrieve the stored value. If none of these are specified,
        // the specified default value will be returned
        // function syntax is getUserStateFromRequest( $key, $request, $default );
        
        $jsm_table = $app->getUserStateFromRequest( "$option.jsm_table", 'jsm_table', '' );
        $jl_table = $app->getUserStateFromRequest( "$option.jl_table", 'jl_table', '' );
        $season_id = $app->getUserState( "$option.season_id", '0' );
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jl_table<br><pre>'.print_r($jl_table,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' season_id<br><pre>'.print_r($season_id,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsm_table<br><pre>'.print_r($jsm_table,true).'</pre>'),'');

        // Das "i" nach der Suchmuster-Begrenzung kennzeichnet eine Suche ohne
            // Berücksichtigung von Groß- und Kleinschreibung
            if ( preg_match("/project_team/i", $jsm_table) ) 
            {
            
            $query = $db->getQuery(true);
            $query->clear();
            $query->select('COUNT(pt.id) AS total');
            $query->from($jl_table.' AS pt');
            $query->join('INNER','#__sportsmanagement_project AS p ON p.id = pt.project_id');
            $query->where('pt.import = 0');
            
            if ( $season_id )
            {
                $query->where('p.season_id = '.$season_id);
            }
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            
            $db->setQuery($query);
            $total = $db->loadResult();
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'total<br><pre>'.print_r($total,true).'</pre>'),'');
            
            return $total;
            }
            
            if (preg_match("/team_player/i", $jsm_table)) 
            {
            $query = $db->getQuery(true);
            $query->clear();
            $query->select('COUNT(tp.id) AS total');
            $query->from($jl_table.' AS tp');
            $query->join('INNER','#__sportsmanagement_project_team AS pt ON pt.id = tp.projectteam_id');
            $query->join('INNER','#__sportsmanagement_project AS p ON p.id = pt.project_id');
            $query->where('tp.import = 0');
            
            if ( $season_id )
            {
                $query->where('p.season_id = '.$season_id);
            }
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            
            $db->setQuery($query);
            $total = $db->loadResult();
            
            return $total;    
            }    


}        

/**
 * sportsmanagementModeljoomleagueimports::checkimport()
 * 
 * @return void
 */
function checkimport()
{
$app = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $option = JRequest::getCmd('option');
        $post = JRequest::get('post');
        $exportfields = array();
        $cid = $post['cid'];
        $jl = $post['jl'];
        $jsm = $post['jsm'];
        $season_id= $post['filter_season'];
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'post<br><pre>'.print_r($post,true).'</pre>'),'');
        
        
  foreach ( $cid as $key => $value )
        {
        $jsm_table = $jsm[$value];
        $jl_table = $jl[$value];
        
        // Das "i" nach der Suchmuster-Begrenzung kennzeichnet eine Suche ohne
            // Berücksichtigung von Groß- und Kleinschreibung
            if (preg_match("/project_team/i", $jsm_table)) 
            {
                // store the variable that we would like to keep for next time
                // function syntax is setUserState( $key, $value );
                $app->setUserState( "$option.jsm_table", $jsm_table );
                $app->setUserState( "$option.jl_table", $jl_table );
                $app->setUserState( "$option.season_id", $season_id );
                //JRequest::setVar('jsm_table', $jsm_table);
            return true;    
            }
            elseif (preg_match("/team_player/i", $jsm_table)) 
            {
                // store the variable that we would like to keep for next time
                // function syntax is setUserState( $key, $value );
                $app->setUserState( "$option.jsm_table", $jsm_table );
                $app->setUserState( "$option.jl_table", $jl_table );
                $app->setUserState( "$option.season_id", $season_id );
                //JRequest::setVar('jsm_table', $jsm_table);
            return true;    
            }
            elseif (preg_match("/team_staff/i", $jsm_table)) 
            {
                // store the variable that we would like to keep for next time
                // function syntax is setUserState( $key, $value );
                $app->setUserState( "$option.jsm_table", $jsm_table );
                $app->setUserState( "$option.jl_table", $jl_table );
                $app->setUserState( "$option.season_id", $season_id );
                //JRequest::setVar('jsm_table', $jsm_table);
            return true;    
            }
            else
            {
            return false;
            }
        }        
    
}


function get_success()
{
return self::$_success;    
}


/**
 * sportsmanagementModeljoomleagueimports::importjoomleaguenew()
 * 
 * @return
 */
function importjoomleaguenew()
{
// Reference global application object
$app = JFactory::getApplication();
// JInput object
$jinput = $app->input;
$option = $jinput->getCmd('option');

self::$_success = '';

$sports_type_id = $jinput->post->get('filter_sports_type', 0);
//$this->setState('filter.sports_type', $sports_type_id);
$app->setUserState( $option.'.filter_sports_type', $sports_type_id );

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' sports_type_id <br><pre>'.print_r($sports_type_id,true).'</pre>'),'');

$db = JFactory::getDbo(); 
$query = $db->getQuery(true);

//$post = JRequest::get('post');
$exportfields = array();        
$table_copy = array();        

//$table_copy[] = 'associations';

$table_copy[] = 'club';
$table_copy[] = 'division';
$table_copy[] = 'league';
$table_copy[] = 'match';
$table_copy[] = 'match_commentary';
$table_copy[] = 'person';
$table_copy[] = 'playground';

$table_copy[] = 'position';
$table_copy[] = 'position_eventtype';
$table_copy[] = 'position_statistic';

$table_copy[] = 'project';
$table_copy[] = 'project_position';
$table_copy[] = 'match_referee';

$table_copy[] = 'rosterposition';

$table_copy[] = 'round';
$table_copy[] = 'season';
$table_copy[] = 'statistic';
$table_copy[] = 'team';
$table_copy[] = 'team_trainingdata';
$table_copy[] = 'team_player';
$table_copy[] = 'team_staff';

$table_copy[] = 'template_config';


$table_copy[] = 'prediction_admin';
$table_copy[] = 'prediction_game';
$table_copy[] = 'prediction_groups';
$table_copy[] = 'prediction_member';
$table_copy[] = 'prediction_project';
$table_copy[] = 'prediction_result';
$table_copy[] = 'prediction_template';
        
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' table_copy <br><pre>'.print_r($table_copy,true).'</pre>'),'');        

$tables = $db->getTableList();
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' tables <br><pre>'.print_r($tables,true).'</pre>'),'');     

//$table_copy = array(); 
//$table_copy[] = 'club';

/**
* als erstes kommen die tabellen, die nur kopiert werden !
*/        
$my_text = '';  
$my_text .= '<span style="color:'.self::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
$my_text .= '<br />';

foreach ( $table_copy as $key => $value )
{
    
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' key <br><pre>'.print_r($key,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' value <br><pre>'.print_r($value,true).'</pre>'),'');
    
$jl_table = '#__joomleague_'.$value;
$jsm_table = '#__sportsmanagement_'.$value;

/**
 * hier überprüfen wir noch sicherheitshalber ob die jl tabelle existiert
 */
$prefix = $db->getPrefix();
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' prefix <br><pre>'.print_r($prefix,true).'</pre>'),'');
$key_table = array_search($prefix.'joomleague_'.$value, $tables);
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jl_table <br><pre>'.print_r($jl_table,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' key_table <br><pre>'.print_r($key_table,true).'</pre>'),'');

if ( $key_table )
{
/**
* hier muss auch wieder zwischen den joomla versionen unterschieden werden
*/                
if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
$jl_fields = $db->getTableColumns('#__joomleague_'.$value);
$jsm_fields = $db->getTableColumns('#__sportsmanagement_'.$value);
$jl_fields[$jl_table] = $jl_fields;
$jsm_fields[$jsm_table] = $jsm_fields;

}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here
$jl_fields = $db->getTableFields('#__joomleague_'.$value);
$jsm_fields = $db->getTableFields('#__sportsmanagement_'.$value);
}

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jl_fields <br><pre>'.print_r($jl_fields,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsm_fields <br><pre>'.print_r($jsm_fields,true).'</pre>'),'');            

if ( count($jl_fields[$jl_table]) === 0 )
{
//$app->enqueueMessage(JText::_('Die Tabelle: ( '.$jl_table.' ) koennen nicht kopiert werden. Tabelle: ( '.$jl_table.' ) ist nicht vorhanden!'),'Error');    

$my_text .= '<span style="color:'.self::$storeFailedColor. '"<strong>Die Tabelle: ( '.$jl_table.' ) kann nicht kopiert werden. Tabelle: ( '.$jl_table.' ) ist nicht vorhanden!</strong>'.'</span>';
$my_text .= '<br />';

}
else
{

/**
* die landesverbände übernehmen
*/    
/*
if ( $value == 'associations' )
{
$db->truncateTable($jsm_table);    
}    
else
{
$db->truncateTable($jsm_table);    
}
*/

$db->truncateTable($jsm_table);
//sportsmanagementModeldatabasetool::setSportsManagementTableQuery($jsm_table,'truncate');


$query = $db->getQuery(true);
$query->clear();
$query->select('COUNT(id) AS total');
$query->from($jsm_table);
$db->setQuery($query);

$totals = $db->loadResult();

if ( $totals )
{
//$app->enqueueMessage(JText::_('Daten aus der Tabelle: ( '.$jl_table.' ) koennen nicht kopiert werden. Tabelle: ( '.$jsm_table.' ) nicht leer!'),'Error');  
$my_text .= '<span style="color:'.self::$storeFailedColor. '"<strong>Daten aus der Tabelle: ( '.$jl_table.' ) koennen nicht kopiert werden. Tabelle: ( '.$jsm_table.' ) nicht leer!</strong>'.'</span>';
$my_text .= '<br />';   
}
else
{

unset($jsm_field_array);
unset($exportfields);    
$jsm_field_array = $jsm_fields[$jsm_table];
    
foreach ( $jl_fields[$jl_table] as $key2 => $value2 )
            {
                //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'key2<br><pre>'.print_r($key2,true).'</pre>'),'');
                //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'value2<br><pre>'.print_r($value2,true).'</pre>'),'');
                
                switch ($key2)
                {
                    case 'import':
                    case 'ordering':
                    case 'checked_out':
                    case 'checked_out_time':
                    case 'modified':
                    case 'modified_by':
                    case 'out':
                    break;
                    default:
                    if (array_key_exists($key2, $jsm_field_array)) 
                    {
                    //$exportfields[] = $db->Quote($key2);
                    $exportfields[] = $key2;
                    }
                    break;
                }
                
            }
            
            $select_fields = implode(',', $exportfields);
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'exportfields<br><pre>'.print_r($exportfields,true).'</pre>'),'');
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'select_fields<br><pre>'.print_r($select_fields,true).'</pre>'),'');
            
            $query = $db->getQuery(true);
            $query->clear();
            $query = 'INSERT INTO '.$jsm_table.' ('.$select_fields.') SELECT '.$select_fields.' FROM '.$jl_table;
            $db->setQuery($query);
            if (!$db->query())
		    {
			$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
		    }
            else
            {
            //$app->enqueueMessage(JText::_('Daten aus der Tabelle: ( '.$jl_table.' ) in die Tabelle: ( '.$jsm_table.' ) importiert!'),'Notice');    

$my_text .= '<span style="color:'.self::$storeSuccessColor. '"<strong>Daten aus der Tabelle: ( '.$jl_table.' ) in die Tabelle: ( '.$jsm_table.' ) importiert!</strong>'.'</span>';
$my_text .= '<br />';             
            
            }

if ( $value == 'position' )
{
$query = $db->getQuery(true);
$query->clear();
// Fields to update.
$fields = array(
    $db->quoteName('sports_type_id') . ' = ' . $sports_type_id
);
 
// Conditions for which records should be updated.
$conditions = array(
    $db->quoteName('sports_type_id') . ' != '.$sports_type_id
);
 
$query->update($db->quoteName('#__sportsmanagement_position'))->set($fields)->where($conditions);

$db->setQuery($query);
$result = $db->execute();
}

}   

}

}
         
}

self::$_success['Tabellenkopie:'] = $my_text;

/**
 * nach der kopie der tabellen müssen wir die sportart bei den mannschaften setzen.
 * sonst gibt es in der übersicht der projektmannschaften eine fehlermeldung.
 */
$query = $db->getQuery(true);
$my_text = '';
$my_text .= '<span style="color:'.self::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
$my_text .= '<br />'; 
// Fields to update.
$fields = array(
    $db->quoteName('sports_type_id') . ' = '. $sports_type_id 
);
 
// Conditions for which records should be updated.
$conditions = array(
    $db->quoteName('sports_type_id') . ' != '. $sports_type_id
);
 
$query->update($db->quoteName('#__sportsmanagement_team'))->set($fields)->where($conditions);
$db->setQuery($query);
sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

$my_text .= '<span style="color:'.self::$storeSuccessColor. '"<strong>'.self::$db_num_rows.' Daten in der Tabelle: ( __sportsmanagement_team ) aktualisiert!</strong>'.'</span>';
$my_text .= '<br />';     

$query->update($db->quoteName('#__sportsmanagement_project'))->set($fields)->where($conditions);
$db->setQuery($query);
sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

$my_text .= '<span style="color:'.self::$storeSuccessColor. '"<strong>'.self::$db_num_rows.' Daten in der Tabelle: ( __sportsmanagement_project ) aktualisiert!</strong>'.'</span>';
$my_text .= '<br />';     

self::$_success['Tabellenaktualisierung:'] = $my_text; 

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'success<br><pre>'.print_r(self::$_success,true).'</pre>'),'');

/**
 * jetzt kommen die hauptpositionen
 */


/**
 * jetzt kommen die nebenpositionen
 */

$my_text = '';
self::$_success['Positionen:'] = $my_text;

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jl_position -> <br><pre>'.print_r($jl_position,true).'</pre>'),'');

/**
 * als nächstes müssen wir die ereignisse verarbeiten
 */

$my_text = '';
self::$_success['Eventtypes:'] = $my_text;

/**
 * jetzt werden die ereignisse zu den positionen verarbeitet
 */
$my_text = '';  
self::$_success['Position Eventtypes:'] = $my_text;

/**
 * dann die positions id´s in den tabellen updaten
 */

$my_text = '';
self::$_success['Positions:'] = $my_text;

/**
 * jetzt kommt die umsetzung in die neue struktur
 */

$query = $db->getQuery(true);
$query->clear();
$query->select('*');
$query->from('#__sportsmanagement_project');
$db->setQuery($query);
$result = $db->loadObjectList();


//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'success<br><pre>'.print_r(self::$_success,true).'</pre>'),'');
//$app->setUserState( "$option.success", self::$_success );
//return;

$db->truncateTable('#__sportsmanagement_project_team');
$db->truncateTable('#__sportsmanagement_season_team_id');
$db->truncateTable('#__sportsmanagement_season_person_id');
$db->truncateTable('#__sportsmanagement_season_team_person_id');

/*
sportsmanagementModeldatabasetool::setSportsManagementTableQuery('#__sportsmanagement_project_team','truncate');
sportsmanagementModeldatabasetool::setSportsManagementTableQuery('#__sportsmanagement_season_team_id','truncate');
sportsmanagementModeldatabasetool::setSportsManagementTableQuery('#__sportsmanagement_season_person_id','truncate');
sportsmanagementModeldatabasetool::setSportsManagementTableQuery('#__sportsmanagement_season_team_person_id','truncate');
*/

//return; 

if ( $result )
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project -> <br><pre>'.print_r($result,true).'</pre>'),'');  

foreach( $result as $row )
{
/**
 * jetzt werden die projekt-mannschaften umgesetzt
 */    
$jl_table = '#__joomleague_project_team';
$jsm_table = '#__sportsmanagement_project_team';
$mdl = JModelLegacy::getInstance("joomleagueimport", "sportsmanagementModel");
$mdl->newstructurjlimport($row->season_id,$jl_table,$jsm_table,$row->id);

/**
 * jetzt werden die team spieler umgesetzt
 */
$jl_table = '#__sportsmanagement_team_player';
$jsm_table = '#__sportsmanagement_team_player';
$mdl = JModelLegacy::getInstance("joomleagueimport", "sportsmanagementModel");
$mdl->newstructurjlimport($row->season_id,$jl_table,$jsm_table,$row->id);

/**
 * jetzt werden die team mitarbeiter umgesetzt
 */
$jl_table = '#__sportsmanagement_team_staff';
$jsm_table = '#__sportsmanagement_team_staff';
$mdl = JModelLegacy::getInstance("joomleagueimport", "sportsmanagementModel");
$mdl->newstructurjlimport($row->season_id,$jl_table,$jsm_table,$row->id);

/**
 * jetzt werden die project referees umgesetzt
 */
$jl_table = '#__joomleague_project_referee';
$jsm_table = '#__sportsmanagement_project_referee';
$mdl = JModelLegacy::getInstance("joomleagueimport", "sportsmanagementModel");
$mdl->newstructurjlimport($row->season_id,$jl_table,$jsm_table,$row->id);

}

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' __sportsmanagement_team_player -> <br><pre>'.print_r(self::$team_player,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' __sportsmanagement_team_staff -> <br><pre>'.print_r(self::$team_staff,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' __joomleague_project_referee -> <br><pre>'.print_r(self::$project_referee,true).'</pre>'),'');

}    

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'success<br><pre>'.print_r(self::$team_player,true).'</pre>'),'');


$query = $db->getQuery(true);
$query->clear();
$query = 'INSERT INTO #__sportsmanagement_project_referee (id,project_id,project_position_id,person_id,published,picture)
select
mp.id,  
mp.project_id, 
propos.id as project_position_id,
stp.id as person_id,
1 as published,
mp.picture
from #__joomleague_project_referee as mp
inner join #__joomleague_project as p on p.id = mp.project_id

left join #__sportsmanagement_project_position as propos on propos.project_id = mp.project_id
and propos.position_id = mp.position_id

left join #__sportsmanagement_season_person_id as stp on 
stp.person_id = mp.person_id
and stp.season_id = p.season_id
where stp.persontype = 3;';
$db->setQuery($query);

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'query<br><pre>'.print_r($query,true).'</pre>'),'');

sportsmanagementModeldatabasetool::runJoomlaQuery();




$app->setUserState( "$option.success", self::$_success );
return;



/**
 * jetzt werden die match events importiert
 */
$my_text = '';
$my_text .= '<span style="color:'.self::$existingInDbColor. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
$my_text .= '<br />';
$query = $db->getQuery(true);
$query->clear();
$query->select('me.*,r.project_id');
$query->from('#__joomleague_match_event as me');
$query->join('INNER', '#__sportsmanagement_match AS m ON m.id = me.match_id');
$query->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id');
$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = r.project_id');

$db->setQuery($query);
$result = $db->loadObjectList();

if ( $result )
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' __joomleague_match_event -> <br><pre>'.print_r($result,true).'</pre>'),'');

foreach( $result as $row )
{
/**
 * vorher müssen wir überprüfen ob es den eintrag schon gibt.
 * ansonsten haben wir doppelte einträge.
 */
$query = $db->getQuery(true);
$query->clear();
$query->select('me.id');
$query->from('#__sportsmanagement_match_event as me');
$query->where('me.match_id = '.$row->match_id );
$query->where('me.projectteam_id = '.$row->projectteam_id );
$query->where('me.teamplayer_id = '.self::$team_player[$row->project_id][$row->teamplayer_id] );
$query->where('me.event_time = '.$row->event_time );
$query->where('me.event_type_id = '.$jl_eventtype[$row->event_type_id] );
//$query->where('me.event_sum = '.$row->event_sum );
$db->setQuery($query);
$result_me = $db->loadResult();

if ( $result_me )
{
$my_text .= '<span style="color:'.self::$existingInDbColor. '"<strong>Project ('.$row->project_id.') Match Event: ( '.$row->id.' ) in der Tabelle vorhanden!</strong>'.'</span>';
$my_text .= '<br />';     
}
else
{
$mdl = JModelLegacy::getInstance("matchevent", "sportsmanagementModel");
$mdlTable = $mdl->getTable();   

$mdlTable->match_id = $row->match_id;
$mdlTable->projectteam_id = $row->projectteam_id;
$mdlTable->teamplayer_id = self::$team_player[$row->project_id][$row->teamplayer_id];
$mdlTable->event_time = $row->event_time;
$mdlTable->event_type_id = $jl_eventtype[$row->event_type_id];
$mdlTable->event_sum = $row->event_sum;
$mdlTable->notice = $row->notice;
$mdlTable->notes = $row->notes;
if ($mdlTable->store()===false)
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
$my_text .= '<span style="color:'.self::$existingInDbColor. '"<strong>Project ('.$row->project_id.') Match Event: ( '.$row->id.' ) in der Tabelle vorhanden!</strong>'.'</span>';
$my_text .= '<br />'; 
}
else
{
$my_text .= '<span style="color:'.self::$storeSuccessColor. '"<strong>Project ('.$row->project_id.') Match Event: ( '.$row->id.' ) in der Tabelle angelegt!</strong>'.'</span>';
$my_text .= '<br />'; 
}

}
    
}

}
self::$_success['Match Event:'] = $my_text;

/**
 * jetzt werden die project referees importiert
 */
$my_text = '';
$my_text .= '<span style="color:'.self::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
$my_text .= '<br />';
$query = $db->getQuery(true);
$query->clear();
$query->select('me.*');
$query->from('#__joomleague_project_referee as me');
$query->join('INNER', '#__sportsmanagement_project AS m ON m.id = me.project_id');
$db->setQuery($query);
$result = $db->loadObjectList();

if ( $result )
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' __joomleague_match_event -> <br><pre>'.print_r($result,true).'</pre>'),'');

foreach( $result as $row )
{
/**
 * vorher müssen wir überprüfen ob es den eintrag schon gibt.
 * ansonsten haben wir doppelte einträge.
 */
$query = $db->getQuery(true);
$query->clear();
$query->select('me.id');
$query->from('#__sportsmanagement_project_referee as me');
$query->where('me.project_id = '.$row->project_id );
$query->where('me.person_id = '.self::$project_referee[$row->project_id][$row->person_id] );
$query->where('me.project_position_id = '.$row->project_position_id );

$db->setQuery($query);
$result_me = $db->loadResult();

if ( $result_me )
{
$my_text .= '<span style="color:'.self::$existingInDbColor. '"<strong>Project ('.$row->project_id.') Referee person_id: ( '.$row->person_id.' ) person_season_id: ( '.self::$project_referee[$row->project_id][$row->person_id].' ) in der Tabelle vorhanden!</strong>'.'</span>';
$my_text .= '<br />';     
}
else
{
$mdl = JModelLegacy::getInstance("projectreferee", "sportsmanagementModel");
$mdlTable = $mdl->getTable();   

$mdlTable->project_id = $row->project_id;
$mdlTable->person_id = self::$project_referee[$row->project_id][$row->person_id];

$mdlTable->project_position_id = $row->project_position_id;

$mdlTable->notes = $row->notes;
$mdlTable->picture = $row->picture;
$mdlTable->published = 1;
if ($mdlTable->store()===false)
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
$my_text .= '<span style="color:'.self::$existingInDbColor. '"<strong>Project ('.$row->project_id.') Referee person_id: ( '.$row->person_id.' ) person_season_id: ( '.self::$project_referee[$row->project_id][$row->person_id].' ) in der Tabelle vorhanden!</strong>'.'</span>';
$my_text .= '<br />'; 
}
else
{
$my_text .= '<span style="color:'.self::$storeSuccessColor. '"<strong>Project ('.$row->project_id.') Referee person_id: ( '.$row->person_id.' ) person_season_id: ( '.self::$project_referee[$row->project_id][$row->person_id].' ) in der Tabelle angelegt!</strong>'.'</span>';
$my_text .= '<br />'; 
}

}

}

}
self::$_success['Project Referee:'] = $my_text;
    
/**
 * jetzt werden die match player importiert
 */
$my_text = '';
$my_text .= '<span style="color:'.self::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
$my_text .= '<br />';
$query = $db->getQuery(true);
$query->clear();
$query->select('me.*,r.project_id');
$query->from('#__joomleague_match_player as me');
$query->join('INNER', '#__sportsmanagement_match AS m ON m.id = me.match_id');
$query->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id');
$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = r.project_id');
$db->setQuery($query);
$result = $db->loadObjectList();

if ( $result )
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' __joomleague_match_event -> <br><pre>'.print_r($result,true).'</pre>'),'');

foreach( $result as $row )
{
/**
 * vorher müssen wir überprüfen ob es den eintrag schon gibt.
 * ansonsten haben wir doppelte einträge.
 */
$query = $db->getQuery(true);
$query->clear();
$query->select('me.id');
$query->from('#__sportsmanagement_match_player as me');
$query->where('me.match_id = '.$row->match_id );
$query->where('me.teamplayer_id = '.self::$team_player[$row->project_id][$row->teamplayer_id] );
$query->where('me.project_position_id = '.$row->project_position_id );
$query->where('me.came_in = '.$row->came_in );
//$query->where('me.in_for = '.$row->in_for );
$query->where('me.out = '.$row->out );
$query->where('me.in_out_time LIKE '.$db->Quote(''.$row->in_out_time.'') );
$db->setQuery($query);
$result_me = $db->loadResult();

if ( $result_me )
{
$my_text .= '<span style="color:'.self::$existingInDbColor. '"<strong>Project ('.$row->project_id.') Match Player: ( '.$row->id.' ) in der Tabelle vorhanden!</strong>'.'</span>';
$my_text .= '<br />';     
}
else
{
$mdl = JModelLegacy::getInstance("matchplayer", "sportsmanagementModel");
$mdlTable = $mdl->getTable();   

$mdlTable->match_id = $row->match_id;
$mdlTable->teamplayer_id = self::$team_player[$row->project_id][$row->teamplayer_id];
$mdlTable->project_position_id = $row->project_position_id;
$mdlTable->came_in = $row->came_in;
$mdlTable->in_for = self::$team_player[$row->project_id][$row->in_for];
$mdlTable->out = $row->out;
$mdlTable->in_out_time = $row->in_out_time;
if ($mdlTable->store()===false)
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
$my_text .= '<span style="color:'.self::$existingInDbColor. '"<strong>Project ('.$row->project_id.') Match Player: ( '.$row->id.' ) in der Tabelle vorhanden!</strong>'.'</span>';
$my_text .= '<br />'; 
}
else
{
$my_text .= '<span style="color:'.self::$storeSuccessColor. '"<strong>Project ('.$row->project_id.') Match Player: ( '.$row->id.' ) in der Tabelle angelegt!</strong>'.'</span>';
$my_text .= '<br />'; 
}

}


}

}
self::$_success['Match Player:'] = $my_text;


/**
 * jetzt werden die match staff importiert
 */
$my_text = '';
$my_text .= '<span style="color:'.self::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
$my_text .= '<br />';
$query = $db->getQuery(true);
$query->clear();
$query->select('me.*,r.project_id');
$query->from('#__joomleague_match_staff as me');
$query->join('INNER', '#__sportsmanagement_match AS m ON m.id = me.match_id');
$query->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id');
$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = r.project_id');
$db->setQuery($query);
$result = $db->loadObjectList();

if ( $result )
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' __joomleague_match_event -> <br><pre>'.print_r($result,true).'</pre>'),'');

foreach( $result as $row )
{
/**
 * vorher müssen wir überprüfen ob es den eintrag schon gibt.
 * ansonsten haben wir doppelte einträge.
 */
$query = $db->getQuery(true);
$query->clear();
$query->select('me.id');
$query->from('#__sportsmanagement_match_staff as me');
$query->where('me.match_id = '.$row->match_id );
$query->where('me.team_staff_id = '.self::$team_staff[$row->project_id][$row->team_staff_id] );
$query->where('me.project_position_id = '.$row->project_position_id );

$db->setQuery($query);
$result_me = $db->loadResult();

if ( $result_me )
{
$my_text .= '<span style="color:'.self::$existingInDbColor. '"<strong>Project ('.$row->project_id.') Match Staff: ( '.$row->id.' ) in der Tabelle vorhanden!</strong>'.'</span>';
$my_text .= '<br />';     
}
else
{
$mdl = JModelLegacy::getInstance("matchstaff", "sportsmanagementModel");
$mdlTable = $mdl->getTable();   

$mdlTable->match_id = $row->match_id;
$mdlTable->team_staff_id = self::$team_staff[$row->project_id][$row->team_staff_id];
$mdlTable->project_position_id = $row->project_position_id;

if ($mdlTable->store()===false)
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
$my_text .= '<span style="color:'.self::$existingInDbColor. '"<strong>Project ('.$row->project_id.') Match Staff: ( '.$row->id.' ) in der Tabelle vorhanden!</strong>'.'</span>';
$my_text .= '<br />'; 
}
else
{
$my_text .= '<span style="color:'.self::$storeSuccessColor. '"<strong>Project ('.$row->project_id.') Match Staff: ( '.$row->id.' ) in der Tabelle angelegt!</strong>'.'</span>';
$my_text .= '<br />'; 
}

}


}

}
self::$_success['Match Staff:'] = $my_text;


/**
 * jetzt werden die match staff statistic importiert
 */
$my_text = '';
$my_text .= '<span style="color:'.self::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
$my_text .= '<br />';
$query = $db->getQuery(true);
$query->clear();
$query->select('me.*,r.project_id');
$query->from('#__joomleague_match_staff_statistic as me');
$query->join('INNER', '#__sportsmanagement_match AS m ON m.id = me.match_id');
$query->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id');
$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = r.project_id');
$db->setQuery($query);
$result = $db->loadObjectList();

if ( $result )
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' __joomleague_match_event -> <br><pre>'.print_r($result,true).'</pre>'),'');

foreach( $result as $row )
{
/**
 * vorher müssen wir überprüfen ob es den eintrag schon gibt.
 * ansonsten haben wir doppelte einträge.
 */
$query = $db->getQuery(true);
$query->clear();
$query->select('me.id');
$query->from('#__sportsmanagement_match_staff_statistic as me');
$query->where('me.match_id = '.$row->match_id );
$query->where('me.projectteam_id = '.$row->projectteam_id );
$query->where('me.team_staff_id = '.self::$team_staff[$row->project_id][$row->team_staff_id] );
$query->where('me.statistic_id = '.$row->statistic_id );


$db->setQuery($query);
$result_me = $db->loadResult();

if ( $result_me )
{
$my_text .= '<span style="color:'.self::$existingInDbColor. '"<strong>Project ('.$row->project_id.') Match Staff Statistic: ( '.$row->id.' ) in der Tabelle vorhanden!</strong>'.'</span>';
$my_text .= '<br />';     
}
else
{
$mdl = JModelLegacy::getInstance("matchstaffstatistic", "sportsmanagementModel");
$mdlTable = $mdl->getTable();   

$mdlTable->match_id = $row->match_id;
$mdlTable->projectteam_id = $row->projectteam_id;
$mdlTable->team_staff_id = self::$team_staff[$row->project_id][$row->team_staff_id];
$mdlTable->statistic_id = $row->statistic_id;

if ($mdlTable->store()===false)
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
$my_text .= '<span style="color:'.self::$existingInDbColor. '"<strong>Project ('.$row->project_id.') Match Staff Statistic: ( '.$row->id.' ) in der Tabelle vorhanden!</strong>'.'</span>';
$my_text .= '<br />'; 
}
else
{
$my_text .= '<span style="color:'.self::$storeSuccessColor. '"<strong>Project ('.$row->project_id.') Match Staff Statistic: ( '.$row->id.' ) in der Tabelle angelegt!</strong>'.'</span>';
$my_text .= '<br />'; 
}

}


}

}
else
{
$my_text .= '<span style="color:'.self::$storeFailedColor. '"<strong>Match Staff Statistic: in der Tabelle nicht vorhanden!</strong>'.'</span>';
$my_text .= '<br />';     
}
self::$_success['Match Staff Statistic:'] = $my_text;

/**
 * jetzt werden die templates umgesetzt
 */
$my_text = '';
$my_text .= '<span style="color:'.self::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
$my_text .= '<br />';
$defaultpath = JPATH_COMPONENT_SITE.DS.'settings'.DS.'default';
$query = $db->getQuery(true); 
$query->clear();
// Select some fields
$query->select('*');
// From the table
$query->from('#__sportsmanagement_template_config');
$db->setQuery($query);
$result = $db->loadObjectList();

if ( $result )
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' __sportsmanagement_template_config -> <br><pre>'.print_r($result,true).'</pre>'),'');

foreach( $result as $row )
{
$defaultvalues = array();
$defaultvalues = explode('\n', $row->params);
$parameter = new JRegistry;
                    
if(version_compare(JVERSION,'3.0.0','ge')) 
{
$ini = $parameter->loadString($defaultvalues[0]);
}
else
{
$ini = $parameter->loadINI($defaultvalues[0]);
}

$xmlfile = $defaultpath.DS.$row->template.'.xml';

/**
 * ist die datei vorhanden ?
 */
if( file_exists($xmlfile) )
{

$newparams = array();
$xml = JFactory::getXML($xmlfile,true);
foreach ($xml->fieldset as $paramGroup)
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' paramGroup -> '.$template.' <pre>'.print_r($paramGroup,true).'</pre>'),'');
foreach ($paramGroup->field as $param)
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' param -> '.$template.' <pre>'.print_r($param,true).'</pre>'),'');
$newparams[(string)$param->attributes()->name] = (string)$param->attributes()->default;
}
} 

foreach ( $newparams as $key => $value )
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' key -> '.$template.' <pre>'.print_r($key,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' key ini -> '.$template.' <pre>'.print_r($ini->get($key),true).'</pre>'),'');
    
if(version_compare(JVERSION,'3.0.0','ge')) 
{
$value = $ini->get($key);
}
else
{
//$value = $ini->getValue($key);
}
if ( isset($value) )
{
$newparams[$key] = $value;
}
} 

$t_params = json_encode( $newparams ); 
                            
}
else
{
$ini = $parameter->toArray($ini);
$t_params = json_encode( $ini );     
}   

// Create an object for the record we are going to update.
$object = new stdClass();
// Must be a valid primary key value.
$object->id = $row->id;
$object->params = $t_params;
// Update their details in the table using id as the primary key.
$result_update = JFactory::getDbo()->updateObject('#__sportsmanagement_template_config', $object, 'id');
if ($result_update)
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
$my_text .= '<span style="color:'.self::$storeSuccessColor. '"<strong>Project ('.$row->project_id.') Template: ( '.$row->id.' '.$row->template.' ) umgesetzt!</strong>'.'</span>';
$my_text .= '<br />'; 
}
else
{
$my_text .= '<span style="color:'.self::$storeFailedColor. '"<strong>Project ('.$row->project_id.') Template: ( '.$row->id.' '.$row->template.' ) nicht umgesetzt!</strong>'.'</span>';
$my_text .= '<br />'; 
}
             
}

}        
self::$_success['Template:'] = $my_text;        

/**
 * und zum schluss müssen wir noch die  konfiguration der komponente änder
 * damit der import nicht 2 mal durchgeführt wird
 */
$params = JComponentHelper::getParams($option);
$xmlfile = JPATH_ADMINISTRATOR.DS.'components'.DS.$option.DS.'config.xml';

$jRegistry = new JRegistry;
//$jRegistry->loadString($params->toString('ini'), 'ini');
$ini = $jRegistry->loadString($params);

$newparams = array();
$xml = JFactory::getXML($xmlfile,true);
foreach ($xml->fieldset as $paramGroup)
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' paramGroup -> '.$template.' <pre>'.print_r($paramGroup,true).'</pre>'),'');
foreach ($paramGroup->field as $param)
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' param -> '.$template.' <pre>'.print_r($param,true).'</pre>'),'');
$newparams[(string)$param->attributes()->name] = (string)$param->attributes()->default;
}
} 

foreach ( $newparams as $key => $value )
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' key -> '.$template.' <pre>'.print_r($key,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' key ini -> '.$template.' <pre>'.print_r($ini->get($key),true).'</pre>'),'');
    
if(version_compare(JVERSION,'3.0.0','ge')) 
{
$value = $ini->get($key);
}
else
{
//$value = $ini->getValue($key);
}
if ( isset($value) )
{
$newparams[$key] = $value;
}
} 

$newparams['cfg_jl_import'] = 0;

$t_params = json_encode( $newparams ); 

//$db->setQuery('UPDATE #__extensions SET params = ' .
//                                $db->quote( $t_params ) .
//                                ' WHERE name = "com_sportsmanagement"' );
//                                $db->query();
                                
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' params ->  <pre>'.print_r($t_params,true).'</pre>'),'');

$app->setUserState( "$option.success", self::$_success );
                    
}
        
/**
 * sportsmanagementModeljoomleagueimports::import()
 * 
 * @return void
 */
function import()
    {
        $app = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        $option = JRequest::getCmd('option');
        $post = JRequest::get('post');
        $exportfields = array();
        $cid = $post['cid'];
        $jl = $post['jl'];
        $jlid = $post['jlid'];
        $jsm = $post['jsm'];
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($post,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($cid,true).'</pre>'),'');
        
        foreach ( $cid as $key => $value )
        {
            $jl_fields = $db->getTableFields($jl[$value]);
            $jsm_fields = $db->getTableFields($jsm[$value]);
            
            $jsm_table = $jsm[$value];
            $jl_table = $jl[$value];
            // wenn in der jsm tabelle einträge vorhanden sind
            // dann wird nicht kopiert. es soll kein schaden entstehen
            $query = $db->getQuery(true);
            $query->clear();
            $query->select('COUNT(id) AS total');
            $query->from($jsm_table);
            $db->setQuery($query);
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            
            $totals = $db->loadResult();
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'totals<br><pre>'.print_r($totals,true).'</pre>'),'');
            
            // noch die zu importierenden tabellen prüfen
            // Das "i" nach der Suchmuster-Begrenzung kennzeichnet eine Suche ohne
            // Berücksichtigung von Groß- und Kleinschreibung
            if ( preg_match("/project_team/i", $jsm_table) || preg_match("/team_player/i", $jsm_table) || preg_match("/team_staff/i", $jsm_table) ) 
            {
            $app->enqueueMessage(JText::_('Sie muessen die Daten aus der Tabelle: ( '.$jl_table.' ) in die neue Struktur umsetzen!'),'');
            // wir müssen ein neues feld an die tabelle zum import einfügen
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jl_fields<br><pre>'.print_r($jl_fields,true).'</pre>'),'');
            if (array_key_exists('import', $jl_fields[$jl_table]  ) )
            {
                $app->enqueueMessage(JText::_('Importfeld ist vorhanden'),'');
            }
            else
            {
                $app->enqueueMessage(JText::_('importfeld ist nicht vorhanden'),'');
                $query = $db->getQuery(true);
                $query = 'ALTER TABLE '.$jl_table.' ADD import INT(11) NOT NULL DEFAULT 0 ';
                $db->setQuery($query);
                $db->query();
                if ( preg_match("/team_player/i", $jsm_table) ) 
                {
                    $query = $db->getQuery(true);
                    $query = 'ALTER TABLE #__joomleague_match_event ADD import TINYINT(1) NOT NULL DEFAULT 0 ';
                    $db->setQuery($query);
                    $db->query();
                    $query = $db->getQuery(true);
                    $query = 'ALTER TABLE #__joomleague_match_player ADD import TINYINT(1) NOT NULL DEFAULT 0 ';
                    $db->setQuery($query);
                    $db->query();
                    $query = $db->getQuery(true);
                    $query = 'ALTER TABLE #__joomleague_match_referee ADD import TINYINT(1) NOT NULL DEFAULT 0 ';
                    $db->setQuery($query);
                    $db->query();
                    $query = $db->getQuery(true);
                    $query = 'ALTER TABLE #__joomleague_match_staff ADD import TINYINT(1) NOT NULL DEFAULT 0 ';
                    $db->setQuery($query);
                    $db->query();
                
                }
            }
            
            } 
            else 
            {
            

            if ( $totals )
            {
            $app->enqueueMessage(JText::_('Daten aus der Tabelle: ( '.$jl[$value].' ) koennen nicht kopiert werden. Tabelle: ( '.$jsm[$value].' ) nicht leer!'),'Error');     
            }
            else
            {
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($jl_fields,true).'</pre>'),'');
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'jsm_fields<br><pre>'.print_r($jsm_fields,true).'</pre>'),'');
            
            $jsm_field_array = $jsm_fields[$jsm[$value]];
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'jsm_field_array<br><pre>'.print_r($jsm_field_array,true).'</pre>'),'');
            
            foreach ( $jl_fields[$jl[$value]] as $key2 => $value2 )
            {
                //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'key2<br><pre>'.print_r($key2,true).'</pre>'),'');
                //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'value2<br><pre>'.print_r($value2,true).'</pre>'),'');
                
                switch ($key2)
                {
                    case 'import':
                    case 'ordering':
                    case 'checked_out':
                    case 'checked_out_time':
                    case 'modified':
                    case 'modified_by':
                    case 'out':
                    break;
                    default:
                    if (array_key_exists($key2, $jsm_field_array)) 
                    {
                    //$exportfields[] = $db->Quote($key2);
                    $exportfields[] = $key2;
                    }
                    break;
                }
                
            }
            
            $select_fields = implode(',', $exportfields);
            
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'exportfields<br><pre>'.print_r($exportfields,true).'</pre>'),'');
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'select_fields<br><pre>'.print_r($select_fields,true).'</pre>'),'');
            
            $query->clear();
            $query = 'INSERT INTO '.$jsm[$value].' ('.$select_fields.') SELECT '.$select_fields.' FROM '.$jl[$value];
            $db->setQuery($query);
            if (!$db->query())
		    {
			$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
		    }
            else
            {
            $app->enqueueMessage(JText::_('Daten aus der Tabelle: ( '.$jl[$value].' ) in die Tabelle: ( '.$jsm[$value].' ) importiert!'),'Notice');    
            }
            
            // Create an object for the record we are going to update.
            $object = new stdClass();
            // Must be a valid primary key value.
            $object->id = $jlid[$value];
            $object->import = 1;
            // Update their details in the users table using id as the primary key.
            $result = JFactory::getDbo()->updateObject('#__'.COM_SPORTSMANAGEMENT_TABLE.'_jl_tables', $object, 'id');   
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'query<br><pre>'.print_r($query,true).'</pre>'),'');
            
            unset($exportfields);
            
            // in der template tabelle stehen die parameter nicht im json format
            if (preg_match("/template_config/i", $jsm_table)) 
            {
                $app->enqueueMessage(JText::_('Die Parameter aus der Tabelle: ( '.$jsm_table.' ) werden in das JSON-Format umgesetzt!'),'');
                $query = $db->getQuery(true);
                $query->clear();
                $query->select('id,params,template');
                $query->from($jsm_table);
                $db->setQuery($query);
                $results = $db->loadObjectList();
                
                foreach($results as $param )
                {
                    $xmlfile = JPATH_COMPONENT_SITE.DS.'settings'.DS.'default'.DS.$param->template.'.xml';
                    
                    if ( JFile::exists($xmlfile) )
			        {
                    $form = JForm::getInstance($param->template, $xmlfile,array('control'=> ''));
		            $form->bind($param->params);
                    $newparams = array();
                    foreach($form->getFieldset($fieldset->name) as $field)
                    {
                    $newparams[$field->name] = $field->value;
                    }
                    $t_params = json_encode( $newparams );

                    // Create an object for the record we are going to update.
                    $object = new stdClass();
                    // Must be a valid primary key value.
                    $object->id = $param->id;
                    $object->params = $t_params;
                    // Update their details in the users table using id as the primary key.
                    $result = JFactory::getDbo()->updateObject($jsm_table, $object, 'id');
                    }   	
                }
            
            }
            
            } 
            
            }   
        
        
        }
     
        }
            
}    
?>
