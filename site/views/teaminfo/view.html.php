<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * sportsmanagementViewTeamInfo
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewTeamInfo extends JViewLegacy
{
	/**
	 * sportsmanagementViewTeamInfo::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	function display( $tpl = null )
	{
		// Get a reference of the page instance in joomla
		$document	= JFactory::getDocument();
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        
		$model		= $this->getModel();
		$config		= sportsmanagementModelProject::getTemplateConfig( $this->getName(),$model::$cfg_which_database );
		$project	= sportsmanagementModelProject::getProject($model::$cfg_which_database);
        $this->assign( 'checkextrafields', sportsmanagementHelper::checkUserExtraFields($model::$cfg_which_database) );
//        $app->enqueueMessage(JText::_('teaminfo checkextrafields -> '.'<pre>'.print_r($this->checkextrafields,true).'</pre>' ),'');
		$this->assignRef( 'project', $project );
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project<br><pre>'.print_r($this->project,true).'</pre>'),'');
        
		$isEditor = sportsmanagementModelProject::hasEditPermission('projectteam.edit');

		if ( isset($this->project->id) )
		{
			$overallconfig = sportsmanagementModelProject::getOverallConfig($model::$cfg_which_database);
			$this->assignRef('overallconfig',  $overallconfig);
			$this->assignRef('config', $config );
			$team = $model->getTeamByProject(1);
			$this->assignRef('team',  $team );
			$club = $model->getClub() ;
			$this->assignRef('club', $club);
			$seasons = $model->getSeasons( $config,0 );
			$this->assignRef('seasons', $seasons );
			$this->assignRef('showediticon', $isEditor);
			$this->assignRef('projectteamid', $model::$projectteamid);
            $this->assignRef('teamid', $model::$teamid);
            
            $trainingData = $model->getTrainigData($this->project->id);
			$this->assignRef('trainingData', $trainingData );
            if ( $this->checkextrafields )
            {
            $this->assignRef('extrafields', sportsmanagementHelper::getUserExtraFields($model::$teamid,'frontend') );
            }

			$daysOfWeek=array(
				1 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_MONDAY'),
				2 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_TUESDAY'),
				3 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_WEDNESDAY'),
				4 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_THURSDAY'),
				5 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_FRIDAY'),
				6 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SATURDAY'),
				7 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SUNDAY')
			);
			$this->assignRef( 'daysOfWeek', $daysOfWeek );
            
      
      if ( $this->team->merge_clubs )
      {
      $merge_clubs = $model->getMergeClubs( $this->team->merge_clubs );
			$this->assignRef( 'merge_clubs', $merge_clubs );
      }
      
            
            if ( $this->config['show_history_leagues'] == 1 )
	{
	   $seasons = $model->getSeasons( $config,1 );
			$this->assignRef('seasons', $seasons );
            $this->assign( 'leaguerankoverview', $model->getLeagueRankOverview( $this->seasons ) );
			$this->assign( 'leaguerankoverviewdetail', $model->getLeagueRankOverviewDetail( $this->seasons ) );
}

		}
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
            {
                $my_text = 'team -><pre>'.print_r($this->team,true).'</pre>';
            $my_text .= 'seasons -><pre>'.print_r($seasons,true).'</pre>'; 
          sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' team<br><pre>'.print_r($this->team,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' seasons<br><pre>'.print_r($seasons,true).'</pre>'),'');
}

    	
		$extended = sportsmanagementHelper::getExtended($team->teamextended, 'team');
		$this->assignRef( 'extended', $extended );
    //$this->assign('show_debug_info', JComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info',0) );
    //$this->assign('use_joomlaworks', JComponentHelper::getParams('com_sportsmanagement')->get('use_joomlaworks',0) );
    
		// Set page title
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_PAGE_TITLE' );
		if ( isset( $this->team ) )
		{
			$pageTitle .= ': ' . $this->team->tname;
		}
		$document->setTitle( $pageTitle );
        
        $view = $jinput->getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        

		parent::display( $tpl );
	}
}
?>