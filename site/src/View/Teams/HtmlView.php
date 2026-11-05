<?php
/**
 * Native Joomla 5/6 frontend SportsManagement Teams view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Teams;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\TeamsModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $teams = [];

    /**
     * Disable legacy presentation bootstrap for the native teams view.
     *
     * The Joomla 5/6 view uses its namespaced model and project base view,
     * so historical global template helper dependencies are unnecessary.
     *
     * @return bool False because this view uses native dependencies.
     */
    protected function requiresLegacyPresentationDependencies(): bool
    {
        return false;
    }

    /**
     * Prepare the team records, division and document title for display.
     *
     * Loads the native TeamsModel, reads the current division and the
     * configured club playground setting, and assembles the project and
     * division context for Joomla's browser document title.
     *
     * @return void
     * @throws \RuntimeException If the expected TeamsModel is unavailable.
     */
    protected function prepareView(): void
    {
        /** @var TeamsModel $model */
        $model = $this->getModel();

        if (!$model instanceof TeamsModel) {
            throw new \RuntimeException('Teams view requires TeamsModel.', 500);
        }
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
