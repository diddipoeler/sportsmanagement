<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       positionlist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * FormFieldpositionlist
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldpositionlist extends ListField
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'positionlist';

	/**
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 *
	 * @since 11.1
	 */
	protected function getOptions()
	{
		$db    = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->createQuery();

		$query->select('pos.id AS value, pos.name AS text');
		$query->from('#__sportsmanagement_position AS pos');
		$query->join('INNER', '#__sportsmanagement_sports_type AS s ON s.id = pos.sports_type_id');
		$query->where('pos.published = 1');
		$query->order('pos.ordering, pos.name');
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$options = array();
		}

		foreach ($options as $row)
		{
			$row->text = Text::_($row->text);
		}

		return array_merge(parent::getOptions(), $options);
	}
}
