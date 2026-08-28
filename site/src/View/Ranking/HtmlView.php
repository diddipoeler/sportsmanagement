<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Ranking;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ExtendedFormHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\RankingPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Legacy\RankingCalculationAdapter;
use Diddipoeler\Component\SportsManagement\Site\Legacy\RankingHelperFacade;
use Diddipoeler\Component\SportsManagement\Site\Model\RankingMapModel;
use Diddipoeler\Component\SportsManagement\Site\Model\RankingModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 HTML view for project rankings. */
final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $currentRanking = [];
    public array $homeRank = [];
    public array $awayRank = [];
    public array $previousRanking = [];
    public array $firstRank = [];
    public array $secondRank = [];
    public array $teams = [];
    public array $favteams = [];
    public array $divisions = [];
    public array $rounds = [];
    public array $roundsoption = [];
    public array $previousgames = [];
    public array $columns = [];
    public array $colors = [];
    public array $colorsByDivision = [];
    public array $mapconfig = [];
    public array $mapTeams = [];
    public array $rankingNotes = [];
    public array $lists = [];
    public array $paramconfig = [];
    public array $tableconfig = [];
    public array $activeRanking = [];
    public $rssfeeditems = null;
    public int $round = 0;
    public int $current_round = 0;
    public int $from = 0;
    public int $to = 0;
    public int $part = 0;
    public int $type = 0;
    public int $divLevel = 0;
    public int $cfg_which_database = 0;
    public int $season_id = 0;
    public string $sortOrder = '';
    public string $sortDirection = 'ASC';
    public string $activeTableId = '';
    public string $activeTableTitle = '';

    protected function requiresLegacyPresentationDependencies(): bool
    {
        return false;
    }

    protected function prepareView(): void
    {
        /** @var RankingModel $model */
        $model = $this->getModel();
        if (!$model instanceof RankingModel) {
            throw new \RuntimeException('Ranking view requires RankingModel.', 500);
        }

        if (!$this->project) {
            $this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_RANKING_ERROR_PROJECTID_REQUIRED'), 'error');
            return;
        }

        $this->cfg_which_database = $this->input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
        $this->season_id = (int) ($this->project->season_id ?? $this->input->getInt('s', 0));
        $this->config['table_class'] = trim((string) ($this->config['table_class'] ?? 'table table-striped')) ?: 'table table-striped';
        $this->config['club_link_logo'] = $this->config['club_link_logo'] ?? 1;
        $this->tableconfig = $this->config;
        $this->paramconfig = [
            'p' => (string) ($this->project->slug ?? $this->project->id ?? ''),
            'r' => (string) $this->input->getString('r', ''),
            'division' => (string) $this->input->getString('division', ''),
            'type' => (string) $this->input->getInt('type', 0),
            'from' => (string) $this->input->getInt('from', 0),
            'to' => (string) $this->input->getInt('to', 0),
            'part' => (string) $this->input->getInt('part', 0),
        ];

        $calculation = RankingCalculationAdapter::calculate(
            $model,
            $this->project,
            $this->config,
            $this->cfg_which_database,
            $this->input->getInt('r', 0),
            $this->input->getInt('from', 0),
            $this->input->getInt('to', 0),
            $this->input->getInt('part', 0),
            $this->input->getInt('type', 0),
            $this->input->getInt('division', 0),
            $this->input->getInt('divLevel', (int) ($this->config['default_division_view'] ?? 0))
        );

        $this->round = (int) $calculation['round'];
        $this->current_round = $model->getCurrentRound();
        $this->from = (int) $calculation['from'];
        $this->to = (int) $calculation['to'];
        $this->part = (int) $calculation['part'];
        $this->type = (int) $calculation['type'];
        $this->divLevel = (int) $calculation['divLevel'];
        $this->currentRanking = (array) $calculation['currentRanking'];
        $this->homeRank = (array) $calculation['homeRank'];
        $this->awayRank = (array) $calculation['awayRank'];
        $this->previousRanking = (array) $calculation['previousRanking'];
        $this->firstRank = (array) $calculation['firstRank'];
        $this->secondRank = (array) $calculation['secondRank'];

        $this->teams = $model->getProjectTeamsIndexed(0);
        $this->favteams = array_values(array_map('intval', $model->getFavTeams()));
        $this->divisions = $model->getDivisions(0);
        $selectedDivision = $this->input->getInt('division', 0);
        $this->division = $selectedDivision > 0 ? ($this->divisions[$selectedDivision] ?? null) : null;
        $this->rounds = $model->getRounds('ASC', false);
        $this->roundsoption = $model->getRoundOptions('ASC');
        $this->previousgames = (array) ($model->getPreviousGames($this->round) ?: []);
        $this->columns = RankingPresentationHelper::columns($this->config);
        $this->colors = $model->parseColors((string) ($this->config['colors'] ?? ''));
        $this->colorsByDivision = $this->buildDivisionColors($model);
        $this->rankingNotes = $this->buildRankingNotes();
        $this->notes = RankingHelperFacade::getNotes();
        $this->tips = RankingHelperFacade::getTips();
        $this->warnings = RankingHelperFacade::getWarnings();

        $this->sortOrder = $this->input->getCmd('order', '');
        $this->sortDirection = strtoupper($this->input->getCmd('dir', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $this->applyPresentationSorting();
        $this->buildLists();
        $this->prepareRss($model);
        $this->prepareMap($model);
        $this->prepareAssets();

        $title = Text::_('COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE');
        if ($title === 'COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE') {
            $title = Text::_('COM_SPORTSMANAGEMENT_XML_RANKING_LAYOUT_TITLE');
        }
        if (trim((string) ($this->project->name ?? '')) !== '') {
            $title .= ': ' . (string) $this->project->name;
        }
        $this->getDocument()->setTitle($title);
    }

    private function applyPresentationSorting(): void
    {
        if (empty($this->config['column_sorting']) || $this->sortOrder === '') {
            return;
        }

        foreach (['currentRanking', 'homeRank', 'awayRank', 'firstRank', 'secondRank'] as $property) {
            foreach ($this->{$property} as $divisionId => $ranking) {
                $this->{$property}[$divisionId] = RankingPresentationHelper::sort(
                    (array) $ranking,
                    $this->sortOrder,
                    $this->sortDirection,
                    $this->teams
                );
            }
        }
    }

    private function buildLists(): void
    {
        $this->lists = [
            'frommatchday' => $this->roundsoption,
            'tomatchday' => $this->roundsoption,
            'type' => [
                (object) ['value' => 0, 'text' => Text::_('COM_SPORTSMANAGEMENT_RANKING_FULL_RANKING')],
                (object) ['value' => 1, 'text' => Text::_('COM_SPORTSMANAGEMENT_RANKING_HOME_RANKING')],
                (object) ['value' => 2, 'text' => Text::_('COM_SPORTSMANAGEMENT_RANKING_AWAY_RANKING')],
            ],
        ];
    }

    private function buildRankingNotes(): array
    {
        if (empty($this->config['show_notes'])) {
            return [];
        }

        $notes = [];
        foreach ($this->teams as $team) {
            $points = (float) ($team->start_points ?? 0);
            if ($points == 0.0) {
                continue;
            }

            $notes[] = (object) [
                'team' => (string) ($team->name ?? ''),
                'points' => $points,
                'reason' => (string) ($team->reason ?? ''),
            ];
        }

        return $notes;
    }

    private function buildDivisionColors(RankingModel $model): array
    {
        $colors = [0 => $this->colors];

        foreach ($this->divisions as $divisionId => $division) {
            $raw = trim((string) ($division->rankingparams ?? ''));
            if ($raw === '') {
                $colors[(int) $divisionId] = $this->colors;
                continue;
            }

            try {
                $registry = new Registry();
                $registry->loadString($raw);
                $definitions = (array) $registry->get('rankingparams', []);
                $entries = [];

                foreach ($definitions as $definition) {
                    $definition = (array) $definition;
                    if ($definition === []) {
                        continue;
                    }
                    $entries[] = implode(',', array_values($definition));
                }

                $colors[(int) $divisionId] = $entries !== []
                    ? $model->parseColors(implode(';', $entries))
                    : $this->colors;
            } catch (\Throwable) {
                $colors[(int) $divisionId] = $this->colors;
            }
        }

        return $colors;
    }

    private function prepareRss(RankingModel $model): void
    {
        if (empty($this->overallconfig['show_project_rss_feed'])) {
            return;
        }

        $extended = ExtendedFormHelper::load((string) ($this->project->extended ?? ''), 'project');
        $rssLink = $extended
            ? (string) $extended->getValue('COM_SPORTSMANAGEMENT_PROJECT_RSS_FEED', null, '')
            : '';

        if ($rssLink !== '') {
            $this->rssfeeditems = $model->getRssFeeds(
                $rssLink,
                (int) ($this->overallconfig['rssitems'] ?? 5)
            );
        }
    }

    private function prepareMap(RankingModel $model): void
    {
        if (empty($this->config['show_ranking_maps'])) {
            return;
        }

        $this->mapconfig = $model->getTemplateConfig('map');
        $mapModel = new RankingMapModel();
        $mapModel->setDatabaseSelector($this->cfg_which_database);
        $mapModel->setProjectId((int) ($this->project->id ?? 0));
        $this->mapTeams = $mapModel->getTeams();
    }

    private function prepareAssets(): void
    {
        $wa = $this->getDocument()->getWebAssetManager();
        $base = Uri::root(true);
        $siteScript = JPATH_SITE . '/components/com_sportsmanagement/assets/js/smsportsmanagement.js';

        if (is_file($siteScript)) {
            $wa->registerAndUseScript(
                'com_sportsmanagement.ranking.site',
                $base . '/components/com_sportsmanagement/assets/js/smsportsmanagement.js',
                [],
                [],
                ['jquery']
            );
        }

        if ($this->mapTeams !== []) {
            $wa->registerAndUseStyle(
                'com_sportsmanagement.ranking.leaflet',
                'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
            );
            $wa->registerAndUseScript(
                'com_sportsmanagement.ranking.leaflet',
                'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
            );
        }
    }
}
