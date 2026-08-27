<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage rankingalltime
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\RankingalltimeCalculatorModel;
use Diddipoeler\Component\SportsManagement\Site\Model\RankingalltimeModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

if (!class_exists(RankingalltimeModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/RankingalltimeModel.php';
}

if (!class_exists(RankingalltimeCalculatorModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/RankingalltimeCalculatorModel.php';
}

/**
 * All-time ranking view.
 */
class sportsmanagementViewRankingAllTime extends sportsmanagementView
{
    public function init()
    {
        $this->ranking_order = [];
        $comeFromMenu = false;

        $app = Factory::getApplication();
        $input = $app->getInput();
        $menu = $app->getMenu();
        $item = $menu->getActive();

        if ($item && (($item->query['view'] ?? '') === 'rankingalltime')) {
            $params = $menu->getParams((int) $item->id);
            foreach ($params->toArray() as $key => $value) {
                $this->config[$key] = $value;
            }
            $comeFromMenu = true;
        }

        $this->ranking_order = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) ($this->config['ranking_order'] ?? ''))
        )));

        $databaseSelector = $input->getInt('cfg_which_database', 0);
        $dataModel = new RankingalltimeModel();
        $dataModel->setDatabaseSelector($databaseSelector);

        $calculator = $this->model instanceof RankingalltimeCalculatorModel
            ? $this->model
            : new RankingalltimeCalculatorModel();
        $calculator->setDatabaseSelector($databaseSelector);
        $this->model = $calculator;

        $this->document->addScript(Uri::root(true) . '/components/' . $this->option . '/assets/js/smsportsmanagement.js');

        $this->projectids = $dataModel->getProjectIds();
        $this->projectnames = $dataModel->getProjectNames();
        $this->project_ids = implode(',', $this->projectids);

        $teamRows = $dataModel->getAllTeams($this->projectids);
        $this->teams = $dataModel->initialiseTeams($teamRows);

        $calculator->_teams = $teamRows;
        $calculator->teams = $this->teams;

        RankingalltimeCalculatorModel::$rankingalltimetips[] = Text::_(
            'Wir verarbeiten ' . count($this->projectids) . ' Projekte/Saisons !'
        );
        RankingalltimeCalculatorModel::$rankingalltimetips[] = Text::_(
            'Wir verarbeiten ' . count($this->teams) . ' Vereine !'
        );

        $forceRankingCache = (bool) ComponentHelper::getParams('com_sportsmanagement')->get('force_ranking_cache', 0);
        if ($forceRankingCache) {
            $this->matches = [];
            $calculator->_matches = [];
        } else {
            $this->matches = $dataModel->getAllMatches($this->projectids);
            $calculator->_matches = $this->matches;
            RankingalltimeCalculatorModel::$rankingalltimetips[] = Text::_(
                'Wir verarbeiten ' . count($this->matches) . ' Spiele !'
            );
        }

        $useNegPoints = (int) ($this->config['use_negpoints_ranking_all_time'] ?? 0);
        $this->ranking = $calculator->getAllTimeRanking($useNegPoints);
        $this->tableconfig = $calculator->getAllTimeParams($comeFromMenu, $this->config);
        $this->currentRanking = $calculator->getCurrentRanking($this->ranking_order);
        $this->config = $calculator->getAllTimeParams($comeFromMenu, $this->config);

        $this->action = $this->uri->toString();
        $this->colors = $dataModel->parseColors((string) ($this->config['colors'] ?? ''));

        $this->document->setTitle(Text::_('COM_SPORTSMANAGEMENT_RANKINGALLTIME_PAGE_TITLE'));

        $this->warnings = RankingalltimeCalculatorModel::$rankingalltimewarnings;
        $this->tips = RankingalltimeCalculatorModel::$rankingalltimetips;
        $this->notes = RankingalltimeCalculatorModel::$rankingalltimenotes;

        $this->warnings = array_merge($this->warnings, sportsmanagementModelProject::$warnings);
        $this->tips = array_merge($this->tips, sportsmanagementModelProject::$tips);
        $this->notes = array_merge($this->notes, sportsmanagementModelProject::$notes);
    }
}
