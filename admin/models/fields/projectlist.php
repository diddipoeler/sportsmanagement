<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       projectlist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\Database\DatabaseInterface;

/**
 * FormFieldprojectlist
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldprojectlist extends ListField
{
	public $type = 'projectlist';

	/**
	 * Method to get the field options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		$db    = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->createQuery();
		$query->select('l.id AS value, l.name AS text');
		$query->from('#__sportsmanagement_project AS l');
		$query->order('l.name');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		return array_merge(parent::getOptions(), $options);
	}
}
