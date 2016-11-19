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
class sportsmanagementViewTeamInfo extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewTeamInfo::init()
	 * 
	 * @return void
	 */
	function init()
	{

        $this->checkextrafields = sportsmanagementHelper::checkUserExtraFields('frontend',sportsmanagementModelTeamInfo::$cfg_which_database);

		if ( isset($this->project->id) )
		{
			$this->team = sportsmanagementModelTeamInfo::getTeamByProject(1);
			$this->club = sportsmanagementModelTeamInfo::getClub();
			$this->seasons = sportsmanagementModelTeamInfo::getSeasons( $this->config,0 );
			$this->showediticon =sportsmanagementModelProject::hasEditPermission('projectteam.edit');
			$this->projectteamid = sportsmanagementModelTeamInfo::$projectteamid;
            $this->teamid = sportsmanagementModelTeamInfo::$teamid;
			$this->trainingData = sportsmanagementModelTeamInfo::getTrainigData($this->project->id);
            if ( $this->checkextrafields )
            {
            $this->extrafields = sportsmanagementHelper::getUserExtraFields(sportsmanagementModelTeamInfo::$teamid,'frontend',sportsmanagementModelTeamInfo::$cfg_which_database);
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
			$this->merge_clubs = $merge_clubs;
      }
      
            
            if ( $this->config['show_history_leagues'] )
	{
			$this->seasons = sportsmanagementModelTeamInfo::getSeasons( $this->config,1 );
            $this->leaguerankoverview = sportsmanagementModelTeamInfo::getLeagueRankOverview( $this->seasons );
			$this->leaguerankoverviewdetail = sportsmanagementModelTeamInfo::getLeagueRankOverviewDetail( $this->seasons );
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

    	
		$this->extended = sportsmanagementHelper::getExtended($this->team->teamextended, 'team');
    
		// Set page title
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_PAGE_TITLE' );
		if ( isset( $this->team ) )
		{
			$pageTitle .= ': ' . $this->team->tname;
		}
		$this->document->setTitle( $pageTitle );

/**
 * da wir komplett mit bootstrap arbeiten benötigen wir das nicht mehr        
 * $view = $jinput->getVar( "view") ;
 * $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
 * $document->addCustomTag($stylelink);
 */
        
        if ( !isset($this->config['table_class']) )
        {
            $this->config['table_class'] = 'table';
        }

	}
}
?>