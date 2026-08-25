<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Stats;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\StatsModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public string $chart_version = '2.7.3';
    public int $actualround = 0;
    public $highest_home = null;
    public $highest_away = null;
    public $totals = null;
    public int $totalrounds = 0;
    public array $attendanceranking = [];
    public $bestavg = 0;
    public $bestavgteam = 0;
    public $worstavg = 0;
    public $worstavgteam = 0;
    public int $limit = 3;
    public array $round_labels = [];
    public array $flashconfig = [];
    public array $matchDayGoalsCount = [];
    public int $matchDayGoalsCountMax = 0;
    public array $homeSum = [];
    public array $awaySum = [];
    public string $chart_url = '';

    protected function prepareView(): void
    {
        /** @var StatsModel $model */
        $model = $this->getModel();
        if (!$model instanceof StatsModel) {
            throw new \RuntimeException('Stats view requires StatsModel.', 500);
        }

        $document = $this->getDocument();
        $document->addScript(
            'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/' . rawurlencode($this->chart_version) . '/Chart.js'
        );
        $document->addStyleSheet(Uri::root(true) . '/components/com_sportsmanagement/assets/css/stats.css');

        if ($this->project) {
            $this->division = $model->getDivision();
            if (!isset($this->overallconfig['seperator'])) {
                $this->overallconfig['seperator'] = ':';
            }

            $this->actualround = $model->getCurrentRoundNumber();
            $this->highest_home = $model->getHighest('HOME');
            $this->highest_away = $model->getHighest('AWAY');
            $this->totals = $model->getSeasonTotals();
            $this->totalrounds = $model->getTotalRounds();
            $this->attendanceranking = $model->getAttendanceRanking();
            $this->bestavg = $model->getBestAvg();
            $this->bestavgteam = $model->getBestAvgTeam();
            $this->worstavg = $model->getWorstAvg();
            $this->worstavgteam = $model->getWorstAvgTeam();
            $this->chart_url = $model->getChartURL();

            foreach ($model->getRounds('ASC', false) as $round) {
                $this->round_labels[] = json_encode(
                    (string) ($round->name ?? ''),
                    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
                );
            }

            $this->_setChartdata(array_merge($model->getTemplateConfig('flash'), $this->config));
        }

        $pageTitle = Text::_('COM_SPORTSMANAGEMENT_STATS_PAGE_TITLE');
        if ($this->project) {
            $pageTitle .= ': ' . (string) ($this->project->name ?? '');
            if ($this->division) {
                $pageTitle .= ': ' . (string) ($this->division->name ?? '');
            }
        }
        $document->setTitle($pageTitle);
        $this->headertitle = Text::_('COM_SPORTSMANAGEMENT_STATS_TITLE');
    }

    public function _setChartdata($config): void
    {
        /** @var StatsModel $model */
        $model = $this->getModel();
        if (!$model instanceof StatsModel) {
            return;
        }

        $this->flashconfig = (array) $config;
        $homeSum = [];
        $awaySum = [];
        $matchDayGoalsCount = [];
        $matchDayGoalsCountMax = 0;

        foreach ($model->getChartData() as $row) {
            $homeGoals = (int) ($row->homegoalspd ?? 0);
            $awayGoals = (int) ($row->guestgoalspd ?? 0);
            $homeSum[] = $homeGoals;
            $awaySum[] = $awayGoals;
            $matchDayGoalsCount[] = ($homeGoals === 0 && $awayGoals === 0) ? null : $homeGoals + $awayGoals;
            $matchDayGoalsCountMax = max($matchDayGoalsCountMax, $homeGoals + $awayGoals);
        }

        $this->matchDayGoalsCount = $matchDayGoalsCount;
        $this->matchDayGoalsCountMax = $matchDayGoalsCountMax;
        $this->homeSum = $homeSum;
        $this->awaySum = $awaySum;
    }
}
