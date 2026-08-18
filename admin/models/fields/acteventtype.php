<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       acteventtype.php
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
 * FormFieldacteventtype
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldacteventtype extends ListField
{
	protected $type = 'acteventtype';

	/**
	 * FormFieldacteventtype::getOptions()
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		$options   = array();
		$targetTable = preg_replace('/[^A-Za-z0-9_]/', '', (string) $this->element['targettable']);
		$selectId = Factory::getApplication()->getInput()->getInt('id', 0);

		if ($targetTable === '' || !$selectId)
		{
			return parent::getOptions();
		}

		$db    = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->createQuery();
		$query->select('s.id AS value, s.name AS text');
		$query->from('#__sportsmanagement_eventtype AS s');
		$query->join('INNER', '#__sportsmanagement_' . $targetTable . ' AS t ON t.sports_type_id = s.sports_type_id');
		$query->where('t.id = ' . (int) $selectId);
		$query->order('s.name');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		foreach ($options as $row)
		{
			$row->text = Text::_($row->text);
		}

		return array_merge(parent::getOptions(), $options);
	}
}
