<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      jsmgcalendarimport.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage jsmgcalendar
 */

defined('_JEXEC') or die();

JLoader::import('joomla.application.component.model');

JLoader::import('components.com_sportsmanagement.libraries.google-php.Google.autoload', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.libraries.google-php.Google.Client', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.libraries.google-php.Google.Service.Calendar', JPATH_ADMINISTRATOR);


/**
 * sportsmanagementModeljsmgcalendarImport
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class sportsmanagementModeljsmgcalendarImport extends JModelLegacy 
{
    
    var $_name = 'sportsmanagement';
    //var $_name = '';

	/**
	 * sportsmanagementModeljsmgcalendarImport::__construct()
	 * 
	 * @return void
	 */
	public function __construct() 
    {
		parent::__construct();

	}
    
    /**
     * sportsmanagementModeljsmgcalendarImport::import()
     * 
     * @return void
     */
    public function import()
	{

$app = JFactory::getApplication();
// JInput object
$jinput = $app->input;
$option = $jinput->getCmd('option');
// Create an instance of a default Http object.
$http = JHttpFactory::getHttp();
//$params = \JComponentHelper::getParams($option);

//$google = new Google;
$this->jsmdb = sportsmanagementHelper::getDBConnection();
$this->jsmquery = $this->jsmdb->getQuery(true);

// Client ID and Client Secret can be obtained  through the Google API Console (https://code.google.com/apis/console/).    
$google_client_id = JComponentHelper::getParams($option)->get('google_api_clientid','');
$google_client_secret = JComponentHelper::getParams($option)->get('google_api_clientsecret','');
$google_api_key = JComponentHelper::getParams($option)->get('google_api_developerkey','');
$google_api_redirecturi = JComponentHelper::getParams($option)->get('google_api_redirecturi','');
$google_mail_account = JComponentHelper::getParams($option)->get('google_mail_account','');

//$client = new Google_Client();
$client = new Google_Client(
				array(
						'ioFileCache_directory' => JFactory::getConfig()->get('tmp_path')
				));
$client->setApprovalPrompt('force');
$client->setClientId($google_client_id);
$client->setClientSecret($google_client_secret);
$client->setAccessType("offline");
$client->setScopes(array(
				'https://www.googleapis.com/auth/calendar'
		));
$client->setRedirectUri(JURI::current().'?option='.$option.'&task=jsmgcalendarimport.import' );            
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' client<br><pre>'.print_r($client,true).'</pre>'),'Notice');

$uri = $client->createAuthUrl();
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' uri<br><pre>'.print_r($uri,true).'</pre>'),'Notice');

if (! $app->input->get('code'))
{
$app->redirect($client->createAuthUrl());
$app->close();
}

$cal = new Google_Service_Calendar($client);
$token = $client->authenticate($app->input->get('code', null, null));
$client->setAccessToken($token);

try{
$calList = $cal->calendarList->listCalendarList();
} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' calList<br><pre>'.print_r($calList,true).'</pre>'),'Notice');	   
       
/*
foreach ( $calList->getItems() as $calendarListEntry ) 
{
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getID<br><pre>'.print_r($calendarListEntry->getID(),true).'</pre>'),'Notice');	 
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getTitle<br><pre>'.print_r($calendarListEntry->getTitle(),true).'</pre>'),'Notice');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getColor<br><pre>'.print_r($calendarListEntry->getColor(),true).'</pre>'),'Notice');
}
*/


while(true) 
{
  foreach ($calList->getItems() as $calendarListEntry) 
  {
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getSummary<br><pre>'.print_r($calendarListEntry->getSummary(),true).'</pre>'),'Notice');	 
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getID<br><pre>'.print_r($calendarListEntry->getID(),true).'</pre>'),'Notice');	 
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' backgroundColor<br><pre>'.print_r($calendarListEntry->backgroundColor,true).'</pre>'),'Notice');


$this->jsmquery->clear();
$this->jsmquery->select('id');
$this->jsmquery->from('#__sportsmanagement_gcalendar');
$this->jsmquery->where('calendar_id LIKE '.$this->jsmdb->Quote(''.$calendarListEntry->getID().''));
$this->jsmdb->setQuery( $this->jsmquery );
$result_sel = $this->jsmdb->loadResult();

if ( !$result_sel )
{
// wenn nichts gefunden wurde neu anlegen
$newcalendar = new stdClass();
$newcalendar->calendar_id = $calendarListEntry->getID();
$newcalendar->name = $calendarListEntry->getSummary();
$newcalendar->color = $calendarListEntry->backgroundColor;
$newcalendar->username = $google_mail_account;
// Insert the object
$result_insert = JFactory::getDbo()->insertObject('#__sportsmanagement_gcalendar', $newcalendar);
}

	 
//    echo $calendarListEntry->getSummary();
  }
  $pageToken = $calList->getNextPageToken();
  if ($pageToken) 
  {
    $optParams = array('pageToken' => $pageToken);
    $calList= $service->calList->listCalendarList($optParams);
  } 
  else 
  {
    break;
  }
}

       
    }   

}