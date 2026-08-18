<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       color.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\Uri\Uri;

/**
 * FormFieldColor
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class JFormFieldColor extends TextField
{
	protected $type = 'Color';

	/**
	 * FormFieldColor::getInput()
	 *
	 * @return string
	 */
	public function getInput()
	{
		$document = Factory::getApplication()->getDocument();
		$document->addScript(Uri::base() . 'components/com_gcalendar/libraries/jscolor/jscolor.js');

		return parent::getInput();
	}

	/**
	 * FormFieldColor::setup()
	 *
	 * @param   SimpleXMLElement  $element
	 * @param   mixed             $value
	 * @param   string|null       $group
	 *
	 * @return bool
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return                 = parent::setup($element, $value, $group);
		$this->element['class'] = trim((string) $this->element['class'] . ' color');

		return $return;
	}
}
