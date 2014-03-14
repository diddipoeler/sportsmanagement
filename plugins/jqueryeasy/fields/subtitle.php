<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.form.formfield');

class JFormFieldSubtitle extends JFormField {
		
	public $type = 'Subtitle';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	protected function getLabel() {
		
		$html = '';
		$value = trim($this->element['title']);

		$html .= '<div style="clear: both;"></div>';
		$html .= '<div style="margin: 20px 0 20px 20px; font-weight: bold; padding: 5px; color: #444444; border-bottom: 1px solid #444444;">';
		if ($value) {
			$html .= JText::_($value);
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