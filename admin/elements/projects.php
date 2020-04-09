<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage elements
 * @file       projects.php
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
 * JFormFieldProjects
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldProjects extends FormField
{
	protected $type = 'projects';

	/**
	 * JFormFieldProjects::getInput()
	 *
	 * @return
	 */
	protected function getInput()
	{
		$db   = sportsmanagementHelper::getDBConnection();
		$lang = Factory::getLanguage();

		// Welche tabelle soll genutzt werden
		$params = ComponentHelper::getParams('com_sportsmanagement');

		$extension = "com_sportsmanagement";
		$source    = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		|| $lang->load($extension, $source, null, false, false)
		|| $lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		|| $lang->load($extension, $source, $lang->getDefault(), false, false);

		$query = $db->getQuery(true);
		$query->select('p.id, concat(p.name, \' (' . Text::_('COM_SPORTSMANAGEMENT_GLOBAL_LEAGUE') . ': \', l.name, \')\', \' (' . Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SEASON') . ': \', s.name, \' )\' ) as name');
		$query->from('#__sportsmanagement_project AS p');
		$query->join('LEFT', '#__sportsmanagement_season AS s ON s.id = p.season_id ');
		$query->join('LEFT', '#__sportsmanagement_league AS l ON l.id = p.league_id');
		$query->where('p.published = 1');
		$query->order('p.id DESC');
		$db->setQuery($query);
		$projects = $db->loadObjectList();

		if ($this->required == false)
		{
			$mitems = array(HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));
		}

		foreach ($projects as $project)
		{
			$mitems[] = HTMLHelper::_('select.option', $project->id, '&nbsp;&nbsp;&nbsp;' . $project->name);
		}

		$output = HTMLHelper::_('select.genericlist', $mitems, $this->name . '[]', 'class="inputbox" style="width:90%;" multiple="multiple" size="10"', 'value', 'text', $this->value, $this->id);

		return $output;
	}
}
