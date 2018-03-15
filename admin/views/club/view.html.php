<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage club
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 

/**
 * sportsmanagementViewClub
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewClub extends sportsmanagementView
{
	
	
	/**
	 * sportsmanagementViewClub::init()
	 * 
	 * @return
	 */
	public function init ()
	{
		
        $starttime = microtime(); 
        
        //$this->option = $option;
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r(JFactory::getApplication()->input->getVar('tmpl'),true).'</pre>'),'Notice');
        $this->tmpl	= $this->jinput->get('tmpl');
        
        
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' item<br><pre>'.print_r($item,true).'</pre>'),'');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        	
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
        
        if ( $this->item->latitude != 255 )
        {
            $this->googlemap = true;
        }
        else
        {
            $this->googlemap = false;
        }
       

        if ( $this->item->id )
        {
            // alles ok
            //$timestamp = strtotime($this->item->founded);
            //$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' item<br><pre>'.print_r($this->item,true).'</pre>'),'');
            if ( $this->item->founded == '0000-00-00' )
            {
                $this->item->founded = '';
                $this->form->setValue('founded','');
            }
            //$timestamp = strtotime($this->item->dissolved);
            //$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' dissolved item<br><pre>'.print_r($timestamp,true).'</pre>'),'');            
            if ( $this->item->dissolved == '0000-00-00' )
            {
                $this->item->dissolved = '';
                $this->form->setValue('dissolved','');
            }
            
        }
        else
        {
            $this->form->setValue('founded', '');
            $this->form->setValue('dissolved', '');
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
		
		$extended = sportsmanagementHelper::getExtended($this->item->extended, 'club');
		$this->extended	= $extended;
        $extendeduser = sportsmanagementHelper::getExtendedUser($this->item->extendeduser, 'club');		
		$this->extendeduser	= $extendeduser;
		//$this->assign('cfg_which_media_tool', JComponentHelper::getParams($option)->get('cfg_which_media_tool',0) );
        
        $this->checkextrafields	= sportsmanagementHelper::checkUserExtraFields();
        $lists = array();
        if ( $this->checkextrafields )
        {
            $lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields($this->item->id);
            //$app->enqueueMessage(JText::_('sportsmanagementViewClub ext_fields'.'<pre>'.print_r($lists['ext_fields'],true).'</pre>' ),'');
        }
        
        // die mannschaften zum verein
        if ( $this->item->id )
        {
            $this->teamsofclub = $this->model->teamsofclub($this->item->id);
        }
        
        $this->lists = $lists;
        
//        $document->addScript('http://maps.google.com/maps/api/js?&sensor=false&language=de');
//        $document->addScript(JURI::root(true).'/administrator/components/com_sportsmanagement/assets/js/gmap3.min.js');

$this->document->addScript((JBrowser::getInstance()->isSSLConnection() ? "https" : "http") . '://maps.googleapis.com/maps/api/js?libraries=places&language=de');
$this->document->addScript(JURI::base() . 'components/'.$this->option.'/assets/js/geocomplete.js');

        if( version_compare(JSM_JVERSION,'4','eq') ) 
{
	}
		else
		{
$this->document->addScript(JURI::base() . 'components/'.$this->option.'/views/club/tmpl/edit.js');
		}
		
//$this->document->addScript(JURI::root(true).'/administrator/components/com_sportsmanagement/assets/js/gmap3.min.js');
                             
	}
 
	
	/**
	 * sportsmanagementViewClub::addToolBar()
	 * 
	 * @return void
	 */
	protected function addToolBar() 
	{
		$app	= JFactory::getApplication();
		$jinput	= $app->input;
		$jinput->set('hidemainmenu', true);
		$isNew = $this->item->id ? $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_EDIT') : $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_ADD_NEW');
        $this->icon = 'club';
       
        parent::addToolbar();
	}
    
	
}
