<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.form.formfield');

class JFormFieldJqueryuiversion extends JFormField {
		
	public $type = 'Jqueryuiversion';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	protected function getLabel() {
		
		$html = '';
		
		$html .= '<div style="clear: both;"></div>';
		
		return $html;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput() {
		
		$html = '';
		
		// to check for jQuery UI
		// if ($.ui && $.ui.version) { // jQuery UI is loaded }
		
		$html .= '<script type="text/javascript">';
		$html .= 'jQuery(document).ready(function($) {';
		$html .= '	if ($.ui) {';
		$html .= '	  var version = $.ui.version;';
		$html .= '	  $(".jqueryuiversion span").replaceWith("<span>" + "'.JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_JOOMLAJQUERYUIVERSION_LABEL').'" + version + "</span>");';
		$html .= '  }';
		$html .= '});';
		$html .= '</script>';
		
		$html .= '<div class="jqueryuiversion alert alert-info">';
		
		$html .= '<span>'.JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_NOJOOMLAJQUERYUIVERSION_LABEL').'</span>';
		
		$html .= '</div>';
		
		return $html;
	}

}
?>