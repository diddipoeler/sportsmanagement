<?php
/**
 * GCalendar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GCalendar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GCalendar.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package		GCalendar
 * @author		Digital Peak http://www.digital-peak.com
 * @copyright	Copyright (C) 2007 - 2013 Digital Peak. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JLoader::import('joomla.application.component.model');

JLoader::import('components.com_sportsmanagement.libraries.google-php.Google.autoload', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.libraries.GCalendar.google_calendar', JPATH_ADMINISTRATOR);

JLoader::import('components.com_sportsmanagement.libraries.google-php.Google.Client', JPATH_ADMINISTRATOR);
JLoader::import('components.com_sportsmanagement.libraries.google-php.Google.Service.Calendar', JPATH_ADMINISTRATOR);

class sportsmanagementModeljsmgcalendarImport extends JModelLegacy 
{
    
    var $_name = 'sportsmanagement';

	public function __construct() 
    {
		parent::__construct();

		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
	}

    
    
    
    
    public function importold()
	{
	$app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        
        $google_api_clienid = $jinput->post->get('google_api_clienid', 0, 'STR');
        $google_api_clientsecret = $jinput->post->get('google_api_clientsecret', 0, 'STR');
        
        	$client = new Google_Client(
				array(
						'ioFileCache_directory' => JFactory::getConfig()->get('tmp_path') . '/plg_dpcalendar_google/Google_Client'
				));
		$client->setApplicationName("sportsmanagement");
		$client->setClientId($google_api_clienid);
		$client->setClientSecret($google_api_clientsecret);
		$client->setScopes(array(
				'https://www.googleapis.com/auth/calendar'
		));
		$client->setAccessType('offline');

		$uri = JFactory::getURI();
		if (filter_var($uri->getHost(), FILTER_VALIDATE_IP))
		{
			$uri->setHost('localhost');
		}
		$client->setRedirectUri(
				$uri->toString(array(
						'scheme',
						'host',
						'port',
						'path'
				)) . '?option=com_sportsmanagement&task=jsmgcalendarimport.import');

        
        
        
        
   // $client = jsmGoogleCalendarHelper::getClient($google_api_clienid, $google_api_clientsecret);
//$client->setApplicationName("Calendar test");
$service = new Google_Service_Calendar($client);    
   $cals = $service->calendarList->listCalendarList();        
       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' cals<br><pre>'.print_r($cals,true).'</pre>'),'Notice');
       
    }   
    
    public function import ()
	{
		$app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        
        $google_api_clienid = $jinput->post->get('google_api_clienid', 0, 'STR');
        $google_api_clientsecret = $jinput->post->get('google_api_clientsecret', 0, 'STR');

		$session = JFactory::getSession(array(
				'expire' => 30
		));


		// If we are on the callback from google don't save
		if (! $app->input->get('code'))
		{
			
//            $params = $app->input->get('params', array(
//					'client-id' => null,
//					'client-secret' => null
//			), 'array');
            
            $params['client-id'] = $google_api_clienid;
            $params['client-secret'] = $google_api_clientsecret;
            
			$session->set('client-id', $params['client-id'], $this->_name);
			$session->set('client-secret', $params['client-secret'], $this->_name);
		}

        
		$clientId = $session->get('client-id', null, $this->_name);
		$clientSecret = $session->get('client-secret', null, $this->_name);
        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' clientId<br><pre>'.print_r($clientId,true).'</pre>'),'Notice');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' clientSecret<br><pre>'.print_r($clientSecret,true).'</pre>'),'Notice');

		if ($app->input->get('code'))
		{
			$session->set('client-id', null, $this->_name);
			$session->set('client-secret', null, $this->_name);
		}
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' session<br><pre>'.print_r($session,true).'</pre>'),'Notice');

		try
		{
			$client = jsmGoogleCalendarHelper::getClient($clientId, $clientSecret);
			$client->setApprovalPrompt('force');
            
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' client<br><pre>'.print_r($client,true).'</pre>'),'Notice');
            
			if (empty($client))
			{
				return;
			}
            
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' code<br><pre>'.print_r($app->input->get('code'),true).'</pre>'),'Notice');

/*
			if (! $app->input->get('code'))
			{
				$app->redirect($client->createAuthUrl());
				$app->close();
			}
*/

			$cal = new Google_Service_Calendar($client);
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' input<br><pre>'.print_r($app->input,true).'</pre>'),'Notice');
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' cal<br><pre>'.print_r($cal,true).'</pre>'),'Notice');
            
            $calendars = $cal->calendarList->listCalendarList();
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' calendars<br><pre>'.print_r($calendars['items'],true).'</pre>'),'Notice');
                
			//$token = $client->authenticate($app->input->get('code', null, null));
            $token = $client->authenticate($app->input->get('tstoken', null, null));
            
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' token<br><pre>'.print_r($token,true).'</pre>'),'Notice');
            
			if ($token === true)
			{
				die();
			}

			if ($token)
			{
				$client->setAccessToken($token);

				$calendars = $cal->calendarList->listCalendarList();
                
                $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' calendars<br><pre>'.print_r($calendars,true).'</pre>'),'Notice');

				$tok = json_decode($token, true);
                /*
				foreach ($calendars['items'] as $cal)
				{
					$model = JModelLegacy::getInstance('Extcalendar', 'DPCalendarModel');
					$params = new JRegistry();
					$params->set('refreshToken', $tok['refresh_token']);
					$params->set('client-id', $clientId);
					$params->set('client-secret', $clientSecret);
					$params->set('calendarId', $cal['id']);
					$params->set('action-create', true);
					$params->set('action-edit', true);
					$params->set('action-delete', true);

					$data = array();
					$data['title'] = $cal['summary'];
					$data['color'] = $cal['backgroundColor'];
					$data['params'] = $params->toString();
					$data['plugin'] = 'google';

					if (! $model->save($data))
					{
						$app->enqueueMessage($model->getError(), 'warning');
					}
				}
                */
			}
		}
		catch (Exception $e)
		{
			//Google_Logger_Psr::log($e->getMessage());
		}
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' calendars<br><pre>'.print_r($calendars,true).'</pre>'),'Notice');
	
    	$app->redirect(
				JFactory::getSession()->get('extcalendarOrigin',
						'index.php?option=com_sportsmanagement&view=jsmgcalendarimport&layout=login', 'sportsmanagement'));

		$app->close();
	}
    
    public function login() 
    {
    // Initialise variables.
	$app = JFactory::getApplication();    
    // JInput object
    $jinput = $app->input;
    
    $user = $jinput->post->get('user', 0, 'STR');
    $pass = $jinput->post->get('pass', 0, 'STR');    
    
    $google_api_clienid = $jinput->post->get('google_api_clienid', 0, 'STR');
    $google_api_clientsecret = $jinput->post->get('google_api_clientsecret', 0, 'STR');
    
    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' user<br><pre>'.print_r($user,true).'</pre>'),'Notice');
    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' pass<br><pre>'.print_r($pass,true).'</pre>'),'Notice');
    
    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' google_api_clienid<br><pre>'.print_r($google_api_clienid,true).'</pre>'),'Notice');
    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' google_api_clientsecret<br><pre>'.print_r($google_api_clientsecret,true).'</pre>'),'Notice');
    
    $serviceName = Zend_Gdata_Calendar::AUTH_SERVICE_NAME; // predefined service name for calendar
    $client = Zend_Gdata_ClientLogin::getHttpClient($user, $pass, $serviceName);
    $service = new Zend_Gdata_Calendar($client);
    
    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' service<br><pre>'.print_r($service,true).'</pre>'),'Notice');
    
    }    


	public function setId($id) 
    {
		// Set id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	public function getOnlineData() 
    {
		try {
			$user = JRequest::getVar('user', null);
			$pass = JRequest::getVar('pass', null);

			$calendars = jsmGCalendarZendHelper::getCalendars($user, $pass);
			if ($calendars == null) {
				return null;
			}

			$this->_data = array();
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_sportsmanagement/tables');
			foreach ($calendars as $calendar) {
				$table = $this->getTable('jsmGCalendar', 'sportsmanagementTable');
				$table->id = 0;
				$cal_id = substr($calendar->getId(),strripos($calendar->getId(),'/')+1);
				$table->calendar_id = $cal_id;
				$table->username = $user;
				$table->password = $pass;
				$table->name = (string)$calendar->getTitle();
				if(strpos($calendar->getColor(), '#') === 0){
					$color = str_replace("#","", (string)$calendar->getColor());
					$table->color = $color;
				}

				$this->_data[] = $table;
			}
		} catch(Exception $e){
			JError::raiseWarning(200, $e->getMessage());
			$this->_data = null;
		}

		return $this->_data;
	}

	public function getDBData() 
    {
		$query = "SELECT * FROM #__sportsmanagement_gcalendar";
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}

	public function store()	
    {
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_sportsmanagement/tables');
		$row = $this->getTable('jsmGCalendar', 'sportsmanagementTable');

		$cids = JRequest::getVar('cid', array(0), 'post', 'array');
		foreach ($cids as $index => $cid) {
			$data = unserialize(base64_decode($cid));
			$row->id = 0;
			$row->calendar_id = $data['id'];
			$row->color = $data['color'];
			$row->name = $data['name'];
            $row->username = JComponentHelper::getParams('com_sportsmanagement')->get('google_mail_account','');
            $row->password = JComponentHelper::getParams('com_sportsmanagement')->get('google_mail_password','');

			if (!$row->check()) {
				JError::raiseWarning( 500, $row->getError() );
				return false;
			}

			if (!$row->store()) {
				JError::raiseWarning( 500, $row->getError() );
				return false;
			}
		}
		return true;
	}
}