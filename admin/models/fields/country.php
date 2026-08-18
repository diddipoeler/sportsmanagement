<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       country.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Field\ListField;

JLoader::import('components.com_sportsmanagement.helpers.countries', JPATH_SITE);

/**
 * FormFieldCountry
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2013
 * @access    public
 */
class JFormFieldCountry extends ListField
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'Country';

	/**
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 *
	 * @since 11.1
	 */
	protected function getOptions()
	{
		$options = JSMCountries::getCountryOptions();

		return array_merge(parent::getOptions(), $options);
	}
}
