<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Eventsranking;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EventsrankingModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Uri\Uri;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public int $matchid = 0;
    public int $teamid = 0;
    public array $teams = [];
    public array $favteams = [];
    public array $eventtypes = [];
    public int $limit = 0;
    public int $limitstart = 0;
    public ?Pagination $pagination = null;
    public array $eventranking = [];
    public bool $multiple_events = false;
    public string $pagetitle = '';

    protected function prepareView(): void
    {
        /** @var EventsrankingModel $model */
        $model = $this->getModel();
        if (!$model instanceof EventsrankingModel) {
            throw new \RuntimeException('Eventsranking view requires EventsrankingModel.', 500);
        }

        $this->matchid = EventsrankingModel::$matchid;
        $this->teamid = (int) $model->getTeamId();
        $this->teams = $model->getTeamsIndexedById();
        $this->favteams = $model->getFavTeams();
        $sportsTypeId = (int) ($this->project->sports_type_id ?? 0);
        $this->eventtypes = EventsrankingModel::getEventTypes($sportsTypeId);
        $this->limit = EventsrankingModel::getLimit();
        $this->limitstart = EventsrankingModel::getLimitStart();
        $this->pagination = $model->getPagination();

        $isDart = ($this->project->sport_type_name ?? '') === 'COM_SPORTSMANAGEMENT_ST_DART';
        $this->eventranking = (array) ($model->getEventRankings(
            $this->limit,
            $this->limitstart,
            null,
            $isDart,
            $sportsTypeId
        ) ?? []);
        $this->multiple_events = count($this->eventtypes) > 1;

        $prefix = Text::_('COM_SPORTSMANAGEMENT_EVENTSRANKING_PAGE_TITLE');
        if ($this->multiple_events) {
            $prefix .= ' - ' . Text::_('COM_SPORTSMANAGEMENT_EVENTSRANKING_TITLE');
        } elseif ($this->eventtypes) {
            $event = reset($this->eventtypes);
            if (is_object($event)) {
                $prefix .= ' - ' . Text::_((string) ($event->name ?? ''));
            }
        }

        $titleInfo = \sportsmanagementHelper::createTitleInfo($prefix);
        if ($this->teamid > 0 && isset($this->teams[$this->teamid])) {
            $titleInfo->team1Name = $this->teams[$this->teamid]->name ?? '';
        }
        if ($this->project) {
            $titleInfo->projectName = $this->project->name ?? '';
            $titleInfo->leagueName = $this->project->league_name ?? '';
            $titleInfo->seasonName = $this->project->season_name ?? '';
        }
        if ($this->division) {
            $titleInfo->divisionName = $this->division->name ?? '';
        }

        $this->config['table_class'] = $this->config['table_class'] ?? 'table';
        $this->pagetitle = \sportsmanagementHelper::formatTitle(
            $titleInfo,
            $this->config['page_title_format'] ?? 0
        );
        $this->headertitle = $this->pagetitle;
        $document = $this->getDocument();
        $document->setTitle($this->pagetitle);
        $document->addScript(Uri::root(true) . '/components/com_sportsmanagement/assets/js/smsportsmanagement.js');
    }
}
