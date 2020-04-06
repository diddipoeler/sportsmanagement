<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       project.php
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
 * JFormFieldProject
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldProject extends FormField
{
	protected $type = 'project';

	/**
	 * JFormFieldProject::getInput()
	 *
	 * @return
	 */
	protected function getInput()
	{
		$db            = sportsmanagementHelper::getDBConnection();
		$lang        = Factory::getLanguage();

		// Welche tabelle soll genutzt werden
		$params = ComponentHelper::getParams('com_sportsmanagement');

			  $extension    = "com_sportsmanagement";
		$source     = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		||    $lang->load($extension, $source, null, false, false)
		||    $lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||    $lang->load($extension, $source, $lang->getDefault(), false, false);

			  $query = $db->getQuery(true);
		$query->select('p.id, concat(p.name, \' (' . Text::_('COM_SPORTSMANAGEMENT_GLOBAL_LEAGUE') . ': \', l.name, \')\', \' (' . Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SEASON') . ': \', s.name, \' )\' ) as name');
		$query->from('#__sportsmanagement_project AS p');
		$query->join('LEFT', '#__sportsmanagement_season AS s ON s.id = p.season_id ');
		$query->join('LEFT', '#__sportsmanagement_league AS l ON l.id = p.league_id');
		$query->where('p.published = 1');
		$query->order('p.ordering DESC');
		$db->setQuery($query);
		$projects = $db->loadObjectList();
		$mitems = array(HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));

		foreach ($projects as $project)
		{
			$mitems[] = HTMLHelper::_('select.option',  $project->id, Text::_($project->name));
		}

		return  HTMLHelper::_('select.genericlist',  $mitems, $this->name, 'class="inputbox" style="width:50%;" size="1"', 'value', 'text', $this->value, $this->id);
	}
}
