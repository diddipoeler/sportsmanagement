<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.form.formfield');

class JFormFieldWarningjqueryui extends JFormField {
		
	public $type = 'Warningjqueryui';

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
		
		$jQueryIsSet = false;
		if ($registry->get('jqueryinfrontend') != 0) {
			$jQueryIsSet = true;
		}
		
		$jQueryUIIsSet = false;
		if ($registry->get('jqueryuiinfrontend') != 0) {
			$jQueryUIIsSet = true;
		}
		
		$html = '';
		
		$html .= '<script type="text/javascript">';
		$html .= 'jQuery(document).ready(function($) {';
		$html .= '	$("#jform_params_jqueryinfrontend").change(function() { if ( $("#jform_params_jqueryinfrontend input:checked").val() == 0 && $("#jform_params_jqueryuiinfrontend input:checked").val() > 0) { alert("'.JText::_('PLG_SYSTEM_JQUERYEASY_WARNING_CANNOTUSEJQUERYUIWITHOUTJQUERY').'"); } });';
		$html .= '	$("#jform_params_jqueryuiinfrontend").change(function() { if ( $("#jform_params_jqueryuiinfrontend input:checked").val() > 0 && $("#jform_params_jqueryinfrontend input:checked").val() == 0) { alert("'.JText::_('PLG_SYSTEM_JQUERYEASY_WARNING_CANNOTUSEJQUERYUIWITHOUTJQUERY').'"); } });';
		$html .= '});';
		$html .= '</script>';
		
		if (!$jQueryIsSet && $jQueryUIIsSet) {
			$html .= '<div class="alert alert-error"><span>'.JText::_('PLG_SYSTEM_JQUERYEASY_WARNING_CANNOTUSEJQUERYUIWITHOUTJQUERY').'</span></div>';
		}
		
		return $html;
	}

}
?>