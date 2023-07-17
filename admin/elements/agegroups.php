<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage elements
 * @file       agegroups.php
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
 * JFormFieldagegroups
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldagegroups extends JFormField
{
	protected $type = 'agegroups';

	/**
	 * JFormFieldagegroups::getInput()
	 *
	 * @return
	 */
	function getInput()
	{
		$result = array();
		$db     = sportsmanagementHelper::getDBConnection();
		$app    = Factory::getApplication();
		$lang   = Factory::getLanguage();
		$option = Factory::getApplication()->input->getCmd('option');

		// Welche tabelle soll genutzt werden
		$params         = ComponentHelper::getParams($option);
		$database_table = $params->get('cfg_which_database_table');
		$select_id      = Factory::getApplication()->input->getVar('id');

		if ($select_id)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('sports_type_id');
			$query->from('#__sportsmanagement_team AS t');
			$query->where('t.id = ' . $select_id);
			$db->setQuery($query);
			$sports_type = $db->loadResult();

			if ($sports_type)
			{
				$query = 'SELECT id, name FROM #__' . $database_table . '_agegroup where sportstype_id = ' . $sports_type . ' ORDER BY name ASC ';
				$db->setQuery($query);

				if (!$result = $db->loadObjectList())
				{
					sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);

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
					$mitems[] = HTMLHelper::_('select.option', $item->id, '&nbsp;' . $item->name . ' (' . $item->id . ')');
				}

				return HTMLHelper::_(
					'select.genericlist', $mitems, $this->name,
					'class="inputbox" size="1"', 'value', 'text', $this->value, $this->id
				);
			}
		}

	}
}

