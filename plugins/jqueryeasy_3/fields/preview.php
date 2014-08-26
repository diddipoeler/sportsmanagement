<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.form.formfield');

class JFormFieldPreview extends JFormField {
		
	public $type = 'Preview';

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
		
		$notice_msg_tmpl = '<div style="font-weight: bold; background-color: #C7EEFE; font-size: 120%; margin: 10px 30px 0 30px; padding: 3px; text-align: center">%notice%</div>';
				
		if (!JPluginHelper::isEnabled('system', 'jqueryeasy')) {
			return $html = str_replace('%notice%', JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_ENABLEPLUGIN_LABEL'), $notice_msg_tmpl);
		}		
		
		$app = JFactory::getApplication();
		if (!$app->get('jQuery')) {
			return $html = str_replace('%notice%', JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_NOJQUERY_LABEL'), $notice_msg_tmpl);
		}
		
		$html = '';
		
		$type = strtolower($this->type);
		
		ob_start();
			require_once dirname(__FILE__).'/'.$type.'/tmpl/default.php';
			$html .= ob_get_contents();
		ob_end_clean();
		
		return $html;
	}

}
?>