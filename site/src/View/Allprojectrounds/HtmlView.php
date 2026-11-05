<?php
/**
 * Native Joomla 5/6 frontend SportsManagement Allprojectrounds view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Allprojectrounds;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\AllprojectroundsModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public string $tableclass = 'table';
    public int $show_favteaminfo = 0;
    public int $projectid = 0;
    public array $projectmatches = [];
    public array $rounds = [];
    public array $favteams = [];
    public array $projectteamid = [];
    public string $content = '';

    /**
     * Skip the legacy presentation bootstrap for this Joomla 5/6 view.
     *
     * The namespaced model and modern project view provide the required
     * services without loading historical global presentation helpers.
     *
     * @return bool False: no legacy dependencies are required.
     */
    protected function requiresLegacyPresentationDependencies(): bool
    {
        return false;
    }

    /**
     * Prepare project rounds, fixtures and favourite-team information.
     *
     * Requires the native AllprojectroundsModel, reads filtered table
     * and favourite-team display options, loads the active project's
     * matches, rounds and teams, and builds the rendered round columns.
     * Sets the translated page heading for the selected competition.
     *
     * @return void
     * @throws \RuntimeException If the native rounds model is unavailable.
     */
    protected function prepareView(): void
    {
        /** @var AllprojectroundsModel $model */
        $model = $this->getModel();
        if (!$model instanceof AllprojectroundsModel) {
            throw new \RuntimeException('Allprojectrounds view requires AllprojectroundsModel.', 500);
        }

        $this->tableclass = $this->input->getCmd('table_class', (string) ($this->config['table_class'] ?? 'table'));
        $this->show_favteaminfo = $this->input->getInt('show_favteaminfo', (int) ($this->config['show_favteaminfo'] ?? 0));
        $this->projectid = (int) ($this->project->id ?? 0);
        $this->projectmatches = $model->getProjectMatches();
        $this->rounds = $model->getRounds('ASC', false);
        $this->config = array_merge($this->config, $model->getAllRoundsParams());
        $this->favteams = $model->getFavTeams();
        $this->projectteamid = $model->getProjectTeamID($this->favteams);
        $this->content = $model->getRoundsColumn($this->rounds, $this->config);
        $this->headertitle = Text::sprintf(
            'COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS2',
            (string) ($this->project->name ?? '')
        );
    }
}
