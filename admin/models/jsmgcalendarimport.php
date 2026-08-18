<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jsmgcalendar
 * @file       jsmgcalendarimport.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

$googleAutoload = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/libraries/google-php/vendor/autoload.php';

if (is_file($googleAutoload))
{
	require_once $googleAutoload;
}

/**
 * sportsmanagementModeljsmgcalendarImport
 */
class sportsmanagementModeljsmgcalendarImport extends BaseDatabaseModel
{
	protected $_name = 'sportsmanagement';
	protected $jsmdb;
	protected $jsmquery;

	/**
	 * Import calendars from the configured Google account.
	 *
	 * @return boolean True on success.
	 */
	public function import()
	{
		$app    = Factory::getApplication();
		$input  = $app->getInput();
		$option = $input->getCmd('option', 'com_sportsmanagement');
		$params = ComponentHelper::getParams($option);

		if (!class_exists('Google_Client') || !class_exists('Google_Service_Calendar'))
		{
			$app->enqueueMessage('Google API client is not available.', 'error');
			return false;
		}

		$this->jsmdb    = sportsmanagementHelper::getDBConnection();
		$this->jsmquery = $this->jsmdb->getQuery(true);

		$googleClientId     = (string) $params->get('google_api_clientid', '');
		$googleClientSecret = (string) $params->get('google_api_clientsecret', '');
		$googleMailAccount  = (string) $params->get('google_mail_account', '');
		$code               = $input->get('code', '', 'raw');
		$session            = $app->getSession();

		if (!$code)
		{
			$session->set('client-id', $googleClientId, $this->_name);
			$session->set('client-secret', $googleClientSecret, $this->_name);
		}

		$clientId     = $session->get('client-id', null, $this->_name);
		$clientSecret = $session->get('client-secret', null, $this->_name);

		if ($code)
		{
			$session->set('client-id', null, $this->_name);
			$session->set('client-secret', null, $this->_name);
		}

		$client = new Google_Client(
			array('ioFileCache_directory' => Factory::getConfig()->get('tmp_path'))
		);
		$client->setApplicationName('JSMCalendar');
		$client->setClientId($googleClientId);
		$client->setClientSecret($googleClientSecret);
		$client->setScopes(array('https://www.googleapis.com/auth/calendar'));
		$client->setAccessType('offline');

		$uri = Uri::getInstance();

		if (filter_var($uri->getHost(), FILTER_VALIDATE_IP))
		{
			$uri->setHost('localhost');
		}

		$client->setRedirectUri(
			$uri->toString(array('scheme', 'host', 'port', 'path'))
			. '?option=' . $option . '&task=jsmgcalendarimport.import'
		);
		$client->setApprovalPrompt('force');

		if (!$code)
		{
			$app->redirect($client->createAuthUrl());
			$app->close();
			return true;
		}

		$token = $client->authenticate($code);
		$client->setAccessToken($token);

		$tokenData = is_string($token) ? json_decode($token, true) : $token;
		$tokenData = is_array($tokenData) ? $tokenData : array();
		$refreshToken = $tokenData['refresh_token'] ?? null;

		$cal = new Google_Service_Calendar($client);

		try
		{
			$calList = $cal->calendarList->listCalendarList();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage(), 'error');
			return false;
		}

		$userId = (int) $app->getIdentity()->id;

		while (true)
		{
			foreach ($calList->getItems() as $calendarListEntry)
			{
				$calendarId = (string) $calendarListEntry->getID();
				$title      = (string) $calendarListEntry->getSummary();
				$calendarParams = new Registry();
				$calendarParams->set('refreshToken', $refreshToken);
				$calendarParams->set('client-id', $clientId);
				$calendarParams->set('client-secret', $clientSecret);
				$calendarParams->set('calendarId', $calendarId);
				$calendarParams->set('action-create', true);
				$calendarParams->set('action-edit', true);
				$calendarParams->set('action-delete', true);

				$this->jsmquery->clear();
				$this->jsmquery->select('id');
				$this->jsmquery->from('#__sportsmanagement_gcalendar');
				$this->jsmquery->where('calendar_id = ' . $this->jsmdb->quote($calendarId));
				$this->jsmdb->setQuery($this->jsmquery);
				$existingId = (int) $this->jsmdb->loadResult();
				$now        = Factory::getDate()->toSql();

				if (!$existingId)
				{
					$newCalendar              = new stdClass();
					$newCalendar->calendar_id = $calendarId;
					$newCalendar->name        = $title;
					$newCalendar->color       = $calendarListEntry->backgroundColor;
					$newCalendar->username    = $googleMailAccount;
					$newCalendar->params      = $calendarParams->toString();
					$newCalendar->title       = $title;
					$newCalendar->alias       = OutputFilter::stringURLSafe($title);
					$newCalendar->created     = $now;
					$newCalendar->created_by  = $userId;
					$newCalendar->modified    = $now;
					$newCalendar->modified_by = $userId;
					$this->jsmdb->insertObject('#__sportsmanagement_gcalendar', $newCalendar);
				}
				else
				{
					$calendar              = new stdClass();
					$calendar->id          = $existingId;
					$calendar->params      = $calendarParams->toString();
					$calendar->title       = $title;
					$calendar->alias       = OutputFilter::stringURLSafe($title);
					$calendar->modified    = $now;
					$calendar->modified_by = $userId;
					$this->jsmdb->updateObject('#__sportsmanagement_gcalendar', $calendar, 'id');
				}
			}

			$pageToken = $calList->getNextPageToken();

			if (!$pageToken)
			{
				break;
			}

			$calList = $cal->calendarList->listCalendarList(array('pageToken' => $pageToken));
		}

		return true;
	}
}
