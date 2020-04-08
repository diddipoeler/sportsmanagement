<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       serialnumber.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Form\FormField;

if (!class_exists('sportsmanagementHelper'))
{
	/**
 * add the classes for handling
*/
	$classpath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components/com_sportsmanagement' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'sportsmanagement.php';
	JLoader::register('sportsmanagementHelper', $classpath);
}

/**
 * JFormFieldserialnumber
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2019
 * @version   $Id$
 * @access    public
 */
class JFormFieldserialnumber extends FormField
{
	public $type = 'serialnumber';

	/**
	 * JFormFieldserialnumber::getInput()
	 *
	 * @return
	 */
	protected function getInput()
	{
		$app = Factory::getApplication();

		if (!$this->value)
		{
			$this->value = sportsmanagementHelper::jsmsernum();
		}

		$html = '<input type="text" id="' . $this->id . '" name="' . $this->name . '" value="' . $this->value . '" />';

		return $html;

	}

}
