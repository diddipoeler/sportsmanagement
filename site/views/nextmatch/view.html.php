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
//require_once(JPATH_COMPONENT . DS . 'models' . DS . 'results.php');
class sportsmanagementViewNextMatch extends JView
{
	function display($tpl = null)
	{
		// Get a reference of the page instance in joomla
		$document= JFactory::getDocument();
    //$version = urlencode(sportsmanagementHelper::getVersion());
//		$css='components/com_sportsmanagement/assets/css/tabs.css?v='.$version;
//		$document->addStyleSheet($css);
		
		$model = $this->getModel();
		$match = $model->getMatch();

		$config = sportsmanagementModelProject::getTemplateConfig($this->getName());
		$tableconfig = sportsmanagementModelProject::getTemplateConfig( "ranking" );

		$this->assign( 'project',			sportsmanagementModelProject::getProject() );
		$this->assignRef( 'config',			$config );
		$this->assignRef( 'tableconfig',		$tableconfig );
		$this->assign( 'overallconfig',		sportsmanagementModelProject::getOverallConfig() );
		if ( !isset( $this->overallconfig['seperator'] ) )
		{
			$this->overallconfig['seperator'] = ":";
		}
		$this->assignRef( 'match',			$match);

		if ($match)
		{
			$newmatchtext = "";
			if($match->new_match_id > 0)
			{
				$ret = $model->getMatchText($match->new_match_id);
				$matchTime = sportsmanagementHelperHtml::showMatchTime($ret, $this->config, $this->overallconfig, $this->project);
				$matchDate = JHtml::date($ret->match_date,JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_GAMES_DATE' ));
				$newmatchtext = $matchDate . " " . $matchTime . ", " . $ret->t1name . " - " . $ret->t2name;
			}
			$this->assignRef( 'newmatchtext',	$newmatchtext);
			$prevmatchtext = "";
			if($match->old_match_id > 0)
			{
				$ret = $model->getMatchText($match->old_match_id);
				$matchTime = sportsmanagementHelperHtml::showMatchTime($ret, $this->config, $this->overallconfig, $this->project);
				$matchDate = JHtml::date($ret->match_date,JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_GAMES_DATE' ));
				$prevmatchtext = $matchDate . " " . $matchTime . ", " . $ret->t1name . " - " . $ret->t2name;
			}
			$this->assignRef( 'oldmatchtext',	$prevmatchtext);

			$this->assign( 'teams', 		$model->getMatchTeams() );

			$this->assign( 'referees', 	$model->getReferees() );
			$this->assign( 'playground',	$model->getPlayground( $this->match->playground_id ) );

			$this->assign( 'homeranked',	$model->getHomeRanked() );		
			$this->assign( 'awayranked',	$model->getAwayRanked() );
			$this->assign( 'chances', 	$model->getChances() );		

			$this->assign( 'home_highest_home_win',	$model->getHomeHighestHomeWin() );
			$this->assign( 'away_highest_home_win',	$model->getAwayHighestHomeWin() );
			$this->assign( 'home_highest_home_def',	$model->getHomeHighestHomeDef() );
			$this->assign( 'away_highest_home_def',	$model->getAwayHighestHomeDef() );
			$this->assign( 'home_highest_away_win',	$model->getHomeHighestAwayWin() );
			$this->assign( 'away_highest_away_win',	$model->getAwayHighestAwayWin() );
			$this->assign( 'home_highest_away_def',	$model->getHomeHighestAwayDef() );
			$this->assign( 'away_highest_away_def',	$model->getAwayHighestAwayDef() );

			$games = $model->getGames();
			$gamesteams = $model->getTeamsFromMatches( $games );
			$this->assignRef( 'games', $games );
			$this->assignRef( 'gamesteams', $gamesteams );
			
			
			$previousx = $this->get('previousx');
			$teams = sportsmanagementModelProject::getTeamsIndexedByPtid();
			
			$this->assignRef('previousx', $previousx);
			$this->assignRef('allteams',  $teams);
            $this->assign('matchcommentary',$model->getMatchCommentary());
		}
        
        //$this->assign('show_debug_info', JComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info',0) );

		// Set page title
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_PAGE_TITLE' );
		if ( isset( $this->teams ) ) 
		{
			$pageTitle .= ": ".$this->teams[0]->name." ".JText::_( "COM_SPORTSMANAGEMENT_NEXTMATCH_VS" )." ".$this->teams[1]->name;
		}
		$document->setTitle( $pageTitle );

		parent::display( $tpl );
	}
}
?>