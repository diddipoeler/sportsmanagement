<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Statsrankingteams;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\StatsrankingteamsModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Uri\Uri;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $teams = [];
    public array $stats = [];
    public array $playersstats = [];
    public array $teamsstats = [];
    public array $teamstotal = [];

    protected function prepareView(): void
    {
        /** @var StatsrankingteamsModel $model */
        $model = $this->getModel();
        if (!$model instanceof StatsrankingteamsModel) {
            throw new \RuntimeException('Statsrankingteams view requires StatsrankingteamsModel.', 500);
        }

        $this->teams = $model->getTeamsIndexedById();
        $this->stats = $model->getProjectUniqueStats();
        $this->playersstats = $model->getPlayersStats();
        $this->teamsstats = $model->getTeamsStats();
        $this->teamstotal = $model->getTeamsTotal($this->teamsstats);

        $this->getDocument()->addScript(Uri::root(true) . '/components/com_sportsmanagement/assets/js/smsportsmanagement.js');
    }
}
