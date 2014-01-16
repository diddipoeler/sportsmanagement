<?php
/**
 * @version		2.7
 * @package		Tabs & Sliders (plugin)
 * @author    JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2012 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JWTSHelper {

	// Path overrides for MVC templating
	function getTemplatePath($pluginName,$folder){

		$mainframe = &JFactory::getApplication();
		$p = new JObject;
		$pluginGroup = 'content';

		if(file_exists(JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.$pluginName.DS.str_replace('/',DS,$folder))){
			$p->folder = JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.$pluginName.DS.$folder;
			$p->http = JURI::root(true).'/templates/'.$mainframe->getTemplate().'/html/'.$pluginName.'/'.$folder;
		} else {
			if(version_compare(JVERSION,'1.6.0','ge')) {
				// Joomla! 1.6+
				$p->folder = JPATH_SITE.DS.'plugins'.DS.$pluginGroup.DS.$pluginName.DS.$pluginName.DS.'tmpl'.DS.$folder;
				$p->http = JURI::root(true).'/plugins/'.$pluginGroup.'/'.$pluginName.'/'.$pluginName.'/tmpl/'.$folder;
			} else {
				// Joomla! 1.5
				$p->folder = JPATH_SITE.DS.'plugins'.DS.$pluginGroup.DS.$pluginName.DS.'tmpl'.DS.$folder;
				$p->http = JURI::root(true).'/plugins/'.$pluginGroup.'/'.$pluginName.'/tmpl/'.$folder;
			}
		}
		return $p;
	}

} // end class
