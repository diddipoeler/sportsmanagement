<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage nextmatch
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\NextmatchViewDataModel;
use Diddipoeler\Component\SportsManagement\Site\Model\PlaygroundModel;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

if (!class_exists(NextmatchViewDataModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/NextmatchViewDataModel.php';
}

if (!class_exists(PlaygroundModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/PlaygroundModel.php';
}

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
    public function init()
    {
        $this->alloverevents = [];
        $this->statgames = [];
        $this->games = [];
        $this->teams = [];

        $model = $this->model;
        $databaseSelector = $model->getDatabaseSelector();
        $match = $model->getMatch();

        $viewDataModel = new NextmatchViewDataModel();
        $viewDataModel->setDatabaseSelector($databaseSelector);
        $playgroundModel = new PlaygroundModel();
        $playgroundModel->setDatabaseSelector($databaseSelector);

        $this->document->addScript(Uri::root(true) . '/components/' . $this->option . '/assets/js/smsportsmanagement.js');

        $config = $model->getTemplateConfig($this->getName());
        $tableconfig = $model->getTemplateConfig('ranking');

        $this->project = $model->getProject();
        $this->config = $config;
        $this->tableconfig = $tableconfig;
        $this->overallconfig = $model->getOverallConfig();
        $this->overallevents = $viewDataModel->getProjectEvents((int) ($this->project->id ?? 0));

        if (!isset($this->overallconfig['seperator'])) {
            $this->overallconfig['seperator'] = ':';
        }

        /** We need extended_cols for "pure" config as well: TODO why do we not merge whole overall config like seen in other views? */
        $this->config['extended_cols'] = $this->overallconfig['extended_cols'] ?? 0;
        $this->config['show_project_kunena_link'] = $this->overallconfig['show_project_kunena_link'] ?? 0;

        $this->match = $match;

        if ($match) {
            $newmatchtext = '';

            if ((int) ($match->new_match_id ?? 0) > 0) {
                $ret = $viewDataModel->getMatchText((int) $match->new_match_id);
                if ($ret) {
                    $matchTime = sportsmanagementHelperHtml::showMatchTime($ret, $this->config, $this->overallconfig, $this->project);
                    $matchDate = HTMLHelper::date($ret->match_date, Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_GAMES_DATE'));
                    $newmatchtext = $matchDate . ' ' . $matchTime . ', ' . $ret->t1name . ' - ' . $ret->t2name;
                }
            }

            $this->newmatchtext = $newmatchtext;
            $prevmatchtext = '';

            if ((int) ($match->old_match_id ?? 0) > 0) {
                $ret = $viewDataModel->getMatchText((int) $match->old_match_id);
                if ($ret) {
                    $matchTime = sportsmanagementHelperHtml::showMatchTime($ret, $this->config, $this->overallconfig, $this->project);
                    $matchDate = HTMLHelper::date($ret->match_date, Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_GAMES_DATE'));
                    $prevmatchtext = $matchDate . ' ' . $matchTime . ', ' . $ret->t1name . ' - ' . $ret->t2name;
                }
            }

            $this->oldmatchtext = $prevmatchtext;
            $this->teams = $model->getMatchTeams();
            $this->referees = $model->getReferees();
            $this->playground = PlaygroundModel::getPlayground((int) ($this->match->playground_id ?? 0));
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

            $this->games = $model->getGames();
            $this->gamesteams = $model->getTeamsFromMatches($this->games, $config);

            $this->previousx = $model->getPreviousX($config);
            $this->allteams = [];
            foreach ($model->getProjectTeams(0) as $team) {
                $projectTeamId = (int) ($team->projectteamid ?? 0);
                if ($projectTeamId > 0) {
                    $this->allteams[$projectTeamId] = $team;
                }
            }
            $this->matchcommentary = $viewDataModel->getMatchCommentary((int) $this->match->id);
        }

        $this->gesamtspiele = [];

        if (!empty($this->games) && isset($this->teams[0])) {
            foreach ($this->games as $game) {
                if (!isset($game->team1_result, $game->team2_result)) {
                    continue;
                }

                if (!array_key_exists($game->leaguename, $this->gesamtspiele)) {
                    $this->gesamtspiele[$game->leaguename] = new \stdClass();
                    $this->gesamtspiele[$game->leaguename]->gesamtspiele = 0;
                    $this->gesamtspiele[$game->leaguename]->gewonnen = 0;
                    $this->gesamtspiele[$game->leaguename]->verloren = 0;
                    $this->gesamtspiele[$game->leaguename]->unentschieden = 0;
                    $this->gesamtspiele[$game->leaguename]->plustore = 0;
                    $this->gesamtspiele[$game->leaguename]->minustore = 0;
                    $this->gesamtspiele[$game->leaguename]->localwin = 0;
                    $this->gesamtspiele[$game->leaguename]->localdraw = 0;
                    $this->gesamtspiele[$game->leaguename]->locallost = 0;
                    $this->gesamtspiele[$game->leaguename]->awaywin = 0;
                    $this->gesamtspiele[$game->leaguename]->awaydraw = 0;
                    $this->gesamtspiele[$game->leaguename]->awaylost = 0;
                }

                $this->gesamtspiele[$game->leaguename]->gesamtspiele += 1;

                if ($game->team1_id == $this->teams[0]->id) {
                    if ($game->team1_result > $game->team2_result) {
                        $this->gesamtspiele[$game->leaguename]->gewonnen += 1;
                        $this->gesamtspiele[$game->leaguename]->localwin += 1;
                    }

                    if ($game->team1_result < $game->team2_result) {
                        $this->gesamtspiele[$game->leaguename]->verloren += 1;
                        $this->gesamtspiele[$game->leaguename]->localdraw += 1;
                    }

                    if ($game->team1_result == $game->team2_result) {
                        $this->gesamtspiele[$game->leaguename]->unentschieden += 1;
                        $this->gesamtspiele[$game->leaguename]->locallost += 1;
                    }

                    $this->gesamtspiele[$game->leaguename]->plustore += $game->team1_result;
                    $this->gesamtspiele[$game->leaguename]->minustore += $game->team2_result;
                }

                if ($game->team2_id == $this->teams[0]->id) {
                    if ($game->team1_result < $game->team2_result) {
                        $this->gesamtspiele[$game->leaguename]->gewonnen += 1;
                        $this->gesamtspiele[$game->leaguename]->awaywin += 1;
                    }

                    if ($game->team1_result > $game->team2_result) {
                        $this->gesamtspiele[$game->leaguename]->verloren += 1;
                        $this->gesamtspiele[$game->leaguename]->awaydraw += 1;
                    }

                    if ($game->team1_result == $game->team2_result) {
                        $this->gesamtspiele[$game->leaguename]->unentschieden += 1;
                        $this->gesamtspiele[$game->leaguename]->awaylost += 1;
                    }

                    $this->gesamtspiele[$game->leaguename]->plustore += $game->team2_result;
                    $this->gesamtspiele[$game->leaguename]->minustore += $game->team1_result;
                }

                if (!isset($this->statgames['home'][$game->team1_result . '-' . $game->team2_result])) {
                    $this->statgames['home'][$game->team1_result . '-' . $game->team2_result] = 0;
                }

                if (!isset($this->statgames['gesamt'][$game->team1_result . '-' . $game->team2_result])) {
                    $this->statgames['gesamt'][$game->team1_result . '-' . $game->team2_result] = 0;
                }

                if (!isset($this->statgames['away'][$game->team1_result . '-' . $game->team2_result])) {
                    $this->statgames['away'][$game->team1_result . '-' . $game->team2_result] = 0;
                }

                if (!isset($this->statgames['gesamt'][$game->team2_result . '-' . $game->team1_result])) {
                    $this->statgames['gesamt'][$game->team2_result . '-' . $game->team1_result] = 0;
                }

                if ($game->team1_id == $this->teams[0]->id) {
                    $this->statgames['home'][$game->team1_result . '-' . $game->team2_result] += 1;
                    $this->statgames['gesamt'][$game->team1_result . '-' . $game->team2_result] += 1;
                } elseif ($game->team2_id == $this->teams[0]->id) {
                    $this->statgames['away'][$game->team1_result . '-' . $game->team2_result] += 1;
                    $this->statgames['gesamt'][$game->team2_result . '-' . $game->team1_result] += 1;
                }
            }
        }

        /** Set page title */
        $pageTitle = Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_PAGE_TITLE');

        if (isset($this->teams[0], $this->teams[1])) {
            $pageTitle .= ': ' . $this->teams[0]->name . ' ' . Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_VS') . ' ' . $this->teams[1]->name;
        }

        $this->document->setTitle($pageTitle);

        if (!isset($this->config['table_class'])) {
            $this->config['table_class'] = 'table';
        }
    }
}
