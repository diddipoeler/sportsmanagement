<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
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
class sportsmanagementModelmatchgooglecalendar extends JSMModelAdmin
{

	const MATCH_ROSTER_STARTER			= 0;
	const MATCH_ROSTER_SUBSTITUTE_IN	= 1;
	const MATCH_ROSTER_SUBSTITUTE_OUT	= 2;
	const MATCH_ROSTER_RESERVE			= 3;
    
    var $teams = NULL;
static $_season_id = 0;
static $_project_id = 0;

	/**
	 * Override parent constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelLegacy
	 * @since   3.2
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	
//    $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' config<br><pre>'.print_r($config,true).'</pre>'),'');
//    $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getName<br><pre>'.print_r($this->getName(),true).'</pre>'),'');
    
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
    $app = JFactory::getApplication();
    // JInput object
    $jinput = $app->input;
    $option = $jinput->getCmd('option');
    //$params = \JComponentHelper::getParams($option);
    
    $google_client_id = JComponentHelper::getParams($option)->get('google_api_clientid','');
    $google_client_secret = JComponentHelper::getParams($option)->get('google_api_clientsecret','');
        
    $options = new JRegistry();  
    $input = new JInput;  
    
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
$object->setOption('redirecturi', JURI::root() );

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
    
    
      
            
    
}
?>
