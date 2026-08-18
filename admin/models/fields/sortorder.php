<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       sortorder.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * FormFieldsortorder
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class JFormFieldsortorder extends ListField
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'sortorder';

	/**
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 *
	 * @since 11.1
	 */
	protected function getOptions()
	{
		$options = array();
		$templateSortOrders = (int) ComponentHelper::getParams('com_sportsmanagement')->get('template_sort_orders', 0);

		for ($i = 1; $i <= $templateSortOrders; $i++)
		{
			$options[] = HTMLHelper::_('select.option', $i, $i, 'value', 'text');
		}

		return array_merge(parent::getOptions(), $options);
	}
}
