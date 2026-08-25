<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Teaminfo;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Legacy\RankingProjectFacade;
use Diddipoeler\Component\SportsManagement\Site\Model\TeaminfoModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public ?object $team = null;
    public ?object $club = null;
    public bool $showediticon = false;
    public int $projectteamid = 0;
    public int $teamid = 0;
    public array $trainingData = [];
    public array $daysOfWeek = [];
    public $checkextrafields = false;
    public $extrafields = null;
    public array $merge_clubs = [];
    public array $seasons = [];
    public array $leaguerankoverview = [];
    public array $leaguerankoverviewdetail = [];
    public $extended = null;
    public array $output = [];
    public $document;
    public int $columns = 0;
    public string $divclass = '';

    protected function prepareView(): void
    {
        // Joomla injects the Document after constructing the view. Keep the
        // historical property for the tmpl files, but initialise it only once
        // display() has started and the Document is available.
        $this->document = $this->getDocument();

        /** @var TeaminfoModel $model */
        $model = $this->getModel();
        if (!$model instanceof TeaminfoModel) {
            throw new \RuntimeException('Teaminfo view requires TeaminfoModel.', 500);
        }

        // Teaminfo calculates ranking data for the current team across several
        // historical projects. JSMRanking still expects the former global
        // sportsmanagementModelProject API, so bind it to the native project
        // model before the season history is prepared.
        RankingProjectFacade::setModel($model);
        if (!class_exists('sportsmanagementModelProject', false)) {
            class_alias(RankingProjectFacade::class, 'sportsmanagementModelProject');
        }

        $this->warnings = [];
        $this->tips = [];
        $this->notes = [];
        $this->checkextrafields = \sportsmanagementHelper::checkUserExtraFields(
            'frontend',
            TeaminfoModel::$cfg_which_database
        );

        if ($this->project && (int) ($this->project->id ?? 0) > 0) {
            $this->team = TeaminfoModel::getTeamByProject(1);
            $this->club = TeaminfoModel::getClub();
            $this->showediticon = $model->hasEditPermission('projectteam.edit');
            $this->projectteamid = TeaminfoModel::$projectteamid;
            $this->teamid = TeaminfoModel::$teamid;
            $this->trainingData = TeaminfoModel::getTrainigData((int) $this->project->id);

            if ($this->checkextrafields) {
                $this->extrafields = \sportsmanagementHelper::getUserExtraFields(
                    TeaminfoModel::$teamid,
                    'frontend',
                    TeaminfoModel::$cfg_which_database
                );
            }

            $this->daysOfWeek = [
                1 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MONDAY'),
                2 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_TUESDAY'),
                3 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_WEDNESDAY'),
                4 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_THURSDAY'),
                5 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_FRIDAY'),
                6 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SATURDAY'),
                7 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SUNDAY'),
            ];

            if ($this->team && !empty($this->team->merge_clubs)) {
                $this->merge_clubs = $model->getMergeClubs($this->team->merge_clubs);
            }

            $this->seasons = TeaminfoModel::getSeasons($this->config, 1);
            $this->leaguerankoverview = TeaminfoModel::getLeagueRankOverview($this->seasons);
            $this->leaguerankoverviewdetail = TeaminfoModel::getLeagueRankOverviewDetail($this->seasons);
        }

        if ($this->team) {
            $this->extended = \sportsmanagementHelper::getExtended(
                $this->team->teamextended ?? '',
                'team'
            );
        }

        $pageTitle = Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_PAGE_TITLE');
        if ($this->team) {
            $teamName = (string) ($this->team->tname ?? $this->team->name ?? '');
            if ($teamName !== '') {
                $pageTitle .= ': ' . $teamName;
            }
        }

        $this->headertitle = $pageTitle;
        $this->document->setTitle($pageTitle);
        $this->config['table_class'] = (string) ($this->config['table_class'] ?? 'table');
    }
}
