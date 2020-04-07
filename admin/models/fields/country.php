<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       country.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @subpackage fields
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;

JLoader::import('components.com_sportsmanagement.helpers.countries', JPATH_SITE);
jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');


/**
 * FormFieldCountry
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2013
 * @access    public
 */
class JFormFieldCountry extends \JFormFieldList
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
		$app = Factory::getApplication();
		$option = $app->input->getCmd('option');
		/**
		 * Initialize variables.
		 */
		$options = JSMCountries::getCountryOptions();

			 /**
		 * Merge any additional options in the XML definition.
		 */
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

}
