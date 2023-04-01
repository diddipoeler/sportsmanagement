<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage elements
 * @file       events.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * JFormFieldEvents
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldEvents extends JFormField
{
	protected $type = 'events';

	/**
	 * JFormFieldEvents::getInput()
	 *
	 * @return
	 */
	function getInput()
	{
		$db   = sportsmanagementHelper::getDBConnection();
		$lang = Factory::getLanguage();

		// Welche tabelle soll genutzt werden
		$params         =& ComponentHelper::getParams('com_sportsmanagement');
		$database_table = $params->get('cfg_which_database_table');

		$extension = "com_sportsmanagement";
		$source    = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		|| $lang->load($extension, $source, null, false, false)
		|| $lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		|| $lang->load($extension, $source, $lang->getDefault(), false, false);

		$query = 'SELECT e.id, e.name FROM #__' . $database_table . '_eventtype e WHERE published=1 ORDER BY name';
		$db->setQuery($query);
		$events = $db->loadObjectList();
		$mitems = array(HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));

		foreach ($events as $event)
		{
			$mitems[] = HTMLHelper::_('select.option', $event->id, '&nbsp;' . Text::_($event->name) . ' (' . $event->id . ')');
		}

		$output = HTMLHelper::_('select.genericlist', $mitems, $this->name, 'class="inputbox" multiple="multiple" size="10"', 'value', 'text', $this->value, $this->id);

		return $output;
	}
}

