<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

// no direct access
defined( '_JEXEC' ) or die;

// import library dependencies
jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.file');

class plgSystemJqueryeasy extends JPlugin {
	
	public function __construct( &$subject, $config ) {
		
		parent::__construct( $subject, $config );	
				
		$this->loadLanguage();
        
        $this->_jqpath = '';
		$this->_jquipath = '';
		$this->_jquicsspath = '';		
		$this->_jqnoconflictpath = '';
		
		$this->_jqmigratepath = '';
		
		$this->_supplement_scripts = array();
		$this->_supplement_stylesheets = array();
		
		$this->_showreport = false;
		$this->_verbose_array = array();
		
		$this->_usejQuery = false;
		$this->_usejQueryUI = false;
		
		$this->_enabled = true;
		
		$this->_timeafterroute = 0;
		$this->_timebeforerender = 0;
		$this->_timeafterrender = 0;
		
		$this->_back_compat_path = true;
	}
	
	function onAfterRoute() {
		
		if (JFactory::getDocument()->getType() !== 'html') { 
			// put here so JFactory::getDocument() does not break feeds (will break if used in any function before onAfterRoute)
			// https://groups.google.com/forum/?fromgroups#!topic/joomla-dev-general/S0GYKhLm92A
			$this->_enabled = false;
			return;
		}
		
        $app = JFactory::getApplication();		
		$doc = JFactory::getDocument();
		
		$this->_showreport = $this->params->get('showreport', false);
		$this->_back_compat_path = $this->params->get('back_compat_paths', true);
		
		$time_start = microtime(true);
				
		$suffix = $app->isAdmin() ? 'backend' : 'frontend';
		
		// disable plugin in selected templates
		if ($app->isSite()) {
			
			$templates_array = $this->params->get('templateid', array('none'));
			
			if (!is_array($templates_array)) { // before the plugin is saved, the value is the string 'none'
				$templates_array = explode(' ', $templates_array);
			}
			
			$array_of_template_values = array_count_values($templates_array);
			if (isset($array_of_template_values['none']) && $array_of_template_values['none'] > 0) { // 'none' was selected
				// keep the plugin enabled
			} else {		
				if (!empty($app->getTemplate(true)->id)) {		
					$current_template_id = $app->getTemplate(true)->id;				
					foreach ($array_of_template_values as $key => $value) {
						if ($current_template_id == $key) {
							$this->_enabled = false;
							return;
						}
					}	
				}		
			}		
		}		
		
		// enable plugin only on the allowed pages
		$includedPaths = trim( (string) $this->params->get('enableonlyin'.$suffix, ''));
		if ($includedPaths) {
			$paths = array_map('trim', (array) explode("\n", $includedPaths));
			$current_uri_string = JURI::getInstance()->toString();
			
			//if ($this->_showreport) {
			//	$this->_verbose_array[] = JText::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_ENABLEPLUGININPAGES');
			//	$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_CURRENTURI', $current_uri_string);
			//}			
			
			$found = false;
			foreach ($paths as $path) {	
				$paths_compare = self::path_compare($current_uri_string, $path, $this->_back_compat_path);
				if ($paths_compare) {
					$found = true;
				}
			}				
			if (!$found) {
				$this->_enabled = false;
				return;
			}
		} else {		
			// disable plugin in the listed pages
			$excludedPaths = trim( (string) $this->params->get('disablein'.$suffix, ''));
			if ($excludedPaths) {
				$paths = array_map('trim', (array) explode("\n", $excludedPaths));
				$current_uri_string = JURI::getInstance()->toString();
			
				//if ($this->_showreport) {
				//	$this->_verbose_array[] = JText::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_DISABLEPLUGININPAGES');
				//	$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_CURRENTURI', $current_uri_string);
				//}			
			
				foreach ($paths as $path) {					
					$paths_compare = self::path_compare($current_uri_string, $path, $this->_back_compat_path);
					if ($paths_compare) {
						$this->_enabled = false;
						return;
					}
				}
			}
		}
		
		// prepare spaces to fill with script, stylesheets, scripts and stylesheets declarations
		
		$javascript = trim( (string) $this->params->get('addjavascript'.$suffix, ''));
		if (!empty($javascript)) {
			$this->_supplement_scripts = array_map('trim', (array) explode("\n", $javascript));
			$i = 0;
			foreach($this->_supplement_scripts as $path) {
				$doc->addScript("ADD_SCRIPT_HERE".$i);
				$i++;
			}
		}
		
		$javascript_declaration = trim( (string) $this->params->get('addjavascriptdeclaration'.$suffix, ''));
		if (!empty($javascript_declaration)) {
			$doc->addScriptDeclaration("ADD_SCRIPT_DECLARATION_HERE");
		}
			
		$css = trim( (string) $this->params->get('addcss'.$suffix, ''));
		if (!empty($css)) {
			$this->_supplement_stylesheets = array_map('trim', (array) explode("\n", $css));
			$i = 0;
			foreach($this->_supplement_stylesheets as $path) {
				$doc->addStyleSheet("ADD_STYLESHEET_HERE".$i);
				$i++;
			}
		}
		
		$css_declaration = trim( (string) $this->params->get('addcssdeclaration'.$suffix, ''));
		if (!empty($css_declaration)) {
			$doc->addStyleDeclaration("ADD_STYLESHEET_DECLARATION_HERE");
		}	
		
		$useWhat = $this->params->get('jqueryin'.$suffix, 0);
		switch ($useWhat) {
			case 1: $this->_usejQuery = true; break;
			case 2: $this->_usejQuery = true; $this->_usejQueryUI = true; break;
			default: break;
		}	
		
		$time_end = microtime(true);
		$this->_timeafterroute = $time_end - $time_start;
		
		if (!$this->_usejQuery) {
			return;
		}
		
        $jQueryVersion = $this->params->get('jqueryversion'.$suffix, '1.8');		
		$jQuerySubversion = $this->params->get('jquerysubversion'.$suffix, '');
		if ($jQuerySubversion != '') {
			$jQuerySubversion = '.'.$jQuerySubversion;
		}
        		
		$jQueryHTTP = $this->params->get('whichhttp'.$suffix,'https');
		$jQueryHTTP = ($jQueryHTTP == 'none') ? '' : $jQueryHTTP.':';
		
		$jQueryCompressed = '';
		if ($this->params->get('compression'.$suffix,'compressed') == 'compressed') {
			$jQueryCompressed = '.min';
		}
		
		// jQuery path
        
        if ($jQueryVersion == 'local') {
        	$localVersionPath = trim($this->params->get('localversion'.$suffix, ''));
        	if ($localVersionPath) {         		
        		if (JFile::exists(JPATH_ROOT.$localVersionPath)) {
        		//if (JFile::exists($_SERVER['DOCUMENT_ROOT'].JURI::root(true).$localVersionPath)) {
        			$this->_jqpath = JURI::root(true).$localVersionPath;
        		} else {
        			if ($this->_showreport) {
        				$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_COULDNOTFINDFILE', JPATH_ROOT.$localVersionPath);
        			}
        		}
        	} else {
        		if ($this->_showreport) {
        			$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_EMPTYLOCALFILE', 'jQuery');
        		}
        	}
        } else {
        	$this->_jqpath = $jQueryHTTP."//ajax.googleapis.com/ajax/libs/jquery/".$jQueryVersion.$jQuerySubversion."/jquery".$jQueryCompressed.".js";
        }
		
        if (!empty($this->_jqpath)) {
        	$doc->addScript("JQEASY_JQLIB");	
        }	
		
		// jQuery Migrate
		
		$localPathMigrate = trim($this->params->get('localpathmigrate'.$suffix, ''));		
		if ($localPathMigrate) {
			if (JFile::exists(JPATH_ROOT.$localPathMigrate)) {
				$this->_jqmigratepath = JURI::root(true).$localPathMigrate;
			} else {
				if ($this->_showreport) {
					$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_COULDNOTFINDFILE', JPATH_ROOT.$localPathMigrate);
				}
			}
		}		
		
        if (!empty($this->_jqmigratepath)) {
        	$doc->addScript("JQEASY_JQMIGRATELIB");	
        }	
		
        // no conflict path
        
		$addjQueryNoConflict = $this->params->get('addnoconflict'.$suffix, 1);
		if ($addjQueryNoConflict == 1) {
        	$doc->addScriptDeclaration("JQEASY_JQNOCONFLICT");
		} else if ($addjQueryNoConflict == 2) {
			$doc->addScript("JQEASY_JQNOCONFLICT");
        	$this->_jqnoconflictpath = JURI::root(true)."/plugins/system/jqueryeasy/jquerynoconflict.js";
		}
		
		$time_end = microtime(true);
		$this->_timeafterroute = $time_end - $time_start;
	
		$app->set('jQuery', true);
		
		if (!$this->_usejQueryUI) {
			return;
		}
		
		$jQueryUIVersion = $this->params->get('jqueryuiversion'.$suffix, '1.9');	
		$jQueryUISubversion = $this->params->get('jqueryuisubversion'.$suffix, '');
		if ($jQueryUISubversion != '') {
			$jQueryUISubversion = '.'.$jQueryUISubversion;
		}
		$jQueryUITheme = $this->params->get('jqueryuitheme'.$suffix,'base');			
		
		// jQuery UI path
		
		if ($jQueryUIVersion == 'local') {
			$localVersionPath = trim($this->params->get('localuiversion'.$suffix, ''));
			if ($localVersionPath) {
				if (JFile::exists(JPATH_ROOT.$localVersionPath)) {
					$this->_jquipath = JURI::root(true).$localVersionPath;
				} else {
					if ($this->_showreport) {
						$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_COULDNOTFINDFILE', JPATH_ROOT.$localVersionPath);
					}
				}
			} else {
				if ($this->_showreport) {
					$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_EMPTYLOCALFILE', 'jQuery UI');
				}
			}
		} else {
			$this->_jquipath = $jQueryHTTP."//ajax.googleapis.com/ajax/libs/jqueryui/".$jQueryUIVersion.$jQueryUISubversion."/jquery-ui".$jQueryCompressed.".js";
		}
		
		if (!empty($this->_jquipath)) {
			$doc->addScript("JQEASY_JQUILIB");
		}
		
		// jQuery UI CSS path
	
		if ($jQueryUITheme != 'none') {				
			if ($jQueryUITheme == 'custom') {
				$localVersionPath = trim($this->params->get('jqueryuithemecustom'.$suffix, ''));
				if ($localVersionPath) {
					if (JFile::exists(JPATH_ROOT.$localVersionPath)) {
						$this->_jquicsspath = JURI::root(true).$localVersionPath;
					} else {
						if ($this->_showreport) {
							$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_COULDNOTFINDFILE', JPATH_ROOT.$localVersionPath);
						}
					}
				} else {
					if ($this->_showreport) {
						$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_EMPTYLOCALCSSFILE', 'jQuery UI');
					}
				}
			} else {
				$this->_jquicsspath = $jQueryHTTP."//ajax.googleapis.com/ajax/libs/jqueryui/".$jQueryUIVersion.$jQueryUISubversion."/themes/".$jQueryUITheme."/jquery-ui.css";
			}
			
			if (!empty($this->_jquicsspath)) {
				$doc->addStyleSheet("JQEASY_JQUICSS");
			}
		}

		$time_end = microtime(true);
		$this->_timeafterroute = $time_end - $time_start;
	}
	
	function onBeforeRender() {

		if (!$this->_enabled) {
			return;
		}
		
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();	
		
		if ($app->isAdmin() && !$this->_usejQuery) { // no need to reorder libraries if jQuery not used in backend
			return;
		}
		
		$time_start = microtime(true);

		// at this point, jQuery and MooTools libraries are loaded in the wrong order, if jQuery is enabled
		// we have jQuery, MooTools and other libraries loaded in that order
		// take all 'media/system/js' libraries and put them in front of all others	
		
		$headerdata = $doc->getHeadData();
		$scripts = $headerdata['scripts'];
		$headerdata['scripts'] = array();
				
		$ignore_caption = $this->params->get('disablecaptions', 0);
		$library_needing_mootools_present = false;
		
		$js_needing_mootools = array("mooRainbow.js", "mootree.js");
		$js_to_ignore = array("mootools-core.js", "mootools-more.js"); // uncompressed versions are not taken into account because used for debug
			
		$quoted_path = preg_quote('media/system/js/', '/');		
		
		// put MooTools in front
		foreach ($scripts as $url => $type) {
			if (preg_match('#'.$quoted_path.'#s', $url)) {	
				
				if ($app->isSite()) {
					foreach ($js_needing_mootools as $library) {
						if (preg_match('#'.$quoted_path.$library.'#s', $url)) {
							$library_needing_mootools_present = true;
						}
					}
				}
				
				if ($ignore_caption && $app->isSite() && preg_match('#'.$quoted_path.'caption#s', $url)) {
					//unset($headerdata['scripts'][$url]);
				} else {
					$headerdata['scripts'][$url] = $type;
				}
				
				unset($scripts[$url]);
			}
		}

		// make sure we follow with all jQuery scripts
		foreach ($scripts as $url => $type) {
			if (preg_match('#JQEASY_#s', $url)) {
				$headerdata['scripts'][$url] = $type;
				unset($scripts[$url]);
			}
		}
		
		// remaining scripts
		foreach ($scripts as $url => $type) {
			$headerdata['scripts'][$url] = $type;
		}
		
		if ($this->_showreport) {
			$this->_verbose_array[] = JText::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_REORDEREDLIBRARIES');
		}
	
		// get rid of MooTools only if :
		// + on its own, with no other library using MooTools loaded from media/system/js
		// + in frontend
		// + view != form (submit weblink, edit or create article)
		// + tmpl != component (component.php used to get images from editor for instance)
		// + not in specified pages
		
		if ($this->params->get('disablemootools', 0) && $app->isSite() && !$library_needing_mootools_present) {
			// $_GET['view'] available if SEF URLs set to yes or not			
			if (isset($_GET['view']) && $_GET['view'] == 'form') { 
				// do nothing
			} else if (isset($_GET['tmpl']) && $_GET['tmpl'] == 'component') {
				// do nothing
			} else {
				foreach ($headerdata['scripts'] as $url => $type) {
					$ignore = false;
					foreach ($js_to_ignore as $library) {
						if (preg_match('#'.$quoted_path.$library.'#s', $url)) {
							// found library to ignore
							$ignore = true;
						}
					}
				
					// DO NOT REMOVE if a page has been specifically listed as not to disable MooTools
					$exceptPaths = trim( (string) $this->params->get('keepmootoolsin', ''));
					if ($exceptPaths) {
						$this->_exceptpaths = array_map('trim', (array) explode("\n", $exceptPaths));
						$current_uri_string = JURI::getInstance()->toString();
			
						//if ($this->_showreport) {
						//	$this->_verbose_array[] = JText::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_DISABLEMOOTOOLSINPAGES');
						//	$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_CURRENTURI', $current_uri_string);
						//}			
				
						foreach ($this->_exceptpaths as $path) {							
							$paths_compare = self::path_compare($current_uri_string, $path, $this->_back_compat_path);
							if ($paths_compare) {
								$ignore = false;
							}
						}
					}
				
					if ($ignore) {
						unset($headerdata['scripts'][$url]);
						if ($this->_showreport) {
							$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDMOOTOOLSLIBRARY', $url);
						}
					}
				}
			}		
		}
		
		// also we have script declarations loaded alongside MooTools libraries
		// if getting rid of libraries, also need to get rid of script declarations associated to them
		
		if ($ignore_caption && $app->isSite()) {
			$headerdata['script'] = preg_replace('#([a-zA-Z0-9();,\'_:\.-\s]*)JCaption([a-zA-Z0-9();,\'_:\.-\s]*)#', '', $headerdata['script']);
			//$headerdata['script'] = preg_replace('#([a-zA-Z0-9();,\'_:\.-\s]*)function(){}#', '', $headerdata['script']);
			if ($this->_showreport) {	
				$this->_verbose_array[] = JText::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVECAPTION');
			}
		}	
		
		$doc->setHeadData($headerdata);		

		$time_end = microtime(true);
		$this->_timebeforerender = $time_end - $time_start;
	}
		
	function onAfterRender() {
		
		if (!$this->_enabled) {
			return;
		}
		
		$app = JFactory::getApplication();
		
		$time_start = microtime(true);
		
		$suffix = $app->isAdmin() ? 'backend' : 'frontend';	
		
		$body = JResponse::getBody();
		
		if ($this->_usejQuery) {
		
			$remainingScripts = trim( (string) $this->params->get('stripremainingscripts'.$suffix, ''));
			if ($remainingScripts) {
				$remainingScripts = array_map('trim', (array) explode("\n", $remainingScripts));
				foreach ($remainingScripts as $script) {
					$count = 0;
					$body = preg_replace('#<script[^>]*'.$script.'[^>]*></script>#', '', $body, -1, $count);
					if ($count > 0 && $this->_showreport) {
						$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_STRIPPEDREMAININGSCRIPT', $script, $count);
					}
				}
			}
			
			$remainingStylesheets = trim( (string) $this->params->get('stripremainingcss'.$suffix, ''));
			if ($remainingStylesheets) {
				$remainingStylesheets = array_map('trim', (array) explode("\n", $remainingStylesheets));
				foreach ($remainingStylesheets as $stylesheet) {
					$count = 0;
					$body = preg_replace('#<link[^>]*'.$stylesheet.'[^>]*/>#', '', $body, -1, $count);
					if ($count > 0 && $this->_showreport) {
						$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_STRIPPEDREMAININGCSS', $stylesheet, $count);
					}
				}
			}		
			
			// remove all '...jQuery.noConflict(...);' or '... $.noConflict(...);'
			$removejQueryNoConflict = $this->params->get('removenoconflict'.$suffix, 1);
			if ($removejQueryNoConflict) {
				$matches = array();
				if (preg_match_all('#[^}^;^\n^>]*(jQuery|\$)\.noConflict\((true|false|)\);#', $body, $matches, PREG_SET_ORDER) > 0) {	

					$quoted_javascript = preg_quote('<script type="text/javascript">', '/');
					
					foreach ($matches as $match) {						
						$quoted_match = preg_quote($match[0], '#'); // prepares for regexp
						
						if (preg_match('#('.$quoted_javascript.'[\S\s]*?'.$quoted_match.')#', $body)) { // makes sure we are in a javascript tag with anything in between the script tag and the noConflict code
							$body = preg_replace('#'.$quoted_match.'#', '', $body, 1);
							if ($this->_showreport) {
								$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDNOCONFLICTSCRIPTDECLARATIONS', $match[0]);
							}
						}
					}
			
					$count = 0;
					$body = preg_replace('#<script type="text/javascript">[\s]*?</script>#', '', $body, -1, $count); // remove newly empty scripts, if any
					if ($count > 0 && $this->_showreport) {
						$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDEMPTYSCRIPTTAGS', $count);
					}
				}
				
				// remove potential jquery-noconflict.js (different combinations)
				$count = 0;
				$body = preg_replace('#src="([\\\/a-zA-Z0-9_:\.-]*)jquery[.-]no[.-]*[cC]onflict\.js"#', 'GARBAGE', $body, -1, $count);
				if ($count > 0 && $this->_showreport) {
					$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDNOCONFLICTSCRIPTS', $count);
				}				
			}			
			
			$do_not_add_libraries = false;
			$move_unique_library = false;
			$move_unique_libraryui = false;
			$move_unique_cssui = false;			
			
			$replace_when_unique = $this->params->get('replacewhenunique'.$suffix, 1);
			$add_when_missing = $this->params->get('addwhenmissing'.$suffix, 1);			
			
			// remove all other references to jQuery library except some
			$ignoreScripts = trim( (string) $this->params->get('ignorescripts'.$suffix, ''));
			if ($ignoreScripts) {
				$ignoreScripts = array_map('trim', (array) explode("\n", $ignoreScripts));
			}
			
			if (empty($ignoreScripts) && $add_when_missing && $replace_when_unique) { // faster this way
				$count = 0;
				$body = preg_replace('#src="([\\\/a-zA-Z0-9_:\.-]*)jquery([0-9\.-]|core|min|pack)*?.js"#', 'GARBAGE', $body, -1, $count); // find jQuery versions
				if ($count > 0 && $this->_showreport) {
					$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERY', $count);
				}
			} else {			
				$matches = array();
				if (preg_match_all('#src="([\\\/a-zA-Z0-9_:\.-]*)jquery([0-9\.-]|core|min|pack)*?.js"#', $body, $matches, PREG_SET_ORDER) >= 0) {
										
					$nbr_of_matches = sizeof($matches);
					if ($nbr_of_matches == 0 && !$add_when_missing) {
						$do_not_add_libraries = true;
						if ($this->_showreport) {
							$this->_verbose_array[] = JText::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_NOJQUERYLIBRARIESADDED');
						}
					} elseif ($nbr_of_matches == 1 && !$replace_when_unique) {
						foreach ($matches as $match) {
							$this->_jqpath = rtrim(substr($match[0], 5), '"');
							$move_unique_library = true;
							if ($this->_showreport) {
								$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_KEEPINGUNIQUELIBRARY', $this->_jqpath);
							}
						}
					}					
					
					foreach ($matches as $match) {
						$quoted_match = preg_quote($match[0], '/'); // prepares for regexp
						$ignore = false;
						if ($ignoreScripts) {
							foreach ($ignoreScripts as $script) {
								if (stripos($match[0], $script) !== false) { // library needs to be ignored for removal
									$ignore = true;
									if ($this->_showreport) {
										$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_IGNORESCRIPT', $script);
									}
								}
							}
						}
						if (!$ignore) { // remove the library
							$body = preg_replace('#'.$quoted_match.'#', 'GARBAGE', $body, 1);
							if ($this->_showreport) {
								if ($nbr_of_matches == 1 && !$replace_when_unique) {
									// do not show any message
								} else {
									$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYLIBRARY', rtrim(substr($match[0], 5), '"'));
								}
							}
						}
					}
				}
			}
	        
			// use jQuery version set in the plugin			
			if (!empty($this->_jqpath)) {
				if ($do_not_add_libraries) {
					$body = preg_replace('#([\\\/a-zA-Z0-9_:\.-]*)JQEASY_JQLIB#', 'GARBAGE', $body, 1);
				} else {
					$body = preg_replace('#([\\\/a-zA-Z0-9_:\.-]*)JQEASY_JQLIB#', $this->_jqpath, $body, 1);
					if ($this->_showreport) {
						if ($move_unique_library) {
							$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_MOVEDJQUERY', $this->_jqpath);
						} else {
							$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERY', $this->_jqpath);
						}
					}
				}
			} else {
				if ($this->_showreport) {
					$this->_verbose_array[] = JText::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_ERRORADDINGJQUERY');
				}
			}
			
			// use jQuery Migrate
			if (!empty($this->_jqmigratepath)) {
				$body = preg_replace('#([\\\/a-zA-Z0-9_:\.-]*)JQEASY_JQMIGRATELIB#', $this->_jqmigratepath, $body, 1);
				if ($this->_showreport) {
					$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERYMIGRATE', $this->_jqmigratepath);
				}
			}
					
			// replace deleted occurences	
			$addjQueryNoConflict = $this->params->get('addnoconflict'.$suffix, 1);
	        if ($addjQueryNoConflict == 1) {
	        	if ($do_not_add_libraries) {
	        		$body = preg_replace('#JQEASY_JQNOCONFLICT#', '', $body, 1);
	        	} else {
		        	$body = preg_replace('#JQEASY_JQNOCONFLICT#', 'jQuery.noConflict();', $body, 1); // add unique jQuery.noConflict();
		        	if ($this->_showreport) {
		        		$this->_verbose_array[] = JText::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDNOCONFLICTDECLARATION');
		        	}
	        	}
	        } elseif ($addjQueryNoConflict == 2) {
	        	if ($do_not_add_libraries) {
	        		$body = preg_replace('#([\\\/a-zA-Z0-9_:\.-]*)JQEASY_JQNOCONFLICT#', 'GARBAGE', $body, 1);	        		
	        	} else {
		        	$body = preg_replace('#([\\\/a-zA-Z0-9_:\.-]*)JQEASY_JQNOCONFLICT#', $this->_jqnoconflictpath, $body, 1); // add jquerynoconflict.js   
		        	if ($this->_showreport) {
		        		$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDNOCONFLICTSCRIPT', $this->_jqnoconflictpath);
		        	}
	        	}
	        }  
	        
	        // replace '$(document).ready(function()' with 'jQuery(document).ready(function($)'
	        if ($this->params->get('replacedocumentready'.$suffix, 1)) {        
		        $count = 0;
				$body = preg_replace('#\$\(document\).ready\(function\(\)#s', 'jQuery(document).ready(function($)', $body, -1, $count);
		        if ($count > 0 && $this->_showreport) {
		        	$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REPLACEDDOCUMENTREADY', $count);
		        }
	        }        
			
			if ($this->_usejQueryUI) {
				
				// remove all other references to jQuery UI library
				if (!$replace_when_unique) {
					$matches = array();
					if (preg_match_all('#src="([\\\/a-zA-Z0-9_:\.-]*)jquery[.-]ui([0-9\.-]|core|custom|min|pack)*?.js"#', $body, $matches, PREG_SET_ORDER) > 0) {
							
						$nbr_of_matches = sizeof($matches);
						if ($nbr_of_matches == 1) {
							foreach ($matches as $match) {
								$this->_jquipath = rtrim(substr($match[0], 5), '"');
								$quoted_match = preg_quote($match[0], '/'); // prepares for regexp
								$body = preg_replace('#'.$quoted_match.'#', 'GARBAGE', $body, 1);
								if ($this->_showreport) {
									$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_KEEPINGUNIQUELIBRARYUI', $this->_jquipath);
								}
							}
						} else {							
							foreach ($matches as $match) {
								$quoted_match = preg_quote($match[0], '/'); // prepares for regexp
								$body = preg_replace('#'.$quoted_match.'#', 'GARBAGE', $body, 1);
								if ($this->_showreport) {
									$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYUILIBRARY', rtrim(substr($match[0], 5), '"'));
								}
							}
						}
					}
				} else { // faster this way
					$count = 0;
					$body = preg_replace('#src="([\\\/a-zA-Z0-9_:\.-]*)jquery[.-]ui([0-9\.-]|core|custom|min|pack)*?.js"#', 'GARBAGE', $body, -1, $count); // find jQuery UI versions
					if ($count > 0 && $this->_showreport) {
						$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYUI', $count);
					}
				}
				
				// use jQuery UI version set in the plugin
				if (!empty($this->_jquipath)) {
					if ($do_not_add_libraries) {
						$body = preg_replace('#([\\\/a-zA-Z0-9_:\.-]*)JQEASY_JQUILIB#', 'GARBAGE', $body, 1);
					} else {
						$body = preg_replace('#([\\\/a-zA-Z0-9_:\.-]*)JQEASY_JQUILIB#', $this->_jquipath, $body, 1);
						if ($this->_showreport) {
							if ($move_unique_libraryui) {
								$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_MOVEDJQUERYUI', $this->_jquipath);
							} else {
								$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERYUI', $this->_jquipath);
							}
						}
					}
				} else {
					if ($this->_showreport) {
						$this->_verbose_array[] = JText::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_ERRORADDINGJQUERYUI');
					}
				}					
			
				// remove all other references to jQuery UI stylesheets
				if (!$replace_when_unique) {
					$matches = array();
					if (preg_match_all('#href="([\\\/a-zA-Z0-9_:\.-]*)jquery[.-]ui([0-9\.-]|core|custom|min|pack)*?.css"#', $body, $matches, PREG_SET_ORDER) > 0) {
							
						$nbr_of_matches = sizeof($matches);
						if ($nbr_of_matches == 1) {
							foreach ($matches as $match) {
								$this->_jquicsspath = rtrim(substr($match[0], 5), '"');
								$quoted_match = preg_quote($match[0], '/'); // prepares for regexp
								$body = preg_replace('#'.$quoted_match.'#', 'GARBAGE', $body, 1);
								if ($this->_showreport) {
									$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_KEEPINGUNIQUECSSUI', $this->_jquicsspath);
								}
							}
						} else {
							foreach ($matches as $match) {
								$quoted_match = preg_quote($match[0], '/'); // prepares for regexp
								$body = preg_replace('#'.$quoted_match.'#', 'GARBAGE', $body, 1);
								if ($this->_showreport) {
									$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYUICSSLINK', rtrim(substr($match[0], 5), '"'));
								}
							}
						}
					}
				} else { // faster this way
					$count = 0;
					$body = preg_replace('#href="([\\\/a-zA-Z0-9_:\.-]*)jquery[.-]ui([0-9\.-]|core|custom|min|pack)*?.css"#', 'GARBAGE', $body, -1, $count); // find jQuery UI CSS versions
					if ($count > 0 && $this->_showreport) {
						$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDJQUERYUICSS', $count);
					}
				}
				
				// use jQuery UI CSS set in the plugin
				if (!empty($this->_jquicsspath)) {
					if ($do_not_add_libraries) {
						$body = preg_replace('#([\\\/a-zA-Z0-9_:\.-]*)JQEASY_JQUICSS#', 'GARBAGE', $body, 1);
					} else {
						$body = preg_replace('#([\\\/a-zA-Z0-9_:\.-]*)JQEASY_JQUICSS#', $this->_jquicsspath, $body, 1);
						if ($this->_showreport) {
							if ($move_unique_cssui) {
								$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_MOVEDJQUERYUICSS', $this->_jquicsspath);
							} else {
								$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDJQUERYUICSS', $this->_jquicsspath);
							}
						}
					}
				} else {
					if ($this->_showreport) {
						$this->_verbose_array[] = JText::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_ERRORADDINGJQUERYUICSS');
					}
				}
				
				// remove all obsolete link tags
				$count = 0;
				$body = preg_replace('#<link[^>]*GARBAGE[^>]*/>#', '', $body, -1, $count); // remove newly empty stylesheets
				if ($count > 0 && $this->_showreport) {
					$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDEMPTYLINKTAGS', $count);
				}
			}
			
			// remove all obsolete script tags
			$count = 0;
			$body = preg_replace('#<script[^>]*GARBAGE[^>]*></script>#', '', $body, -1, $count); // remove newly empty scripts
			if ($count > 0 && $this->_showreport) {
				$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDEMPTYSCRIPTTAGS', $count);
			}
		}
		
		// remove window.addEvent('load', function() {}); left after removal of 'new JCaption('img.caption');'
		$ignore_caption = $this->params->get('disablecaptions', 0);
		if ($ignore_caption && $app->isSite()) {
			$count = 0;
			$body = preg_replace('#window.addEvent\(\'load\', function\(\) {[\s]*?}\);#', '', $body, -1, $count); // remove newly empty scripts
			if ($count > 0 && $this->_showreport) {
				$this->_verbose_array[] = JText::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEDEMPTYSCRIPTWINDOWADDEVENT');
			}
		}
		
		if (!empty($this->_supplement_scripts)) {
			foreach($this->_supplement_scripts as $path) {
				$body = preg_replace('#([\\\/a-zA-Z0-9_:\.-]*)ADD_SCRIPT_HERE([0-9]*)#', $path, $body, 1);
	        	if ($this->_showreport) {
	        		$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDSCRIPT', $path);
	        	}
			}
		}
		
		$javascript_declaration = trim( (string) $this->params->get('addjavascriptdeclaration'.$suffix, ''));
		if (!empty($javascript_declaration)) {
			$body = preg_replace('#ADD_SCRIPT_DECLARATION_HERE#', $javascript_declaration, $body, 1);
        	if ($this->_showreport) {        		
        		$lines = array_map('trim', (array) explode("\n", $javascript_declaration)); 
        		$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDSCRIPTDECLARATION', $lines[0]);
        	}
		}
			
		if (!empty($this->_supplement_stylesheets)) {
			foreach($this->_supplement_stylesheets as $path) {
				$body = preg_replace('#([\\\/a-zA-Z0-9_:\.-]*)ADD_STYLESHEET_HERE([0-9]*)#', $path, $body, 1);
	        	if ($this->_showreport) {
	        		$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDSTYLESHEET', $path);
	        	}
			}
		}
		
		$css_declaration = trim( (string) $this->params->get('addcssdeclaration'.$suffix, ''));
		if (!empty($css_declaration)) {
			$body = preg_replace('#ADD_STYLESHEET_DECLARATION_HERE#', $css_declaration, $body, 1);
        	if ($this->_showreport) {
        		$lines = array_map('trim', (array) explode("\n", $css_declaration));
        		$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_ADDEDSTYLESHEETDECLARATION', $lines[0]);
        	}
		}		
		
		if ($this->params->get('removeblanklines'.$suffix, 0)) {
			$count = 0;
			$body = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $body, -1, $count); // gets all of the empty lines in the source and replaces them with a simple carriage return to preserve the content structure.
			if ($count > 0 && $this->_showreport) {
				$this->_verbose_array[] = JText::sprintf('PLG_SYSTEM_JQUERYEASY_VERBOSE_REMOVEBLANKLINES', $count);
			}
		}

		$time_end = microtime(true);
		$this->_timeafterrender = $time_end - $time_start;
			
		// output the results (verbose or not)
		
		$output = $body;
		
		if ($this->_showreport) {
			
			$pattern = '#</body>#';
			$replacement = '<div style="display: block; float: left; width: 100%; background-color: #D9EDF7; color: #48484C;">';
			$replacement .= '<dl style="padding: 15px; margin: 20px; border: 1px solid #BCE8F1; border-radius: 4px; background-color: #FFFFFF;">';
			$replacement .= '<dt style="padding: 5px; border: 1px solid #DDDDDD; border-radius: 4px; background-color: #F5F5F5; margin-bottom: 10px">'.JText::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_JQUERYEASY').'</dt>';
			
			if (!empty($this->_verbose_array)) {
				foreach ($this->_verbose_array as $verbose) {
					
					$color = '#48484C';
					switch (substr($verbose, 0, 3)) {
						case 'INF': $color = '#3A87AD'; break;
						case 'DEL': $color = '#C09853'; break;
						case 'ERR': $color = '#B94A48'; break;
						case 'ADD': $color = '#468847'; break;
						default: $color = '#48484C'; break;
					}
								
					$replacement .= '<dd style="color: '.$color.';">'.substr($verbose, 4).'</dd>';
				}
			} else {
				$replacement .= '<dd>'.JText::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_NOCHANGESMADE').'</dd>';
			}
			
			$replacement .= '<dd style="padding-top: 10px">'.JText::_('PLG_SYSTEM_JQUERYEASY_VERBOSE_EXECUTIONTIME').': '.($this->_timeafterroute + $this->_timebeforerender + $this->_timeafterrender).'</dd>';
						
			$output = preg_replace($pattern, $replacement.'</dl></div></body>', $output, 1);
		}
				
		JResponse::setBody($output);
		
		return true;
	}	
	
	static protected function path_compare($uri, $path, $use_backward_compatibility)
	{		
		$first_pos = (strpos($path, '*') === 0) ? true: false;
		$last_pos = (strrpos($path, '*') === (strlen($path) - 1)) ? true: false;
		
		if (($first_pos && $last_pos && !$use_backward_compatibility) || ($first_pos && $use_backward_compatibility)) { // any URL containing $path
			$path = trim($path, '*');
			if (stripos($uri, $path) !== false) {
				return true;
			}
		} else if ($first_pos && !$last_pos && !$use_backward_compatibility) { // any URL ending with $path
			$path = ltrim($path, '*');			
			$path_length = strlen($path);
			$uri_tip = substr($uri, -$path_length);
			if (strcasecmp($uri_tip, $path) == 0) { // compare end of URI with $path
				return true;
			}				
		} else if (!$first_pos && $last_pos && !$use_backward_compatibility) { // any URL starting with $path
			$path = rtrim($path, '*');		
			if (stripos($uri, JURI::root().ltrim($path, '/')) !== false) {
				return true;
			}
		} else {
			if (strcasecmp($uri, JURI::root().ltrim($path, '/')) == 0) { // case-insensitive string comparison
				return true;
			}
		}
		
		return false;
	}	
	
}