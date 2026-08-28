<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Clubplan;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\ClubplanModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $favteams = [];
    public ?object $club = null;
    public int $type = 0;
    public int $teamartsel = 0;
    public int $teamprojectssel = 0;
    public int $teamseasonssel = 0;
    public array $allmatches = [];
    public array $homematches = [];
    public array $awaymatches = [];
    public array $matches = [];
    public string $startdate = '';
    public string $enddate = '';
    public array $teams = [];
    public array $teamart = [];
    public array $teamprojects = [];
    public array $teamseasons = [];
    public array $lists = [];

    public function __construct($config = [])
    {
        parent::__construct($config);

        // Transitional compatibility for the remaining sorted-by-date layout.
        if (!class_exists('sportsmanagementModelClubPlan', false)) {
            class_alias(ClubplanModel::class, 'sportsmanagementModelClubPlan');
        }
    }

    protected function prepareView(): void
    {
        /** @var ClubplanModel $model */
        $model = $this->getModel();
        if (!$model instanceof ClubplanModel) {
            throw new \RuntimeException('Clubplan view requires ClubplanModel.', 500);
        }

        $document = $this->getDocument();
        $assets = $document->getWebAssetManager();
        $assets->registerAndUseScript(
            'com_sportsmanagement.clubplan',
            Uri::root(true) . '/components/com_sportsmanagement/assets/js/smsportsmanagement.js',
            ['version' => 'auto']
        );
        $assets->addInlineScript(
            "document.addEventListener('DOMContentLoaded', function () { if (typeof hideclubplandate === 'function') { hideclubplandate(); } });"
        );

        $this->favteams = $model->getFavTeams();
        $this->club = $model->getClub();
        $this->type = ClubplanModel::$type;
        $this->teamartsel = ClubplanModel::$teamartsel;
        $this->teamprojectssel = ClubplanModel::$teamprojectssel;
        $this->teamseasonssel = ClubplanModel::$teamseasonssel;

        if ($this->teamprojectssel > 0) {
            ClubplanModel::$project_id = $this->teamprojectssel;
        }
        if ($this->teamseasonssel > 0 || $this->teamartsel > 0) {
            ClubplanModel::$project_id = 0;
        }

        if ($this->type <= 0) {
            $this->type = (int) ($this->config['type_matches'] ?? 4);
        } else {
            $this->config['type_matches'] = $this->type;
        }

        $matchType = (int) ($this->config['type_matches'] ?? 4);
        $orderBy = (string) ($this->config['MatchesOrderBy'] ?? 'ASC');
        switch ($matchType) {
            case 1:
                $this->homematches = $model->getAllMatches($orderBy, 1);
                break;
            case 2:
                $this->awaymatches = $model->getAllMatches($orderBy, 2);
                break;
            case 0:
            case 3:
            case 4:
                $this->allmatches = $model->getAllMatches($orderBy, $matchType);
                break;
            default:
                $this->homematches = $model->getAllMatches($orderBy, 1);
                $this->awaymatches = $model->getAllMatches($orderBy, 2);
                break;
        }

        $this->startdate = $model->getStartDate();
        $this->enddate = $model->getEndDate();
        $this->teams = $model->getTeams();
        $this->teamart = $model->getTeamsArt();
        $this->teamprojects = $model->getTeamsProjects();
        $this->teamseasons = $model->getTeamsSeasons();
        $this->lists = $this->buildLists();

        $pageTitle = Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_TITLE');
        if ($this->club && isset($this->club->name)) {
            $pageTitle .= ': ' . $this->club->name;
        }
        $document->setTitle($pageTitle);

        $projectId = $this->project && !empty($this->project->id) ? '&p=' . (int) $this->project->id : '';
        $clubId = $this->club && !empty($this->club->id) ? '&cid=' . (int) $this->club->id : '';
        $rssVar = $clubId !== '' ? $clubId : $projectId;
        $feed = 'index.php?option=com_sportsmanagement&view=clubplan' . $rssVar . '&format=feed';
        $document->addHeadLink(
            Route::_($feed . '&type=rss'),
            'alternate',
            'rel',
            ['type' => 'application/rss+xml', 'title' => Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_RSSFEED')]
        );

        $clubName = $this->club && isset($this->club->name) ? (string) $this->club->name : '';
        $this->headertitle = trim(Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_PAGE_TITLE') . ' ' . $clubName);
        $this->config['table_class'] = $this->config['table_class'] ?? 'table';
    }

    private function buildLists(): array
    {
        $fromTeamArt = [HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAMART'))];
        $fromTeamArt = array_merge($fromTeamArt, $this->teamart);

        $fromTeamProjects = [HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PROJECT'))];
        $fromTeamProjects = array_merge($fromTeamProjects, $this->teamprojects);

        $fromTeamSeasons = [HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_SEASON'))];
        $fromTeamSeasons = array_merge($fromTeamSeasons, $this->teamseasons);

        $types = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_FES_CLUBPLAN_PARAM_OPTION_TYPE_MATCHES_ALL')),
            HTMLHelper::_('select.option', '1', Text::_('COM_SPORTSMANAGEMENT_FES_CLUBPLAN_PARAM_OPTION_TYPE_MATCHES_HOME')),
            HTMLHelper::_('select.option', '2', Text::_('COM_SPORTSMANAGEMENT_FES_CLUBPLAN_PARAM_OPTION_TYPE_MATCHES_AWAY')),
        ];

        return [
            'fromteamart' => $fromTeamArt,
            'fromteamprojects' => $fromTeamProjects,
            'fromteamseasons' => $fromTeamSeasons,
            'type' => $types,
        ];
    }
}
