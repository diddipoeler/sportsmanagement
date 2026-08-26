<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Statsranking;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\StatsrankingModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $teams = [];
    public int $teamid = 0;
    public array $favteams = [];
    public array $stats = [];
    public array $playersstats = [];
    public int $limit = 0;
    public int $limitstart = 0;
    public bool $multiple_stats = false;
    public string $pagetitle = '';

    protected function prepareView(): void
    {
        /** @var StatsrankingModel $model */
        $model = $this->getModel();
        if (!$model instanceof StatsrankingModel) {
            throw new \RuntimeException('Statsranking view requires StatsrankingModel.', 500);
        }

        $this->teamid = $model->getTeamId();
        $this->teams = $model->getTeamsIndexedById();
        if ($this->teamid > 0) {
            $this->teams = isset($this->teams[$this->teamid])
                ? [$this->teamid => $this->teams[$this->teamid]]
                : [];
        }

        $this->favteams = $model->getFavTeams();
        $this->stats = $model->getProjectUniqueStats();
        $this->playersstats = $model->getPlayersStats();
        $this->limit = $model->getLimit();
        $this->limitstart = $model->getLimitStart();
        $this->multiple_stats = count($this->stats) > 1;

        $teamName = $this->teamid > 0 && isset($this->teams[$this->teamid])
            ? (string) ($this->teams[$this->teamid]->name ?? '')
            : ' ';
        $prefix = Text::sprintf('COM_SPORTSMANAGEMENT_STATSRANKING_PAGE_TITLE', $teamName);

        if ($this->multiple_stats) {
            $prefix .= ' - ' . Text::_('COM_SPORTSMANAGEMENT_STATSRANKING_TITLE');
        } elseif ($this->stats) {
            $stat = reset($this->stats);
            if (is_object($stat)) {
                $prefix .= ' - ' . Text::_((string) ($stat->name ?? ''));
            }
        }

        $titleInfo = \sportsmanagementHelper::createTitleInfo($prefix);
        if ($this->project) {
            $titleInfo->projectName = $this->project->name ?? '';
            $titleInfo->leagueName = $this->project->league_name ?? '';
            $titleInfo->seasonName = $this->project->season_name ?? '';
        }
        if ($this->division) {
            $titleInfo->divisionName = $this->division->name ?? '';
        }

        $this->pagetitle = \sportsmanagementHelper::formatTitle(
            $titleInfo,
            $this->config['page_title_format'] ?? 0
        );
        $this->headertitle = $this->pagetitle;
        $this->getDocument()->setTitle($this->pagetitle);
        $this->getDocument()->addScript(Uri::root(true) . '/components/com_sportsmanagement/assets/js/smsportsmanagement.js');
    }
}
