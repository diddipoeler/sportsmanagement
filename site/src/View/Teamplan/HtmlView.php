<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Teamplan;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Legacy\TeamplanCommentsFacade;
use Diddipoeler\Component\SportsManagement\Site\Legacy\TeamplanCountriesFacade;
use Diddipoeler\Component\SportsManagement\Site\Legacy\TeamplanHelperFacade;
use Diddipoeler\Component\SportsManagement\Site\Legacy\TeamplanHtmlFacade;
use Diddipoeler\Component\SportsManagement\Site\Legacy\TeamplanProjectFacade;
use Diddipoeler\Component\SportsManagement\Site\Model\TeamplanModel;
use Diddipoeler\Component\SportsManagement\Site\Model\TeamplanViewDataModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Native Joomla 5/6 HTML view for the team plan.
 *
 * Remaining historical template calls are isolated through narrow facades
 * while the individual tmpl files are migrated to namespaced helpers.
 */
final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $rounds = [];
    public array $teams = [];
    public array $favteams = [];
    public int $ptid = 0;
    public int $teamId = 0;
    public int $seasonId = 0;
    public int $databaseSelector = 0;
    public array $projectevents = [];
    public array $matches = [];
    public array $matches_refering = [];
    public array $matchesperround = [];
    public $document;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->initialisePresentationCompatibilityConstants();

        if (!class_exists('sportsmanagementHelper', false)) {
            class_alias(TeamplanHelperFacade::class, 'sportsmanagementHelper');
        }
        if (!class_exists('sportsmanagementModelProject', false)) {
            class_alias(TeamplanProjectFacade::class, 'sportsmanagementModelProject');
        }
        if (!class_exists('sportsmanagementHelperHtml', false)) {
            class_alias(TeamplanHtmlFacade::class, 'sportsmanagementHelperHtml');
        }
        if (!class_exists('sportsmanagementModelComments', false)) {
            class_alias(TeamplanCommentsFacade::class, 'sportsmanagementModelComments');
        }
        if (!class_exists('JSMCountries', false)) {
            class_alias(TeamplanCountriesFacade::class, 'JSMCountries');
        }
    }

    protected function prepareView(): void
    {
        $this->document = $this->getDocument();

        /** @var TeamplanModel $model */
        $model = $this->getModel();
        if (!$model instanceof TeamplanModel) {
            throw new \RuntimeException('Teamplan view requires TeamplanModel.', 500);
        }

        $this->databaseSelector = $this->input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
        $this->seasonId = $this->input->getInt('s', 0);
        $this->teamId = max(0, $this->input->getInt('tid', 0));
        $model->setDatabaseSelector($this->databaseSelector);

        TeamplanProjectFacade::setModel($model);
        TeamplanHtmlFacade::$project = $this->project;

        $assets = $this->document->getWebAssetManager();
        $assets->registerAndUseScript(
            'com_sportsmanagement.teamplan',
            Uri::root(true) . '/components/com_sportsmanagement/assets/js/smsportsmanagement.js',
            ['version' => 'auto']
        );
        $assets->registerAndUseScript(
            'com_sportsmanagement.teamplan.print-preview',
            Uri::root(true) . '/components/com_sportsmanagement/assets/js/printPreview.js',
            ['version' => 'auto'],
            [],
            ['jquery']
        );

        if (!empty($this->config['show_date_image'])) {
            $assets->registerAndUseStyle(
                'com_sportsmanagement.teamplan.calendar',
                Uri::root(true) . '/components/com_sportsmanagement/assets/css/calendar.css',
                ['version' => 'auto']
            );
        }

        if ($this->project && (int) ($this->project->id ?? 0) > 0) {
            $ordering = (string) ($this->config['plan_order'] ?? 'ASC');
            $this->rounds = $model->getPlanRounds($ordering);
            $this->teams = $model->getPlanTeams();
            TeamplanHtmlFacade::$teams = $this->teams;
            $this->favteams = $model->getPlanFavTeams();
            $this->division = $model->getPlanDivision();
            $this->ptid = $model->getProjectTeamId();
            $this->projectevents = $model->getPlanProjectEvents();

            // Referees are added below in one batched query. Prevent the model
            // from running one referee query for every single match.
            $matchConfig = $this->config;
            $matchConfig['show_referee'] = 0;
            $this->matches = $model->getMatches($matchConfig);
            $this->matches_refering = $model->getMatchesRefering($matchConfig);
            $this->matchesperround = $model->getMatchesPerRound($matchConfig, $this->rounds);

            if (!empty($this->config['show_referee'])) {
                $viewDataModel = new TeamplanViewDataModel();
                $viewDataModel->setDatabaseSelector($this->databaseSelector);
                $this->attachReferees($viewDataModel);
            }
        }

        if ($this->ptid > 0 && isset($this->teams[$this->ptid])) {
            $pageTitleSubject = (string) ($this->teams[$this->ptid]->name ?? '');
        } else {
            $pageTitleSubject = (string) ($this->project->name ?? '');
        }

        $pageTitle = Text::sprintf('COM_SPORTSMANAGEMENT_TEAMPLAN_PAGE_TITLE', $pageTitleSubject);
        $this->headertitle = $pageTitle;
        $this->document->setTitle($pageTitle);
        $this->config['table_class'] = (string) ($this->config['table_class'] ?? 'table');
    }

    private function attachReferees(TeamplanViewDataModel $viewDataModel): void
    {
        $matchIds = [];
        foreach ($this->allMatchLists() as $matchList) {
            foreach ($matchList as $match) {
                $matchId = (int) ($match->id ?? 0);
                if ($matchId > 0) {
                    $matchIds[$matchId] = $matchId;
                }
            }
        }

        if ($matchIds === []) {
            return;
        }

        $referees = $viewDataModel->getMatchReferees(
            array_values($matchIds),
            !empty($this->project->teams_as_referees)
        );

        foreach ($this->allMatchLists() as $matchList) {
            foreach ($matchList as $match) {
                $matchId = (int) ($match->id ?? 0);
                $match->referees = $referees[$matchId] ?? [];
            }
        }
    }

    /** @return array<int, array<int, object>> */
    private function allMatchLists(): array
    {
        $lists = [$this->matches, $this->matches_refering];
        foreach ($this->matchesperround as $roundMatches) {
            if (is_array($roundMatches)) {
                $lists[] = $roundMatches;
            }
        }

        return $lists;
    }

    private function initialisePresentationCompatibilityConstants(): void
    {
        if (!\defined('JSM_PATH')) {
            \define('JSM_PATH', 'components/com_sportsmanagement');
        }
        if (!\defined('COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS')) {
            \define('COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS', $this->params->get('boostrap_div_class'));
        }
        if (!\defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE')) {
            \define('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE', $this->params->get('cfg_which_database'));
        }
        if (!\defined('COM_SPORTSMANAGEMENT_LOAD_BOOTSTRAP')) {
            \define('COM_SPORTSMANAGEMENT_LOAD_BOOTSTRAP', $this->params->get('cfg_load_bootstrap'));
        }
        if (!\defined('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO')) {
            \define('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO', $this->params->get('show_query_debug_info'));
        }
    }
}
