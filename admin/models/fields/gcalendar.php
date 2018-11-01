<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      gcalendar.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage fields
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

JLoader::import('components.com_sportsmanagement.libraries.util', JPATH_ADMINISTRATOR);
FormHelper::loadFieldClass('list');

/**
 * FormFieldGCalendar
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2017
 * @version $Id$
 * @access public
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
		$accounts = jsmGCalendarDBUtil::getAllCalendars();
		$options = array();
		foreach($accounts as $account)
		{
			$options[] = HTMLHelper::_('select.option', $account->id, $account->name);
		}
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
    
}