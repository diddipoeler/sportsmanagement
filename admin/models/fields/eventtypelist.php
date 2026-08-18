<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       eventtypelist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

/**
 * FormFieldeventtypelist
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldeventtypelist extends ListField
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'eventtypelist';

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
		$query->from('#__sportsmanagement_eventtype AS pos');
		$query->where('pos.published = 1');
		$query->order('pos.ordering, pos.name');
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			Log::add($e->getMessage(), Log::NOTICE, 'jsmerror');
			$options = array();
		}

		foreach ($options as $row)
		{
			$row->text = Text::_($row->text);
		}

		return array_merge(parent::getOptions(), $options);
	}
}
