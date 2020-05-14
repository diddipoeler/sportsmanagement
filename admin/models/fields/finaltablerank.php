<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       leaguelevel.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');


/**
 * FormFieldfinaltablerank
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2017
 * @version   $Id$
 * @access    public
 */
class JFormFieldfinaltablerank extends \JFormFieldList
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'finaltablerank';

	/**
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 *
	 * @since 11.1
	 */
	protected function getOptions()
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');
		/**
		 * Initialize variables.
		 */
		for ($a = 1; $a < 41; $a++)
		{
			$options[] = HTMLHelper::_('select.option', $a, Text::_('COM_SPORTSMANAGEMENT_ADMIN_FINALTABLERANK') . ' - ' . $a);
		}

		/**
		 * Merge any additional options in the XML definition.
		 */
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

}
