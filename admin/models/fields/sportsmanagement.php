<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       sportsmanagement.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

// Import the list field type
jimport('joomla.form.helper');
FormHelper::loadFieldClass('list');

/**
 * SportsManagement Form Field class for the SportsManagement component
 */
class JFormFieldsportsmanagement extends \JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var string
	 */
	protected $type = 'sportsmanagement';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return array        An array of JHtml options.
	 */
	protected function getOptions()
	{
		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
		$query->select('#__sportsmanagement.id as id,greeting,#__categories.title as category,catid');
		$query->from('#__sportsmanagement');
		$query->leftJoin('#__categories on catid=#__categories.id');
		$db->setQuery((string) $query);
		$messages = $db->loadObjectList();
		$options  = array();

		if ($messages)
		{
			foreach ($messages as $message)
			{
				$options[] = HTMLHelper::_('select.option', $message->id, $message->greeting . ($message->catid ? ' (' . $message->category . ')' : ''));
			}
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
