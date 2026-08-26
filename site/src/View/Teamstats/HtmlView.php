<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Teamstats;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\TeamstatsModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public string $chart_version = '2.7.3';
    public int $actualround = 0;
    public $team = null;
    public $highest_home = null;
    public $highest_away = null;
    public $highestdef_home = null;
    public $highestdef_away = null;
    public $highestdraw_home = null;
    public $highestdraw_away = null;
    public $totalshome = null;
    public $totalsaway = null;
    public $matchdaytotals = null;
    public int $totalrounds = 0;
    public $totalattendance = 0;
    public $bestattendance = 0;
    public $worstattendance = 0;
    public $averageattendance = 0;
    public string $chart_url = '';
    public $nogoals_against = null;
    public string $logo = '';
    public array $results = [];
    public array $round_labels = [];
    public array $flashconfig = [];
    public int $matchDayGoalsCountMax = 0;
    public array $forSum = [];
    public array $againstSum = [];

    protected function prepareView(): void
    {
        /** @var TeamstatsModel $model */
        $model = $this->getModel();
        if (!$model instanceof TeamstatsModel) {
            throw new \RuntimeException('Teamstats view requires TeamstatsModel.', 500);
        }

        if (!isset($this->overallconfig['seperator'])) {
            $this->overallconfig['seperator'] = ':';
        }

        if (!empty($this->config['show_goals_stats_flash'])) {
            $this->getDocument()->addScript(
                'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/' . rawurlencode($this->chart_version) . '/Chart.js'
            );
        }

        if ($this->project) {
            $this->actualround = $model->getCurrentRound();
            $this->team = $model->getTeam();
            $this->highest_home = $model->getHighest('HOME', 'WIN');
            $this->highest_away = $model->getHighest('AWAY', 'WIN');
            $this->highestdef_home = $model->getHighest('HOME', 'DEF');
            $this->highestdef_away = $model->getHighest('AWAY', 'DEF');
            $this->highestdraw_home = $model->getHighest('HOME', 'DRAW');
            $this->highestdraw_away = $model->getHighest('AWAY', 'DRAW');
            $this->totalshome = $model->getSeasonTotals('HOME');
            $this->totalsaway = $model->getSeasonTotals('AWAY');
            $this->matchdaytotals = $model->getMatchDayTotals();
            $this->totalrounds = $model->getTotalRounds();
            $this->totalattendance = $model->getTotalAttendance();
            $this->bestattendance = $model->getBestAttendance();
            $this->worstattendance = $model->getWorstAttendance();
            $this->averageattendance = $model->getAverageAttendance();
            $this->chart_url = $model->getChartURL();
            $this->nogoals_against = $model->getNoGoalsAgainst();
            $this->logo = $model->getLogo();
            $this->results = $model->getResults();

            if (!empty($this->config['show_goals_stats_flash'])) {
                foreach ($model->getRounds('ASC') as $round) {
                    $this->round_labels[] = json_encode(
                        (string) ($round->name ?? ''),
                        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
                    );
                }

                $this->_setChartdata(array_merge($model->getTemplateConfig('flash'), $this->config));
            }
        }

        $pageTitle = Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_PAGE_TITLE');
        if ($this->team && isset($this->team->name)) {
            $pageTitle .= ': ' . $this->team->name;
        }
        $this->getDocument()->setTitle($pageTitle);

        $teamName = $this->team && isset($this->team->name) ? (string) $this->team->name : '';
        $this->headertitle = Text::_('COM_SPORTSMANAGEMENT_TEAMSTATS_TITLE')
            . ($teamName !== '' ? ' - ' . $teamName : '');
    }

    public function _setChartdata($config): void
    {
        /** @var TeamstatsModel $model */
        $model = $this->getModel();
        if (!$model instanceof TeamstatsModel) {
            return;
        }

        $this->flashconfig = (array) $config;
        $forSum = [];
        $againstSum = [];
        $matchDayGoalsCountMax = 0;

        foreach ($model->getChartData() as $row) {
            $goalsFor = (int) ($row->goalsfor ?? 0);
            $goalsAgainst = (int) ($row->goalsagainst ?? 0);
            $forSum[] = $goalsFor;
            $againstSum[] = $goalsAgainst;
            $matchDayGoalsCountMax = max($matchDayGoalsCountMax, $goalsFor + $goalsAgainst);
        }

        $this->forSum = $forSum;
        $this->againstSum = $againstSum;
        $this->matchDayGoalsCountMax = $matchDayGoalsCountMax;
    }
}
