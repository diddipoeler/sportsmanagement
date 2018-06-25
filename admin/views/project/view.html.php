<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');


jimport('joomla.html.parameter.element.timezones');

require_once(JPATH_COMPONENT.DS.'models'.DS.'sportstypes.php');
require_once(JPATH_COMPONENT.DS.'models'.DS.'leagues.php');


/**
 * sportsmanagementViewProject
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewProject extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewProject::init()
	 * 
	 * @return
	 */
	public function init ()
	{
		
		$tpl = '';
		$starttime = microtime(); 
        $lists = array();
        
		if ( $this->getLayout() == 'panel' || $this->getLayout() == 'panel_3' || $this->getLayout() == 'panel_4' )
		{
			$this->_displayPanel($tpl);
			return;
		}
        
        JFactory::getApplication()->input->setVar('hidemainmenu', true);

		
        
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
		
        
        $this->form->setValue('sports_type_id', 'request', $this->item->sports_type_id);
        $this->form->setValue('agegroup_id', 'request', $this->item->agegroup_id);
        
        $extended = sportsmanagementHelper::getExtended($this->item->extended, 'project');		
		$this->extended	= $extended;
        
        $extendeduser = sportsmanagementHelper::getExtendedUser($this->item->extendeduser, 'project');		
		$this->extendeduser	= $extendeduser;
        
               
        //$this->assign('cfg_which_media_tool', JComponentHelper::getParams($option)->get('cfg_which_media_tool',0) );
        
        $isNew = $this->item->id == 0;
        if ( $isNew )
        {
            $this->form->setValue('start_date', null, date("Y-m-d"));
            $this->form->setValue('start_time', null, '18:00');
            $this->form->setValue('admin', null, $this->user->id);
            $this->form->setValue('editor', null, $this->user->id);
            //$user->id ;
        }
        
        $this->checkextrafields	= sportsmanagementHelper::checkUserExtraFields();
		if ( $this->checkextrafields )
		{
			if ( !$isNew )
			{
				$lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields($this->item->id);
            }
            //$app->enqueueMessage(JText::_('view -> '.'<pre>'.print_r($lists['ext_fields'],true).'</pre>' ),'');
        }
        
        $this->form->setValue('fav_team', null, explode(',',$this->item->fav_team) );
        
        $this->lists	= $lists;
 

	}
	
	
    
	/**
	 * sportsmanagementViewProject::_displayPanel()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function _displayPanel($tpl)
	{
    $starttime = microtime();
           
	$this->item = $this->get('Item');
    
	if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
		{
			$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
		}
    
	$iProjectDivisionsCount = 0;
	$mdlProjectDivisions = JModelLegacy::getInstance("divisions", "sportsmanagementModel");
	$iProjectDivisionsCount = $mdlProjectDivisions->getProjectDivisionsCount($this->item->id);
	
	if ( $this->item->project_art_id != 3 )
	{
		$iProjectPositionsCount = 0;
		$mdlProjectPositions = JModelLegacy::getInstance('Projectpositions', 'sportsmanagementModel');
/**
 *     sind im projekt keine positionen vorhanden, dann
 *     bitte einmal die standard positionen, torwart, abwehr,
 *     mittelfeld und st�rmer einf�gen
 */
    if ( !$iProjectPositionsCount )
	{
		$mdlProjectPositions->insertStandardProjectPositions($this->item->id,$this->item->sports_type_id); 
	}
	
	$iProjectPositionsCount = $mdlProjectPositions->getProjectPositionsCount($this->item->id);
    

    
	}
    	
	$iProjectRefereesCount = 0;
	$mdlProjectReferees = JModelLegacy::getInstance('Projectreferees', 'sportsmanagementModel');
	$iProjectRefereesCount = $mdlProjectReferees->getProjectRefereesCount($this->item->id);
		
	$iProjectTeamsCount = 0;
	$mdlProjecteams = JModelLegacy::getInstance('Projectteams', 'sportsmanagementModel');
	$iProjectTeamsCount = $mdlProjecteams->getProjectTeamsCount($this->item->id);
		
	$iMatchDaysCount = 0;
	$mdlRounds = JModelLegacy::getInstance("Rounds", "sportsmanagementModel");
	$iMatchDaysCount = $mdlRounds->getRoundsCount($this->item->id);
		
	$this->project	= $this->item;
	$this->count_projectdivisions	= $iProjectDivisionsCount;
	$this->count_projectpositions	= $iProjectPositionsCount;
	$this->count_projectreferees	= $iProjectRefereesCount;
	$this->count_projectteams	= $iProjectTeamsCount;
	$this->count_matchdays	= $iMatchDaysCount;
    
    // store the variable that we would like to keep for next time
    // function syntax is setUserState( $key, $value );
    $this->app->setUserState( "$this->option.pid", $this->item->id);
    $this->app->setUserState( "$this->option.season_id", $this->item->season_id);
    $this->app->setUserState( "$this->option.project_art_id", $this->item->project_art_id);
    $this->app->setUserState( "$this->option.sports_type_id", $this->item->sports_type_id);

    }
       
    /**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
    
    $isNew = $this->item->id ? $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_EDIT') : $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_ADD_NEW');
        $this->icon = 'project';
   
        $bar = JToolBar::getInstance('toolbar');
        switch ( JComponentHelper::getParams($this->option)->get('which_article_component') )
    {
        case 'com_content':
        $bar->appendButton('Link', 'featured', 'Kategorie', 'index.php?option=com_categories&extension=com_content');
        break;
        case 'com_k2':
        $bar->appendButton('Link', 'featured', 'Kategorie', 'index.php?option=com_k2&view=categories');
        break;
    }
        
        parent::addToolbar();
	}
    
    
    
}
?>
