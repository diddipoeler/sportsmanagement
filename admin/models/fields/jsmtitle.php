<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      jsmtitle.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage fields
 */

// no direct access
defined('_JEXEC') or die ;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;


/**
 * FormFieldJSMTitle
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2018
 * @version $Id$
 * @access public
 */
class FormFieldJSMTitle extends FormField {
		
	public $type = 'JSMTitle';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	protected function getLabel() {
		
		$html = '';
		$value = trim($this->element['title']);
		$image_src = trim($this->element['imagesrc']);

		$html .= '<div style="clear: both;"></div>';
		$html .= '<div style="margin: 10px 0 10px 0; text-transform: uppercase; letter-spacing: 3px; font-weight: bold; padding: 5px; background-color: #F4F4F4; color: #444444">';
		if ($image_src) {
			$html .= '<img style="margin: -5px 2px 0 0; float: left; padding: 0px; width: 24px; height: 24px" src="'.$image_src.'">';
		}
		if ($value) {
			$html .= Text::_($value);
		}
		$html .= '</div>';

		return $html;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput() {
		return '';
	}

}
?>