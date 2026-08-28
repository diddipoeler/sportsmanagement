<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Matrix;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\MatrixModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** Joomla 5/6 site view for the matrix layout. */
final class HtmlView extends SportsManagementProjectHtmlView
{
    public int $divisionid = 0;
    public int $roundid = 0;
    public ?object $round = null;
    public array $teams = [];
    public array $results = [];
    public array $russiamatrix = [];
    public array $divisions = [];
    public array $favteams = [];

    public function __construct($config = [])
    {
        parent::__construct($config);

        // Keep the historical matrix layouts available while their PHP view
        // controller is migrated to Joomla's namespaced MVC layer.
        $this->addTemplatePath(JPATH_SITE . '/components/com_sportsmanagement/views/matrix/tmpl');
    }

    protected function prepareView(): void
    {
        $model = $this->getModel();

        if (!$model instanceof MatrixModel) {
            throw new \RuntimeException('Matrix view requires MatrixModel.', 500);
        }

        $this->divisionid = $model->getDivisionId();
        $this->roundid = MatrixModel::$roundid;
        $this->division = $model->getDivision();
        $this->round = $model->getRound();

        $this->teams = $model->getProjectTeamsIndexed($this->divisionid);
        $teamNameField = (string) ($this->config['teamnames'] ?? 'name');
        if ($teamNameField === '') {
            $teamNameField = 'name';
        }
        $this->config['teamnames'] = $teamNameField;

        foreach ($this->teams as $team) {
            if (isset($team->{$teamNameField}) && trim((string) $team->{$teamNameField}) !== '') {
                $team->name = (string) $team->{$teamNameField};
            }
        }

        if (!isset($this->config['image_placeholder'])) {
            $this->config['image_placeholder'] = '';
        }

        if ($this->project) {
            $this->results = $model->getMatrixResults((int) $this->project->id);
        }

        if (!empty($this->config['show_matrix_russia'])) {
            $this->russiamatrix = $model->getRussiaMatrixResults($this->teams, $this->results);
        }

        if (($this->project->project_type ?? '') === 'DIVISIONS_LEAGUE' && $this->divisionid === 0) {
            $this->divisions = $model->getDivisions();
            $this->attachDivisionRankingNotes();
        }

        if ($this->project !== null) {
            $this->favteams = $model->getFavTeams();
        }

        $pageTitle = Text::_('COM_SPORTSMANAGEMENT_MATRIX_PAGE_TITLE');
        if (!empty($this->project->name)) {
            $pageTitle .= ': ' . $this->project->name;
        }
        $this->getDocument()->setTitle($pageTitle);

        $base = Uri::root(true);
        $this->getDocument()->getWebAssetManager()->registerAndUseStyle(
            'com_sportsmanagement.site.matrix',
            $base . '/components/com_sportsmanagement/assets/css/matrix.css'
        );
    }

    private function attachDivisionRankingNotes(): void
    {
        $rankingReasons = [];

        foreach ($this->results as $result) {
            $divisionId = (int) ($result->division_id ?? 0);
            if ($divisionId <= 0) {
                continue;
            }

            foreach ($this->teams as $team) {
                $projectTeamId = (int) ($team->projectteamid ?? 0);
                if (
                    $projectTeamId !== (int) ($result->projectteam1_id ?? 0)
                    && $projectTeamId !== (int) ($result->projectteam2_id ?? 0)
                ) {
                    continue;
                }

                $team->division_id = $divisionId;
                $startPoints = (float) ($team->start_points ?? 0);
                if ($startPoints == 0.0) {
                    continue;
                }

                $color = $startPoints < 0 ? 'red' : 'green';
                $teamName = (string) ($team->name ?? '');
                $reason = (string) ($team->reason ?? '');
                $rankingReasons[$divisionId][$teamName] = sprintf(
                    '<span style="color:%s">%s: %s Punkte Grund: %s</span>',
                    $color,
                    htmlspecialchars($teamName, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars((string) $startPoints, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($reason, ENT_QUOTES, 'UTF-8')
                );
            }
        }

        foreach ($this->divisions as $division) {
            $divisionId = (int) ($division->id ?? 0);
            if (isset($rankingReasons[$divisionId])) {
                $division->notes = implode(', ', $rankingReasons[$divisionId]);
            }
        }
    }
}
