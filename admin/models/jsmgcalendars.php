<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      jsmgcalendars.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
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

if (! defined('JSM_PATH'))
{
DEFINE( 'JSM_PATH','components/com_sportsmanagement' );
}

if (!class_exists('sportsmanagementHelper')) 
{
require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.JSM_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'sportsmanagement.php');  
}



Table::addIncludePath(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.JSM_PATH.DIRECTORY_SEPARATOR.'tables');

class sportsmanagementModeljsmGCalendars extends ListModel 
{

	function check_google_api()
	{
$importFile = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.JSM_PATH.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'google-php/Google/autoload.php';
//Factory::getApplication()->enqueueMessage(Text::_('Admin Verzeichnis '.$importFile.' ist vorhanden!'),'Notice');		
if (File::exists ( $importFile )) {
Log::add(Text::_('Google API vorhanden'), Log::NOTICE, 'jsmerror');		
}
		
		
	}
	
	protected function _getList($query, $limitstart = 0, $limit = 0) 
    {
		$items = parent::_getList($query, $limitstart, $limit);
		if ($items === null) {
			return $items;
		}
		$tmp = array();
		foreach ($items as $item) {
			$table = Table::getInstance('jsmGCalendar', 'sportsmanagementTable');
			$table->load($item->id);
			$tmp[] = $table;
		}
		return $tmp;
	}

	protected function getListQuery() 
    {
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
		$user	= Factory::getUser();

		$query->select('*');
		$calendarIDs = $this->getState('ids', null);
		if (!empty($calendarIDs)) {
			if (is_array($calendarIDs)) {
				$query->where('id IN ( '.implode(',', array_map('intval', $calendarIDs)).')');
			} else {
				$query->where($condition = 'id = '.(int)rtrim($calendarIDs, ','));
			}
		}

		// Implement View Level Access
		if (!$user->authorise('core.admin'))
		{
			$groups	= implode(',', $user->getAuthorisedViewLevels());
			$query->where('access IN ('.$groups.')');
		}

		$query->from('#__sportsmanagement_gcalendar');
		return $query;
	}
}
