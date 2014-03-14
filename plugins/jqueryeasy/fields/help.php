<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.form.formfield');

class JFormFieldHelp extends JFormField {
		
	public $type = 'Help';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	protected function getLabel() {
		
		$html = '';
		
		$type = strtolower($this->type);
		
 		ob_start();
 			require_once dirname(__FILE__) . DS . $type . DS . 'tmpl' . DS . 'default.php';
			$html .= ob_get_contents();
 		ob_end_clean();

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