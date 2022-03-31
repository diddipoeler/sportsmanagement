<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      match.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage match
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\CMS\Input\Input;

// import Joomla modelform library
//jimport('joomla.application.component.modeladmin');
JLoader::import('components.com_sportsmanagement.libraries.dbutil', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.libraries.util', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.libraries.GCalendar.GCalendarZendHelper', JPATH_ADMINISTRATOR);
JLoader::import('joomla.utilities.simplecrypt');

//use Joomla google;
JLoader::import('libraries.joomla.google.google', JPATH_ADMINISTRATOR);
JLoader::import('libraries.joomla.google.data.calendar', JPATH_ADMINISTRATOR);



/**
 * sportsmanagementModelMatch
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 * 
 * https://docs.joomla.org/Sending_email_from_extensions
 * 
 */
class sportsmanagementModelMatch extends JSMModelAdmin
{

const MATCH_ROSTER_STARTER = 0;
const MATCH_ROSTER_SUBSTITUTE_IN = 1;
const MATCH_ROSTER_SUBSTITUTE_OUT = 2;
const MATCH_ROSTER_RESERVE = 3;
    
var $teams = NULL;
static $_season_id = 0;
static $_project_id = 0;

var $storeFailedColor = 'red';
var $storeSuccessColor = 'green';
var $existingInDbColor = 'orange';

var $csv_player = array();
var $csv_in_out = array();
var $csv_cards = array();
var $csv_staff = array();
var $projectteamid = 0;
	
	/**
	 * Override parent constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     BaseDatabaseModel
	 * @since   3.2
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	
//    $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' config<br><pre>'.print_r($config,true).'</pre>'),'');
//    $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' getName<br><pre>'.print_r($this->getName(),true).'</pre>'),'');
    
	}	   
    
    
/**
 * sportsmanagementModelMatch::getRoundMatches()
 * 
 * @param mixed $round_id
 * @return
 */
function getRoundMatches($round_id)
	{
	// Reference global application object
        $app = Factory::getApplication();
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection();
        // Create a new query object.
        $query = $db->getQuery(true);
$query->select('m.*');
$query->from('#__sportsmanagement_match AS m');
$query->where('m.round_id = '.$round_id);
$query->order('m.match_number');


		$db->setQuery($query);
		return ($db->loadObjectList());
	}

	
    /**
     * sportsmanagementModelMatch::getMatchEvents()
     * 
     * @param integer $match_id
     * @return
     */
    public static function getMatchEvents($match_id=0)
	{
	   // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $project_id	= $app->getUserState( "$option.pid", '0' );
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection();
        // Create a new query object.
        $query = $db->getQuery(true);
        $query->select('me.*,t.name AS team,et.name AS event,CONCAT(t1.firstname," \'",t1.nickname,"\' ",t1.lastname) AS player1');
        //$query->select('CONCAT(t2.firstname," \'",t2.nickname,"\' ",t2.lastname) AS player2');
        $query->from('#__sportsmanagement_match_event AS me');
        $query->join('LEFT','#__sportsmanagement_season_team_person_id AS tp1 ON tp1.id = me.teamplayer_id');
        $query->join('LEFT','#__sportsmanagement_season_team_id AS st1 ON st1.team_id = tp1.team_id and st1.season_id = tp1.season_id');

        $query->join('LEFT','#__sportsmanagement_project_team AS pt1 ON st1.id = pt1.team_id');
        $query->join('LEFT','#__sportsmanagement_person AS t1 ON t1.id = tp1.person_id AND t1.published = 1');
        $query->join('LEFT','#__sportsmanagement_team AS t ON t.id = st1.team_id');
        $query->join('LEFT','#__sportsmanagement_eventtype AS et ON et.id = me.event_type_id ');
        
        $query->join('LEFT', '#__sportsmanagement_person_project_position AS ppp on ppp.person_id = tp1.id and ppp.project_id = pt1.project_id');
       
        $query->where('pt1.project_id = '.$project_id);
        
        $query->where('me.match_id = '.$match_id);
        $query->order('me.event_time ASC');
        
        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
        $db->setQuery($query);
		return $db->loadObjectList();
	}
    
    
    /**
     * sportsmanagementModelMatch::count_result()
     * 
     * @param integer $count_result
     * @return void
     */
    function count_result($count_result=0)
    {
        // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
	   // Get a db connection.
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $pks = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');
        $post = Factory::getApplication()->input->post->getArray(array());
        $result = true;
		for ($x = 0; $x < count($pks); $x++)
		{
		// Create an object for the record we are going to update.
            $object = new stdClass();
            // Must be a valid primary key value.
            $object->id = $pks[$x];
            $object->count_result = $count_result;
        try {
          // Update their details in the table using id as the primary key.
                $result_update = Factory::getDbo()->updateObject('#__sportsmanagement_match', $object, 'id', true);
                sprintf(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_SAVED'),$pks[$x]);
                $app->enqueueMessage(sprintf(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_SAVED'),$pks[$x]),'Notice');
            }
catch (Exception $e){
$app->enqueueMessage(__METHOD__.' '.__LINE__.Text::_($e->getMessage()),'Error');
$result = false;
}
            
            
            
        }  
    }
    
    /**
     * sportsmanagementModelMatch::getProjectRoundCodes()
     * 
     * @param mixed $project_id
     * @return
     */
    function getProjectRoundCodes($project_id)
	{
	   // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
	   // Get a db connection.
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id,roundcode,round_date_first');
        $query->from('#__sportsmanagement_round');
        $query->where('project_id = '.$project_id);
        $query->order('roundcode,round_date_first ASC');
        
		$db->setQuery($query);
		return $db->loadObjectList();
	}
    
    
    /**
     * sportsmanagementModelMatch::insertgooglecalendar()
     * 
     * @return void
     * https://packagist.org/packages/joomla/google
     */
    function insertgooglecalendar()
    {
    // Reference global application object
    $app = Factory::getApplication();
    // JInput object
    $jinput = $app->input;
    $option = $jinput->getCmd('option');
    //$params = \ComponentHelper::getParams($option);
    
    $google_client_id = ComponentHelper::getParams($option)->get('google_api_clientid','');
    $google_client_secret = ComponentHelper::getParams($option)->get('google_api_clientsecret','');
        
    $options = new Registry();  
    $input = new Input;  
    
//$options->set('clientid', $google_client_id.'.apps.googleusercontent.com');
//$options->set('clientsecret', $google_client_secret);
$google = new JGoogle($options);
$app->enqueueMessage(__METHOD__.' '.__LINE__.' google<br><pre>'.print_r($google, true).'</pre><br>','Notice');    


$oauth = new JOAuth2Client($options,null,$input);
$auth = new JGoogleAuthOauth2($options, $oauth);

$options->set('clientid', $google_client_id.'.apps.googleusercontent.com');
$options->set('clientsecret', $google_client_secret);

$result = $auth->authenticate();
$app->enqueueMessage(__METHOD__.' '.__LINE__.' result<br><pre>'.print_r($result, true).'</pre><br>','Notice');
    
    //$oauth = new JOAuth2Client($options, $http, $input);
    //$auth = new JGoogleAuthOauth2($options, $oauth);
    //$object = new JGoogleDataCalendar($options, $auth);
    $object = new JGoogleDataCalendar($options);

/*    
$token['access_token'] = 'accessvalue';
$token['refresh_token'] = 'refreshvalue';
$token['created'] = time() - 1800;
$token['expires_in'] = 3600;
$this->oauth->setToken($token);
*/
            
// Client ID and Client Secret can be obtained  through the Google API Console (https://code.google.com/apis/console/).
//$options->set('clientid', 'google_client_id.apps.googleusercontent.com');
//$options->set('clientsecret', 'google_client_secret');
$object->setOption('clientid', $google_client_id.'.apps.googleusercontent.com' );
//$object->setOption('clientid', '329080032937.apps.googleusercontent.com');
$object->setOption('clientsecret', $google_client_secret );
$object->setOption('redirecturi', Uri::root() );

// 329080032937-f4b8095v2jb8ecbmpe33tvej2koh3m4b
// wzbJSgn4-w-6pg_qNLhcw4jT

//$google = new JGoogle($options);

// Get a calendar API object
//$calendar = $google->data('calendar');

//$app->enqueueMessage(__METHOD__.' '.__LINE__.' isAuth<br><pre>'.print_r($calendar->isAuth(), true).'</pre><br>','Notice');

/*
// If the client hasn't been authenticated via OAuth yet, redirect to the appropriate URL and terminate the program
if (!$calendar->isAuth())
{
	JResponse::sendHeaders();
	die();
}
*/

//$ini_google_calendar = $calendar->listCalendars($options);    

//$url = 'https://www.googleapis.com/calendar/v3/users/me/calendarList?' . http_build_query($options);

$result = $object->listCalendars($options);

$app->enqueueMessage(__METHOD__.' '.__LINE__.' object<br><pre>'.print_r($object, true).'</pre><br>','Notice');
$app->enqueueMessage(__METHOD__.' '.__LINE__.' options<br><pre>'.print_r($options, true).'</pre><br>','Notice');

//$app->enqueueMessage(__METHOD__.' '.__LINE__.' url<br><pre>'.print_r($url, true).'</pre><br>','Notice');
$app->enqueueMessage(__METHOD__.' '.__LINE__.' result<br><pre>'.print_r($result, true).'</pre><br>','Notice');

    
    /*
    $init_jgoogle = new JGoogle($gh_options,$auth);
    $app->enqueueMessage(__METHOD__.' '.__LINE__.' $init_jgoogle<br><pre>'.print_r($init_jgoogle, true).'</pre><br>','Notice');
    
    $ini_google = new JGoogleDataCalendar($gh_options,$auth);    
    $ini_google_calendar = $ini_google->listCalendars($gh_options);
    
    $app->enqueueMessage(__METHOD__.' '.__LINE__.' ini_google<br><pre>'.print_r($ini_google, true).'</pre><br>','Notice');
    $app->enqueueMessage(__METHOD__.' '.__LINE__.' ini_google_calendar<br><pre>'.print_r($ini_google_calendar, true).'</pre><br>','Notice');
    */    
    }  
    
    
      
    /**
     * sportsmanagementModelMatch::insertgooglecalendar()
     * http://framework.zend.com/manual/1.12/de/zend.gdata.calendar.html
     * @return
     */
    function insertgooglecalendarold()
    {
        // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $timezone = ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('timezone','');
        
        $app->enqueueMessage(__METHOD__.' '.__LINE__.' timezone<br><pre>'.print_r($timezone, true).'</pre><br>','Notice');
        
        // Get a db connection.
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        
        $post = Factory::getApplication()->input->post->getArray(array());
        $pks = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');
        $project_id	= $app->getUserState( "$option.pid", '0' );
        
        //$app->enqueueMessage(__METHOD__.' '.__FUNCTION__.' project_id<br><pre>'.print_r($project_id, true).'</pre><br>','Notice');
        //$app->enqueueMessage(__METHOD__.' '.__FUNCTION__.' pks<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
        
        $match_ids = implode(",", $pks);
        //$app->enqueueMessage(__METHOD__.' '.__FUNCTION__.' match_ids<br><pre>'.print_r($match_ids, true).'</pre><br>','Notice');
        
        // Select some fields
		$query->select('p.name,p.gcalendar_id,p.game_regular_time,p.halftime,p.gcalendar_use_fav_teams,p.fav_team,gc.username,gc.password,gc.calendar_id');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project as p');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_gcalendar AS gc ON gc.id = p.gcalendar_id');
        $query->where('p.id = ' . $project_id);
        $db->setQuery($query);
        
        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
		$gcalendar_id = $db->loadObject();
        
        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($gcalendar_id,true).'</pre>'),'');
        
        // jetzt die spiele
        $query->clear();
        // select some fields
        $query->select('m.id,m.match_date,m.team1_result,m.team2_result,m.gcal_event_id,DATE_FORMAT(m.time_present,"%H:%i") time_present');
        $query->select('playground.name AS playground_name,playground.zipcode AS playground_zipcode,playground.city AS playground_city,playground.address AS playground_address');
        $query->select('pt1.project_id');
        $query->select('t1.name as hometeam,t2.name as awayteam');
        $query->select('r.name as roundname');
        $query->select('d1.name as divhome');
        $query->select('d2.name as divaway');
        $query->select('CASE WHEN CHAR_LENGTH(t1.alias) AND CHAR_LENGTH(t2.alias) THEN CONCAT_WS(\':\',m.id,CONCAT_WS("_",t1.alias,t2.alias)) ELSE m.id END AS slug ');
        // from 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m');
        // join
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ON m.round_id = r.id ');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON m.projectteam1_id = pt1.id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON m.projectteam2_id = pt2.id');
        
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st1 ON st1.id = pt1.team_id ');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st2 ON st2.id = pt2.team_id ');
        
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON t1.id = st1.team_id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON t2.id = st2.team_id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS d1 ON pt1.division_id = d1.id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS d2 ON pt2.division_id = d2.id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_playground AS playground ON playground.id = m.playground_id');
		       
        
        // where
        $query->where('m.published = 1');
        $query->where('m.id IN ('.$match_ids.' )');
        $query->where('r.project_id = '.(int)$project_id);
        
        if ($gcalendar_id->gcalendar_use_fav_teams )
        {
        $query->where('( t1.id IN ('.$gcalendar_id->fav_team.' ) OR t2.id IN ('.$gcalendar_id->fav_team.' )  )');    
        }
        
        // group
        $query->group('m.id ');
        // order
        $query->order('m.match_date ASC,m.match_number'); 
        
        $db->setQuery($query);
        $result = $db->loadObjectList(); 
        
        if ( !$result )
	    {
		$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' getErrorMsg<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
        $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' dump<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
	    }
        
//        $app->enqueueMessage(__METHOD__.' '.__FUNCTION__.' result<br><pre>'.print_r($result, true).'</pre><br>','');


        
        $calendar = jsmGCalendarDBUtil::getCalendar($gcalendar_id->gcalendar_id);
        
        if ( $gcalendar_id )
        {
            
            if ( $result )
            {
            $cryptor = new JSimpleCrypt();
            $gcalendar_id->password = $cryptor->decrypt($gcalendar_id->password);
            
//            $app->enqueueMessage(__METHOD__.' '.__FUNCTION__.' gcalendar_id<br><pre>'.print_r($gcalendar_id->gcalendar_id, true).'</pre><br>','');
//            $app->enqueueMessage(__METHOD__.' '.__FUNCTION__.' calendar_id<br><pre>'.print_r($gcalendar_id->calendar_id, true).'</pre><br>','');
//            $app->enqueueMessage(__METHOD__.' '.__FUNCTION__.' username<br><pre>'.print_r($gcalendar_id->username, true).'</pre><br>','');
//            $app->enqueueMessage(__METHOD__.' '.__FUNCTION__.' password<br><pre>'.print_r($gcalendar_id->password, true).'</pre><br>','');
            
            //$client = new Zend_Http_Client();
            //$client = Zend_Gdata_ClientLogin::getHttpClient($gcalendar_id->username, $gcalendar_id->password, Zend_Gdata_Calendar::AUTH_SERVICE_NAME);
            $client = Zend_Gdata_ClientLogin::getHttpClient($gcalendar_id->username, $gcalendar_id->password, Zend_Gdata_Calendar::AUTH_SERVICE_NAME);
            $service = new Zend_Gdata_Calendar($client);
            $service->setMajorProtocolVersion(2);
            
            foreach ( $result as $row )
            {
            // Erstellt einen neuen Eintrag und verwendet die magische Factory
            // Methode vom Kalender Service
            $event = $service->newEventEntry();
            
            if ($row->gcal_event_id) 
            {
				$query = $service->newEventQuery();
                $query->setUser($gcalendar_id->calendar_id);
                $query->setVisibility('private');
                $query->setProjection('full');
                $query->setEvent($row->gcal_event_id);
                
                //$event = jsmGCalendarZendHelper::getEvent($calendar, $row->gcal_event_id);
                $event = $service->getCalendarEventEntry($query);
                //$app->enqueueMessage(__METHOD__.' '.__FUNCTION__.' alter event<br><pre>'.print_r($event, true).'</pre><br>','');
			}
            
            // Gibt das Event bekannt mit den gewünschten Informationen
            // Beachte das jedes Attribu als Instanz der zugehörenden Klasse erstellt wird
            $event->title = $service->newTitle($gcalendar_id->name.', '.$row->roundname);
            $event->where = array($service->newWhere($row->playground_name.','.$row->playground_city .','.$row->playground_address     ));
            $event->content = $service->newContent($row->hometeam.' - '.$row->awayteam.' ('.$row->team1_result.':'.$row->team2_result.')');
            
            // Setze das Datum und verwende das RFC 3339 Format.
            list($date,$time) = explode(" ",$row->match_date);
            $time = strftime("%H:%M",strtotime($time));
            $endtime = date('H:i', strtotime('+'.($gcalendar_id->game_regular_time + $gcalendar_id->halftime ).' minutes', strtotime($time))); 
            
//            $allDay = '0';
//            $startDate = jsmGCalendarUtil::getDateFromString($date, $time, $allDay, $timezone);
//			$endDate = jsmGCalendarUtil::getDateFromString($date, $endtime, $allDay, $timezone);
//            
//            $app->enqueueMessage(__METHOD__.' '.__FUNCTION__.' startDate<br><pre>'.print_r($startDate, true).'</pre><br>','');
//            $app->enqueueMessage(__METHOD__.' '.__FUNCTION__.' endDate<br><pre>'.print_r($endDate, true).'</pre><br>','');
            
            $startDate = $date;
            $startTime = $time;
            $endDate = $date;
            $endTime = $endtime;
            $tzOffset = "-00";
 
            $when = $service->newWhen();
            $when->startTime = "{$startDate}T{$startTime}:00.000{$tzOffset}:00";
            $when->endTime = "{$endDate}T{$endTime}:00.000{$tzOffset}:00";
            $event->when = array($when);
        
            if ( $row->gcal_event_id )
            {
            
            $event = $service->updateEntry($event,'https://www.google.com/calendar/feeds/'.$gcalendar_id->calendar_id.'/private/full/'.$row->gcal_event_id);
               
            //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' response<br><pre>'.print_r($response,true).'</pre>'),'Notice');
             
            //$event = $service->insertEntry($event, 'https://www.google.com/calendar/feeds/'.$gcalendar_id->calendar_id.'/private/full/');    
            }
            else
            {
            $event = $service->insertEntry($event, 'https://www.google.com/calendar/feeds/'.$gcalendar_id->calendar_id.'/private/full');
            
            
            //$app->enqueueMessage(__METHOD__.' '.__FUNCTION__.' event_insert<br><pre>'.print_r($event->id->text, true).'</pre><br>','');
            
            $event_id = substr($event->id, strrpos($event->id, '/')+1);
            $row->gcal_event_id = $event_id;
            
            //$app->enqueueMessage(__METHOD__.' '.__FUNCTION__.' event_id<br><pre>'.print_r($event_id, true).'</pre><br>','');
            
            // die event id updaten
            // Create an object for the record we are going to update.
            $object = new stdClass();
            // Must be a valid primary key value.
            $object->id = $row->id;
            $object->gcal_event_id = $row->gcal_event_id;
            // Update their details in the users table using id as the primary key.
            $result = Factory::getDbo()->updateObject('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match', $object, 'id');
            
            
            }

            }
            
            }
            
            return true;
        }
        else
        {
            return false;
        }
        
    }
    
    
    
    /**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param	array	$data	An array of input data.
	 * @param	string	$key	The name of the key for the primary key.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Check specific edit permission then general edit permission.
		return Factory::getUser()->authorise('core.edit', 'com_sportsmanagement.message.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}
    
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'match', $prefix = 'sportsmanagementTable', $config = array()) 
	{
	$config['dbo'] = sportsmanagementHelper::getDBConnection(); 
		return Table::getInstance($type, $prefix, $config);
	}
    
	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_sportsmanagement.match', 'match', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
		return $form;
	}
    
	/**
	 * Method to get the script that have to be included on the form
	 *
	 * @return string	Script files
	 */
	public function getScript() 
	{
		return 'administrator/components/com_sportsmanagement/models/forms/sportsmanagement.js';
	}
    
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_sportsmanagement.edit.match.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
		}
		return $data;
	}
	
	/**
	 * Method to save item order
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function saveorder($pks = NULL, $order = NULL)
	{
		$row =& $this->getTable();
		
		// update ordering values
		for ($i=0; $i < count($pks); $i++)
		{
			$row->load((int) $pks[$i]);
			if ($row->ordering != $order[$i])
			{
				$row->ordering=$order[$i];
				if (!$row->store())
				{
					sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, Factory::getDbo()->getErrorMsg(), __LINE__);
					return false;
				}
			}
		}
		return true;
	}
    
    
    /**
     * sportsmanagementModelMatch::savestats()
     * 
     * @param mixed $data
     * @return
     */
    function savestats($data)
	{
	   $app = Factory::getApplication();
       //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'');
		$match_id = $data['match_id'];
		if (isset($data['cid']))
		{
			// save all checked rows
			foreach ($data['teamplayer_id'] as $idx => $tpid)
			{
				$teamplayer_id  = $data['teamplayer_id'][$idx];
				$projectteam_id = $data['projectteam_id'][$idx];
				//clear previous data
				$query=' DELETE FROM #__sportsmanagement_match_statistic '
				.' WHERE match_id='. $this->_db->Quote($match_id)
				.'   AND teamplayer_id='. $this->_db->Quote($teamplayer_id);
				$this->_db->setQuery($query);
				$res = $this->_db->execute();
				foreach ($data as $key => $value)
				{
					if (preg_match('/^stat'.$teamplayer_id.'_([0-9]+)/',$key,$reg) && $value!="")
					{
						$statistic_id=$reg[1];
						$stat = Table::getInstance('Matchstatistic','sportsmanagementTable');
						$stat->match_id = $match_id;
						$stat->projectteam_id = $projectteam_id;
						$stat->teamplayer_id = $teamplayer_id;
						$stat->statistic_id = $statistic_id;
						$stat->value = ($value=="") ? null : $value;
						if (!$stat->check())
						{
							echo "stat check failed!"; die();
						}
						if (!$stat->store())
						{
							echo "stat store failed!"; die();
						}
					}
				}
			}
		}
		//staff stats
		if (isset($data['staffcid']))
		{
			// save all checked rows
			foreach ($data['team_staff_id'] as $idx => $stid)
			{
				$team_staff_id = $data['team_staff_id'][$idx];
				$projectteam_id = $data['sprojectteam_id'][$idx];
				//clear previous data
				$query=' DELETE FROM #__sportsmanagement_match_staff_statistic '
				.' WHERE match_id='. $this->_db->Quote($match_id)
				.'   AND team_staff_id='. $this->_db->Quote($team_staff_id);
				$this->_db->setQuery($query);
				$res=$this->_db->execute();
				foreach ($data as $key => $value)
				{
					if (ereg('^staffstat'.$team_staff_id.'_([0-9]+)',$key,$reg) && $value!="")
					{
						$statistic_id=$reg[1];
						$stat = Table::getInstance('Matchstaffstatistic','sportsmanagementTable');
						$stat->match_id = $match_id;
						$stat->projectteam_id = $projectteam_id;
						$stat->team_staff_id = $team_staff_id;
						$stat->statistic_id = $statistic_id;
						$stat->value = ($value == "") ? null : $value;
						if (!$stat->check())
						{
							echo "stat check failed!"; die();
						}
						if (!$stat->store())
						{
							echo "stat store failed!"; die();
						}
					}
				}
			}
		}
		return true;
	}
    
    
    /**
	 * Method to update checked project match
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function saveshort()
	{
		$app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        //$show_debug_info = ComponentHelper::getParams($option)->get('show_debug_info',0) ;
        // Get the input
        $pks = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');
        $post = Factory::getApplication()->input->post->getArray(array());
        
        
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' pks<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post, true).'</pre><br>','Notice');
        
        
        $result = true;
		for ($x = 0; $x < count($pks); $x++)
		{
			// änderungen im datum oder der uhrzeit
            $tblMatch = $this->getTable();;
            $tblMatch->load((int) $pks[$x]);
            
            // Create an object for the record we are going to update.
            $object = new stdClass();
            // Must be a valid primary key value.
            $object->id = $pks[$x];
            $object->team1_result = NULL;
            $object->team2_result = NULL;
        
            list($date,$time) = explode(" ",$tblMatch->match_date);
            $this->_match_time_new = $post['match_time'.$pks[$x]].':00';
            $this->_match_date_new = $post['match_date'.$pks[$x]];
            $this->_match_time_old = $time;
            $this->_match_date_old = sportsmanagementHelper::convertDate($date);
            
            $post['match_date'.$pks[$x]] = sportsmanagementHelper::convertDate($post['match_date'.$pks[$x]],0);
            $post['match_date'.$pks[$x]] = $post['match_date'.$pks[$x]].' '.$post['match_time'.$pks[$x]].':00';
            
            if ( $post['match_date'.$pks[$x]] != $tblMatch->match_date )
            {
                $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_ADMIN_CHANGE'),'Notice');
                self::sendEmailtoPlayers();
                
            }

			$object->match_number = $post['match_number'.$pks[$x]];
            $object->match_date = $post['match_date'.$pks[$x]];
            if ( isset($post['result_type'.$pks[$x]]) )
            {
            $object->result_type = $post['result_type'.$pks[$x]];
            }
            else
            {
            $object->result_type = 0;    
            }
            if ( isset($post['match_result_type'.$pks[$x]]) )
            {
	$object->match_result_type = $post['match_result_type'.$pks[$x]];
	    }
	else
	{
	$object->match_result_type = 0;	
	}

            $object->crowd = $post['crowd'.$pks[$x]];
            $object->round_id = $post['round_id'.$pks[$x]];
            if ( isset($post['division_id'.$pks[$x]]) )
            {
	$object->division_id = $post['division_id'.$pks[$x]];
	    }
	else
	{
	$object->division_id = 0;	
	}
	$object->projectteam1_id = $post['projectteam1_id'.$pks[$x]];
            $object->projectteam2_id = $post['projectteam2_id'.$pks[$x]];
            $object->team1_single_matchpoint = $post['team1_single_matchpoint'.$pks[$x]];
            $object->team2_single_matchpoint = $post['team2_single_matchpoint'.$pks[$x]];
            $object->team1_single_sets = $post['team1_single_sets'.$pks[$x]];
            $object->team2_single_sets = $post['team2_single_sets'.$pks[$x]];
            $object->team1_single_games = $post['team1_single_games'.$pks[$x]];
            $object->team2_single_games = $post['team2_single_games'.$pks[$x]];
            $object->content_id = $post['content_id'.$pks[$x]];
            $object->match_timestamp = sportsmanagementHelper::getTimestamp($object->match_date);

/**
 * handelt es sich um eine turnierrunde ?
 */
$this->jsmquery->clear(); 
$this->jsmquery->select('tournement');
$this->jsmquery->from('#__sportsmanagement_round');
$this->jsmquery->where('id = '.$object->round_id);
$this->jsmdb->setQuery($this->jsmquery);
$tournement_round = $this->jsmdb->loadResult();
                    
            if ( $post['use_legs'] )
            {
                foreach ( $post['team1_result_split'.$pks[$x]] as $key => $value )
                {
                    if ( is_numeric($post['team1_result_split'.$pks[$x]][$key]) )
                    {
                        if ( $post['team1_result_split'.$pks[$x]][$key] > $post['team2_result_split'.$pks[$x]][$key] )
                        {
                            $object->team1_result	+= 1;
                            $object->team2_result	+= 0; 
                        }
                        if ( $post['team1_result_split'.$pks[$x]][$key] < $post['team2_result_split'.$pks[$x]][$key] )
                        {
                            $object->team1_result	+= 0;
                            $object->team2_result	+= 1; 
                        }
                        if ( $post['team1_result_split'.$pks[$x]][$key] == $post['team2_result_split'.$pks[$x]][$key] )
                        {
                            $object->team1_result	+= 1;
                            $object->team2_result	+= 1; 
                        }
                    }
		else
                    {
                    $object->team1_result	= $post['team1_result'.$pks[$x]];
            	$object->team2_result	= $post['team2_result'.$pks[$x]];
                    }	
                }
            }
            else
            {

            if ( is_numeric($post['team1_result'.$pks[$x]]) && is_numeric($post['team2_result'.$pks[$x]]) )    
            {    
            $object->team1_result	= $post['team1_result'.$pks[$x]];
            $object->team2_result	= $post['team2_result'.$pks[$x]];
            /**
             * wer ist der sieger des spiels in der turnierrunde
             * nach regulärer spielzeit
             */
            if ( $tournement_round )
            {
            if ( $object->team1_result > $object->team2_result )
            {
                $object->team_won = $object->projectteam1_id;
                $object->team_lost = $object->projectteam2_id;
            }
            if ( $object->team1_result < $object->team2_result )
            {
                $object->team_won = $object->projectteam2_id;
                $object->team_lost = $object->projectteam1_id;
            }        
            }
            }
             
            if ( is_numeric($post['team1_result_ot'.$pks[$x]]) && is_numeric($post['team2_result_ot'.$pks[$x]]) )    
            {    
            $object->team1_result_ot	= $post['team1_result_ot'.$pks[$x]];
            $object->team2_result_ot	= $post['team2_result_ot'.$pks[$x]];
            /**
             * wer ist der sieger des spiels in der turnierrunde
             * nach verlängerung
             */
            if ( $tournement_round )
            {
            if ( $object->team1_result_ot > $object->team2_result_ot )
            {
                $object->team_won = $object->projectteam1_id;
                $object->team_lost = $object->projectteam2_id;
            }
            if ( $object->team1_result_ot < $object->team2_result_ot )
            {
                $object->team_won = $object->projectteam2_id;
                $object->team_lost = $object->projectteam1_id;
            }        
            }
            }
            
            if ( is_numeric($post['team1_result_so'.$pks[$x]]) && is_numeric($post['team2_result_so'.$pks[$x]]) )    
            {    
            $object->team1_result_so	= $post['team1_result_so'.$pks[$x]];
            $object->team2_result_so	= $post['team2_result_so'.$pks[$x]];
            /**
             * wer ist der sieger des spiels in der turnierrunde
             * nach elfmeterschiessen
             */
            if ( $tournement_round )
            {
            if ( $object->team1_result_so > $object->team2_result_so )
            {
                $object->team_won = $object->projectteam1_id;
                $object->team_lost = $object->projectteam2_id;
            }
            if ( $object->team1_result_so < $object->team2_result_so )
            {
                $object->team_won = $object->projectteam2_id;
                $object->team_lost = $object->projectteam1_id;
            }        
            }
            }
                            
            }
            
            
            $object->team1_result_split	= implode(";",$post['team1_result_split'.$pks[$x]]);
            $object->team2_result_split	= implode(";",$post['team2_result_split'.$pks[$x]]);

          try {
          // Update their details in the table using id as the primary key.
        $result_update = Factory::getDbo()->updateObject('#__sportsmanagement_match', $object, 'id', true);
        sprintf(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_SAVED'),$pks[$x]);
                $app->enqueueMessage(sprintf(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_SAVED'),$pks[$x]),'Notice');
            }
catch (Exception $e){
$app->enqueueMessage(__METHOD__.' '.__LINE__.Text::_($e->getMessage()),'Error');
$result = false;
}

		}
		return $result;

	}
    
    /**
	 * Method to remove a matchday
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	function delete(&$pks)
	{
	$app = Factory::getApplication();
    /* Ein Datenbankobjekt beziehen */
    $db = Factory::getDbo();
    /* Ein JDatabaseQuery Objekt beziehen */
    $query = Factory::getDbo()->getQuery(true);
    
    //$app->enqueueMessage(Text::_('match delete pk<br><pre>'.print_r($pks,true).'</pre>'   ),'');
    
	$result = false;
    if (count($pks))
		{

			$cids = implode(',',$pks);
            // wir löschen mit join
            $query = 'DELETE ms,mss,mst,mev,mre,mpl
            FROM #__sportsmanagement_match as m    
            LEFT JOIN #__sportsmanagement_match_statistic as ms
            ON ms.match_id = m.id
            LEFT JOIN #__sportsmanagement_match_staff_statistic as mss
            ON mss.match_id = m.id
            LEFT JOIN #__sportsmanagement_match_staff as mst
            ON mst.match_id = m.id
            LEFT JOIN #__sportsmanagement_match_event as mev
            ON mev.match_id = m.id
            LEFT JOIN #__sportsmanagement_match_referee as mre
            ON mre.match_id = m.id
            LEFT JOIN #__sportsmanagement_match_player as mpl
            ON mpl.match_id = m.id
            WHERE m.id IN ('.$cids.')';
            Factory::getDbo()->setQuery($query);
            Factory::getDbo()->execute();
            if (!Factory::getDbo()->execute()) 
            {
                //$app->enqueueMessage(Text::_('match delete query getErrorMsg<br><pre>'.print_r(Factory::getDbo()->getErrorMsg(),true).'</pre>'),'Error');
            }
            
            //$app->enqueueMessage(Text::_('match delete query<br><pre>'.print_r($query,true).'</pre>'   ),'');
            
            return parent::delete($pks);
        }    
   return true;     
   }
   
   
    
    /**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data)
	{
	   $app = Factory::getApplication();
       $date = Factory::getDate();
	   $user = Factory::getUser();
       $post = Factory::getApplication()->input->post->getArray(array());
       $option = Factory::getApplication()->input->getCmd('option');
       $parentsave = true;
       /* Ein Datenbankobjekt beziehen */
       $db = Factory::getDbo();
       // Set the values
	   $data['modified'] = $date->toSql();
	   $data['modified_by'] = $user->get('id');
       $data['id'] = $post['id'];

       //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'Notice');
       //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
       
       if (isset($post['extended']) && is_array($post['extended'])) 
		{
			// Convert the extended field to a string.
			$parameter = new Registry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string)$parameter;
		}
        
        $data['team1_bonus'] = $post['team1_bonus'];
        $data['team2_bonus'] = $post['team2_bonus'];
	$data['team1_legs'] = $post['team1_legs'];
        $data['team2_legs'] = $post['team2_legs'];
        $data['match_result_detail'] = $post['match_result_detail'];
        $data['count_result'] = $post['count_result'];
        $data['alt_decision'] = $post['alt_decision'];
        $data['team1_result_decision'] = $post['team1_result_decision'];
        $data['team2_result_decision'] = $post['team2_result_decision'];
        $data['decision_info'] = $post['decision_info'];
        $data['team_won'] = $post['team_won'];
        
        $data['match_timestamp'] = sportsmanagementHelper::getTimestamp($data['match_date']);
        
        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        
         // zuerst sichern, damit wir bei einer neuanlage die id haben
         try{   
       $parentsave = parent::save($data);
       }
catch (Exception $e)
{
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getCode()), 'error');
}
       if ( $parentsave )
       {
			$id =  (int) $this->getState($this->getName().'.id');
            $isNew = $this->getState($this->getName() . '.new');
            $data['id'] = $id;
            
            if ( $isNew )
            {
                //Here you can do other tasks with your newly saved record...
                $app->enqueueMessage(Text::plural(strtoupper($option) . '_N_ITEMS_CREATED', $id),'');
            }
        
        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' getErrorMsg<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' getState<pre>'.print_r($this->getState(),true).'</pre>' ),'Error');
        
        return true;   
		}
        else
        {
        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' getErrorMsg<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
        return false;    
        }  
        
        //return true; 
    }
      
    /**
     * sportsmanagementModelMatch::getMatchSingleData()
     * 
     * @param mixed $match_id
     * @return
     */
    public static function getMatchSingleData($match_id)
	{
		$option = Factory::getApplication()->input->getCmd('option');
	   $app = Factory::getApplication();
        // Get a db connection.
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        // Select some fields
        $query->select('m.*');
        // From 
		$query->from('#__sportsmanagement_match_single AS m');
        
        // Where
        $query->where('m.match_id = '.(int) $match_id );
        
        $db->setQuery($query);
        $result = $db->loadObjectList();
        
        return $result;    
    }
    
    /**
     * sportsmanagementModelMatch::getMatchRelationsOptions()
     * 
     * @param mixed $project_id
     * @param integer $excludeMatchId
     * @return
     */
    public static function getMatchRelationsOptions($project_id,$excludeMatchId=0)
	{
	   $option = Factory::getApplication()->input->getCmd('option');
	   $app = Factory::getApplication();
        // Get a db connection.
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        // Select some fields
        $query->select('m.id AS value,m.match_date, p.timezone, t1.name AS t1_name, t2.name AS t2_name');
        // From 
		$query->from('#__sportsmanagement_match AS m');
        
        $query->join('LEFT','#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id');
        $query->join('LEFT','#__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id');
        
        $query->join('LEFT','#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
        $query->join('LEFT','#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');
        
        $query->join('LEFT','#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
        $query->join('LEFT','#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
        
        $query->join('LEFT','#__sportsmanagement_project AS p ON p.id = pt1.project_id');
        
        // Where
        $query->where('pt1.project_id = '.(int) $project_id );
        $query->where('m.id NOT in ('.$excludeMatchId.')' );
        $query->where('m.published = 1' );
        
        $query->order('m.match_date DESC,t1.short_name' );
try{               
		$db->setQuery($query);
		$matches = $db->loadObjectList();
        }
catch (Exception $e)
{
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getCode()), 'error');
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'error');
	$matches = false;
}
        
		if ($matches)
		{
			foreach ($matches as $match)
			{
			 if ( empty( $match->timezone ) )
             {
                $match->timezone = 'Europe/Berlin';
             }
				sportsmanagementHelper::convertMatchDateToTimezone($match);
			}
		}
		return $matches;
	}
        
     /**
	 * Method to load content matchday data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public static function getMatchData($match_id, $cfg_which_database = 0)
	{
		$option = Factory::getApplication()->input->getCmd('option');
	   $app = Factory::getApplication();
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database );
        $query = $db->getQuery(true);
        // Select some fields
        $query->select('m.*,CASE m.time_present	when NULL then NULL	else DATE_FORMAT(m.time_present, "%H:%i") END AS time_present,m.extended as matchextended');
        $query->select('t1.name AS hometeam, t1.id AS t1id');
        $query->select('t2.name as awayteam, t2.id AS t2id');
        $query->select('pt1.project_id');
        $query->select('CONCAT_WS(\':\',pg.id,pg.alias) AS playground_slug ');
        $query->select('pg.picture AS playground_picture,pg.name AS playground_name ');
        // From 
		$query->from('#__sportsmanagement_match AS m');
        $query->join('INNER',' #__sportsmanagement_project_team AS pt1 ON pt1.id = m.projectteam1_id ');
        $query->join('INNER',' #__sportsmanagement_season_team_id as st1 ON st1.id = pt1.team_id ');
        $query->join('INNER',' #__sportsmanagement_team AS t1 ON t1.id = st1.team_id ');
        $query->join('INNER',' #__sportsmanagement_project_team AS pt2 ON pt2.id = m.projectteam2_id ');
        $query->join('INNER',' #__sportsmanagement_season_team_id as st2 ON st2.id = pt2.team_id ');
        $query->join('INNER',' #__sportsmanagement_team AS t2 ON t2.id = st2.team_id ');
        $query->join('LEFT',' #__sportsmanagement_playground AS pg ON pg.id = m.playground_id ');
        // Where
        $query->where('m.id = '.(int) $match_id );
                
		

          try {
          	$db->setQuery($query);
            $result = $db->loadObject();
            }
catch (Exception $e){
$app->enqueueMessage(__METHOD__.' '.__LINE__.Text::_($e->getMessage()),'Error');
$result = false;
}            
        
			//$this->_data=Factory::getDbo()->loadObject();
			return $result;
		
	}
    
    /**
     * sportsmanagementModelMatch::getTeamPersons()
     * 
     * @param mixed $projectteam_id
     * @param bool $filter
     * @param mixed $persontype
     * @return
     */
    public static function getTeamPersons($projectteam_id,$filter=false,$persontype)
	{
	   $option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
        self::$_season_id = $app->getUserState( "$option.season_id", '0' );
        self::$_project_id = $app->getUserState( "$option.pid", '0' );

        $query = Factory::getDbo()->getQuery(true);
        
        // Select some fields
        $query->select('sp.id AS value');
        $query->select('pl.firstname,pl.nickname,pl.lastname,pl.info,sp.jerseynumber,pl.ordering');
        
        $query->select('pos.name AS positionname');

        // From 
		$query->from('#__sportsmanagement_person AS pl');
        $query->join('INNER',' #__sportsmanagement_season_team_person_id AS sp ON sp.person_id = pl.id ');
        $query->join('INNER',' #__sportsmanagement_season_team_id AS st ON st.team_id = sp.team_id and st.season_id = sp.season_id ');
        
        $query->join('LEFT', '#__sportsmanagement_person_project_position AS ppp on ppp.person_id = sp.person_id');
        $query->join('LEFT',' #__sportsmanagement_project_position AS ppos ON ppos.id = ppp.project_position_id');
        $query->join('LEFT',' #__sportsmanagement_position AS pos ON pos.id = ppos.position_id ');
        
        $query->join('INNER',' #__sportsmanagement_project_team AS pt ON pt.team_id = st.id ');
        $query->where('pt.id = '.  $projectteam_id);
        $query->where('pl.published = 1');
        $query->where('sp.persontype = '.$persontype);
        $query->where('ppp.persontype = '.$persontype);
        $query->where('sp.season_id = '.self::$_season_id);
        
        $query->where('ppp.project_id = '.self::$_project_id);

		if (is_array($filter) && count($filter) > 0)
		{
            $query->where("sp.id NOT IN (".implode(',',$filter).")");
		}

        $query->order("pl.lastname ASC");
		Factory::getDbo()->setQuery($query);
		
        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
        $result = Factory::getDbo()->loadObjectList();
            if ( !$result )
		    {
		    switch ($persontype)
            {
                case 1:
                $position_value = 'COM_SPORTSMANAGEMENT_SOCCER_F_PLAYERS';
                //$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NOPLAYER_IN_POSITION',Text::_($position_value) ),'Error');
                break;
                case 2:
                $position_value = 'COM_SPORTSMANAGEMENT_SOCCER_F_COACH';
                //$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NOPLAYER_IN_POSITION',Text::_($position_value)),'Error');
                break;
            }  

		    }
        
        
        return $result;
	}
    
    
    /**
	 * Method to return substitutions made by a team during a match
	 * if no team id is passed,all substitutions should be returned (to be done!!)
	 * @access	public
	 * @return	array of substitutions
	 *
	 */
	public static function getSubstitutions($tid=0,$match_id)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
        self::$_season_id	= $app->getUserState( "$option.season_id", '0' );
        $project_id	= $app->getUserState( "$option.pid", '0' );
        $starttime = microtime(); 
        // Get a db connection.
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        
		//$project_id=$app->getUserState($option.'project');
		$in_out = array();
        $query->select('mp.id,mp.came_in,mp.teamplayer_id,mp.match_id,mp.in_out_time');
        $query->select('p1.firstname AS firstname, p1.nickname AS nickname, p1.lastname AS lastname');
        $query->select('p2.firstname AS out_firstname,p2.nickname AS out_nickname, p2.lastname AS out_lastname');
        $query->select('pos.name AS in_position');
        
        $query->from('#__sportsmanagement_match_player AS mp');
        $query->join('LEFT',' #__sportsmanagement_season_team_person_id AS sp1 ON sp1.id = mp.teamplayer_id ');
        $query->join('LEFT',' #__sportsmanagement_person AS p1 ON sp1.person_id = p1.id ');
        $query->join('LEFT',' #__sportsmanagement_position AS pos ON pos.id = p1.position_id ');
        $query->join('LEFT',' #__sportsmanagement_project_position AS ppos ON ppos.position_id = mp.project_position_id ');
        $query->join('LEFT',' #__sportsmanagement_season_team_person_id AS sp2 ON sp2.id = mp.in_for ');
        $query->join('LEFT',' #__sportsmanagement_person AS p2 ON sp2.person_id = p2.id ');
        
        $query->join('LEFT', '#__sportsmanagement_person_project_position AS ppp on ppp.person_id = sp1.id and ppp.project_id = ppos.project_id');
        
        $query->join('LEFT','#__sportsmanagement_season_team_id AS st1 ON st1.team_id = sp1.team_id and st1.season_id = sp1.season_id'); 
        $query->join('LEFT','#__sportsmanagement_project_team AS pt1 ON st1.id = pt1.team_id');

        $query->where('pt1.project_id = '.$project_id);
        $query->where('mp.match_id = '.$match_id);
        $query->where('came_in > 0');
        $query->where('pt1.id = '.$tid);
        $query->order("(mp.in_out_time+0)");
        
        $query->group("mp.id,mp.came_in,mp.teamplayer_id,mp.match_id,mp.in_out_time");
 try {       
		$db->setQuery($query);
        $in_out[$tid] = $db->loadObjectList();
 }
catch (Exception $e)
{
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
	$in_out[$tid] = false;
}        
        
		return $in_out;
	}
    
    
    /**
	 * returns starters player id for the specified team
	 *
	 * @param int $team_id
	 * @param int $project_position_id
	 * @return array of player ids
	 */
	public static function getRoster($team_id, $project_position_id=0, $match_id,$position_value)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
        self::$_season_id = $app->getUserState( "$option.season_id", '0' );
        self::$_project_id = $app->getUserState( "$option.pid", '0' );

        $query = Factory::getDbo()->getQuery(true);
        
        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' team_id'.'<pre>'.print_r($team_id,true).'</pre>' ),'');
        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' project_position_id'.'<pre>'.print_r($project_position_id,true).'</pre>' ),'');
        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' match_id'.'<pre>'.print_r($match_id,true).'</pre>' ),'');
        
        // Select some fields
        $query->select('mp.id AS table_id,mp.match_id,mp.teamplayer_id AS value,mp.trikot_number AS trikot_number,mp.captain AS captain');
        $query->select('pl.firstname,pl.nickname,pl.lastname,pl.info,pl.ordering,pl.position_id as person_position_id');
        $query->select('pos.name AS positionname,pos.id as position_position_id');
        $query->select('sp.jerseynumber,sp.project_position_id as stp_project_position_id');
        //$query->select('ppos.position_id,ppos.id AS pposid');

        // From 
	$query->from('#__sportsmanagement_match_player AS mp');
        $query->join('INNER',' #__sportsmanagement_season_team_person_id AS sp ON sp.id = mp.teamplayer_id ');
        $query->join('INNER',' #__sportsmanagement_person AS pl ON pl.id = sp.person_id ');
        
        //$query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_person_project_position AS ppp on ppp.person_id = pl.id');
       // $query->join('LEFT', '#__sportsmanagement_person_project_position AS ppp on ppp.person_id = pl.id and ppp.persontype = sp.persontype');
        
        $query->join('LEFT',' #__sportsmanagement_project_position AS ppos ON ppos.position_id = mp.project_position_id ');
        $query->join('LEFT',' #__sportsmanagement_position AS pos ON pos.id = ppos.position_id ');
        //$query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.position_id = pos.id ');
        
        $query->join('INNER','#__sportsmanagement_season_team_id AS st1 ON st1.team_id = sp.team_id ');
        $query->join('LEFT',' #__sportsmanagement_project_team AS pt ON pt.team_id = st1.id ');
        
        $query->where('mp.match_id = '.$match_id);
        
        $query->where('ppos.project_id = '.self::$_project_id);
        
        //$query->where('tpl.projectteam_id = '.$team_id);
        $query->where('mp.came_in = '.self::MATCH_ROSTER_STARTER);
        $query->where('pl.published = 1');
	
        if ($project_position_id > 0)
		{
          //$query->where('pl.position_id = '.$project_position_id);
          //$query->where('sp.project_position_id = '.$project_position_id);
          $query->where('ppos.id = '.$project_position_id);
          //$query->where('ppos.id = '.$project_position_id);
		}

        if ($team_id > 0)
		{
          $query->where('pt.id = '.$team_id);
		}
        
        //$query->order('ppos.position_id');
        $query->order('mp.ordering ASC');
		Factory::getDbo()->setQuery($query);
		$result = Factory::getDbo()->loadObjectList('value');
        
//        $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' <pre>'.print_r($query->dump(),true).'</pre>'),'');
        
        if ( !$result )
		    {
		    //$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NOPLAYER_IN_POSITION',$position_value),'Error');  
            //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' <pre>'.print_r($query->dump(),true).'</pre>'),'Error');
		    }
            
		return $result;
	}
    
    
    /**
     * sportsmanagementModelMatch::getMatchText()
     * 
     * @param mixed $match_id
     * @return
     */
    public static function getMatchText($match_id=0,$cfg_which_database=0)
	{
	   $app = Factory::getApplication();
       $option = Factory::getApplication()->input->getCmd('option');
       
       $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database, FALSE ) ;
	   $query = $db->getQuery(true);
       
       // Select some fields
		$query->select('m.*');
        $query->select('t1.name as t1name,t2.name as t2name');
        $query->from('#__sportsmanagement_match AS m ');
        $query->join('INNER','#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id ');
        $query->join('INNER','#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
        $query->join('INNER','#__sportsmanagement_team AS t1 ON t1.id = st1.team_id ');
        $query->join('INNER','#__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id ');
        $query->join('INNER','#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');
        $query->join('INNER','#__sportsmanagement_team AS t2 ON t2.id = st2.team_id ');
        $query->where('m.id = ' . $match_id );
        $query->where('m.published = 1');
        $query->order('m.match_date, t1.short_name');
          try{          
		$db->setQuery($query);
        $result = $db->loadObject();
         }
catch (Exception $e)
{
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
	$result = false;
}
 $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect        
		return $result;
	}
    
    /**
	 * Method to return teams and match data
		*
		* @access	public
		* @return	array
		* @since 0.1
		*/
	public static function getMatchTeams($match_id)
	{
	   $app = Factory::getApplication();
       $option = Factory::getApplication()->input->getCmd('option');
       // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
       
       // Select some fields
		$query->select('mc.*');
        $query->select('t1.name as team1,t2.name as team2');
        $query->select('u.name AS editor');
        $query->from('#__sportsmanagement_match AS mc ');
        $query->join('INNER','#__sportsmanagement_project_team AS pt1 ON mc.projectteam1_id = pt1.id ');
        $query->join('INNER','#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
        $query->join('INNER','#__sportsmanagement_team AS t1 ON t1.id = st1.team_id ');
        $query->join('INNER','#__sportsmanagement_project_team AS pt2 ON mc.projectteam2_id = pt2.id ');
        $query->join('INNER','#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');
        $query->join('INNER','#__sportsmanagement_team AS t2 ON t2.id = st2.team_id ');
        $query->join('LEFT','#__users u ON u.id = mc.checked_out');
        $query->where('mc.id = ' . $match_id );
try{
		$db->setQuery($query);
		$result = $db->loadObject();
        }
catch (Exception $e)
{
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
    $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getCode()), 'error');
	$result = false;
}
$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect  
return $result;
	}
    
    /**
	 * returns starters referees id for the specified team
	 *
	 * @param int $project_position_id
	 * @return array of referee ids
	 */
	public static function getRefereeRoster($project_position_id=0,$match_id=0,$project_referee_id=0)
	{
	// Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Get a db connection.
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $result = '';

// Select some fields
$query->select('pref.id AS value,pr.firstname,pr.nickname,pr.lastname,pr.email');
$query->from('#__sportsmanagement_match_referee AS mr');
$query->join('LEFT','#__sportsmanagement_project_referee AS pref ON mr.project_referee_id=pref.id AND pref.published = 1');
$query->join('LEFT','#__sportsmanagement_season_person_id AS spi ON pref.person_id=spi.id');
$query->join('LEFT','#__sportsmanagement_person AS pr ON spi.person_id=pr.id AND pr.published = 1');
$query->where('mr.match_id = '. $match_id);

		if ( $project_position_id )
		{
		$query->where('mr.project_position_id = '.$project_position_id);	
		}
        if ( $project_referee_id )
		{
		$query->where('mr.project_referee_id = '.$project_referee_id);	
		}
		$query->order('mr.project_position_id, mr.ordering ASC');
try{
		$db->setQuery($query);
		$result = $db->loadObjectList('value');
         }
catch (Exception $e){
    echo $e->getMessage();
}

return $result;
	}
    
    /**
	 * Method to return the projects referees array
	 *
	 * @access	public
	 * @return	array
	 * @since 0.1
	 */
	public static function getProjectReferees($already_sel=false, $project_id)
	{
	// Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Get a db connection.
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $result = '';

// Select some fields
$query->select('pref.id AS value,pl.firstname,pl.nickname,pl.lastname,pl.info,pos.name AS positionname');
$query->from('#__sportsmanagement_person AS pl');

$query->join('LEFT','#__sportsmanagement_season_person_id AS spi ON spi.person_id=pl.id');

$query->join('LEFT','#__sportsmanagement_project_referee AS pref ON pref.person_id=spi.id AND pref.published=1');
$query->join('LEFT','#__sportsmanagement_project_position AS ppos ON ppos.id=pref.project_position_id');
$query->join('LEFT','#__sportsmanagement_position AS pos ON pos.id=ppos.position_id');
$query->where('pref.project_id='.$project_id);
$query->where('pl.published = 1');

		
		if (is_array($already_sel) && count($already_sel) > 0)
		{
		$query->where('pref.id NOT IN ('.implode(',',$already_sel).')' );
		}

		$query->order('pl.lastname ASC');
        try{
		$db->setQuery($query);
		$result = $db->loadObjectList('value');
         }
catch (Exception $e){
    echo $e->getMessage();
}

return $result;


	}
    
 
    
    /**
	 * returns players who played for the specified team
	 *
	 * @param int $team_id
	 * @param int $project_position_id
	 * @return array of players
	 */
	public static function getMatchPersons($projectteam_id,$project_position_id=0,$match_id, $table)
	{
		$app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        
        $query = Factory::getDbo()->getQuery(true);
        
        self::$_season_id = $app->getUserState( "$option.season_id", '0' );
        self::$_project_id	= $app->getUserState( "$option.pid", '0' );
        
//        if ( COM_SPORTSMANAGEMENT_USE_NEW_TABLE )
//        {
//            
//            
//        }
//        else
//        {
        switch($table)
        {
            case 'player':
            $id = 'teamplayer_id';
            $query->select('mp.'.$id.' AS tpid,mp.'.$id.',mp.project_position_id,mp.match_id,mp.id as update_id');
            break;
            case 'staff':
            $id = 'team_staff_id';
            $query->select('mp.'.$id.' ,mp.project_position_id,mp.match_id,mp.id as update_id');
            break;
        }
        
        // Select some fields
        $query->select('pt.id as projectteam_id');
        $query->select('sp.id AS value');
        $query->select('pl.firstname,pl.nickname,pl.lastname');
        $query->select('pos.name AS positionname');
        $query->select('ppos.position_id,ppos.id AS pposid');
        // From 
		$query->from('#__sportsmanagement_match_'.$table.' AS mp');
        $query->join('INNER','#__sportsmanagement_season_team_person_id AS sp ON mp.'.$id.' = sp.id');
        $query->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = sp.team_id ');
        $query->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');

$query->join('LEFT', '#__sportsmanagement_person_project_position AS ppp on ppp.person_id = sp.person_id and ppp.persontype = sp.persontype');
$query->join('LEFT',' #__sportsmanagement_project_position as ppos ON ppos.id = ppp.project_position_id');
 

$query->join('INNER',' #__sportsmanagement_person AS pl ON pl.id = sp.person_id');
$query->join('INNER',' #__sportsmanagement_position AS pos ON pos.id = ppos.position_id');

		// Where
        $query->where('mp.match_id = '.$match_id);
        $query->where('pl.published = 1');
        
        $query->where('ppp.project_id = '.self::$_project_id);
       
        if ($projectteam_id > 0)
		{
          $query->where('pt.id = '.$projectteam_id);
		}
            
        if ( $project_position_id > 0 )
		{
            // Where
            $query->where('mp.project_position_id = '.$project_position_id);
		}
		// Order
        $query->order('mp.project_position_id, mp.ordering,	pl.lastname, pl.firstname ASC');
        Factory::getDbo()->setQuery($query);
        
        $result = Factory::getDbo()->loadObjectList($id);
       
        if ( !$result )
       {

        }
        
		return $result;
       


	}
    

    

    
    /**
     * sportsmanagementModelMatch::getInputStats()
     * 
     * @param mixed $project_id
     * @return
     */
    public static function getInputStats($project_id)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
        
        $query = Factory::getDbo()->getQuery(true);
        
        require_once (JPATH_COMPONENT_ADMINISTRATOR .DS.'statistics'.DS.'base.php');
        
        $query->select('stat.id,stat.name,stat.short,stat.class,stat.icon,stat.calculated,ppos.position_id AS posid');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_statistic AS stat ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position_statistic AS ps ON ps.statistic_id = stat.id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.position_id = ps.position_id ');
        $query->where('ppos.project_id = '.  $project_id);
        $query->order('stat.ordering, ps.ordering');
        
		Factory::getDbo()->setQuery($query);
        
        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
		$res = Factory::getDbo()->loadObjectList();
		$stats=array();
		foreach ($res as $k => $row)
		{
			$stat = SMStatistic::getInstance($row->class);
			$stat->bind($row);
			$stat->set('position_id',$row->posid);
			$stats[]=$stat;
		}
        
        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' stats<br><pre>'.print_r($stats,true).'</pre>'),'Notice');
        
		return $stats;
	}
    
    /**
     * sportsmanagementModelMatch::getMatchStatsInput()
     * 
     * @param mixed $match_id
     * @param mixed $projectteam1_id
     * @param mixed $projectteam2_id
     * @return
     */
    public static function getMatchStatsInput($match_id,$projectteam1_id,$projectteam2_id)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
        $starttime = microtime(); 
        
        $query = Factory::getDbo()->getQuery(true);
        
        $query->select('*');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_statistic ');
        $query->where('match_id = '.$match_id);
        
		Factory::getDbo()->setQuery($query);
        
        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		$res = Factory::getDbo()->loadObjectList();
		$stats = array(	$projectteam1_id => array(),
		$projectteam2_id => array());
		foreach ($res as $stat)
		{
			@$stats[$stat->projectteam_id][$stat->teamplayer_id][$stat->statistic_id]=$stat->value;
		}
		return $stats;
	}

	/**
	 * sportsmanagementModelMatch::getMatchStaffStatsInput()
	 * 
	 * @param mixed $match_id
	 * @param mixed $projectteam1_id
	 * @param mixed $projectteam2_id
	 * @return
	 */
	public static function getMatchStaffStatsInput($match_id,$projectteam1_id,$projectteam2_id)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
        $starttime = microtime(); 
        
        $query = Factory::getDbo()->getQuery(true);
        
        $query->select('*');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff_statistic ');
        $query->where('match_id = '.$match_id);
        
		Factory::getDbo()->setQuery($query);
        
        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		$res = Factory::getDbo()->loadObjectList();
		$stats = array($projectteam1_id => array(),$projectteam2_id => array());
		foreach ((array)$res as $stat)
		{
			@$stats[$stat->projectteam_id][$stat->team_staff_id][$stat->statistic_id]=$stat->value;
		}
		return $stats;
	}
    
    
    
    
    /**
	 * Method to return the project positions array (id,name)
	 *
	 * @access	public
	 * @return	array
	 * @since 1.5
	 */
	public static function getProjectPositionsOptions($id=0, $person_type=1,$project_id)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
        $starttime = microtime(); 
        // Get a db connection.
        $db = Factory::getDbo();
        $result = '';
        $query = $db->getQuery(true);
        
        $query->select('ppos.id AS value,pos.name AS text,pos.id AS posid,pos.id AS pposid');
        $query->from('#__sportsmanagement_position AS pos ');
        $query->join('INNER','#__sportsmanagement_project_position AS ppos ON ppos.position_id = pos.id ');
        
        $query->where('ppos.project_id = '.$project_id);
        $query->where('pos.persontype = '.$person_type);

		if ($id > 0)
		{
            $query->where('ppos.position_id = '.$id);
		}
        $query->order('pos.ordering');
        
		$db->setQuery($query);
        
        try {
            $db->setQuery($query);
            $result = $db->loadObjectList('value');
            }
catch (Exception $e){
    echo $e->getMessage();
}

return $result;

	}
    
    /**
     * sportsmanagementModelMatch::getEventsOptions()
     * 
     * @param mixed $project_id
     * @param mixed $match_id
     * @return
     */
    public static function getEventsOptions($project_id=0,$match_id=0)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
        $starttime = microtime(); 
        
        $query = Factory::getDbo()->getQuery(true);
        
        $query->select('et.id AS value,et.name AS text,et.icon AS icon');
        
        if ( $match_id )
        {
        $query->from('#__sportsmanagement_match AS m ');
        $query->join('INNER','#__sportsmanagement_project_position AS ppos ON ppos.project_id = '.$project_id);    
        $query->where('m.id = '.$match_id);
        }
        else
        {
        $query->from('#__sportsmanagement_project_position AS ppos ');
        $query->where('ppos.project_id = '.$project_id);    
        }
        
        $query->join('INNER','#__sportsmanagement_position_eventtype AS pet ON pet.position_id = ppos.position_id ');
        $query->join('INNER','#__sportsmanagement_eventtype AS et ON et.id = pet.eventtype_id ');
        
        $query->where('et.published = 1');
        
        $query->order('et.id,pet.ordering, et.ordering');
        $query->group('et.id');
                    
		Factory::getDbo()->setQuery($query);
        
		$result = Factory::getDbo()->loadObjectList();
        if ( !$result )
        {
            $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_EVENTS_POS'),'Error');
        }
		
        foreach ($result as $event)
        {
            $event->text = Text::_($event->text);
        }
        
		return $result;
	}
    
    /**
	 * Method to update starting lineup list
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function updateReferees($post)
	{
		$app = Factory::getApplication();
        $config = Factory::getConfig();
        $option = Factory::getApplication()->input->getCmd('option');
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        
        $sender = array( 
    $config->get( 'mailfrom' ),
    $config->get( 'fromname' ) 
);

        $mid = $post['id'];
		$peid = array();
		$result = true;
		$positions = $post['positions'];

$paramsmail = ComponentHelper::getParams($option)->get('ishd_referee_insert_match_mail');
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' paramsmail <br><pre>'.print_r($paramsmail ,true).'</pre>'),'');    

//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' post'.'<pre>'.print_r($post,true).'</pre>' ),'');
        
		//$project_id=$post['project'];
		foreach ($positions AS $key => $pos)
		{
			if (isset($post['position'.$key])) 
            { 
                $peid = array_merge((array) $post['position'.$key],$peid);
                 }
		}

//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' peid'.'<pre>'.print_r($peid,true).'</pre>' ),'');
        
		//if ( $peid == null )
        if ( !$peid )
		{ 
		  // Delete all referees assigned to this match
          $query->delete($db->quoteName('#__sportsmanagement_match_referee'));
          $query->where('match_id = '.$mid);
    	}
		else
		{ 
/**
* erst die schiedsrichter selektieren die gelöscht werden
* damit wir eine email senden können, dass sie vom spiel
* ausgetragen werden. 
*/		  
$peids = implode(',',$peid);
$query->clear();
// Select some fields
		$query->select('id');
		// From the match table
		$query->from('#__sportsmanagement_match_referee');
        $query->where('match_id = '.$mid);
        $query->where('project_referee_id NOT IN ('.$peids.')');
		$db->setQuery($query);
        $result_referee_delete = $db->loadObjectList();
$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' delete referees: <br><pre>'.print_r($result_referee_delete ,true).'</pre>'),'Error');        
          
          // Delete all referees which are not selected anymore from this match
			ArrayHelper::toInteger($peid);
			$peids = implode(',',$peid);
            $query->clear();
            $query->delete($db->quoteName('#__sportsmanagement_match_referee'));
            $query->where('match_id = '.$mid);
            $query->where('project_referee_id NOT IN ('.$peids.')');
		}
        
		$db->setQuery($query);
		if (!sportsmanagementModeldatabasetool::runJoomlaQuery()) 
        { 
            $this->setError($db->getErrorMsg()); 
            $result = false; 
        }
        
		foreach ( $positions AS $key => $pos )
		{
			if ( isset($post['position'.$key]) )
			{
				for ($x=0; $x < count($post['position'.$key]); $x++)
				{
					$project_referee_id = $post['position'.$key][$x];
                    $query->clear();
                    // Select some fields
                    $query->select('id');
                    // From the match table
                    $query->from('#__sportsmanagement_match_referee');
                    $query->where('match_id = '.$mid);
                    $query->where('project_referee_id = '.$project_referee_id);
        

					$db->setQuery($query);
                    $result_referee = $db->loadResult();
					if ( $result_referee )
					{
					   // Create an object for the record we are going to update.
        $object = new stdClass();
        // Must be a valid primary key value.
        $object->id = $result;
        $object->project_position_id = $key;
        $object->ordering = $x;
        // Update their details in the table using id as the primary key.
        try {
        $result = $db->updateObject('#__sportsmanagement_match_referee', $object, 'id');
}
catch (Exception $e){
sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
}
 
        
					}
					else
					{
					   $profile = new stdClass();
                $profile->match_id = $mid;
                $profile->project_referee_id = $project_referee_id;
                $profile->project_position_id = $key;
                $profile->ordering = $x;
                // Insert the object into the table.
                try {
                $result = $db->insertObject('#__sportsmanagement_match_referee', $profile);
                
                if ( $result )
                {
                $query->clear();    
                $match_teams = self::getMatchTeams($mid);
                $match_detail = self::getMatchData($mid);
                $refreee_detail = self::getRefereeRoster($key,$mid,$project_referee_id); 
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' refreee_detail <br><pre>'.print_r($refreee_detail ,true).'</pre>'),'');                
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' match_detail <br><pre>'.print_r($match_detail ,true).'</pre>'),'');

$mailer = Factory::getMailer();
$mailer->setSender($sender);
$recipient = $refreee_detail[$project_referee_id]->email;
$mailer->addRecipient($recipient);
                
//$body = "Your body string\nin double quotes if you want to parse the \nnewlines etc";
$body = sprintf($paramsmail,
$refreee_detail[$project_referee_id]->firstname,
$refreee_detail[$project_referee_id]->lastname,
'Schiedsrichterverein',
'Schiedsrichterstufe',
date("d.m.Y - H:i", $match_detail->match_timestamp),
$match_detail->playground_name,
'Ligakurzname',
$match_teams->team1,
$match_teams->team2);

$mailer->setSubject('Neueinteilung Schiedsrichtereinsatz am : '.date("d.m.Y - H:i", $match_detail->match_timestamp));
$mailer->isHTML(true);
$mailer->Encoding = 'base64';
$mailer->setBody($body);
$send = $mailer->Send();
if ( $send !== true ) 
{
$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' Error sending email: <br><pre>'.print_r($send->__toString() ,true).'</pre>'),'Error'); 
} 
else 
{
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' Mail sent <br><pre>'.print_r($mailer ,true).'</pre>'),''); 
}                    
                }
                
                
                
                }
catch (Exception $e){
//sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
}
                					}
				
				}
			}
		}
		return true;
	}
    
/**
 * sportsmanagementModelMatch::getPlayerEventsbb()
 * 
 * @param integer $teamplayer_id
 * @param integer $event_type_id
 * @param integer $match_id
 * @return
 */
function getPlayerEventsbb($teamplayer_id=0,$event_type_id=0,$match_id=0)
	{
	   $query = Factory::getDBO()->getQuery(true);
       
		$ret=array();
		$record = new stdClass();
		$record->id = '';
		$record->event_sum = 0;
        $record->event_time = "";
        $record->notice = "";
		$record->teamplayer_id=$teamplayer_id;
		$record->event_type_id=$event_type_id;
		$ret[0] = $record;
        
        $query->select('me.projectteam_id,me.id,me.match_id,me.teamplayer_id,me.event_type_id,me.event_sum,me.event_time,me.notice');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event AS me');
        $query->where('me.match_id = '.$match_id);
        $query->where('me.teamplayer_id = '.$teamplayer_id);
        $query->where('me.event_type_id = '.$event_type_id);
        $query->order('me.teamplayer_id ASC');

		Factory::getDBO()->setQuery($query);
		$result = Factory::getDBO()->loadObjectList();
		if(count($result)>0)
		{
			return $result;
		}
		return $ret;
	}
        

        
    /**
	 * Method to update starting lineup list
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function updateRoster($post)
	{
	$app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
	$date = Factory::getDate();
    $user = Factory::getUser();
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($post,true).'</pre>'),'Notice');	
// Create a new query object.		
    $db = sportsmanagementHelper::getDBConnection();
    $query = $db->getQuery(true);	
    $result = true;
	$positions = $post['positions'];
	$mid = $post['id'];
	$team = $post['team'];
    $trikotnumbers = $post['trikot_number']; 
    $captain = $post['captain'];

$query->clear();
$query->select('mp.id');
$query->from('#__sportsmanagement_match_player AS mp');
$query->join('INNER',' #__sportsmanagement_season_team_person_id AS sp ON sp.id = mp.teamplayer_id ');
$query->join('INNER','#__sportsmanagement_season_team_id AS st1 ON st1.team_id = sp.team_id ');
$query->join('LEFT',' #__sportsmanagement_project_team AS pt ON pt.team_id = st1.id ');
$query->where('mp.came_in = '.self::MATCH_ROSTER_STARTER);
$query->where('mp.match_id = '.$mid);
$query->where('pt.id = '.$team);
$db->setQuery($query);
try{		
$result = $db->loadColumn();
}
catch (Exception $e){
$msg = $e->getMessage(); // Returns "Normally you would have other code...
$code = $e->getCode(); // Returns '500';
$app->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}	
if ( $result )
{
$query->clear();
$query->delete($db->quoteName('#__sportsmanagement_match_player'));
$query->where('id IN ('.implode(",",$result).')');
$db->setQuery($query);
try{
$result = $db->execute();	
}
catch (Exception $e){
$msg = $e->getMessage(); // Returns "Normally you would have other code...
$code = $e->getCode(); // Returns '500';
$app->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}
	
}	
		
foreach ($positions AS $project_position_id => $pos)
		{
			if (isset($post['position'.$project_position_id]))
			{
				foreach ($post['position'.$project_position_id] AS $ordering => $player_id)
				{
// Create and populate an object.
$temp = new stdClass();
$temp->match_id = $mid;
$temp->teamplayer_id = $player_id;
$temp->project_position_id= $pos->pposid;
$temp->came_in = self::MATCH_ROSTER_STARTER;
$temp->trikot_number = $trikotnumbers[$player_id];
$temp->captain = $captain[$player_id];					
$temp->ordering = $ordering;
$temp->modified = $date->toSql();
$temp->modified_by = $user->get('id');
try{					
// Insert the object
$resultquery = $db->insertObject('#__sportsmanagement_match_player', $temp);    
}
catch (Exception $e){
$msg = $e->getMessage(); // Returns "Normally you would have other code...
$code = $e->getCode(); // Returns '500';
$app->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}
				}
			}
		}	
		
		return $result;
	}
    
    /**
	 * Method to update starting lineup list
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function updateStaff($post)
	{
	$app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
	$date = Factory::getDate();
    $user = Factory::getUser();
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($post,true).'</pre>'),'Notice');	
// Create a new query object.		
    $db = sportsmanagementHelper::getDBConnection();
    $query = $db->getQuery(true);
    $result = true;
	$positions = $post['staffpositions'];
	$mid = $post['id'];
	$team = $post['team'];
		
    $query->clear();
    $query->select('mp.id');
    $query->from('#__sportsmanagement_match_staff AS mp');
    $query->join('INNER',' #__sportsmanagement_season_team_person_id AS sp ON sp.id = mp.team_staff_id ');
    $query->join('INNER','#__sportsmanagement_season_team_id AS st1 ON st1.team_id = sp.team_id ');
    $query->join('LEFT',' #__sportsmanagement_project_team AS pt ON pt.team_id = st1.id ');
    $query->where('mp.match_id = '.$mid);
    $query->where('pt.id = '.$team);
    $db->setQuery($query);
try{
		$result = $db->loadColumn();
}
catch (Exception $e){
$msg = $e->getMessage(); // Returns "Normally you would have other code...
$code = $e->getCode(); // Returns '500';
$app->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}        
        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' result'.'<pre>'.print_r($result,true).'</pre>' ),'');
        
        if ( $result )
        {
        $query->clear();
        $query->delete(Factory::getDBO()->quoteName('#__sportsmanagement_match_staff'));
        $query->where('id IN ('.implode(",",$result).')');
        try {
            $result = $db->execute();	
            }
catch (Exception $e){
$msg = $e->getMessage(); // Returns "Normally you would have other code...
$code = $e->getCode(); // Returns '500';
$app->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}
        
		
        }
        
		foreach ($positions AS $project_position_id => $pos)
		{
			if (isset($post['staffposition'.$project_position_id]))
			{
				foreach ($post['staffposition'.$project_position_id] AS $ordering => $player_id)
				{
				// Create and populate an object.
$temp = new stdClass();
$temp->match_id = $mid;
$temp->team_staff_id = $player_id;
$temp->project_position_id= $pos->pposid;
$temp->ordering = $ordering;
$temp->modified = $date->toSql();
$temp->modified_by = $user->get('id');
try{					
// Insert the object
$resultquery = $db->insertObject('#__sportsmanagement_match_staff', $temp);    
}
catch (Exception $e){
$msg = $e->getMessage(); // Returns "Normally you would have other code...
$code = $e->getCode(); // Returns '500';
$app->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}
				}
			}
		}

		return true;
	}
    
    
    /**
	 * save the submitted substitution
	 *
	 * @param array $data
	 * @return boolean
	 */
	function savesubstitution($data)
	{
		$app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
	    $date = Factory::getDate();
        $user = Factory::getUser();
        $db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true);
        if ( empty($data['project_position_id'])  )
		{
		$this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_NO_SUBST_POSITION_ID'));
		return false;
		}
        
        if ( empty($data['in_out_time'])  )
		{
		$this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_NO_SUBST_TIME'));
		return false;
		}
        
        if ( empty($data['in'])  )
		{
		$this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_NO_SUBST_IN'));
		return false;
		}
        
        if ( empty($data['out'])  )
		{
		$this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_NO_SUBST_OUT'));
		return false;
		}
        
        if ( (int)$data['in_out_time'] > (int)$data['projecttime'] )
		{
		$this->setError(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_SUBST_TIME_OVER_PROJECTTIME', $data['in_out_time'], $data['projecttime']));
		return false;
		}
        
        if (! ($data['matchid']))
		{
			$this->setError("in: " . $data['in'].
							", out: " . $data['out'].
							", matchid: " . $data['matchid'].
							", project_position_id: " . $data['project_position_id']);
			return false;
		}
		$player_in = (int) $data['in'];
		$player_out = (int) $data['out'];
		$match_id = (int) $data['matchid'];
		$in_out_time = $data['in_out_time'];
		$project_position_id = $data['project_position_id'];

/**
 * nicht anlegen, wenn der wechsel schon existiert
 */
		
        $query->clear();
        $query->select('mp.id');
        $query->from('#__sportsmanagement_match_player as mp');
        $query->where('mp.match_id = '.$match_id);
        $query->where('mp.teamplayer_id = '.$player_in);
        $query->where('mp.in_for = '.$player_out);
        $query->where('mp.in_out_time = '.$in_out_time);
        $query->where('mp.came_in = 1');
        $db->setQuery( $query );
		$substitution_id = $db->loadResult();
        if ( $substitution_id )
        {
            return false;
        }
        
        
        if ( $project_position_id == 0 && $player_in > 0 )
		{
/**
 * retrieve normal position of player getting in
 */
			$query->clear();
            $query->select('pt.project_position_id');
            $query->from('#__sportsmanagement_team_player AS pt');
            $query->where('pt.player_id = '.$player_in);
			$db->setQuery( $query );
			$project_position_id = $db->loadResult();
		}
		if( $player_in > 0 ) {
			$in_player_record = new stdClass();
			$in_player_record->match_id				= $match_id;
			$in_player_record->came_in				= self::MATCH_ROSTER_SUBSTITUTE_IN; //1 //1=came in, 2=went out
			$in_player_record->teamplayer_id		= $player_in;
			$in_player_record->in_for				= ($player_out>0) ? $player_out : 0;
			$in_player_record->in_out_time			= $in_out_time;
			$in_player_record->project_position_id	= $project_position_id;
            $in_player_record->modified = $date->toSql();
            $in_player_record->modified_by = $user->get('id');
/**
 * Insert the object into the table.
 */
            try{
            $resultinsert = $db->insertObject('#__sportsmanagement_match_player', $in_player_record);
            }
            catch (Exception $e)
            {
            $app->enqueueMessage(Text::_(__METHOD__.' '.' '.$e->getMessage()), 'error');
            return false;
            }
		}
		if( $player_out > 0 && $player_in == 0 ) {
			$out_player_record = new stdClass();
			$out_player_record->match_id			= $match_id;
			$out_player_record->came_in				= self::MATCH_ROSTER_SUBSTITUTE_OUT; //2; //0=starting lineup
			$out_player_record->teamplayer_id		= $player_out;
			$out_player_record->in_out_time			= $in_out_time;
			$out_player_record->project_position_id	= $project_position_id;
			$out_player_record->out					= 1;
            $out_player_record->modified = $date->toSql();
            $out_player_record->modified_by = $user->get('id');
/**
 * Insert the object into the table.
 */
            try{
            $resultinsert = $db->insertObject('#__sportsmanagement_match_player', $out_player_record);
            }
            catch (Exception $e)
            {
            $app->enqueueMessage(Text::_(__METHOD__.' '.' '.$e->getMessage()), 'error');
            return false;
            }			
		}
		return true;
	}
    
    /**
	 * remove specified subsitution
	 *
	 * @param int $substitution_id
	 * @return boolean
	 */
	function removeSubstitution($substitution_id)
	{
		$app = Factory::getApplication();
        $db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true);
        /**
         * the subsitute isn't getting in so we delete the substitution
         */        
        $query->clear();
        $query->delete('#__sportsmanagement_match_player');
        $query->where("id = ".$db->Quote($substitution_id). " OR id = ".$db->Quote($substitution_id + 1));
        $db->setQuery($query);
        try{
            $db->execute();
            }
            catch (Exception $e)
            {
            $app->enqueueMessage(Text::_(__METHOD__.' '.Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_ERROR_DELETING_SUBST').' '.$e->getMessage()), 'error');
            return false;
            }
		return true;
	}
    
    
    
    /**
     * sportsmanagementModelMatch::deleteevent()
     * 
     * @param mixed $event_id
     * @return
     */
    function deleteevent($event_id)
	{
	$db = Factory::getDbo();
 
    $query = $db->getQuery(true);
 
// delete all custom keys
    $conditions = array(
    $db->quoteName('id') . '='.$event_id
);	
    $query->delete($db->quoteName('#__sportsmanagement_match_event'));
    $query->where($conditions);
    $db->setQuery($query);
	    
/**
 * Delete the object from the table.
 */
            try{
            $db->execute();
$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	
	return true;
            }
            catch (Exception $e)
            {
	$this->setError('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_DELETE_FAILED_EVENT');
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	
	return false; 
            }	
	    
	    return true;	    

	}
    
    
    /**
     * sportsmanagementModelMatch::deletecommentary()
     * 
     * @param mixed $event_id
     * @return
     */
    function deletecommentary($event_id)
	{
	   $db = Factory::getDbo();
 
$query = $db->getQuery(true);
 
// delete all custom keys
$conditions = array(
    $db->quoteName('id') . '='.$event_id
);
 
$query->delete($db->quoteName('#__sportsmanagement_match_commentary'));
$query->where($conditions);
$db->setQuery($query);
	    
/**
 * Delete the object from the table.
 */
            try{
            $db->execute();
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	
	return true;
            }
            catch (Exception $e)
            {
	$this->setError('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_DELETE_FAILED_COMMENTARY');	
$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	
	return false;    
            }	
	    
	    return true;  

	}
    
    
    /**
     * sportsmanagementModelMatch::savecomment()
     * 
     * @param mixed $data
     * @return
     */
    function savecomment($data)
	{
$date = Factory::getDate();
$user = Factory::getUser();
        // live kommentar speichern
        if ( empty($data['event_time']) )
		{
		$this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_COMMENT_NO_TIME'));
		return false;
		}

        
        if ( empty($data['notes']) )
		{
		$this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_COMMENT_NO_COMMENT'));
		return false;
		}
            
        if ( (int)$data['event_time'] > (int)$data['projecttime'] )
		{
		$this->setError(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_COMMENT_TIME_OVER_PROJECTTIME',$data['event_time'],$data['projecttime']));
		return false;
		}
        
        // Get a db connection.
        $db = Factory::getDbo();
        // Create a new query object.
        $query = $db->getQuery(true);
	$temp = new stdClass(); 
$temp->event_time = $data['event_time'];    
$temp->match_id = $data['match_id'];
$temp->type = $data['type'];
$temp->notes = $data['notes'];
$temp->modified = $date->toSql();
$temp->modified_by = $user->get('id');    
	    
/**
 * Insert the object into the table.
 */
            try{
            $resultinsert = $db->insertObject('#__sportsmanagement_match_commentary', $temp);
		    $result = $db->insertid();
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	
	return $result;
            }
            catch (Exception $e)
            {
	$this->setError('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_DELETE_FAILED_EVENT');	
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	
	return false;   
            }	
	    
	    return true;
	    
        
	}
    
    /**
     * sportsmanagementModelMatch::saveevent()
     * 
     * @param mixed $data
     * @return
     */
    function saveevent($data)
	{
$date = Factory::getDate();
$user = Factory::getUser();
        if ( empty($data['event_time']) )
		{
		$this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_EVENT_NO_TIME'));
		return false;
		}
        
        if ( empty($data['event_sum']) )
		{
		$this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_EVENT_NO_EVENT_SUM'));
		return false;
		}
        
        if ( (int)$data['event_time'] > (int)$data['projecttime'] )
		{
		$this->setError(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_EVENT_TIME_OVER_PROJECTTIME',$data['event_time'],$data['projecttime']));
		return false;
		}
   	
	// Get a db connection.
        $db = Factory::getDbo();
        // Create a new query object.
        $query = $db->getQuery(true);
	    
        $query->clear();
        $query->select('mp.id');
        $query->from('#__sportsmanagement_match_event as mp');
        $query->where('mp.match_id = '.$data['match_id']);
        $query->where('mp.projectteam_id = '.$data['projectteam_id']);
        $query->where('mp.teamplayer_id = '.$data['teamplayer_id']);
        $query->where('mp.event_time = '.$data['event_time']);
        $query->where('mp.event_sum = '.$data['event_sum']);
        $db->setQuery( $query );
	$match_event_id = $db->loadResult();
        if ( $match_event_id )
        {
            return false;
        }
	    
	    $temp = new stdClass();
	    //$object = new stdClass();
$temp->match_id = $data['match_id'];    
$temp->projectteam_id = $data['projectteam_id'];
$temp->teamplayer_id = $data['teamplayer_id'];
$temp->event_time = $data['event_time'];
$temp->event_type_id = $data['event_type_id'];
$temp->event_sum = $data['event_sum'];
$temp->notice = $data['notice'];
$temp->notes = $data['notes'];
$temp->modified = $date->toSql();
$temp->modified_by = $user->get('id');   
/**
 * Insert the object into the table.
 */
try{
$resultinsert = $db->insertObject('#__sportsmanagement_match_event', $temp);
$result = $db->insertid();
/**
 * jetzt schauen wir nach, ob es statistiken zu dem event in der position gibt
 */	
$query->clear();
$query->select('st.id,st.params,st.class');
$query->from('#__sportsmanagement_statistic as st');
$query->join('INNER','#__sportsmanagement_position_statistic AS possta ON st.id = possta.statistic_id');	
$query->join('INNER','#__sportsmanagement_match_player AS matplay ON matplay.project_position_id = possta.position_id');	
$query->where('matplay.match_id = '.$data['match_id']);
$query->where('matplay.teamplayer_id = '.$data['teamplayer_id']);
$db->setQuery($query);
$stats_params = $db->loadObjectList();

foreach( $stats_params as $stats_param )
{
if ( $stats_param->class == 'basic' )
{
$event_param = json_decode($stats_param->params, true);
if ( $event_param['event_id'] == $data['event_type_id'] )
{
$statsvalue = $event_param['event_value'];
$statsid = $stats_param->id;
}
}
}	
/**
 * Überprüfen und anlegen
 */
if ( $statsvalue && $statsid )
{
$query->clear();
$query->select('id');
$query->from('#__sportsmanagement_match_statistic');
$query->where('match_id = '.$data['match_id']);
$query->where('teamplayer_id = '.$data['teamplayer_id']);
$query->where('statistic_id = '.$statsid);
$db->setQuery($query);
$match_stats = $db->loadResult();	
if ( !$match_stats )
{
$temp = new stdClass();//$object = new stdClass();
$temp->match_id = $data['match_id'];
$temp->projectteam_id = $data['projectteam_id'];
$temp->teamplayer_id = $data['teamplayer_id'];
$temp->statistic_id = $statsid;
$temp->value = $statsvalue;
$temp->modified = $date->toSql();
$temp->modified_by = $user->get('id');
$resultinsert = $db->insertObject('#__sportsmanagement_match_statistic', $temp);
}
	
}
	
$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	
return $result;
}
catch (Exception $e)
{
$this->setError('COM_SPORTSMANAGEMENT_ADMIN_MATCH_MODEL_DELETE_FAILED_EVENT');	    
$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	
return false;
}	
	    
	    return true;
	    
	}
    
    
    /**
	 * get match commentary
	 *
	 * @return array
	 */
	public static function getMatchCommentary($match_id)
	{
		$option = Factory::getApplication()->input->getCmd('option');
        $app = Factory::getApplication();
        $starttime = microtime(); 
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, $app->getUserState( "com_sportsmanagement.cfg_which_database", FALSE ) );
        $query = $db->getQuery(true);
        
        $query->select('*');
        $query->from('#__sportsmanagement_match_commentary');
        $query->where('match_id = '.$match_id);
        $query->order('event_time DESC');
        
//        $query = "SELECT *  
//    FROM #__".COM_SPORTSMANAGEMENT_TABLE."_match_commentary
//    WHERE match_id = ".(int)$this->matchid." 
//    ORDER BY event_time DESC";


    $db->setQuery($query);
    
    if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {        
        $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		return $db->loadObjectList();
	}
    
    
    /**
     * sportsmanagementModelMatch::sendEmailtoPlayers()
     * 
     * @return void
     */
    function sendEmailtoPlayers()
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        $mailer = Factory::getMailer();
        $user = Factory::getUser();
        // get settings from com_issuetracker parameters
        $params = ComponentHelper::getParams($option);
        $this->project_id = $app->getUserState( "$option.pid", '0' );
        $mdl = BaseDatabaseModel::getInstance("Project", "sportsmanagementModel");
	    $project = $mdl->getProject($this->project_id);
        
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' project<br><pre>'.print_r($project, true).'</pre><br>','Notice');
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' user<br><pre>'.print_r($user, true).'</pre><br>','Notice');
        
        if ( $project->fav_team )
        {
        $mdl = BaseDatabaseModel::getInstance("TeamPersons", "sportsmanagementModel");
	    $teamplayer = $mdl->getProjectTeamplayers($project->fav_team,$project->season_id);
        }
        
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' teamplayer<br><pre>'.print_r($teamplayer, true).'</pre><br>','Notice');
        
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' _match_time_new<br><pre>'.print_r($this->_match_time_new, true).'</pre><br>','Notice');
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' _match_date_new<br><pre>'.print_r($this->_match_date_new, true).'</pre><br>','Notice');
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' _match_time_old<br><pre>'.print_r($this->_match_time_old, true).'</pre><br>','Notice');
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' _match_date_old<br><pre>'.print_r($this->_match_date_old, true).'</pre><br>','Notice');
        
        foreach ( $teamplayer as $player )
        {
        if( $player->email )
        {
        //add the sender Information.
$sender = array( 
    $user->email,
    $user->name
);
$mailer->setSender($sender); 
//add the recipient. $recipient = $user_email;
$mailer->addRecipient($player->email);
//add the subject
$mailer->setSubject($params->get('match_mail_header', ''));
//add body
//$fcontent = Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_ADMIN_MAIL',$this->_match_date_old,$this->_match_time_old,$this->_match_date_new,$this->_match_time_new,$user->name);
$fcontent = $params->get('match_mail_text', '');

$fcontent = str_replace('[FROMDATE]', $this->_match_date_old, $fcontent);
$fcontent = str_replace('[FROMTIME]', $this->_match_time_old, $fcontent);
$fcontent = str_replace('[TODATE]', $this->_match_date_new, $fcontent);
$fcontent = str_replace('[TOTIME]', $this->_match_time_new, $fcontent);
$fcontent = str_replace('[MAILFROM]', $user->name, $fcontent);

$mailer->setBody($fcontent);
$mailer->isHTML(true); 
$send =& $mailer->Send();
if ( $send !== true ) 
{
    //echo 'Error sending email: ' . $send->message;
    $app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_ADMIN_MAIL_SEND_ERROR',$send->message),'Error');
} 
else 
{
    $app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_ADMIN_MAIL_SEND_SUCCESS',$player->firstname,$player->lastname ),'Notice');
}
    
        }    
            
            
        }
                
        
        
        
    }
    
    
    
    /**
     * sportsmanagementModelMatch::getPressebericht()
     * 
     * @return
     */
    function getPressebericht()
    {
    $option = Factory::getApplication()->input->getCmd('option');
	$app = Factory::getApplication(); 
    //$match_id = $cid[0];
    $match_id = Factory::getApplication()->input->getVar('match_id');
    $this->_id = $match_id;
    //$app->enqueueMessage(Text::_('getPressebericht match_id<br><pre>'.print_r($match_id,true).'</pre>'   ),'');
    $file = JPATH_SITE.DS.'media'.DS.'com_sportsmanagement'.DS.'pressebericht'.DS.$match_id.'.jlg';   
    //$file = JPATH_SITE.DS.'tmp'.DS.'pressebericht.jlg';
    $app->enqueueMessage(Text::_('datei = '.$file),'');
    // Where the cache will be stored
    $dcsv['file'] = $file;
//$dcsv['cachefile']	= dirname(__FILE__).'/tmp/'.md5($dcsv['file']);
$dcsv['cachefile'] = JPATH_SITE.DS.'/tmp/'.md5($dcsv['file']);

// If there is no chache saved or is older than the cache time create a new cache
	// open the cache file for writing
	$fp = fopen($dcsv['cachefile'], 'w');
	// save the contents of output buffer to the file
	fwrite($fp, file_get_contents($dcsv['file']));
	// close the file
	fclose($fp);

// New ParseCSV object.
$csv = new JSMparseCSV();
//$csv->encoding('UTF-8', 'UTF-8');
// Parse CSV with auto delimiter detection
$csv->auto($dcsv['cachefile']);

//$app->enqueueMessage(Text::_('getPressebericht csv<br><pre>'.print_r($csv,true).'</pre>'   ),'');
    //$app->enqueueMessage(Text::_('getPressebericht csv->data<br><pre>'.print_r($csv->data,true).'</pre>'   ),'');
    
    /*
    # tab delimited, and encoding conversion
	$csv = new JSMparseCSV();
	//$csv->encoding('UTF-16', 'UTF-8');
	//$csv->delimiter = ";";
    $csv->auto($file);
    //$csv->parse($file);
    $app->enqueueMessage(Text::_('getPressebericht csv<br><pre>'.print_r($csv,true).'</pre>'   ),'');
    $app->enqueueMessage(Text::_('getPressebericht csv->data<br><pre>'.print_r($csv->data,true).'</pre>'   ),'');
    */

   return $csv;
    }
    
    /**
     * sportsmanagementModelMatch::getPresseberichtMatchnumber()
     * 
     * @param mixed $csv_file
     * @return
     */
    function getPresseberichtMatchnumber($csv_file)
    {
    $option = Factory::getApplication()->input->getCmd('option');
	$app = Factory::getApplication();  
    $match_id = Factory::getApplication()->input->getVar('match_id');  
    $tblmatch = Table::getInstance("match", "sportsmanagementTable");
    $tblmatch->load($match_id);
    $match_number = $tblmatch->match_number;
    //$app->enqueueMessage(Text::_('getPresseberichtMatchnumber match number<br><pre>'.print_r($match_number,true).'</pre>'   ),'');
    $csv_match_number = $csv_file->data[0]['Spielberichtsnummer'];
    //$app->enqueueMessage(Text::_('getPresseberichtMatchnumber csv match number<br><pre>'.print_r($csv_match_number,true).'</pre>'   ),'');
    $teile = explode(".",$csv_match_number);
    
    if ( $match_number != $teile[0] )
    {
        $app->enqueueMessage(Text::_('Spielnummer der Datei passt nicht zur Spielnummer im Projekt.'),'Error');
        return false;
    }
    else
    {
        $app->enqueueMessage(Text::_('Spielnummern sind identisch. Datei wird verarbeitet'),'Notice');
        return true;
    }
    
    
    
    }
    
    /**
     * sportsmanagementModelMatch::getPresseberichtReadPlayers()
     * 
     * @param mixed $csv_file
     * @return void
     */
    function getPresseberichtReadPlayers($csv_file)
    {
    $option = Factory::getApplication()->input->getCmd('option');
$app = Factory::getApplication(); 
$db = sportsmanagementHelper::getDBConnection();
    $query = $db->getQuery(true);
    
    $csv_player_count = 40;
    $project_id = $app->getUserState( "$option.pid", '0' ); 
    $match_id = Factory::getApplication()->input->getVar('match_id');    
    $tblmatch = Table::getInstance("match", "sportsmanagementTable");
    $tblmatch->load($match_id);
    $tblproject = Table::getInstance("project", "sportsmanagementTable");
    $tblproject->load($project_id);
    $favteam = $tblproject->fav_team;
	$season_id = $tblproject->season_id;

for($a=0; $a < sizeof($csv_file->titles); $a++ )
    {
$csv_file->titles[$a] = utf8_encode ( $csv_file->titles[$a] );
    }

foreach( $csv_file->data as $key => $key2 )
{
foreach( $key2 as $key3 => $value )
{
$key3 = utf8_encode ($key3);
$key4[$key3] = utf8_encode ($value );
}
$csv_file->data[$key] = $key4;
}
	    
    if ( !$favteam )
    {
$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_PRESSEBERICHT_NO_FAV_TEAM'),'error');
return false;	    
    }
	    
    $tblteam = Table::getInstance("team", "sportsmanagementTable");
    $tblteam->load($favteam);
    
    // Select some fields
    $query->clear();
    $query->select('pt1.id');
    // From the table
$query->from('#__sportsmanagement_project_team as pt1');
$query->join('INNER','#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
$query->join('INNER','#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
    $query->where('pt1.project_id = '.$project_id);  
    $query->where('st1.team_id = '.$favteam);   
        
			$db->setQuery($query);
try{
	    $projectteamid = $db->loadResult();
}
catch (Exception $e){
$msg = $e->getMessage(); // Returns "Normally you would have other code...
$code = $e->getCode(); // Returns '500';
$app->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'error');	
}	
    

    
    if ( $projectteamid )
    {
    $this->projectteamid = $projectteamid;
    $app->enqueueMessage(Text::_('Spieldetails von '.$tblteam->name.' werden verarbeitet.'),'Notice');
    if ( $projectteamid == $tblmatch->projectteam1_id )
    {
        $app->enqueueMessage(Text::_('Heimteam '.$tblteam->name.' wird verarbeitet.'),'Notice');
        $find_csv = 'H';
    }
    elseif ( $projectteamid == $tblmatch->projectteam2_id )
    {
        $app->enqueueMessage(Text::_('Auswärtsteam '.$tblteam->name.' wird verarbeitet.'),'Notice');
        $find_csv = 'G';
    }        
    
    // spieler aufbereiten startelf
    for($a=1; $a <= $csv_player_count; $a++ )
    {
        if ( isset($csv_file->data[0][$find_csv.'-S'.$a.'-Nr']) && !empty($csv_file->data[0][$find_csv.'-S'.$a.'-Nr'])  )
        {
        
        if ( !isset($this->csv_player[$csv_file->data[0][$find_csv.'-S'.$a.'-Nr']]) )
        {
        $this->csv_player[$csv_file->data[0][$find_csv.'-S'.$a.'-Nr']] = new stdClass();
        }
        
        
        $this->csv_player[$csv_file->data[0][$find_csv.'-S'.$a.'-Nr']]->nummer = $csv_file->data[0][$find_csv.'-S'.$a.'-Nr'];
        $this->csv_player[$csv_file->data[0][$find_csv.'-S'.$a.'-Nr']]->name = $csv_file->data[0][$find_csv.'-S'.$a.'-Spieler'];
        $this->csv_player[$csv_file->data[0][$find_csv.'-S'.$a.'-Nr']]->hinweis = $csv_file->data[0][$find_csv.'-S'.$a.'-Hinweis'];
        $this->csv_player[$csv_file->data[0][$find_csv.'-S'.$a.'-Nr']]->status = $csv_file->data[0][$find_csv.'-S'.$a.'-Status'];
        
        $teile = explode(",",$csv_file->data[0][$find_csv.'-S'.$a.'-Spieler']);
        $this->csv_player[$csv_file->data[0][$find_csv.'-S'.$a.'-Nr']]->lastname = htmlspecialchars(trim($teile[0]), ENT_QUOTES);
        $this->csv_player[$csv_file->data[0][$find_csv.'-S'.$a.'-Nr']]->firstname = htmlspecialchars(trim($teile[1]), ENT_QUOTES);
        $this->csv_player[$csv_file->data[0][$find_csv.'-S'.$a.'-Nr']]->person_id = 0;
        $this->csv_player[$csv_file->data[0][$find_csv.'-S'.$a.'-Nr']]->project_person_id = 0;
        $this->csv_player[$csv_file->data[0][$find_csv.'-S'.$a.'-Nr']]->project_position_id = 0;
        $this->csv_player[$csv_file->data[0][$find_csv.'-S'.$a.'-Nr']]->position_id = 0;
        $this->csv_player[$csv_file->data[0][$find_csv.'-S'.$a.'-Nr']]->startaufstellung = 1;
        // gibt es den spieler
        // Select some fields
        if ( $teile[0] )
    {
$person_id = $this->getPersonId($teile[1], $teile[0]);
    }
    
        if ( $person_id )
        {
            if ( !isset($this->csv_player[$csv_file->data[0][$find_csv.'-S'.$a.'-Nr']]) )
            {
            $this->csv_player[$csv_file->data[0][$find_csv.'-S'.$a.'-Nr']] = new stdClass();
            }
        
            $this->csv_player[$csv_file->data[0][$find_csv.'-S'.$a.'-Nr']]->person_id = $person_id;
            $projectpersonid = $this->getSeasonTeamPersonId($person_id, $favteam, $season_id );
            $this->csv_player[$csv_file->data[0][$find_csv.'-S'.$a.'-Nr']]->project_person_id = $projectpersonid->id;
            $this->csv_player[$csv_file->data[0][$find_csv.'-S'.$a.'-Nr']]->project_position_id = $projectpersonid->project_position_id;
            $this->csv_player[$csv_file->data[0][$find_csv.'-S'.$a.'-Nr']]->position_id = $projectpersonid->position_id;
        }
        
        }
    }
    // spieler aufbereiten ersatzbank
    for($a=1; $a <= $csv_player_count; $a++ )
    {
        if ( isset($csv_file->data[0][$find_csv.'-A'.$a.'-Nr']) && !empty($csv_file->data[0][$find_csv.'-A'.$a.'-Nr'])  )
        {
        
        if ( !isset($this->csv_player[$csv_file->data[0][$find_csv.'-A'.$a.'-Nr']]) )
        {
        $this->csv_player[$csv_file->data[0][$find_csv.'-A'.$a.'-Nr']] = new stdClass();
        }
        
        $this->csv_player[$csv_file->data[0][$find_csv.'-A'.$a.'-Nr']]->nummer = $csv_file->data[0][$find_csv.'-A'.$a.'-Nr'];
        $this->csv_player[$csv_file->data[0][$find_csv.'-A'.$a.'-Nr']]->name = $csv_file->data[0][$find_csv.'-A'.$a.'-Spieler'];
        $this->csv_player[$csv_file->data[0][$find_csv.'-A'.$a.'-Nr']]->hinweis = $csv_file->data[0][$find_csv.'-A'.$a.'-Hinweis'];
        $this->csv_player[$csv_file->data[0][$find_csv.'-A'.$a.'-Nr']]->status = $csv_file->data[0][$find_csv.'-A'.$a.'-Status'];
        
        $teile = explode(",",$csv_file->data[0][$find_csv.'-A'.$a.'-Spieler']);
        $this->csv_player[$csv_file->data[0][$find_csv.'-A'.$a.'-Nr']]->lastname = htmlspecialchars(trim($teile[0]), ENT_QUOTES);
        $this->csv_player[$csv_file->data[0][$find_csv.'-A'.$a.'-Nr']]->firstname = htmlspecialchars(trim($teile[1]), ENT_QUOTES);
        $this->csv_player[$csv_file->data[0][$find_csv.'-A'.$a.'-Nr']]->person_id = 0;
        $this->csv_player[$csv_file->data[0][$find_csv.'-A'.$a.'-Nr']]->project_person_id = 0;
        $this->csv_player[$csv_file->data[0][$find_csv.'-A'.$a.'-Nr']]->project_position_id = 0;
        $this->csv_player[$csv_file->data[0][$find_csv.'-A'.$a.'-Nr']]->position_id = 0;
        $this->csv_player[$csv_file->data[0][$find_csv.'-A'.$a.'-Nr']]->startaufstellung = 0;
        // gibt es den spieler ?
        // Select some fields
        if ( $teile[0] )
    {
$person_id = $this->getPersonId($teile[1], $teile[0]);
    }
        if ( $person_id )
        {
            if ( !isset($this->csv_player[$csv_file->data[0][$find_csv.'-A'.$a.'-Nr']]) )
            {
            $this->csv_player[$csv_file->data[0][$find_csv.'-A'.$a.'-Nr']] = new stdClass();
            }
            $this->csv_player[$csv_file->data[0][$find_csv.'-A'.$a.'-Nr']]->person_id = $person_id;
            $projectpersonid = $this->getSeasonTeamPersonId($person_id, $favteam, $season_id );
            $this->csv_player[$csv_file->data[0][$find_csv.'-A'.$a.'-Nr']]->project_person_id = $projectpersonid->id;
            $this->csv_player[$csv_file->data[0][$find_csv.'-A'.$a.'-Nr']]->project_position_id = $projectpersonid->project_position_id;
            $this->csv_player[$csv_file->data[0][$find_csv.'-A'.$a.'-Nr']]->position_id = $projectpersonid->position_id;
        }
        
        }
    }
    
    // jetzt kommen die wechsel
    for($a=1; $a <= $csv_player_count; $a++ )
    {
        if ( isset($csv_file->data[0][$find_csv.'-S'.$a.'-Ausw-Zeit']) && !empty($csv_file->data[0][$find_csv.'-S'.$a.'-Ausw-Zeit'])  )
        {
            if ( !isset($this->csv_in_out[$a]) )
            {
            $this->csv_in_out[$a] = new stdClass();
            }
            $this->csv_in_out[$a]->in_out_time = $csv_file->data[0][$find_csv.'-S'.$a.'-Ausw-Zeit'];
            $this->csv_in_out[$a]->came_in = 1;
            $this->csv_in_out[$a]->in = $csv_file->data[0][$find_csv.'-S'.$a.'-Ausw-Nr'];
            $this->csv_in_out[$a]->out = $csv_file->data[0][$find_csv.'-S'.$a.'-Ausw-Für Nr'];
            $this->csv_in_out[$a]->spieler = $csv_file->data[0][$find_csv.'-S'.$a.'-Ausw-Spieler'];
            $this->csv_in_out[$a]->spielerout = $csv_file->data[0][$find_csv.'-S'.$a.'-Ausw-für-Spieler'];
        }
    }
    
    // jetzt kommen die gelben karten
    for($a=1; $a <= $csv_player_count; $a++ )
    {

        if ( isset($csv_file->data[0][$find_csv.'-S'.$a.'-Gelb-Zeit']) && !empty($csv_file->data[0][$find_csv.'-S'.$a.'-Gelb-Zeit'])  )
        {
            if ( !isset($this->csv_cards[$a]) )
            {
            $this->csv_cards[$a] = new stdClass();
            }
            $this->csv_cards[$a]->event_time = $csv_file->data[0][$find_csv.'-S'.$a.'-Gelb-Zeit'];
            $this->csv_cards[$a]->event_name = 'Gelbe-Karte';
            $this->csv_cards[$a]->event_sum = 1;
            $this->csv_cards[$a]->spielernummer = $csv_file->data[0][$find_csv.'-S'.$a.'-Gelb-Nr'];
            $this->csv_cards[$a]->spieler = $csv_file->data[0][$find_csv.'-S'.$a.'-Gelb-Spieler'];
            $this->csv_cards[$a]->notice = $csv_file->data[0][$find_csv.'-S'.$a.'-Gelb-Grund'];
            $this->csv_cards[$a]->event_type_id = 0;
        }

    }
    
    // jetzt kommen die gelb-roten karten
    $start = sizeof($this->csv_cards) + 1;
    //$app->enqueueMessage(Text::_('getPresseberichtReadPlayers start gelb rote karten<br><pre>'.print_r($start,true).'</pre>'   ),'');
    
    for($b=1; $b <= $csv_player_count; $b++ )
    {

        if ( isset($csv_file->data[0][$find_csv.'-S'.$b.'-Gelbrot-Zeit']) && !empty($csv_file->data[0][$find_csv.'-S'.$b.'-Gelbrot-Zeit'])  )
        {
            $this->csv_cards[$start]->event_time = $csv_file->data[0][$find_csv.'-S'.$b.'-Gelbrot-Zeit'];
            $this->csv_cards[$start]->event_name = 'Gelbrot-Karte';
            $this->csv_cards[$start]->event_sum = 1;
            $this->csv_cards[$start]->spielernummer = $csv_file->data[0][$find_csv.'-S'.$b.'-Gelbrot-Nr'];
            $this->csv_cards[$start]->spieler = $csv_file->data[0][$find_csv.'-S'.$b.'-Gelbrot-Spieler'];
            $this->csv_cards[$start]->notice = $csv_file->data[0][$find_csv.'-S'.$b.'-Gelbrot-Grund'];
            $this->csv_cards[$start]->event_type_id = 0;
            $start++;
        }

    }
    
    // gibt es die karten schon ?
    // gibt es das event schon ?
    foreach ( $this->csv_cards as $key => $value )
    {
    $spielernummer = $value->spielernummer;
    if ( !isset($this->csv_player[$spielernummer]) )
    {
    $this->csv_player[$spielernummer] = new stdClass();
    }
    $project_person_id = $this->csv_player[$spielernummer]->project_person_id;
    if ( $project_person_id )
    {
    	$query->clear();
        $query->select('event_type_id');
        // From the table
		$query->from('#__sportsmanagement_match_event');
        $query->where('match_id = ' . $match_id );
        $query->where('projectteam_id = ' . $projectteamid );
        $query->where('teamplayer_id = ' . $project_person_id );
        
		$db->setQuery($query);
	    try{
		$match_event_id = $db->loadResult();
	    }
catch (Exception $e){
$msg = $e->getMessage(); // Returns "Normally you would have other code...
$code = $e->getCode(); // Returns '500';
$app->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'error');	
}	
        $this->csv_cards[$key]->event_type_id = $match_event_id;
    }    
    }

        // Sämtliche Mannschaftsverantwortlichen verarbeiten
        $i = 1;
        $mannschaftsverantwortlichePositionen = array('Trainer', 'Trainerassistent', 'Arzt', 'Masseur', 'Zeugwart', 'Mannschaftsverantwortlicher');
        foreach ($mannschaftsverantwortlichePositionen as $mannschaftsverantwortlichePosition) {
            if (!isset($this->csv_staff[$i])) {
                $this->csv_staff[$i] = new stdClass();
            }
            $this->csv_staff[$i]->position = $mannschaftsverantwortlichePosition;
            $this->csv_staff[$i]->name = $csv_file->data[0][$find_csv . '-' . $mannschaftsverantwortlichePosition];
            $teile = explode(" ", $this->csv_staff[$i]->name);
            $firstname = htmlspecialchars(trim($teile[0]), ENT_QUOTES);
            $lastname = htmlspecialchars(trim($teile[1]), ENT_QUOTES);
            $this->csv_staff[$i]->lastname = $lastname;
            $this->csv_staff[$i]->firstname = $firstname;
            $this->csv_staff[$i]->person_id = 0;
            $this->csv_staff[$i]->project_person_id = 0;
            $this->csv_staff[$i]->project_position_id = 0;
            $this->csv_staff[$i]->position_id = 0;

            // Falls es den Staff gibt, ein paar Felder selektieren
            if ($lastname) {
                $person_id = $this->getPersonId($firstname, $lastname);

                if ($person_id) {
                    $this->csv_staff[$i]->person_id = $person_id;
                    $projectpersonid = $this->getSeasonTeamPersonId($person_id, $favteam, $season_id);
                    $this->csv_staff[$i]->project_person_id = $projectpersonid->id;
                    $this->csv_staff[$i]->project_position_id = $projectpersonid->project_position_id;
                    $this->csv_staff[$i]->position_id = $projectpersonid->position_id;
                }
            }

            $i++;
        }
   
    
    }
    
    $app->setUserState($option.'csv_staff',$this->csv_staff);
    $app->setUserState($option.'csv_cards',$this->csv_cards);
    $app->setUserState($option.'csv_in_out',$this->csv_in_out);
    $app->setUserState($option.'csv_player',$this->csv_player);
    $app->setUserState($option.'projectteamid',$projectteamid);
    
    //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' csv_staff<br><pre>'.print_r($this->csv_staff,true).'</pre>'   ),'');
    
    }


/**
 * sportsmanagementModelMatch::getPersonId()
 * 
 * @param string $firstname
 * @param string $lastname
 * @return void
 */
function getPersonId($firstname='', $lastname='')
{
// Reference global application object
$app = Factory::getApplication();    
// Get a db connection.
$db = sportsmanagementHelper::getDBConnection();
$person_id = 0;
// Create a new query object.
$query = $db->getQuery(true);        
$query->select('id');
// From the table
$query->from('#__sportsmanagement_person');
$query->where('firstname LIKE ' . $db->Quote( '' . trim($firstname) . '' ) );
$query->where('lastname LIKE ' . $db->Quote( '' . trim($lastname) . '' ) );
$db->setQuery($query);
try{
$person_id = $db->loadResult();
}
catch (Exception $e){
$msg = $e->getMessage(); // Returns "Normally you would have other code...
$code = $e->getCode(); // Returns '500';
$app->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'error');
}    

return $person_id;    
    
}


/**
 * sportsmanagementModelMatch::getSeasonTeamPersonId()
 * 
 * @param integer $person_id
 * @param integer $favteam
 * @param mixed $season_id
 * @return void
 */
function getSeasonTeamPersonId($person_id=0,$favteam=0,$season_id)
{
// Reference global application object
$app = Factory::getApplication();    
// Get a db connection.
$db = sportsmanagementHelper::getDBConnection();
// Create a new query object.
$query = $db->getQuery(true);    
$query->select('id,project_position_id');
// From the table
$query->from('#__sportsmanagement_season_team_person_id');
$query->where('person_id = ' . $person_id );
$query->where('team_id = ' . $favteam);
$query->where('season_id = ' . $season_id );
            
$db->setQuery($query);
try{
$projectpersonid = $db->loadObject();
}
catch (Exception $e){
$msg = $e->getMessage(); // Returns "Normally you would have other code...
$code = $e->getCode(); // Returns '500';
$app->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'error');
$projectpersonid = new stdClass();	
$projectpersonid->id = 0;
$projectpersonid->project_position_id = 0;
}    

if ( !$projectpersonid )
{
$projectpersonid = new stdClass();	
$projectpersonid->id = 0;
$projectpersonid->project_position_id = 0;    
}

$query->clear('');
$query->select('position_id');
$query->from('#__sportsmanagement_project_position');
$query->where('id = ' . $projectpersonid->project_position_id );
$db->setQuery($query);
$projectpersonid->position_id = $db->loadResult();
    
return $projectpersonid;    
    
}


         
/**
 * sportsmanagementModelMatch::savePressebericht()
 * 
 * @return void
 */
function savePressebericht($post = NULL)
{
// Reference global application object
$app = Factory::getApplication();
$user = Factory::getUser();
$date = Factory::getDate();
// JInput object
$jinput = $app->input;
$option = $jinput->getCmd('option');
$project_id = $app->getUserState( "$option.pid", '0' );
//$post = $app->input->post->get('post');	
$projectteamid = $app->getUserState($option.'projectteamid');	
$match_id = $app->input->getVar('match_id');
$project_position_id = $post['project_position_id'];
$project_staff_position_id = $post['project_staff_position_id'];
$inout_position_id = $post['inout_position_id'];
$project_events_id = $post['project_events_id'];
$startaufstellung = $post['startaufstellung'];
$playerprojectpersonid = $post['playerprojectpersonid'];
$playernumber = $post['player'];
$playerfirstname = $post['playerfirstname'];
$playerlastname = $post['playerlastname'];
$playerpersonid = $post['playerpersonid'];

$season_id = $post['season_id'];
$fav_team = $post['fav_team'];
$projectteamid = $post['projectteamid'];


$this->csv_staff = $app->getUserState($option.'csv_staff');
$this->csv_cards = $app->getUserState($option.'csv_cards');
$this->csv_in_out = $app->getUserState($option.'csv_in_out');
$this->csv_player = $app->getUserState($option.'csv_player');

//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' ' .  ' staff<br><pre>'.print_r($this->csv_staff,true).'</pre>'),'notice');
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' ' .  ' cards<br><pre>'.print_r($this->csv_cards,true).'</pre>'),'notice');
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' ' .  ' in out<br><pre>'.print_r($this->csv_in_out,true).'</pre>'),'notice');
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' ' .  ' player<br><pre>'.print_r($this->csv_player,true).'</pre>'),'notice');
	
$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' ' .  ' post<br><pre>'.print_r($post,true).'</pre>'),'error');
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' ' .  ' project_position_id<br><pre>'.print_r($project_position_id,true).'</pre>'),'error');
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' ' .  ' project_staff_position_id<br><pre>'.print_r($project_staff_position_id,true).'</pre>'),'error');
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' ' .  ' inout_position_id<br><pre>'.print_r($inout_position_id,true).'</pre>'),'error');
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' ' .  ' project_events_id<br><pre>'.print_r($project_events_id,true).'</pre>'),'error');
	
// Get a db connection.
$db = sportsmanagementHelper::getDBConnection();
// Create a new query object.
$query = $db->getQuery(true);
$query->clear();

$my_text = '';	

$query->clear();
$query->select('*');
$query->from('#__sportsmanagement_project_position');
$query->where('project_id = '.$project_id);
$db->setQuery( $query );
$result_pro_position = $db->loadAssocList('position_id');
$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' ' .  ' position<br><pre>'.print_r($result_pro_position,true).'</pre>'),'error');


foreach ( $playerlastname as $key => $value )
{

// hat der spieler schon eine personid
if ( !$playerpersonid[$key] )
{
$temp = new stdClass();
$temp->firstname = $playerfirstname[$key];
$temp->lastname = $playerlastname[$key];
$temp->alias = JFilterOutput::stringURLSafe( $temp->firstname.' '.$temp->lastname );
$position_id = $project_position_id[$key];
$temp->position_id = $position_id;
$temp->notes = ' ';
$temp->email = ' ';
$temp->website = ' ';
$temp->published = 1;

$temp->injury_date = -1;
$temp->injury_end = -1;
$temp->injury_detail = ' ';

$temp->suspension_date = -1;
$temp->suspension_end = -1;
$temp->suspension_detail = ' ';

$temp->away_date = -1;
$temp->away_end = -1;
$temp->away_detail = ' ';


// Insert the object into the table.
try {
$result = $db->insertObject('#__sportsmanagement_person', $temp);
$newpersonid = $db->insertid();       
}
catch (Exception $e){
$app->enqueueMessage(__METHOD__.' '.__LINE__.' '. Text::_($e->getMessage()),'Error');
$app->enqueueMessage(__METHOD__.' '.__LINE__.' '. Text::_($e->getCode()),'Error');
}                
                    
}
else
{
$newpersonid = $playerpersonid[$key];  
$position_id = $project_position_id[$key];   
}

if ( $position_id )
{
// zuordnung season personid
// Create a new query object.
$insertquery = $db->getQuery(true);
// Insert columns.
$columns = array('person_id','season_id','persontype','position_id');
// Insert values.
$values = array($newpersonid,$season_id,1,$position_id);
// Prepare the insert query.
$insertquery
->insert($db->quoteName('#__sportsmanagement_season_person_id'))
->columns($db->quoteName($columns))
->values(implode(',', $values));
try {
// Set the query using our newly populated query object and execute it.
$db->setQuery($insertquery);
$db->execute();
}
catch (Exception $e){
$app->enqueueMessage(__METHOD__.' '.__LINE__.' '. Text::_($e->getMessage()),'Error');
$app->enqueueMessage(__METHOD__.' '.__LINE__.' '. Text::_($e->getCode()),'Error');
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($e,true).'</pre>'),'Error');
} 
}

if ( $position_id  && $newpersonid )
{
$position_id = $result_pro_position[$position_id]['id'];
// zuordnung season team personid
$jerseynumber = $playernumber[$key];
// Create a new query object.
$insertquery = $db->getQuery(true);
// Insert columns.
$columns = array('person_id','season_id','team_id','persontype','published','project_position_id','jerseynumber','position_id','notes');
// Insert values.
$values = array($newpersonid,$season_id,$fav_team,1,1,$position_id,$jerseynumber,$position_id,'\''.' '.'\'');
// Prepare the insert query.
$insertquery
->insert($db->quoteName('#__sportsmanagement_season_team_person_id'))
->columns($db->quoteName($columns))
->values(implode(',', $values));
try {
// Set the query using our newly populated query object and execute it.
$db->setQuery($insertquery);
$db->execute();
$new_season_team_person_id = $db->insertid();
$playerprojectpersonid[$key] = $new_season_team_person_id;
}
catch (Exception $e){
$app->enqueueMessage(__METHOD__.' '.__LINE__.' '. Text::_($e->getMessage()),'Error');
$app->enqueueMessage(__METHOD__.' '.__LINE__.' '. Text::_($e->getCode()),'Error');
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($e,true).'</pre>'),'Error');
$new_season_team_person_id = 0;
} 

//$playerprojectpersonid[$key] = $new_season_team_person_id;
}

if ( $position_id && $newpersonid )
{
$position_id = $result_pro_position[$position_id]['id'];
// zuordnung season personid
// Create a new query object.
$insertquery = $db->getQuery(true);
// Insert columns.
$columns = array('person_id','project_id','persontype','project_position_id');
// Insert values.
$values = array($newpersonid,$project_id,1,$position_id);
// Prepare the insert query.
$insertquery
->insert($db->quoteName('#__sportsmanagement_person_project_position'))
->columns($db->quoteName($columns))
->values(implode(',', $values));
try {
// Set the query using our newly populated query object and execute it.
$db->setQuery($insertquery);
$db->execute();
}
catch (Exception $e){
$app->enqueueMessage(__METHOD__.' '.__LINE__.' '. Text::_($e->getMessage()),'Error');
$app->enqueueMessage(__METHOD__.' '.__LINE__.' '. Text::_($e->getCode()),'Error');
//$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($e,true).'</pre>'),'Error');
} 
}

// startaufstellung
$position_id = $project_position_id[$key];
$start = $startaufstellung[$key];
$projectpersonid = $playerprojectpersonid[$key];

//$app->enqueueMessage(__METHOD__.' '.__LINE__.' schlüssel '. Text::_($key),'');
//$app->enqueueMessage(__METHOD__.' '.__LINE__.' start '. Text::_($start),'');
//$app->enqueueMessage(__METHOD__.' '.__LINE__.' position_id '. Text::_($position_id),'');
//$app->enqueueMessage(__METHOD__.' '.__LINE__.' projectpersonid '. Text::_($projectpersonid),'');


$query->clear();
$query->select('*');
$query->from('#__sportsmanagement_match_player');
$query->where('match_id = '.$match_id);
$query->where('teamplayer_id = '.$projectpersonid);
$query->where('project_position_id = '.$position_id);
$db->setQuery( $query );
$result = $db->loadResult();

if ( $start && $position_id && $projectpersonid && !$result )
{
$temp = new stdClass();
$temp->match_id = $match_id;
$temp->teamplayer_id = $projectpersonid;
$temp->project_position_id = $position_id;
$temp->modified = $date->toSql();
$temp->modified_by = $user->id;
// Insert the object into the table.
try {
$result = $db->insertObject('#__sportsmanagement_match_player', $temp);
//$newpersonid = $db->insertid();       
}
catch (Exception $e){
$app->enqueueMessage(__METHOD__.' '.__LINE__.' '. Text::_($e->getMessage()),'Error');
$app->enqueueMessage(__METHOD__.' '.__LINE__.' '. Text::_($e->getCode()),'Error');
}      
}

	// Auswechslungen verarbeiten (aber nur wenn der Spieler nicht in der Startaufstellung stand)
    if (!$start) {
        foreach ($this->csv_in_out as $auswechslungKey => $auswechslungObject) {
            // Überprüfen ob der eingewechselte Spieler zum aktuellen Spieler passt (Nachname + Nummer muss stimmen)
            $jerseynumber = $playernumber[$key];
            if ($auswechslungObject->spieler == $value && $auswechslungObject->in == $jerseynumber) {

                // Hat der Benutzer eine Position beim Import ausgewählt? Wenn nicht wird die selbe Position verwendet, wie der ausgewechselte Spieler
                $inOutPositionId = $inout_position_id[$auswechslungObject->in];
                if (!$inOutPositionId) {
                    $inOutPositionId = $position_id;
                }

                // Gibt es diesen Spieler schon?
                $query->clear();
                $query->select('*');
                $query->from('#__sportsmanagement_match_player');
                $query->where('match_id = ' . $match_id);
                $query->where('teamplayer_id = ' . $projectpersonid);
                $query->where('project_position_id = ' . $inOutPositionId);
                $db->setQuery($query);
                $result = $db->loadResult();

                if (!$result) {

                    // Die ID des ausgewechselten Spielers suchen (Nachname + Nummer muss stimmen)
                    foreach ($playerlastname as $outPlayerKey => $outPlayerValue) {
                        if ($playernumber[$outPlayerKey] == $auswechslungObject->out && $outPlayerValue == $auswechslungObject->spielerout) {
                            $outTeamplayerId = $playerprojectpersonid[$outPlayerKey];
                        }
                    }

                    // Haben wir den auszuwechselden Spieler gefunden können wir ihn einarbeiten
                    if ($outTeamplayerId) {
                        $temp = new stdClass();
                        $temp->match_id = $match_id;
                        $temp->teamplayer_id = $projectpersonid;
                        $temp->project_position_id = $inOutPositionId;
                        $temp->came_in = $auswechslungObject->came_in;
                        $temp->in_for = $outTeamplayerId;
                        $temp->in_out_time = $auswechslungObject->in_out_time;
                        $temp->modified = $date->toSql();
                        $temp->modified_by = $user->id;

                        try {
                            $result = $db->insertObject('#__sportsmanagement_match_player', $temp);
                        } catch (Exception $e) {
                            $app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getMessage()), 'Error');
                            $app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getCode()), 'Error');
                        }
                    }
                }
            }
        }
    }

	// Events für den aktuellen Spieler verarbeiten
    foreach ($this->csv_cards as $cardIndex => $cardValue) {
        // Überprüfen ob das Event zum aktuellen Spieler passt (Nachname + Nummer muss stimmen)
        $jerseynumber = $playernumber[$key];
        if ($cardValue->spieler == $value && $cardValue->spielernummer == $jerseynumber) {
            // Hat der User ein ProjectEvent ausgewählt? Wenn nicht können wir den Datensatz nicht verarbeiten
            $projectEventTypeId = $project_events_id[$cardIndex - 1];
            if ($projectEventTypeId) {
                // Überprüfen, ob bereits das Event vorhanden ist. Falls ja, müssen wir es nicht nochmals einarbeiten
                $query->clear();
                $query->select('*');
                $query->from('#__sportsmanagement_match_event');
                $query->where('match_id = ' . $match_id);
                $query->where('projectteam_id = ' . $projectteamid);
                $query->where('teamplayer_id = ' . $projectpersonid);
                $query->where('event_time = ' . $cardValue->event_time);
                $query->where('event_type_id = ' . $projectEventTypeId);
                $db->setQuery($query);
                $result = $db->loadResult();
                if (!$result) {
                    // Das Event ist noch nicht vorhanden, deswegen speichern wir es in der Datenbank
                    $temp = new stdClass();
                    $temp->match_id = $match_id;
                    $temp->projectteam_id = $projectteamid;
                    $temp->teamplayer_id = $projectpersonid;
                    $temp->event_time = $cardValue->event_time;
                    $temp->event_type_id = $projectEventTypeId;
                    $temp->event_sum = 1;
                    $temp->notice = $cardValue->notice;
                    $temp->modified = $date->toSql();
                    $temp->modified_by = $user->id;
                    $temp->published = 1;
                    try {
                        $result = $db->insertObject('#__sportsmanagement_match_event', $temp);
                    } catch (Exception $e) {
                        $app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getMessage()), 'Error');
                        $app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . Text::_($e->getCode()), 'Error');
                    }
                } // End if existing entry in db exists
            } // End if event type was selected
        } // End if is actual player
    } // End event foreach

}











	
	
$this->_success_text['Importing general Person data:'] = $my_text;
	
	
	
}
	
	
}
?>
