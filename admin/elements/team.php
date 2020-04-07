<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage elements
 * @file       team.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\FormField;

/**
 * JFormFieldTeam
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldTeam extends FormField
{
	protected $type = 'team';

	/**
	 * JFormFieldTeam::getInput()
	 *
	 * @return
	 */
	function getInput()
	{
		$db = sportsmanagementHelper::getDBConnection();
		$lang = Factory::getLanguage();

		// Welche tabelle soll genutzt werden
		$params = ComponentHelper::getParams('com_sportsmanagement');

			  $extension = "com_sportsmanagement";
		$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		||    $lang->load($extension, $source, null, false, false)
		||    $lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||    $lang->load($extension, $source, $lang->getDefault(), false, false);

			  $query = $db->getQuery(true);
		$query->select('t.id, t.name');
		$query->from('#__sportsmanagement_team AS t');
		$query->order('name');
		$db->setQuery($query);
		$teams = $db->loadObjectList();
		$mitems = array(HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));

		foreach ($teams as $team)
		{
			$mitems[] = HTMLHelper::_('select.option',  $team->id, '&nbsp;' . $team->name . ' (' . $team->id . ')');
		}

			  $output = HTMLHelper::_('select.genericlist',  $mitems, $this->name, 'class="inputbox" size="1"', 'value', 'text', $this->value, $this->id);

		return $output;
	}
}

