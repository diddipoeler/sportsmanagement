<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Teams;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\TeamsModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $teams = [];

    protected function requiresLegacyPresentationDependencies(): bool
    {
        return false;
    }

    protected function prepareView(): void
    {
        /** @var TeamsModel $model */
        $model = $this->getModel();
        $this->division = $model->getDivision();
        $this->teams = $model->getTeams(!empty($this->config['show_club_playground']));

        $title = Text::_('COM_SPORTSMANAGEMENT_TEAMS_TITLE');
        if ($this->project) {
            $title .= ' ' . $this->project->name;
            if ($this->division) {
                $title .= ' : ' . $this->division->name;
            }
        }
        $this->headertitle = Text::_('COM_SPORTSMANAGEMENT_TEAMS_TITLE');
        $this->getDocument()->setTitle($title);
    }
}
