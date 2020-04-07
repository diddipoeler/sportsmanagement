<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       jsmgcalendars.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\Archive\Archive;

if (! defined('JSM_PATH'))
{
	DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

if (!class_exists('sportsmanagementHelper'))
{
	include_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'sportsmanagement.php';
}

// If( version_compare(JSM_JVERSION,'3','eq') )
// {
// jimport('joomla.filesystem.archive');
// }

Table::addIncludePath(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'tables');

/**
 * sportsmanagementModeljsmGCalendars
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2019
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModeljsmGCalendars extends ListModel
{

	/**
	 * sportsmanagementModeljsmGCalendars::check_google_api()
	 *
	 * @return void
	 */
	function check_google_api()
	{
		$importFile = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'google-php/composer.json';

		if (File::exists($importFile))
		{
			 Log::add(Text::_('Google API vorhanden'), Log::NOTICE, 'jsmerror');
		}
		else
		{
			 Log::add(Text::_('Google API nicht vorhanden'), Log::ERROR, 'jsmerror');
			 $link = ComponentHelper::getParams('com_sportsmanagement')->get('google_api_datei', 0);
			 /**
*
 * set the target directory
*/
			 $base_Dir = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;
			 $file['name'] = basename($link);
			 $filename = $file['name'];
			 $filepath = $base_Dir . $filename;

			if (version_compare(JSM_JVERSION, '3', 'eq'))
			{
				/**
*
 * Get the handler to download the package
*/
				try
				{
					$http = JHttpFactory::getHttp(null, array('curl', 'stream'));
				}
				catch (RuntimeException $e)
				{
					Log::add($e->getMessage(), Log::WARNING, 'jsmerror');

					return false;
				}

				/**
*
 * Download the package
*/
				try
				{
					$result = $http->get($link);
					Log::add(Text::_('Google API heruntergeladen'), Log::NOTICE, 'jsmerror');
				}
				catch (RuntimeException $e)
				{
					$my_text = '<span style="color:' . $this->storeFailedColor . '">';
					$my_text .= Text::sprintf('Die ZIP-Datei der Komponente [ %1$s ] konnte nicht kopiert werden!', "</span><strong>" . $link . "</strong>");
					$my_text .= '<br />';

					return false;
				}

				if (!$result || ($result->code != 200 && $result->code != 310))
				{
					return false;
				}

				try
				{
					/**
*
 * Write the file to disk
*/
					File::write($filepath, $result->body);
					Log::add(Text::_('Google API in´s tmp Verzeichnis geladen'), Log::NOTICE, 'jsmerror');
				}
				catch (RuntimeException $e)
				{
					$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
					$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), 'error');

					return false;
				}
			}
			elseif (version_compare(JSM_JVERSION, '4', 'eq'))
			{
				/**
*
 * Download the package at the URL given.
*/
				$p_file = InstallerHelper::downloadPackage($link);
				/**
*
 * Was the package downloaded?
*/
				if (!$p_file)
				{
					$my_text = '<span style="color:' . $this->storeFailedColor . '">';
					$my_text .= Text::sprintf('Die ZIP-Datei der Komponente [ %1$s ] konnte nicht kopiert werden!', "</span><strong>" . $p_file . "</strong>");
					$my_text .= '<br />';
					Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'), 'error');

					return false;
				}
				else
				{
					$my_text = '<span style="color:' . $this->storeSuccessColor . '">';
					$my_text .= Text::sprintf('Die ZIP-Datei der Komponente [ %1$s ] konnte kopiert werden!', "</span><strong>" . $p_file . "</strong>");
					$my_text .= '<br />';
				}
			}

			 /**
*
 * zip entpacken
*/
			 $extractdir = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp';
			 $dest = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $file['name'];

			try
			{
				$archive = new Archive;
				$result = $archive->extract($dest, $extractdir);
				Log::add(Text::_('Google API entpackt'), Log::NOTICE, 'jsmerror');
			}
			catch (Exception $e)
			{
				$this->jsmapp->enqueueMessage(__METHOD__ . ' ' . __LINE__ . Text::_($e->getMessage()), 'Error');
				$result = false;
			}

			 /**
*
 * kopieren
*/
			try
			{
				Folder::copy(JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp/google-api-php-client-2.4.0/', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'google-php/', '', true);
				Log::add(Text::_('Google API kopiert'), Log::NOTICE, 'jsmerror');
			}
			catch (Exception $e)
			{
				Log::add($e->getCode() . ' - ' . $e->getMessage(), Log::ERROR, 'jsmerror');
				$result = false;
			}
		}

	}

	/**
	 * sportsmanagementModeljsmGCalendars::_getList()
	 *
	 * @param   mixed   $query
	 * @param   integer $limitstart
	 * @param   integer $limit
	 * @return
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$items = parent::_getList($query, $limitstart, $limit);

		if ($items === null)
		{
			return $items;
		}

		$tmp = array();

		foreach ($items as $item)
		{
			$table = Table::getInstance('jsmGCalendar', 'sportsmanagementTable');
			$table->load($item->id);
			$tmp[] = $table;
		}

		return $tmp;
	}

	/**
	 * sportsmanagementModeljsmGCalendars::getListQuery()
	 *
	 * @return
	 */
	protected function getListQuery()
	{
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
		$user    = Factory::getUser();

		$query->select('*');
		$calendarIDs = $this->getState('ids', null);

		if (!empty($calendarIDs))
		{
			if (is_array($calendarIDs))
			{
				$query->where('id IN ( ' . implode(',', array_map('intval', $calendarIDs)) . ')');
			}
			else
			{
				$query->where($condition = 'id = ' . (int) rtrim($calendarIDs, ','));
			}
		}

		// Implement View Level Access
		if (!$user->authorise('core.admin'))
		{
			$groups    = implode(',', $user->getAuthorisedViewLevels());
			$query->where('access IN (' . $groups . ')');
		}

		$query->from('#__sportsmanagement_gcalendar');

		return $query;
	}
}
