<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       projectrounds.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
FormHelper::loadFieldClass('list');

/**
 * FormFieldprojectrounds
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2018
 * @version   $Id$
 * @access    public
 */
class JFormFieldprojectrounds extends \JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var   string
	 * @since 1.6
	 */
	protected $type = 'projectrounds';

	/**
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 * @since  1.6
	 */
	protected function getOptions()
	{
		$app = Factory::getApplication();
		$options = array();

		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('a.id AS value, a.name AS text')
			->from('#__sportsmanagement_round AS a');

		if ($menuType = $this->form->getValue('project'))
		{
			$query->where('a.project_id = ' . $db->quote($menuType));
		}

		// Get the options.
		$db->setQuery($query);
		$options = $db->loadObjectList();

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
