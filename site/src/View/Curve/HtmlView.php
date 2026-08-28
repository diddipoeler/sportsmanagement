<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Curve;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\CurveModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public string $chart_version = '2.7.3';
    public array $teamranking = [];
    public int $season_id = 0;
    public int $cfg_which_database = 0;
    public int $selectedTeamId1 = 0;
    public int $selectedTeamId2 = 0;
    public array $colors = [];
    public array $divisions = [];
    public array $favteams = [];
    public $team1 = null;
    public $team2 = null;
    public array $allteams = [];
    public array $team1select = [];
    public array $team2select = [];
    public array $round_labels = [];
    public array $flashconfig = [];

    protected function requiresLegacyPresentationDependencies(): bool
    {
        return false;
    }

    protected function prepareView(): void
    {
        /** @var CurveModel $model */
        $model = $this->getModel();
        if (!$model instanceof CurveModel) {
            throw new \RuntimeException('Curve view requires CurveModel.', 500);
        }

        $this->season_id = $model->getRequestSeasonId();
        $this->cfg_which_database = $model->getDatabaseSelector();

        if (!empty($this->config['which_curve'])) {
            $this->getDocument()->getWebAssetManager()->registerAndUseScript(
                'com_sportsmanagement.curve.chartjs',
                'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/' . rawurlencode($this->chart_version) . '/Chart.js'
            );
        }

        if (!$this->project) {
            return;
        }

        $divisionId = $model->getCurveDivisionId();
        $teamId1 = $model->getSelectedTeamId1();
        $teamId2 = $model->getSelectedTeamId2();
        $divisions = $model->getDivisions();
        $team1Select = [];
        $team2Select = [];

        if ($divisions) {
            foreach ($divisions as $division) {
                $options = [];
                $teams = $model->getTeamsForDivision((int) $division->id);
                foreach ($teams as $index => $team) {
                    $options[] = HTMLHelper::_('select.option', (int) $team->id, (string) $team->name);
                    if ($teamId1 <= 0 && $index === 0) {
                        $teamId1 = (int) $team->id;
                    }
                    if ($teamId2 <= 0 && $index === 1) {
                        $teamId2 = (int) $team->id;
                    }
                }

                $team1Select[(int) $division->id] = $this->buildTeamSelect(
                    $options,
                    'tid1_' . (int) $division->id,
                    $teamId1,
                    (int) $division->id
                );
                $team2Select[(int) $division->id] = $this->buildTeamSelect(
                    $options,
                    'tid2_' . (int) $division->id,
                    $teamId2,
                    (int) $division->id
                );
            }
        } else {
            $division = $model->getDivision($divisionId);
            if (!$division) {
                $division = (object) ['id' => 0, 'name' => ''];
            }
            $divisions = [$division];
            $options = [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_CURVE_CHOOSE_TEAM'))];
            $teams = $model->getTeamsForDivision($divisionId);
            foreach ($teams as $index => $team) {
                $options[] = HTMLHelper::_('select.option', (int) $team->id, (string) $team->name);
                if ($teamId1 <= 0 && $index === 0) {
                    $teamId1 = (int) $team->id;
                }
                if ($teamId2 <= 0 && $index === 1) {
                    $teamId2 = (int) $team->id;
                }
            }

            $resolvedDivisionId = (int) $division->id;
            $team1Select[$resolvedDivisionId] = $this->buildTeamSelect($options, 'tid1', $teamId1, $resolvedDivisionId);
            $team2Select[$resolvedDivisionId] = $this->buildTeamSelect($options, 'tid2', $teamId2, $resolvedDivisionId);
        }

        $model->setSelectedTeamIds($teamId1, $teamId2);
        $this->selectedTeamId1 = $teamId1;
        $this->selectedTeamId2 = $teamId2;

        if (!isset($this->overallconfig['seperator'])) {
            $this->overallconfig['seperator'] = ':';
        }

        $rankingConfig = $model->getTemplateConfig('ranking');
        $this->colors = $model->getColors((string) ($rankingConfig['colors'] ?? ''));
        $this->divisions = array_values($divisions);
        $this->division = $model->getDivision($divisionId);
        $this->favteams = $model->getFavTeams();
        $this->team1 = $model->getTeam1($divisionId);
        $this->team2 = $model->getTeam2($divisionId);
        $this->allteams = $model->getTeamsForDivision($divisionId);
        $this->team1select = $team1Select;
        $this->team2select = $team2Select;

        foreach ($model->getRounds('ASC', false) as $round) {
            $this->round_labels[] = json_encode(
                (string) ($round->name ?? ''),
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
            );
        }

        $this->_setChartdata(array_merge($model->getTemplateConfig('flash'), $this->config));
        $this->getDocument()->setTitle(Text::_('COM_SPORTSMANAGEMENT_CURVE_PAGE_TITLE'));
    }

    public function _setChartdata($config): void
    {
        /** @var CurveModel $model */
        $model = $this->getModel();
        if (!$model instanceof CurveModel) {
            return;
        }

        $this->flashconfig = (array) $config;
        foreach ($this->divisions as $division) {
            $divisionId = (int) ($division->id ?? 0);
            $this->teamranking[$divisionId] = $model->getDataByDivision($divisionId);
        }
    }

    private function buildTeamSelect(array $options, string $name, int $selected, int $divisionId): string
    {
        $onChange = !empty($this->config['which_curve'])
            ? ''
            : 'reload_curve_chart_' . $divisionId . '()';
        $attributes = 'onchange="' . $onChange . '" class="inputbox" style="font-size:9px;"';

        return HTMLHelper::_('select.genericlist', $options, $name, $attributes, 'value', 'text', $selected);
    }
}
