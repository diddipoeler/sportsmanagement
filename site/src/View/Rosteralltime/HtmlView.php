<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Rosteralltime;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\RosteralltimeModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public $state = null;
    public array $items = [];
    public $pagination = null;
    public array $playerposition = [];
    public array $positioneventtypes = [];
    public $rows = [];
    public object $form;
    public string $filter = '';
    public string $sortDirection = 'ASC';
    public string $sortColumn = 'pr.lastname';
    public $team = null;

    protected function prepareView(): void
    {
        /** @var RosteralltimeModel $model */
        $model = $this->getModel();
        if (!$model instanceof RosteralltimeModel) {
            throw new \RuntimeException('Rosteralltime view requires RosteralltimeModel.', 500);
        }

        $this->state = $model->getState();
        $this->items = $model->getItems();
        $this->pagination = $model->getPagination();
        $sportsTypeId = (int) ($this->project->sports_type_id ?? 0);
        $this->playerposition = $model->getPlayerPosition($sportsTypeId);
        $this->positioneventtypes = $model->getPositionEventTypes();
        $this->rows = $model->getTeamPlayers(1, $this->positioneventtypes, $this->items) ?: [];
        $this->team = $model->getTeam();
        $this->filter = (string) $model->getState('filter.search', '');
        $this->sortDirection = (string) $model->getState('filter_order_Dir', 'ASC');
        $this->sortColumn = (string) $model->getState('filter_order', 'pr.lastname');
        $this->form = (object) [
            'limitField' => $this->pagination->getLimitBox(),
        ];

        $this->tips = RosteralltimeModel::$_tips;
        $this->warnings = RosteralltimeModel::$_warnings;
        $this->notes = RosteralltimeModel::$_notes;
        $this->config['table_class'] = $this->config['table_class'] ?? 'table';
        $this->config['show_rosteralllayout'] = $this->config['show_rosteralllayout'] ?? 'players';

        $title = Text::_('COM_SPORTSMANAGEMENT_ROSTERALLTIME_TITLE');
        if ($this->team && isset($this->team->name)) {
            $title .= ': ' . $this->team->name;
        }
        $this->headertitle = $title;
        $this->getDocument()->setTitle($title);
    }
}
