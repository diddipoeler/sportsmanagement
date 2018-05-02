<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage nextmatch
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
class sportsmanagementViewNextMatch extends sportsmanagementView
{
	
    
	/**
	 * sportsmanagementViewNextMatch::init()
	 * 
	 * @return void
	 */
	function init()
	{
	
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
            
		}

		// Set page title
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_PAGE_TITLE' );
		if ( isset( $this->teams ) ) 
		{
			$pageTitle .= ": ".$this->teams[0]->name." ".JText::_( "COM_SPORTSMANAGEMENT_NEXTMATCH_VS" )." ".$this->teams[1]->name;
		}
        
		$this->document->setTitle( $pageTitle );
        
/**
 *         wir benutzen bootstrap
 *         $view = JFactory::getApplication()->input->getVar( "view") ;
 *         $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$this->option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
 *         $this->document->addCustomTag($stylelink);
 */
        
        if ( !isset($this->config['table_class']) )
        {
            $this->config['table_class'] = 'table';
        }

	}
}
?>