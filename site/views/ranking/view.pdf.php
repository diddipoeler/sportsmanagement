<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage ranking
 * @file       view.pdf.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\RankingModel;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Uri\Uri;

if (!class_exists(RankingModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/RankingModel.php';
}

/**
 * PDF ranking view.
 */
class sportsmanagementViewRanking extends HtmlView
{
    /**
     * @param mixed $tpl
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $document = $app->getDocument();
        $uri = Uri::getInstance();
        $option = $input->getCmd('option', 'com_sportsmanagement');
        $databaseSelector = $input->getInt('cfg_which_database', 0);

        $document->addScript(Uri::root(true) . '/components/' . $option . '/assets/js/smsportsmanagement.js');

        /**
         * The legacy model is retained only for the ranking calculation and
         * RSS helper until JSMRanking itself has been migrated.
         */
        $legacyRanking = $this->getModel();

        $rankingReader = new RankingModel();
        $rankingReader->setDatabaseSelector($databaseSelector);
        $project = $rankingReader->getProject();

        if (!$project) {
            $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ERROR_PROJECTMODEL_PROJECT_IS_REQUIRED'), 'error');
            return;
        }

        /** Keep the legacy compute core pointed at the same selected project/database. */
        sportsmanagementModelProject::$projectid = (int) $project->id;
        sportsmanagementModelProject::$cfg_which_database = $databaseSelector;
        sportsmanagementModelRanking::$projectid = (int) $project->id;

        $config = $rankingReader->getTemplateConfig('ranking');
        $rounds = $rankingReader->getRoundOptions('ASC');

        $this->model = $legacyRanking;
        $this->project = $project;
        $this->extended = sportsmanagementHelper::getExtended((string) ($project->extended ?? ''), 'project');
        $this->overallconfig = $rankingReader->getOverallConfig();
        $this->tableconfig = $config;
        $this->config = $config;
        $this->rssfeeditems = '';

        if ((int) ($this->overallconfig['show_project_rss_feed'] ?? 0) === 1 && $legacyRanking) {
            $rssfeedlink = $this->extended->getValue('COM_SPORTSMANAGEMENT_PROJECT_RSS_FEED');
            if ($rssfeedlink) {
                $this->rssfeeditems = $legacyRanking->getRssFeeds(
                    $rssfeedlink,
                    (int) ($this->overallconfig['rssitems'] ?? 0)
                );
            }
        }

        if ((int) ($this->config['show_half_of_season'] ?? 0) === 1) {
            if ((int) ($this->config['show_table_4'] ?? 0) === 1) {
                sportsmanagementModelRanking::$part = 1;
                sportsmanagementModelRanking::$from = 0;
                sportsmanagementModelRanking::$to = 0;
                sportsmanagementModelRanking::computeRanking($databaseSelector, 0, (string) $project->sport_type_name);
                $this->firstRank = sportsmanagementModelRanking::$currentRanking;
            }

            if ((int) ($this->config['show_table_5'] ?? 0) === 1) {
                sportsmanagementModelRanking::$part = 2;
                sportsmanagementModelRanking::$from = 0;
                sportsmanagementModelRanking::$to = 0;
                sportsmanagementModelRanking::computeRanking($databaseSelector, 0, (string) $project->sport_type_name);
                $this->secondRank = sportsmanagementModelRanking::$currentRanking;
            }

            sportsmanagementModelRanking::$part = 0;
            sportsmanagementModelRanking::$from = 0;
            sportsmanagementModelRanking::$to = 0;
        }

        sportsmanagementModelRanking::computeRanking($databaseSelector, 0, (string) $project->sport_type_name);

        $this->round = sportsmanagementModelRanking::$round;
        $this->part = sportsmanagementModelRanking::$part;
        $this->rounds = $rounds;
        $this->divisions = $rankingReader->getDivisions();
        $this->type = sportsmanagementModelRanking::$type;
        $this->from = sportsmanagementModelRanking::$from;
        $this->to = sportsmanagementModelRanking::$to;
        $this->divLevel = sportsmanagementModelRanking::$divLevel;
        $this->previousRanking = sportsmanagementModelRanking::$previousRanking;

        if ((int) ($this->config['show_table_1'] ?? 0) === 1) {
            $this->currentRanking = sportsmanagementModelRanking::$currentRanking;
        }

        if ((int) ($this->config['show_table_2'] ?? 0) === 1) {
            $this->homeRank = sportsmanagementModelRanking::$homeRank;
        }

        if ((int) ($this->config['show_table_3'] ?? 0) === 1) {
            $this->awayRank = sportsmanagementModelRanking::$awayRank;
        }

        if (
            (int) ($this->config['show_table_1'] ?? 0) !== 1
            || (int) ($this->config['show_table_2'] ?? 0) !== 1
            || (int) ($this->config['show_table_3'] ?? 0) !== 1
            || (int) ($this->config['show_table_4'] ?? 0) !== 1
            || (int) ($this->config['show_table_5'] ?? 0) !== 1
        ) {
            $this->currentRanking = sportsmanagementModelRanking::$currentRanking;
        }

        $this->current_round = $rankingReader->getCurrentRound();
        $this->teams = $rankingReader->getProjectTeamsIndexed(0);

        $rankingReason = [];
        if ((int) ($this->config['show_notes'] ?? 0) === 1) {
            foreach ($this->teams as $team) {
                if (!(float) ($team->start_points ?? 0)) {
                    continue;
                }

                $color = (float) $team->start_points < 0 ? 'red' : 'green';
                $rankingReason[$team->name] = '<font color="' . $color . '">'
                    . $team->name . ': ' . $team->start_points
                    . ' Punkte Grund: ' . ($team->reason ?? '') . '</font>';
            }
        }
        $this->ranking_notes = $rankingReason ? implode(', ', $rankingReason) : '';

        $this->previousgames = $rankingReader->getPreviousGames((int) sportsmanagementModelRanking::$round);
        $this->action = $uri->toString();

        $frommatchday = [HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_RANKING_FROM_MATCHDAY'))];
        $frommatchday = array_merge($frommatchday, $rounds);
        $tomatchday = [HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_RANKING_TO_MATCHDAY'))];
        $tomatchday = array_merge($tomatchday, $rounds);

        $this->lists = [
            'frommatchday' => $frommatchday,
            'tomatchday' => $tomatchday,
            'type' => [
                HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_RANKING_FULL_RANKING')),
                HTMLHelper::_('select.option', '1', Text::_('COM_SPORTSMANAGEMENT_RANKING_HOME_RANKING')),
                HTMLHelper::_('select.option', '2', Text::_('COM_SPORTSMANAGEMENT_RANKING_AWAY_RANKING')),
            ],
        ];

        $this->config['colors'] = $this->config['colors'] ?? '';
        $this->colors = $rankingReader->parseColors((string) $this->config['colors']);
        $this->allteams = $rankingReader->getProjectTeams(0);

        if ((int) ($this->config['show_ranking_maps'] ?? 0) === 1) {
            $this->mapconfig = $rankingReader->getTemplateConfig('map');

            if (!empty($this->mapconfig['map_kmlfile'])) {
                $this->geo = new JSMsimpleGMapGeocoder();
                $this->geo->genkml3((int) $project->id, $this->allteams);
            }

            foreach ($this->allteams as $row) {
                foreach ($rankingReader->getLogoHistory((int) $project->season_id, (int) $row->id) as $logoHistory) {
                    if (!empty($logoHistory->logo_big)) {
                        $row->logo_big = $logoHistory->logo_big;
                    }
                }

                $addressParts = [];
                if (!empty($row->club_address)) {
                    $addressParts[] = $row->club_address;
                }
                if (!empty($row->club_state)) {
                    $addressParts[] = $row->club_state;
                }
                if (!empty($row->club_location)) {
                    $addressParts[] = !empty($row->club_zipcode)
                        ? $row->club_zipcode . ' ' . $row->club_location
                        : $row->club_location;
                }
                if (!empty($row->club_country)) {
                    $addressParts[] = JSMCountries::getShortCountryName($row->club_country);
                }
                $row->address_string = implode(', ', $addressParts);
            }
        }

        $pageTitle = Text::_('COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE');
        if (isset($project->name)) {
            $pageTitle .= ': ' . $project->name;
        }
        $document->setTitle($pageTitle);
        $document->addStyleSheet(Uri::root() . 'components/' . $option . '/assets/css/ranking.css');

        parent::display($tpl);
    }
}
