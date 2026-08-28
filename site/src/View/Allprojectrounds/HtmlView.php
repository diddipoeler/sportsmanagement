<?php
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

    protected function requiresLegacyPresentationDependencies(): bool
    {
        return false;
    }

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
