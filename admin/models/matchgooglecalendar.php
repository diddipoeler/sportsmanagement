<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      matchgooglecalendar.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage models
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

// import Joomla modelform library

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
	 * @see     BaseDatabaseModel
	 * @since   3.2
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
   
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
    $input = new JInput;  
    
//$options->set('clientid', $google_client_id.'.apps.googleusercontent.com');
//$options->set('clientsecret', $google_client_secret);
$google = new JGoogle($options);
   


$oauth = new JOAuth2Client($options,null,$input);
$auth = new JGoogleAuthOauth2($options, $oauth);

$options->set('clientid', $google_client_id.'.apps.googleusercontent.com');
$options->set('clientsecret', $google_client_secret);

$result = $auth->authenticate();

    
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





    
      
    }  
    
    
      
            
    
}
?>
