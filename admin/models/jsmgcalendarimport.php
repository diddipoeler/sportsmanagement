<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jsmgcalendar
 * @file       jsmgcalendarimport.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();
use Joomla\CMS\Http\HttpFactory;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Filter\OutputFilter;

JLoader::import('components.com_sportsmanagement.libraries.google-php.vendor.autoload', JPATH_ADMINISTRATOR);

/**
 * sportsmanagementModeljsmgcalendarImport
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2017
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModeljsmgcalendarImport extends BaseDatabaseModel
{
	var $_name = 'sportsmanagement';

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

		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$http   = HttpFactory::getHttp();

		// $google = new Google;
		$this->jsmdb    = sportsmanagementHelper::getDBConnection();
		$this->jsmquery = $this->jsmdb->getQuery(true);

		/**
		 * Client ID and Client Secret can be obtained  through the Google API Console (https://code.google.com/apis/console/).
		 */
		$google_client_id       = ComponentHelper::getParams($option)->get('google_api_clientid', '');
		$google_client_secret   = ComponentHelper::getParams($option)->get('google_api_clientsecret', '');
		$google_api_key         = ComponentHelper::getParams($option)->get('google_api_developerkey', '');
		$google_api_redirecturi = ComponentHelper::getParams($option)->get('google_api_redirecturi', '');
		$google_mail_account    = ComponentHelper::getParams($option)->get('google_mail_account', '');

		$session = Factory::getSession(
			array(
				'expire' => 30
			)
		);

		if (!$app->input->get('code'))
		{
			$session->set('client-id', $google_client_id, $this->_name);
			$session->set('client-secret', $google_client_secret, $this->_name);
		}

		$clientId     = $session->get('client-id', null, $this->_name);
		$clientSecret = $session->get('client-secret', null, $this->_name);

		if ($app->input->get('code'))
		{
			$session->set('client-id', null, $this->_name);
			$session->set('client-secret', null, $this->_name);
		}

		// $client = new Google_Client();
		$client = new Google_Client(
			array(
				'ioFileCache_directory' => Factory::getConfig()->get('tmp_path')
			)
		);
		$client->setApplicationName("JSMCalendar");

		// $client->setApprovalPrompt('force');
		$client->setClientId($google_client_id);
		$client->setClientSecret($google_client_secret);
		$client->setScopes(
			array(
				'https://www.googleapis.com/auth/calendar'
			)
		);
		$client->setAccessType("offline");

		if (version_compare(JSM_JVERSION, '4', 'eq'))
		{
			$uri = Uri::getInstance();
		}
		else
		{
			$uri = Factory::getURI();
		}

		if (filter_var($uri->getHost(), FILTER_VALIDATE_IP))
		{
			$uri->setHost('localhost');
		}

		$client->setRedirectUri(
			$uri->toString(
				array(
					'scheme',
					'host',
					'port',
					'path'
				)
			) . '?option=' . $option . '&task=jsmgcalendarimport.import'
		);
		$client->setApprovalPrompt('force');

		if (!$app->input->get('code'))
		{
			$app->redirect($client->createAuthUrl());
			$app->close();
		}

		$cal   = new Google_Service_Calendar($client);
		$token = $client->authenticate($app->input->get('code', null, null));
		$client->setAccessToken($token);

		try
		{
			$calList = $cal->calendarList->listCalendarList();
		}
		catch (Exception $e)
		{
			$msg  = $e->getMessage(); // Returns "Normally you would have other code...
			$code = $e->getCode(); // Returns '500';
			Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
		}

		// $tok = json_decode($token, true);

		while (true)
		{
			foreach ($calList->getItems() as $calendarListEntry)
			{
				$params = new Registry;
				$params->set('refreshToken', $token['refresh_token']);
				$params->set('client-id', $clientId);
				$params->set('client-secret', $clientSecret);
				$params->set('calendarId', $calendarListEntry->getID());
				$params->set('action-create', true);
				$params->set('action-edit', true);
				$params->set('action-delete', true);

				$this->jsmquery->clear();
				$this->jsmquery->select('id');
				$this->jsmquery->from('#__sportsmanagement_gcalendar');
				$this->jsmquery->where('calendar_id LIKE ' . $this->jsmdb->Quote('' . $calendarListEntry->getID() . ''));
				$this->jsmdb->setQuery($this->jsmquery);
				$result_sel = $this->jsmdb->loadResult();

				if (!$result_sel)
				{
					/**
					 *
					 * wenn nichts gefunden wurde neu anlegen
					 */
					$newcalendar              = new stdClass;
					$newcalendar->calendar_id = $calendarListEntry->getID();
					$newcalendar->name        = $calendarListEntry->getSummary();
					$newcalendar->color       = $calendarListEntry->backgroundColor;
					$newcalendar->username    = $google_mail_account;
					$newcalendar->params      = $params->toString();
					$newcalendar->title       = $calendarListEntry->getSummary();
					$newcalendar->alias       = OutputFilter::stringURLSafe($newcalendar->name);
					$newcalendar->created     = Factory::getDate()->toSql();
					$newcalendar->created_by  = Factory::getUser()->get('id');
					$newcalendar->modified    = Factory::getDate()->toSql();
					$newcalendar->modified_by = Factory::getUser()->get('id');
					$result_insert            = Factory::getDbo()->insertObject('#__sportsmanagement_gcalendar', $newcalendar);
				}
				else
				{
					$object              = new stdClass;
					$object->id          = $result_sel;
					$object->params      = $params->toString();
					$object->title       = $calendarListEntry->getSummary();
					$object->alias       = OutputFilter::stringURLSafe($object->title);
					$object->modified    = Factory::getDate()->toSql();
					$object->modified_by = Factory::getUser()->get('id');
					$result              = Factory::getDbo()->updateObject('#__sportsmanagement_gcalendar', $object, 'id');
				}
			}

			$pageToken = $calList->getNextPageToken();

			if ($pageToken)
			{
				$optParams = array('pageToken' => $pageToken);
				$calList   = $service->calList->listCalendarList($optParams);
			}
			else
			{
				break;
			}
		}

		return true;
	}

}
