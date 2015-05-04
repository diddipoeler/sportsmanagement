<?php
 /**
 * @package		Twitter Bootstrap Integration
 * @subpackage	com_cbootstrap
 * @copyright	Copyright (C) 2012 Conflate. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 * @link		http://www.conflate.nl
 */
 
defined('_JEXEC') or die( 'Restricted access' );


class CBootstrap {

	private $_errors;
	private static $_actions;

	public function __construct() {

	}
	
	public static function load()
    {
//		$comParams = JComponentHelper::getParams('com_cbootstrap');
//		
//		$db = JFactory::getDbo();
		$doc = JFactory::getDocument();
//		$uri = JFactory::getURI();
//
//		$config = JFactory::getConfig();
//		
//		$debug = $config->get('debug');
//		$loadResponsive = $comParams->get('load_respons');
//		$enableJQuery = $comParams->get('en_jquery');
//		$disableMootools = $comParams->get('dis_mootools');
//		
//		if($enableJQuery){
//			if(($jq_version = self::getJQueryVersion()) !== false){
//				switch($enableJQuery){
//					case 1:{
//						$doc->addScript($uri->root(true) . '/media/bootstrap/js/jquery-' . $jq_version . (!$debug?'.min':'') . '.js');
//					}break;
//					case 2:{
//						$googleLink = '//ajax.googleapis.com/ajax/libs/jquery/%s/jquery.min.js';
//						$version = $comParams->get('jquery_v_google', '1.11.2');
//						$doc->addScript(sprintf($googleLink, $version));
//					}break;
//				}
//			}
//		}
//		
//		if($enableJQuery && $disableMootools){
//			foreach($doc->_scripts as $src => $data){
//				if(stristr($src, 'mootools')){
//					unset($doc->_scripts[$src]);
//				}
//			}
//		}elseif($enableJQuery && !$disableMootools){
//			$doc->addScriptDeclaration('jQuery.noConflict();');
//		}
		
		//$doc->addScript($uri->root(true) . '/media/bootstrap/js/bootstrap.' . (!$debug?'min.':'') . 'js');
		//$doc->addStyleSheet($uri->root(true) . '/media/bootstrap/css/bootstrap.' . (!$debug?'min.':'') . 'css');

if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{

//if ( COM_SPORTSMANAGEMENT_LOAD_BOOTSTRAP )
//{    

// Joomla! 2.5 code here
JFactory::getDocument()->addScript('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js');
JFactory::getDocument()->addScript('http://getbootstrap.com/2.3.2/assets/js/bootstrap-tab.js');
JFactory::getDocument()->addStyleSheet('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css');
JFactory::getDocument()->addStyleSheet('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css');

//}

} 
elseif(version_compare(JVERSION,'1.7.0','ge')) 
{
// Joomla! 1.7 code here
} 
elseif(version_compare(JVERSION,'1.6.0','ge')) 
{
// Joomla! 1.6 code here
} 
else 
{
// Joomla! 1.5 code here
}
		
//        $doc->addScript('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js');
//		$doc->addStyleSheet('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css');
//        $doc->addStyleSheet('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css');
        
//        if($loadResponsive){
//			//$doc->addStyleSheet($uri->root(true) . '/media/bootstrap/css/bootstrap-responsive.' . (!$debug?'min.':'') . 'css');
//		}
		
        /*
		$query = "select `title` from `#__cbootstrap_plugins`"
				. " where `state` = 1"
				. "";
		$db->setQuery($query);
		$plugins = $db->loadObjectList();
		if(is_array($plugins) && !empty($plugins))
        {
			foreach($plugins as $plugin)
            {
				$name = (substr($plugin->title, -1, 1) == 's' ? substr($plugin->title, 0, -1) : $plugin->title);
				$doc->addScript($uri->root(true) . '/media/bootstrap/js/bootstrap-' . $name . '.js');
			}
		}
        */
	}
	
	
	

		
}
