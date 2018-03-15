<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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
 * sportsmanagementViewNextMatch
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewNextMatch extends JViewLegacy
{
	/**
	 * sportsmanagementViewNextMatch::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function display($tpl = null)
	{
		// Get a reference of the page instance in joomla
		$document= JFactory::getDocument();
        $option = JFactory::getApplication()->input->getCmd('option');
        $app = JFactory::getApplication();
    //$version = urlencode(sportsmanagementHelper::getVersion());
//		$css='components/com_sportsmanagement/assets/css/tabs.css?v='.$version;
//		$document->addStyleSheet($css);
		
		$model = $this->getModel();
		$match = $model->getMatch();

		$config = sportsmanagementModelProject::getTemplateConfig($this->getName(),$model::$cfg_which_database);
		$tableconfig = sportsmanagementModelProject::getTemplateConfig( "ranking",$model::$cfg_which_database );

		$this->project = sportsmanagementModelProject::getProject($model::$cfg_which_database);
		$this->config = $config;
		$this->tableconfig = $tableconfig;
		$this->overallconfig = sportsmanagementModelProject::getOverallConfig($model::$cfg_which_database);
		if ( !isset( $this->overallconfig['seperator'] ) )
		{
			$this->overallconfig['seperator'] = ":";
		}
		$this->match = $match;

		if ($match)
		{
			$newmatchtext = "";
			if($match->new_match_id > 0)
			{
				$ret = sportsmanagementModelMatch::getMatchText($match->new_match_id);
				$matchTime = sportsmanagementHelperHtml::showMatchTime($ret, $this->config, $this->overallconfig, $this->project);
				$matchDate = JHtml::date($ret->match_date,JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_GAMES_DATE' ));
				$newmatchtext = $matchDate . " " . $matchTime . ", " . $ret->t1name . " - " . $ret->t2name;
			}
			$this->newmatchtext = $newmatchtext;
			$prevmatchtext = "";
			if($match->old_match_id > 0)
			{
				$ret = sportsmanagementModelMatch::getMatchText($match->old_match_id);
				$matchTime = sportsmanagementHelperHtml::showMatchTime($ret, $this->config, $this->overallconfig, $this->project);
				$matchDate = JHtml::date($ret->match_date,JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_GAMES_DATE' ));
				$prevmatchtext = $matchDate . " " . $matchTime . ", " . $ret->t1name . " - " . $ret->t2name;
			}
            
			$this->oldmatchtext = $prevmatchtext;
			$this->teams = $model->getMatchTeams();
			$this->referees = $model->getReferees();
			$this->playground = sportsmanagementModelPlayground::getPlayground( $this->match->playground_id );
			$this->homeranked = $model->getHomeRanked();		
			$this->awayranked = $model->getAwayRanked();
			$this->chances = $model->getChances();		
			$this->home_highest_home_win = $model->getHomeHighestHomeWin();
			$this->away_highest_home_win = $model->getAwayHighestHomeWin();
			$this->home_highest_home_def = $model->getHomeHighestHomeDef();
			$this->away_highest_home_def = $model->getAwayHighestHomeDef();
			$this->home_highest_away_win = $model->getHomeHighestAwayWin();
			$this->away_highest_away_win = $model->getAwayHighestAwayWin();
			$this->home_highest_away_def = $model->getHomeHighestAwayDef();
			$this->away_highest_away_def = $model->getAwayHighestAwayDef();

			$games = $model->getGames();
			$gamesteams = $model->getTeamsFromMatches( $games,$config );
			$this->games = $games;
			$this->gamesteams = $gamesteams;
			
			
			$previousx = $model->getpreviousx($config);
			$teams = sportsmanagementModelProject::getTeamsIndexedByPtid(0,'name',$model::$cfg_which_database);
			
			$this->previousx = $previousx;
			$this->allteams = $teams;
            $this->matchcommentary = sportsmanagementModelMatch::getMatchCommentary($this->match->id);
            
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' homeranked <br><pre>'.print_r($this->homeranked,true).'</pre>'),'');
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' awayranked <br><pre>'.print_r($this->awayranked,true).'</pre>'),'');
            
		}

		// Set page title
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_PAGE_TITLE' );
		if ( isset( $this->teams ) ) 
		{
			$pageTitle .= ": ".$this->teams[0]->name." ".JText::_( "COM_SPORTSMANAGEMENT_NEXTMATCH_VS" )." ".$this->teams[1]->name;
		}
        
		$document->setTitle( $pageTitle );
        
/**
 *         wir benutzen bootstrap
 *         $view = JFactory::getApplication()->input->getVar( "view") ;
 *         $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
 *         $document->addCustomTag($stylelink);
 */
        
        if ( !isset($this->config['table_class']) )
        {
            $this->config['table_class'] = 'table';
        }

		parent::display( $tpl );
	}
}
?>