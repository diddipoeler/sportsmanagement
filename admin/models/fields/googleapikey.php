<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       googleapikey.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Field\TextField;

/**
 * FormFieldGoogleApiKey
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2017
 * @version   $Id$
 * @access    public
 */
class JFormFieldGoogleApiKey extends TextField
{
	protected $type = 'GoogleApiKey';

	/**
	 * Prepare the text field and use the component API key as a default only
	 * when the form does not already provide a value.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the field.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string|null       $group    The field name group control value.
	 *
	 * @return  bool
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		if (($value === null || $value === '') && !isset($element['default']))
		{
			$value = (string) ComponentHelper::getParams('com_sportsmanagement')->get('google_api_developerkey', '');
		}

		return parent::setup($element, $value, $group);
	}
}
