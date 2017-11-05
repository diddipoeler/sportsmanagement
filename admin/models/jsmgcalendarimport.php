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
* 
* https://docs.joomla.org/Supporting_SEF_URLs_in_your_component
* 
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

//		$array = JRequest::getVar('cid',  0, '', 'array');
//		$this->setId((int)$array[0]);
	}

    
//     public function importtest()
//	{
//	$app = JFactory::getApplication();
//        // JInput object
//        $jinput = $app->input;
//        
//    $google_api_clientid = $jinput->post->get('google_api_clientid', 0, 'STR');
//        $google_api_clientsecret = $jinput->post->get('google_api_clientsecret', 0, 'STR');    
//    
//    $client = self::getClient($google_api_clientid, $google_api_clientsecret);
//			$client->setApprovalPrompt('force');
//            
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' client<br><pre>'.print_r($client,true).'</pre>'),'Notice');
//          
//          $cal = new Google_Service_Calendar($client);
//            
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getAccessToken<br><pre>'.print_r($client->getAccessToken(),true).'</pre>'),'Notice');
//            
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' code<br><pre>'.print_r($_GET['code'],true).'</pre>'),'Notice');
//            
//    if (isset($_GET['code'])) {
//  $client->authenticate($_GET['code']);
//  $_SESSION['token'] = $client->getAccessToken();
//  header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
//}
//
//$token = JSession::getFormToken();
//$_SESSION['token'] = $token;
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' token<br><pre>'.print_r($_SESSION['token'],true).'</pre>'),'Notice');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getFormToken<br><pre>'.print_r($token,true).'</pre>'),'Notice');
//
////if ($_SESSION['token']) {
////  $client->setAccessToken($_SESSION['token']);
////}
//
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getAccessToken<br><pre>'.print_r($client->getAccessToken(),true).'</pre>'),'Notice');
//
////if ($client->getAccessToken()) {
//  $calList = $cal->calendarList->listCalendarList();
//  print "<h1>Calendar List</h1><pre>" . print_r($calList, true) . "</pre>";        
//  //      }
//        
//    }
    
//    public function importold()
//	{
//	$app = JFactory::getApplication();
//        // JInput object
//        $jinput = $app->input;
//        
//        $google_api_clienid = $jinput->post->get('google_api_clienid', 0, 'STR');
//        $google_api_clientsecret = $jinput->post->get('google_api_clientsecret', 0, 'STR');
//        
//        	$client = new Google_Client(
//				array(
//						'ioFileCache_directory' => JFactory::getConfig()->get('tmp_path') . '/plg_dpcalendar_google/Google_Client'
//				));
//		$client->setApplicationName("sportsmanagement");
//		$client->setClientId($google_api_clienid);
//		$client->setClientSecret($google_api_clientsecret);
//		$client->setScopes(array(
//				'https://www.googleapis.com/auth/calendar'
//		));
//		$client->setAccessType('offline');
//
//		$uri = JFactory::getURI();
//		if (filter_var($uri->getHost(), FILTER_VALIDATE_IP))
//		{
//			$uri->setHost('localhost');
//		}
//		$client->setRedirectUri(
//				$uri->toString(array(
//						'scheme',
//						'host',
//						'port',
//						'path'
//				)) . '?option=com_sportsmanagement&task=jsmgcalendarimport.import');
//
//        
//        
//        
//        
//   // $client = jsmGoogleCalendarHelper::getClient($google_api_clienid, $google_api_clientsecret);
////$client->setApplicationName("Calendar test");
//$service = new Google_Service_Calendar($client);    
//   $cals = $service->calendarList->listCalendarList();        
//       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' cals<br><pre>'.print_r($cals,true).'</pre>'),'Notice');
//       
//    }   
    
//    public function import2 ()
//	{
//		$app = JFactory::getApplication();
//        // JInput object
//        $jinput = $app->input;
//        
//        $google_api_clienid = $jinput->post->get('google_api_clienid', 0, 'STR');
//        $google_api_clientsecret = $jinput->post->get('google_api_clientsecret', 0, 'STR');
//
//		$session = JFactory::getSession(array(
//				'expire' => 30
//		));
//
//
//		// If we are on the callback from google don't save
//		if (! $app->input->get('code'))
//		{
//			
////            $params = $app->input->get('params', array(
////					'client-id' => null,
////					'client-secret' => null
////			), 'array');
//            
//            $params['client-id'] = $google_api_clienid;
//            $params['client-secret'] = $google_api_clientsecret;
//            
//			$session->set('client-id', $params['client-id'], $this->_name);
//			$session->set('client-secret', $params['client-secret'], $this->_name);
//		}
//
//        
//		$clientId = $session->get('client-id', null, $this->_name);
//		$clientSecret = $session->get('client-secret', null, $this->_name);
//        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' clientId<br><pre>'.print_r($clientId,true).'</pre>'),'Notice');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' clientSecret<br><pre>'.print_r($clientSecret,true).'</pre>'),'Notice');
//
//		if ($app->input->get('code'))
//		{
//			$session->set('client-id', null, $this->_name);
//			$session->set('client-secret', null, $this->_name);
//		}
//        
//        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' session<br><pre>'.print_r($session,true).'</pre>'),'Notice');
//
//		try
//		{
//			$client = self::getClient($clientId, $clientSecret);
//			$client->setApprovalPrompt('force');
//            
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' client<br><pre>'.print_r($client,true).'</pre>'),'Notice');
//            
//			if (empty($client))
//			{
//				return;
//			}
//            
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' code<br><pre>'.print_r($app->input->get('code'),true).'</pre>'),'Notice');
//
///*
//			if (! $app->input->get('code'))
//			{
//				$app->redirect($client->createAuthUrl());
//				$app->close();
//			}
//*/
//
//			$cal = new Google_Service_Calendar($client);
//            
//            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' input<br><pre>'.print_r($app->input,true).'</pre>'),'Notice');
//            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' cal<br><pre>'.print_r($cal,true).'</pre>'),'Notice');
//            
//            $calendars = $cal->calendarList->listCalendarList();
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' calendars<br><pre>'.print_r($calendars['items'],true).'</pre>'),'Notice');
//                
//			//$token = $client->authenticate($app->input->get('code', null, null));
//            $token = $client->authenticate($app->input->get('tstoken', null, null));
//            
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' token<br><pre>'.print_r($token,true).'</pre>'),'Notice');
//            
//			if ($token === true)
//			{
//				die();
//			}
//
//			if ($token)
//			{
//				$client->setAccessToken($token);
//
//				$calendars = $cal->calendarList->listCalendarList();
//                
//                $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' calendars<br><pre>'.print_r($calendars,true).'</pre>'),'Notice');
//
//				$tok = json_decode($token, true);
//                /*
//				foreach ($calendars['items'] as $cal)
//				{
//					$model = JModelLegacy::getInstance('Extcalendar', 'DPCalendarModel');
//					$params = new JRegistry();
//					$params->set('refreshToken', $tok['refresh_token']);
//					$params->set('client-id', $clientId);
//					$params->set('client-secret', $clientSecret);
//					$params->set('calendarId', $cal['id']);
//					$params->set('action-create', true);
//					$params->set('action-edit', true);
//					$params->set('action-delete', true);
//
//					$data = array();
//					$data['title'] = $cal['summary'];
//					$data['color'] = $cal['backgroundColor'];
//					$data['params'] = $params->toString();
//					$data['plugin'] = 'google';
//
//					if (! $model->save($data))
//					{
//						$app->enqueueMessage($model->getError(), 'warning');
//					}
//				}
//                */
//			}
//		}
//		catch (Exception $e)
//		{
//			//Google_Logger_Psr::log($e->getMessage());
//		}
//        
//        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' calendars<br><pre>'.print_r($calendars,true).'</pre>'),'Notice');
//	
//    	$app->redirect(
//				JFactory::getSession()->get('extcalendarOrigin',
//						'index.php?option=com_sportsmanagement&view=jsmgcalendarimport&layout=login', 'sportsmanagement'));
//
//		$app->close();
//	}
    
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
    
    
    
//    public function import3 ()
//	{
//		$app = JFactory::getApplication();
//        // JInput object
//        $jinput = $app->input;
//        
//        $google_api_clienid = $jinput->post->get('google_api_clientid', 0, 'STR');
//        $google_api_clientsecret = $jinput->post->get('google_api_clientsecret', 0, 'STR');
//
//		$session = JFactory::getSession(array(
//				'expire' => 30
//		));
//        
//        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' session<br><pre>'.print_r($session,true).'</pre>'),'Notice');
//
//		// If we are on the callback from google don't save
//		if (! $app->input->get('code'))
//		{
////			$params = $app->input->get('params', array(
////					'google_api_clientid' => null,
////					'google_api_clientsecret' => null
////			), 'array');
//            
//            $params['google_api_clientid'] = $google_api_clienid;
//            $params['google_api_clientsecret'] = $google_api_clientsecret;
//            
//			$session->set('google_api_clientid', $params['google_api_clientid'], $this->_name);
//			$session->set('google_api_clientsecret', $params['google_api_clientsecret'], $this->_name);
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' params <br><pre>'.print_r($params,true).'</pre>'),'Notice');
//		}
//		
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' session google_api_clientid<br><pre>'.print_r($session->get('google_api_clientid', null, $this->_name),true).'</pre>'),'Notice');
//        
//        $clientId = $session->get('google_api_clientid', null, $this->_name);
//		$clientSecret = $session->get('google_api_clientsecret', null, $this->_name);
//
//		if ($app->input->get('code'))
//		{
//			$session->set('google_api_clientid', null, $this->_name);
//			$session->set('google_api_clientsecret', null, $this->_name);
//		}
//        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _name<br><pre>'.print_r($this->_name,true).'</pre>'),'Notice');
//		$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' clientId<br><pre>'.print_r($clientId,true).'</pre>'),'Notice');
//		$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' clientSecret<br><pre>'.print_r($clientSecret,true).'</pre>'),'Notice');
//		$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' code<br><pre>'.print_r($app->input->get('code'),true).'</pre>'),'Notice');
//
//		try
//		{
//			$client = self::getClient($clientId, $clientSecret);
//			$client->setApprovalPrompt('force');
//            
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' client<br><pre>'.print_r($client,true).'</pre>'),'Notice');
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' code<br><pre>'.print_r($app->input->get('code'),true).'</pre>'),'Notice');
//            
//			if (empty($client))
//			{
//				return;
//			}
//
//			if (! $app->input->get('code'))
//			{
//				$app->redirect($client->createAuthUrl());
//				$app->close();
//			}
//
//			$cal = new Google_Service_Calendar($client);
//            
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' cal<br><pre>'.print_r($cal,true).'</pre>'),'Notice');
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' code<br><pre>'.print_r($app->input->get('code'),true).'</pre>'),'Notice');
//            
//			$token = $client->authenticate($app->input->get('code', null, null));
//            
//            
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' token<br><pre>'.print_r($token,true).'</pre>'),'Notice');
//            
//			if ($token === true)
//			{
//				die();
//			}
//
//			if ($token)
//			{
//				$client->setAccessToken($token);
//
//				$calendars = $cal->calendarList->listCalendarList();
//                
//                $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' calendars<br><pre>'.print_r($calendars,true).'</pre>'),'Notice');
//
//				$tok = json_decode($token, true);
//				foreach ($calendars['items'] as $cal)
//				{
//					//$model = JModelLegacy::getInstance('Extcalendar', 'DPCalendarModel');
//					$params = new JRegistry();
//					$params->set('refreshToken', $tok['refresh_token']);
//					$params->set('client-id', $clientId);
//					$params->set('client-secret', $clientSecret);
//					$params->set('calendarId', $cal['id']);
//					$params->set('action-create', true);
//					$params->set('action-edit', true);
//					$params->set('action-delete', true);
//
//					$data = array();
//					$data['title'] = $cal['summary'];
//					$data['color'] = $cal['backgroundColor'];
//					$data['params'] = $params->toString();
//					$data['plugin'] = 'google';
//
////					if (! $model->save($data))
////					{
////						$app->enqueueMessage($model->getError(), 'warning');
////					}
//				}
//			}
//		}
//		catch (Exception $e)
//		{
//			$this->log($e->getMessage());
//		}
//        
//        $app->redirect(
//				JFactory::getSession()->get('extcalendarOrigin',
//						'index.php?option=com_sportsmanagement&view=jsmgcalendarimport&layout=login', 'sportsmanagement'));
//                        
//		$app->close();
//	}
    
    
//    function getClient ($clientId, $clientSecret)
//	{
//		//JLoader::import('dpcalendar.dpcalendar_google.libraries.google-php.Google.autoload', JPATH_PLUGINS);
//        //JLoader::import('components.com_sportsmanagement.libraries.google-php.Google.autoload', JPATH_ADMINISTRATOR);
//
//		$client = new Google_Client(
//				array(
//						'ioFileCache_directory' => JFactory::getConfig()->get('tmp_path') . '/plg_dpcalendar_google/Google_Client'
//				));
//		$client->setApplicationName("sportsmanagement");
//		$client->setClientId($clientId);
//		$client->setClientSecret($clientSecret);
//		$client->setScopes(array(
//				'https://www.googleapis.com/auth/calendar'
//		));
//		$client->setAccessType('offline');
//
//		$uri = JFactory::getURI();
//		if (filter_var($uri->getHost(), FILTER_VALIDATE_IP))
//		{
//			$uri->setHost('localhost');
//		}
//		$client->setRedirectUri(
//				$uri->toString(array(
//						'scheme',
//						'host',
//						'port',
//						'path'
//				)) . '?option=com_sportsmanagement&task=jsmgcalendarimport.import');
//
//		return $client;
//	}
    
//    public function login() 
//    {
//    // Initialise variables.
//	$app = JFactory::getApplication();    
//    // JInput object
//    $jinput = $app->input;
//    
//    $user = $jinput->post->get('user', 0, 'STR');
//    $pass = $jinput->post->get('pass', 0, 'STR');    
//    
//    $google_api_clienid = $jinput->post->get('google_api_clienid', 0, 'STR');
//    $google_api_clientsecret = $jinput->post->get('google_api_clientsecret', 0, 'STR');
//    
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' user<br><pre>'.print_r($user,true).'</pre>'),'Notice');
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' pass<br><pre>'.print_r($pass,true).'</pre>'),'Notice');
//    
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' google_api_clienid<br><pre>'.print_r($google_api_clienid,true).'</pre>'),'Notice');
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' google_api_clientsecret<br><pre>'.print_r($google_api_clientsecret,true).'</pre>'),'Notice');
//    
//    $serviceName = Zend_Gdata_Calendar::AUTH_SERVICE_NAME; // predefined service name for calendar
//    $client = Zend_Gdata_ClientLogin::getHttpClient($user, $pass, $serviceName);
//    $service = new Zend_Gdata_Calendar($client);
//    
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' service<br><pre>'.print_r($service,true).'</pre>'),'Notice');
//    
//    }    


//	public function setId($id) 
//    {
//		// Set id and wipe data
//		$this->_id		= $id;
//		$this->_data	= null;
//	}

//	public function getOnlineData() 
//    {
//		try {
//			$user = JRequest::getVar('user', null);
//			$pass = JRequest::getVar('pass', null);
//
//			$calendars = jsmGCalendarZendHelper::getCalendars($user, $pass);
//			if ($calendars == null) {
//				return null;
//			}
//
//			$this->_data = array();
//			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_sportsmanagement/tables');
//			foreach ($calendars as $calendar) {
//				$table = $this->getTable('jsmGCalendar', 'sportsmanagementTable');
//				$table->id = 0;
//				$cal_id = substr($calendar->getId(),strripos($calendar->getId(),'/')+1);
//				$table->calendar_id = $cal_id;
//				$table->username = $user;
//				$table->password = $pass;
//				$table->name = (string)$calendar->getTitle();
//				if(strpos($calendar->getColor(), '#') === 0){
//					$color = str_replace("#","", (string)$calendar->getColor());
//					$table->color = $color;
//				}
//
//				$this->_data[] = $table;
//			}
//		} catch(Exception $e){
//			JError::raiseWarning(200, $e->getMessage());
//			$this->_data = null;
//		}
//
//		return $this->_data;
//	}

//	public function getDBData() 
//    {
//		$query = "SELECT * FROM #__sportsmanagement_gcalendar";
//		$this->_db->setQuery( $query );
//		return $this->_db->loadObjectList();
//	}

//	public function store()	
//    {
//		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_sportsmanagement/tables');
//		$row = $this->getTable('jsmGCalendar', 'sportsmanagementTable');
//
//		$cids = JRequest::getVar('cid', array(0), 'post', 'array');
//		foreach ($cids as $index => $cid) {
//			$data = unserialize(base64_decode($cid));
//			$row->id = 0;
//			$row->calendar_id = $data['id'];
//			$row->color = $data['color'];
//			$row->name = $data['name'];
//            $row->username = JComponentHelper::getParams('com_sportsmanagement')->get('google_mail_account','');
//            $row->password = JComponentHelper::getParams('com_sportsmanagement')->get('google_mail_password','');
//
//			if (!$row->check()) {
//				JError::raiseWarning( 500, $row->getError() );
//				return false;
//			}
//
//			if (!$row->store()) {
//				JError::raiseWarning( 500, $row->getError() );
//				return false;
//			}
//		}
//		return true;
//	}

}