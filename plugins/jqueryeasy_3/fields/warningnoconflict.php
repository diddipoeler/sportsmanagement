<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.form.formfield');

class JFormFieldWarningnoconflict extends JFormField {
		
	public $type = 'Warningnoconflict';

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
		
		jimport('joomla.plugin.helper');
		
		$plugin = JPluginHelper::getPlugin('system', 'jqueryeasy');
		$params = $plugin->params;
		
		$registry = new JRegistry();
		$registry->loadString($params);
		
		$jQueryFromTemplateIsSet = false;
		if ($registry->get('jqueryinfrontend') == 2) {
			$jQueryFromTemplateIsSet = true;
		}
		
		$jQueryNoConflictSetToNo = false;
		if ($registry->get('addnoconflictfrontend') == 0) {
			$jQueryNoConflictSetToNo = true;
		}
		
		$html = '';
		
		$html .= '<script type="text/javascript">';
		$html .= 'jQuery(document).ready(function($) {';
		$html .= '	$("#jform_params_addnoconflictfrontend").change(function() { if ( $("#jform_params_jqueryinfrontend input:checked").val() == 2 && $("#jform_params_addnoconflictfrontend input:checked").val() > 0) { alert("'.JText::_('PLG_SYSTEM_JQUERYEASY_WARNING_NOCONFLICTADDEDBYTEMPLATE').'"); } });';
		$html .= '});';
		$html .= '</script>';
		
		if ($jQueryFromTemplateIsSet && !$jQueryNoConflictSetToNo) {
			$html .= '<div class="alert alert-info"><span>'.JText::_('PLG_SYSTEM_JQUERYEASY_WARNING_NOCONFLICTADDEDBYTEMPLATE').'</span></div>';
		}
		
		return $html;
	}

}
?>