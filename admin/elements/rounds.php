<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage elements
 * @file       rounds.php
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
 * JFormFieldRounds
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldRounds extends FormField
{
	protected $type = 'rounds';

	/**
	 * JFormFieldRounds::getInput()
	 *
	 * @return
	 */
	protected function getInput()
	{
		$required = $this->element['required'] == 'true' ? 'true' : 'false';
		$order    = $this->element['order'] == 'DESC' ? 'DESC' : 'ASC';
		$db       = sportsmanagementHelper::getDBConnection();
		$lang     = Factory::getLanguage();

		// Welche tabelle soll genutzt werden
		$params = ComponentHelper::getParams('com_sportsmanagement');

		$extension = "com_sportsmanagement";
		$source    = JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $extension);
		$lang->load($extension, JPATH_ADMINISTRATOR, null, false, false)
		|| $lang->load($extension, $source, null, false, false)
		|| $lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		|| $lang->load($extension, $source, $lang->getDefault(), false, false);

		$query = $db->getQuery(true);
		$query->select(
			' SELECT id as value '
			. '      , CASE LENGTH(name) when 0 then CONCAT(' . $db->Quote(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME')) . ', " ", id)	else name END as text '
			. '      , id, name, round_date_first, round_date_last, roundcode '
		);
		$query->from('#__sportsmanagement_round');
		$query->where('project_id= ' . $project_id);
		$query->order('roundcode ' . $order);
		$db->setQuery($query);
		$rounds = $db->loadObjectList();

		if ($required == 'false')
		{
			$mitems = array(HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));
		}

		foreach ($rounds as $round)
		{
			$mitems[] = HTMLHelper::_('select.option', $round->id, '&nbsp;&nbsp;&nbsp;' . $round->name);
		}

		$output = HTMLHelper::_('select.genericlist', $mitems, $this->name . '[]', 'class="inputbox" style="width:90%;" multiple="multiple" size="10"', 'value', 'text', $this->value, $this->id);

		return $output;
	}
}
