<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage elements
 * @file       actseason.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * JFormFieldactseason
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldactseason extends JFormField
{
	protected $type = 'actseason';

	/**
	 * JFormFieldactseason::getInput()
	 *
	 * @return
	 */
	protected function getInput()
	{
		$db     = sportsmanagementHelper::getDBConnection();
		$lang   = Factory::getLanguage();
		$option = Factory::getApplication()->input->getCmd('option');

		// Welche tabelle soll genutzt werden
		$params         = ComponentHelper::getParams('COM_SPORTSMANAGEMENT');
		$database_table = $params->get('cfg_which_database_table');

		$extension = "com_sportsmanagement";
		$source    = JPATH_ADMINISTRATOR . '/components/' . $extension;
		$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
		|| $lang->load($extension, $source, null, false, false)
		|| $lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		|| $lang->load($extension, $source, $lang->getDefault(), false, false);

		$query = 'SELECT s.id, s.name as name
					FROM #__' . $database_table . '_season AS s
					ORDER BY s.name DESC';
		$db->setQuery($query);
		$projects = $db->loadObjectList();

		foreach ($projects as $project)
		{
			$mitems[] = HTMLHelper::_('select.option', $project->id, '&nbsp;&nbsp;&nbsp;' . $project->name);
		}

		$output = HTMLHelper::_('select.genericlist', $mitems, $this->name . '[]', 'class="inputbox" style="width:90%;" ', 'value', 'text', $this->value, $this->id);

		return $output;
	}
}
