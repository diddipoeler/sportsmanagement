<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       projects.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * FormFieldProjects
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldProjects extends ListField
{
	protected $type = 'projects';

	/**
	 * FormFieldProjects::getOptions()
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		$options = array();
		$lang = Factory::getApplication()->getLanguage();

		$params         = ComponentHelper::getParams('com_sportsmanagement');
		$databaseTable  = $params->get('cfg_which_database_table');

		$val   = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
		$value = $this->form->getValue($val, 'request');

		if (!$value)
		{
			$value = $this->form->getValue($val, 'params');
			$div   = 'params';
		}
		else
		{
			$div = 'request';
		}

		$cfgWhichDatabase = $this->form->getValue('cfg_which_database', $div);
		$db = !$cfgWhichDatabase
			? sportsmanagementHelper::getDBConnection()
			: sportsmanagementHelper::getDBConnection(true, $cfgWhichDatabase);

		$extension = 'com_sportsmanagement';
		$source    = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load($extension, JPATH_ADMINISTRATOR, null, false, false)
		|| $lang->load($extension, $source, null, false, false)
		|| $lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		|| $lang->load($extension, $source, $lang->getDefault(), false, false);

		$query = 'SELECT p.id, concat(p.name, \' (' . Text::_('COM_SPORTSMANAGEMENT_GLOBAL_LEAGUE') . ': \', l.name, \')\', \' (' . Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SEASON') . ': \', s.name, \' )\' ) as name
			FROM #__' . $databaseTable . '_project AS p
			LEFT JOIN #__' . $databaseTable . '_season AS s ON s.id = p.season_id
			LEFT JOIN #__' . $databaseTable . '_league AS l ON l.id = p.league_id
			WHERE p.published=1 ORDER BY p.id DESC';
		$db->setQuery($query);
		$projects = $db->loadObjectList();

		$options[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'));

		foreach ($projects as $project)
		{
			$options[] = HTMLHelper::_('select.option', $project->id, '&nbsp;&nbsp;&nbsp;' . $project->name);
		}

		return array_merge(parent::getOptions(), $options);
	}
}
