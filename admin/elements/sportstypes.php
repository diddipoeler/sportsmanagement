<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       sportstypes.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage elements
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\FormField;

/**
 * JFormFieldSportsTypes
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldSportsTypes extends FormField
{
	protected $type = 'sport_types';

	/**
	 * JFormFieldSportsTypes::getInput()
	 *
	 * @return
	 */
	function getInput()
	{
		$result = array();
		$db = sportsmanagementHelper::getDBConnection();
		$app = Factory::getApplication();
		$lang = Factory::getLanguage();
		$option = Factory::getApplication()->input->getCmd('option');

		// Welche tabelle soll genutzt werden
		$params = ComponentHelper::getParams($option);

			  $extension = "COM_SPORTSMANAGEMENT";
		$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		||    $lang->load($extension, $source, null, false, false)
		||    $lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||    $lang->load($extension, $source, $lang->getDefault(), false, false);

			  $query = $db->getQuery(true);
		$query->select('id, name');
		  $query->from('#__sportsmanagement_sports_type');
		  $query->order('name ASC');
		$db->setQuery($query);

		if (!$result = $db->loadObjectList())
		{
			return false;
		}

		foreach ($result as $sportstype)
		{
			$sportstype->name = Text::_($sportstype->name);
		}

		if ($this->required == false)
		{
			$mitems = array(HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));
		}

		foreach ($result as $item)
		{
			$mitems[] = HTMLHelper::_('select.option',  $item->id, '&nbsp;' . $item->name . ' (' . $item->id . ')');
		}

			return HTMLHelper::_(
				'select.genericlist',  $mitems, $this->name,
				'class="inputbox" size="1"', 'value', 'text', $this->value, $this->id
			);
	}
}

