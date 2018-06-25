<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage playground
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewPlayground
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewPlayground extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewPlayground::init()
	 * 
	 * @return
	 */
	public function init ()
	{
		$this->app = JFactory::getApplication();
        $starttime = microtime(); 
        
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		
 
/**
 * Check for errors.
 */
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

        
        
        if ( $this->item->latitude == 255 )
        {
            $this->app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_NO_GEOCODE'),'Error');
            $this->map = false;
        }
        else
        {
            $this->map = true;
        }
		
		$this->extended	= sportsmanagementHelper::getExtended($this->item->extended, 'playground');
        
$this->document->addScript((JBrowser::getInstance()->isSSLConnection() ? "https" : "http") . '://maps.googleapis.com/maps/api/js?libraries=places&language=de');
$this->document->addScript(JURI::base() . 'components/'.$this->option.'/assets/js/geocomplete.js');

if( version_compare(JSM_JVERSION,'4','eq') ) 
{
	}
		else
		{		
		$this->document->addScript(JURI::base() . 'components/'.$this->option.'/views/playground/tmpl/edit.js');
		}

	}
 
	
	/**
	 * sportsmanagementViewPlayground::addToolBar()
	 * 
	 * @return void
	 */
	protected function addToolBar() 
	{
		$jinput = JFactory::getApplication()->input;
        $jinput->set('hidemainmenu', true);
        parent::addToolbar();
	}
    
	
}
