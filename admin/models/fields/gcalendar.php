<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       gcalendar.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage fields
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

FormHelper::loadFieldClass('list');

/**
 * FormFieldGCalendar
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2017
 * @version   $Id$
 * @access    public
 */
class JFormFieldGCalendar extends \JFormFieldList
{
	protected $type = 'GCalendar';

	/**
	 * FormFieldGCalendar::getOptions()
	 *
	 * @return
	 */
	protected function getOptions()
	{
		$options = array();
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id AS value, name AS text');
		$query->from('#__sportsmanagement_gcalendar');
		$query->order('name');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

}
