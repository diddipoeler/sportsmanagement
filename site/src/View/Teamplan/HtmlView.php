<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Teamplan;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\TeamplanModel;
use Diddipoeler\Component\SportsManagement\Site\Model\TeamplanViewDataModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** Native Joomla 5/6 HTML view for the team plan. */
final class HtmlView extends SportsManagementProjectHtmlView
{
    /** @deprecated Kept empty for third-party template compatibility. */
    public array $rounds = [];

    public array $teams = [];
    public array $favteams = [];
    public int $ptid = 0;
    public int $teamId = 0;
    public int $seasonId = 0;
    public int $databaseSelector = 0;
    public array $projectevents = [];
    public array $matches = [];

    /** @deprecated Kept empty for third-party template compatibility. */
    public array $matches_refering = [];

    /** @deprecated Kept empty for third-party template compatibility. */
    public array $matchesperround = [];

    public array $matchEvents = [];
    public array $matchSubstitutions = [];
    public bool $groupMatchesByDate = false;
    public $document;

    protected function requiresLegacyPresentationDependencies(): bool
    {
        return false;
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

        if (!empty($this->config['show_teamplan_print_option'])) {
            $assets->registerAndUseScript(
                'com_sportsmanagement.teamplan.html2pdf',
                'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js'
            );
            $assets->addInlineScript(<<<'JS'
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && typeof window.jQuery.fn.printPreview === 'function') {
        window.jQuery('#btnPrint').printPreview({obj2print: '#teamplanoutput'});
    }

    var exportButton = document.getElementById('exportButton');
    if (!exportButton) {
        return;
    }

    exportButton.addEventListener('click', function () {
        var element = document.getElementById('teamplanoutput');
        if (!element || typeof window.html2pdf !== 'function') {
            return;
        }

        window.html2pdf().set({
            margin: 1,
            filename: 'teamplan.pdf',
            image: {type: 'jpeg', quality: 0.98},
            html2canvas: {scale: 2},
            jsPDF: {unit: 'in', format: 'A3', orientation: 'landscape'}
        }).from(element).save();
    });
});
JS);
        }

        if (!empty($this->config['show_date_image'])) {
            $assets->registerAndUseStyle(
                'com_sportsmanagement.teamplan.calendar',
                Uri::root(true) . '/components/com_sportsmanagement/assets/css/calendar.css',
                ['version' => 'auto']
            );
        }

        if ($this->project && (int) ($this->project->id ?? 0) > 0) {
            $this->teams = $model->getPlanTeams();
            $this->favteams = $model->getPlanFavTeams();
            $this->division = $model->getPlanDivision();
            $this->ptid = $model->getProjectTeamId();

            // Referees are attached below in one batched query.
            $matchConfig = $this->config;
            $matchConfig['show_referee'] = 0;
            $this->matches = $model->getMatches($matchConfig);

            if (!empty($this->config['show_referee'])) {
                $viewDataModel = new TeamplanViewDataModel();
                $viewDataModel->setDatabaseSelector($this->databaseSelector);
                $this->attachReferees($viewDataModel);
            }

            if (!empty($this->config['show_events'])) {
                $this->projectevents = $model->getPlanProjectEvents();
                $this->prepareMatchEventData($model);
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

    private function prepareMatchEventData(TeamplanModel $model): void
    {
        foreach ($this->matches as $match) {
            $matchId = (int) ($match->id ?? 0);
            if ($matchId <= 0) {
                continue;
            }

            $this->matchEvents[$matchId] = $model->getMatchEvents($matchId);
            $this->matchSubstitutions[$matchId] = !empty($this->config['use_tabs_events'])
                ? $model->getMatchSubstitutions($matchId)
                : [];
        }
    }

    private function attachReferees(TeamplanViewDataModel $viewDataModel): void
    {
        $matchIds = [];
        foreach ($this->matches as $match) {
            $matchId = (int) ($match->id ?? 0);
            if ($matchId > 0) {
                $matchIds[$matchId] = $matchId;
            }
        }

        if ($matchIds === []) {
            return;
        }

        $referees = $viewDataModel->getMatchReferees(
            array_values($matchIds),
            !empty($this->project->teams_as_referees)
        );

        foreach ($this->matches as $match) {
            $matchId = (int) ($match->id ?? 0);
            $match->referees = $referees[$matchId] ?? [];
        }
    }
}
