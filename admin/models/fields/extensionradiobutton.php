<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version   1.0.58
 * @file
 * @author    diddipoeler, stony, svdoldie (diddipoeler@gmx.de)
 * @copyright Copyright: 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 *
 * https://docs.joomla.org/Creating_a_custom_form_field_type
 * https://hotexamples.com/examples/-/FormFieldRadio/-/php-FormFieldradio-class-examples.html
 */


defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\RadioField;

/**
 * welche joomla version ?
 */
if (version_compare(substr(JVERSION, 0, 1), '4', 'eq'))
{
	class JSMFormField extends RadioField
	{

	}
}
else
{
	include_once JPATH_LIBRARIES . '/joomla/form/fields/radio.php';

	class JSMFormField extends JFormFieldRadio
	{

	}
}

/**
 * FormFieldExtensionRadioButton
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2017
 * @version   $Id$
 * @access    public
 */
class JFormFieldExtensionRadioButton extends JSMFormField
{
	public $type = 'ExtensionRadioButton';

	/**
	 * FormFieldExtensionRadioButton::getLabel()
	 *
	 * @return void
	 */
	protected function getLabel()
	{

		return parent::getLabel();
	}


	/**
	 * FormFieldExtensionRadioButton::getInput()
	 *
	 * @return void
	 */
	protected function getInput()
	{

		/**
		 * welche joomla version ?
		 */
		if (version_compare(substr(JVERSION, 0, 1), '4', 'eq'))
		{
			// $this->class = "switcher btn-group btn-group-yesno";
			$this->type = "radio";
		}
		else
		{
			$this->class = "radio btn-group btn-group-yesno";
		}

		return parent::getInput();
	}


}
