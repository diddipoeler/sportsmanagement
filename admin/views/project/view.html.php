<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
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
        
		if ( $this->getLayout() == 'panel' || $this->getLayout() == 'panel_3' )
		{
			$this->_displayPanel($tpl);
			return;
		}
        
        JRequest::setVar('hidemainmenu', true);

		
        
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
	$app = JFactory::getApplication();
	$jinput = $app->input;
	$option = $jinput->getCmd('option');
	$uri = JFactory::getURI();
	$user = JFactory::getUser();
    $starttime = microtime();
           
	$this->item = $this->get('Item');
    
	if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
		{
			$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
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
 *     mittelfeld und stürmer einfügen
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
    $app->setUserState( "$option.pid", $this->item->id);
    $app->setUserState( "$option.season_id", $this->item->season_id);
    $app->setUserState( "$option.project_art_id", $this->item->project_art_id);
    $app->setUserState( "$option.sports_type_id", $this->item->sports_type_id);
    
    
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r(JComponentHelper::getParams($option)->get('which_article_component'),true).'</pre>'),'Notice');
    
    //$bar = JToolBar::getInstance('toolbar');
//    
//    switch ( JComponentHelper::getParams($option)->get('which_article_component') )
//    {
//        case 'com_content':
//        $bar->appendButton('Link', 'featured', 'Kategorie', 'index.php?option=com_categories&view=categories&extension=com_content');
//        break;
//        case 'com_k2':
//        $bar->appendButton('Link', 'featured', 'Kategorie', 'index.php?option=com_k2&view=categories');
//        break;
//    }
    
    //parent::addToolbar();    
    
//    JToolbarHelper::divider();
//	sportsmanagementHelper::ToolbarButtonOnlineHelp();
//	JToolbarHelper::preferences(JRequest::getCmd('option'));
//           
//    parent::display($tpl);   
    }
       
    /**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
	
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
    
    $isNew = $this->item->id ? $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_EDIT') : $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_ADD_NEW');
        $this->icon = 'project';
    
    //JRequest::setVar('hidemainmenu', true);
    
        $bar = JToolBar::getInstance('toolbar');
        switch ( JComponentHelper::getParams($option)->get('which_article_component') )
    {
        case 'com_content':
        //$bar->appendButton('Link', 'featured', 'Kategorie', 'index.php?option=com_categories&view=categories&extension=com_content');
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
