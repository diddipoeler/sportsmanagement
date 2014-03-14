<?php
/**
 * @version		2.7
 * @package		Tabs & Sliders (plugin)
 * @author    JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2012 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if(version_compare(JVERSION,'1.6.0','ge')) {
	jimport('joomla.form.formfield');
	class JFormFieldTemplate extends JFormField {

		var	$type = 'template';

		function getInput(){
			return JElementTemplate::fetchElement($this->name, $this->value, $this->element, $this->options['control']);
		}

	}
}

jimport('joomla.html.parameter.element');

class JElementTemplate extends JElement {

	var $_name = 'template';

	function fetchElement($name, $value, & $node, $control_name) {

		jimport('joomla.filesystem.folder');
		$mainframe = &JFactory::getApplication();
		$fieldName = (version_compare( JVERSION, '1.6.0', 'ge' )) ? $name : $control_name.'['.$name.']';

		if(version_compare(JVERSION,'1.6.0','ge')) {
			$pluginTemplatesPath = JPATH_SITE.DS.'plugins'.DS.'content'.DS.'jw_ts'.DS.'jw_ts'.DS.'tmpl';
		} else {
			$pluginTemplatesPath = JPATH_SITE.DS.'plugins'.DS.'content'.DS.'jw_ts'.DS.'tmpl';
		}
		$pluginTemplatesFolders = JFolder::folders($pluginTemplatesPath);
		
		$db =& JFactory::getDBO();
		if(version_compare( JVERSION, '1.6.0', 'ge' )) {
			$query = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home = 1";
		} else {
			$query = "SELECT template FROM #__templates_menu WHERE client_id = 0 AND menuid = 0";
		}
		
		$db->setQuery($query);
		$template = $db->loadResult();
		$templatePath = JPATH_SITE.DS.'templates'.DS.$template.DS.'html'.DS.'jw_ts';
		
		if (JFolder::exists($templatePath)){
			$templateFolders = JFolder::folders($templatePath);
			$folders = @array_merge($templateFolders, $pluginTemplatesFolders);
			$folders = @array_unique($folders);
		} else {
			$folders = $pluginTemplatesFolders;
		}

		sort($folders);

		$options = array();
		foreach($folders as $folder) {
			$options[] = JHTML::_('select.option', $folder, $folder);
		}

		return JHTML::_('select.genericlist', $options, $fieldName, 'class="inputbox"', 'value', 'text', $value);
	}

}
