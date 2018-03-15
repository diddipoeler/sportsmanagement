<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage github
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );


/**
 * sportsmanagementViewgithub
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementViewgithub extends sportsmanagementView
{
	
    /**
     * sportsmanagementViewgithub::init()
     * 
     * @return void
     */
    function init()
	{
	   $this->gh_token = '';
        $this->api_username = '';
        $this->api_password = '';
        $this->issuetitle = '';
        $this->message = '';
        $this->milestone = 0;
        
        if( $this->app->isAdmin() )
        {
         $this->issuetitle = 'Backend-View: '.$this->jinput->getCmd('issueview').' Layout: '.$this->jinput->getCmd('issuelayout');
         $this->milestone = 1;
        }
     
   else
   {
     $this->milestone = 2;
   }
	   
       if ($this->getLayout()=='addissue' || $this->getLayout()=='addissue_3')
		{
			$this->_displayAddIssue();
			return;
		}
        
        if ($this->getLayout()=='github_result' || $this->getLayout()=='github_result_3')
		{
			$this->_displayGithubResult();
			return;
		}

        $this->document->addStyleSheet(JURI::root().'administrator/components/com_sportsmanagement/assets/css/octicons.css');
        $this->commitlist = $this->model->getGithubList();

	}
    
    
    
    /**
     * sportsmanagementViewgithub::_displayGithubResult()
     * 
     * @return void
     */
    function _displayGithubResult()
    {
        
        
    $this->setLayout('github_result');     
    }
    
    /**
     * sportsmanagementViewgithub::_displayAddIssue()
     * 
     * @return void
     */
    function _displayAddIssue()
	{
	// build the html select
        $myoptions = array();
        $myoptions[] = JHtml::_('select.option', 'bug', JText::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_BUG'));
        $myoptions[] = JHtml::_('select.option', 'duplicate', JText::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_DUPLICATE'));
        $myoptions[] = JHtml::_('select.option', 'enhancement', JText::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_ENHANCEMENT'));
        $myoptions[] = JHtml::_('select.option', 'invalid', JText::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_INVALID'));
        $myoptions[] = JHtml::_('select.option', 'question', JText::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_QUESTION'));
        $myoptions[] = JHtml::_('select.option', 'wontfix', JText::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_WONTFIX'));
        $lists['labels'] = JHtml::_('select.genericlist', $myoptions, 'labels', 'class="form-control form-control-inline" size="6"', 'value', 'text', 'bug');
        
        $myoptions = array();
        $myoptions[] = JHtml::_('select.option', '2', JText::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_MI_FRONTEND'));
        $myoptions[] = JHtml::_('select.option', '3', JText::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_MI_MODULES'));
        $myoptions[] = JHtml::_('select.option', '4', JText::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_MI_EXTENSIONS'));
        $myoptions[] = JHtml::_('select.option', '1', JText::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_MI_BACKEND'));
        $lists['milestones'] = JHtml::_('select.genericlist', $myoptions, 'milestones', 'class="form-control form-control-inline" size="4"', 'value', 'text', $this->milestone);
        
        $this->lists = $lists;   
        
        $params = \JComponentHelper::getParams($this->option);
      
        
        if ($params->get('gh_token', '')) 
 		{ 
 			$this->gh_token = $params->get('gh_token', ''); 
 		} 
 		// Set the username and password if set in the params 
 		elseif ($params->get('gh_user', '') && $params->get('gh_password')) 
 		{ 
 			$this->api_username = $params->get('gh_user', ''); 
 			$this->api_password = $params->get('gh_password', ''); 
 		} 
        
	$this->setLayout('add_issue');    
       
    }
    
       
    /**
	 * Add the page title and toolbar.
	 *
	 * @since	1.7
	 */
	protected function addToolbar()
	{
	   // Set toolbar items for the page
		$this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_TITLE');
//	JToolbarHelper::custom('github.addissue', 'purge.png', 'purge_f2.png', JText::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_ADD_ISSUE'), false);   
    sportsmanagementHelper::ToolbarButton('addissue','new',JText::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_ADD_ISSUE'),'github');
       
    }   

}
?>