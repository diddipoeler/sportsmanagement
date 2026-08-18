<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       clublist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\Database\DatabaseInterface;

/**
 * FormFieldClublist
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldClublist extends ListField
{
	public $type = 'clublist';

	/**
	 * Method to get the field options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		$app       = Factory::getApplication();
		$input     = $app->getInput();
		$sportType = (string) $this->element->attributes()->target;
		$clubId    = $input->getInt('club_id', 0);

		if (!$clubId)
		{
			$clubId = (int) $app->getUserState('com_sportsmanagement.club_id', 0);
		}

		$db    = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->createQuery();

		$query->select('c.id AS value, c.name AS text');
		$query->from('#__sportsmanagement_club AS c');
		$query->join('LEFT', '#__sportsmanagement_team AS t ON t.club_id = c.id');

		if ($clubId)
		{
			$query->where('c.id = ' . (int) $clubId);
		}

		if ($sportType !== '')
		{
			$query->join('INNER', '#__sportsmanagement_sports_type AS st ON st.id = t.sports_type_id');
			$query->where('st.name = ' . $db->quote($sportType));
		}

		$query->group('c.id');
		$query->order('c.name');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		return array_merge(parent::getOptions(), $options);
	}
}
