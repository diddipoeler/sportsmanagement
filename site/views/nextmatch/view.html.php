<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage nextmatch
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

/**
 * sportsmanagementViewNextMatch
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
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
		$this->statgames = array();
		$model           = $this->getModel();
		$match           = $model->getMatch();
        $this->document->addScript(Uri::root(true) . '/components/' . $this->option . '/assets/js/smsportsmanagement.js');

		$config      = sportsmanagementModelProject::getTemplateConfig($this->getName(), $model::$cfg_which_database);
		$tableconfig = sportsmanagementModelProject::getTemplateConfig("ranking", $model::$cfg_which_database);

		$this->project       = sportsmanagementModelProject::getProject($model::$cfg_which_database);
		$this->config        = $config;
		$this->tableconfig   = $tableconfig;
		$this->overallconfig = sportsmanagementModelProject::getOverallConfig($model::$cfg_which_database);
        $this->overallevents = sportsmanagementModelProject::getProjectEvents(0, Factory::getApplication()->input->getInt('cfg_which_database', 0));

		if (!isset($this->overallconfig['seperator']))
		{
			$this->overallconfig['seperator'] = ":";
		}

		/** We need extended_cols for "pure" config as well: TODO why do we not merge whole overall config like seen in other views? */
		$this->config['extended_cols']            = $this->overallconfig['extended_cols'];
		$this->config['show_project_kunena_link'] = $this->overallconfig['show_project_kunena_link'];

		$this->match = $match;

		if ($match)
		{
			$newmatchtext = "";

			if ($match->new_match_id > 0)
			{
				$ret          = sportsmanagementModelMatch::getMatchText($match->new_match_id);
				$matchTime    = sportsmanagementHelperHtml::showMatchTime($ret, $this->config, $this->overallconfig, $this->project);
				$matchDate    = HTMLHelper::date($ret->match_date, Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_GAMES_DATE'));
				$newmatchtext = $matchDate . " " . $matchTime . ", " . $ret->t1name . " - " . $ret->t2name;
			}

			$this->newmatchtext = $newmatchtext;
			$prevmatchtext      = "";

			if ($match->old_match_id > 0)
			{
				$ret           = sportsmanagementModelMatch::getMatchText($match->old_match_id);
				$matchTime     = sportsmanagementHelperHtml::showMatchTime($ret, $this->config, $this->overallconfig, $this->project);
				$matchDate     = HTMLHelper::date($ret->match_date, Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_GAMES_DATE'));
				$prevmatchtext = $matchDate . " " . $matchTime . ", " . $ret->t1name . " - " . $ret->t2name;
			}

			$this->oldmatchtext          = $prevmatchtext;
			$this->teams                 = $model->getMatchTeams();
			$this->referees              = $model->getReferees();
			$this->playground            = sportsmanagementModelPlayground::getPlayground($this->match->playground_id);
			$this->homeranked            = $model->getHomeRanked();
			$this->awayranked            = $model->getAwayRanked();
			$this->chances               = $model->getChances();
			$this->home_highest_home_win = $model->getHomeHighestHomeWin();
			$this->away_highest_home_win = $model->getAwayHighestHomeWin();
			$this->home_highest_home_def = $model->getHomeHighestHomeDef();
			$this->away_highest_home_def = $model->getAwayHighestHomeDef();
			$this->home_highest_away_win = $model->getHomeHighestAwayWin();
			$this->away_highest_away_win = $model->getAwayHighestAwayWin();
			$this->home_highest_away_def = $model->getHomeHighestAwayDef();
			$this->away_highest_away_def = $model->getAwayHighestAwayDef();

			$this->games      = $model->getGames();
			$this->gamesteams = $model->getTeamsFromMatches($this->games, $config);

			$previousx = $model->getpreviousx($config);
			$teams     = sportsmanagementModelProject::getTeamsIndexedByPtid(0, 'name', $model::$cfg_which_database);

			$this->previousx       = $previousx;
			$this->allteams        = $teams;
			$this->matchcommentary = sportsmanagementModelMatch::getMatchCommentary($this->match->id);
		}

		$this->gesamtspiele = array();
        if ($this->games)
		{

			foreach ($this->games as $game)
			{

if ( !array_key_exists($game->leaguename, $this->gesamtspiele)) {
$this->gesamtspiele[$game->leaguename] = new stdClass;
$this->gesamtspiele[$game->leaguename]->gesamtspiele = 0;
$this->gesamtspiele[$game->leaguename]->gewonnen = 0;
$this->gesamtspiele[$game->leaguename]->verloren = 0;
$this->gesamtspiele[$game->leaguename]->unentschieden = 0;

$this->gesamtspiele[$game->leaguename]->plustore = 0;
$this->gesamtspiele[$game->leaguename]->minustore = 0;
}

				$this->gesamtspiele[$game->leaguename]->gesamtspiele += 1;

				if ($game->team1_id == $this->teams[0]->id)
				{
				    if ( isset($game->team1_result) && isset($game->team2_result) )
				{
					if ($game->team1_result != null && $game->team2_result != null)
					{
						if ($game->team1_result > $game->team2_result)
						{
							$this->gesamtspiele[$game->leaguename]->gewonnen += 1;
						}

						if ($game->team1_result < $game->team2_result)
						{
							$this->gesamtspiele[$game->leaguename]->verloren += 1;
						}

						if ($game->team1_result == $game->team2_result)
						{
							$this->gesamtspiele[$game->leaguename]->unentschieden += 1;
						}

						$this->gesamtspiele[$game->leaguename]->plustore  += $game->team1_result;
						$this->gesamtspiele[$game->leaguename]->minustore += $game->team2_result;
					}
                    }
				}
				elseif ($game->team2_id == $this->teams[0]->id)
				{
				    if ( isset($game->team1_result) && isset($game->team2_result) )
				{
					if ($game->team1_result != null && $game->team2_result != null)
					{
						if ($game->team1_result < $game->team2_result)
						{
							$this->gesamtspiele[$game->leaguename]->gewonnen += 1;
						}

						if ($game->team1_result > $game->team2_result)
						{
							$this->gesamtspiele[$game->leaguename]->verloren += 1;
						}

						if ($game->team1_result == $game->team2_result)
						{
							$this->gesamtspiele[$game->leaguename]->unentschieden += 1;
						}

						$this->gesamtspiele[$game->leaguename]->plustore  += $game->team2_result;
						$this->gesamtspiele[$game->leaguename]->minustore += $game->team1_result;
					}
                    }
				}
                
                if ( isset($game->team1_result) && isset($game->team2_result) )
				{
				if (!isset($this->statgames['home'][$game->team1_result . '-' . $game->team2_result]))
				{
					$this->statgames['home'][$game->team1_result . '-' . $game->team2_result] = 0;
				}

				if (!isset($this->statgames['gesamt'][$game->team1_result . '-' . $game->team2_result]))
				{
					$this->statgames['gesamt'][$game->team1_result . '-' . $game->team2_result] = 0;
				}

				if (!isset($this->statgames['away'][$game->team1_result . '-' . $game->team2_result]))
				{
					$this->statgames['away'][$game->team1_result . '-' . $game->team2_result] = 0;
				}

				if (!isset($this->statgames['gesamt'][$game->team2_result . '-' . $game->team1_result]))
				{
					$this->statgames['gesamt'][$game->team2_result . '-' . $game->team1_result] = 0;
				}

				if ($game->team1_id == $this->teams[0]->id)
				{
					$this->statgames['home'][$game->team1_result . '-' . $game->team2_result]   += 1;
					$this->statgames['gesamt'][$game->team1_result . '-' . $game->team2_result] += 1;
				}
				elseif ($game->team2_id == $this->teams[0]->id)
				{
					$this->statgames['away'][$game->team1_result . '-' . $game->team2_result]   += 1;
					$this->statgames['gesamt'][$game->team2_result . '-' . $game->team1_result] += 1;
				}
                }
			}
		}

		/** Set page title */
		$pageTitle = Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_PAGE_TITLE');

		if (isset($this->teams))
		{
			$pageTitle .= ": " . $this->teams[0]->name . " " . Text::_("COM_SPORTSMANAGEMENT_NEXTMATCH_VS") . " " . $this->teams[1]->name;
		}

		$this->document->setTitle($pageTitle);

		if (!isset($this->config['table_class']))
		{
			$this->config['table_class'] = 'table';
		}

	}
}

