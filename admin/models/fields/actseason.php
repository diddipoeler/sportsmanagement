<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       actseason.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\Database\DatabaseInterface;

/**
 * FormFieldactseason
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldactseason extends ListField
{
	protected $type = 'actseason';

	/**
	 * FormFieldactseason::getOptions()
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		$db    = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->createQuery();
		$query->select('s.id AS value, s.name AS text');
		$query->from('#__sportsmanagement_season AS s');
		$query->order('s.name');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		return array_merge(parent::getOptions(), $options);
	}
}
