<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.form.formfield');

class JFormFieldJqueryversion extends JFormField {
		
	public $type = 'Jqueryversion';

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
		
		$html .= '<script type="text/javascript">';
		$html .= 'jQuery(document).ready(function($) {';
		$html .= '	var version = $.fn.jquery;';
		$html .= '	if (version != "undefined") { $(".jqueryversions span.jquery").replaceWith("<span>" + "'.JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_JOOMLAJQUERYVERSION_LABEL').'" + version + "</span>"); }';
		
		$html .= '	if ($.ui) {';
		$html .= '	  var uiversion = $.ui.version;';
		$html .= '	  $(".jqueryversions span.jqueryui").replaceWith("<span>" + "'.JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_JOOMLAJQUERYUIVERSION_LABEL').'" + uiversion + "</span>");';
		$html .= '  }';
		
		$html .= '});';
		$html .= '</script>';
		
		$html .= '<div class="jqueryversions alert alert-info">';
		
		$html .= '<span class="jquery">'.JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_NOJOOMLAJQUERYVERSION_LABEL').'</span><br />';
		$html .= '<span class="jqueryui">'.JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_NOJOOMLAJQUERYUIVERSION_LABEL').'</span>';
		
		$html .= '</div>';
		
		return $html;
	}

}
?>