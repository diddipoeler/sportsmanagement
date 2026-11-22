<?php
/**
 * Native Joomla 5/6 frontend SportsManagement Statsrankingteams view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Statsrankingteams;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\StatsrankingteamsModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Document\HtmlDocument;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $teams = [];
    public array $stats = [];
    public array $playersstats = [];
    public array $teamsstats = [];
    public array $teamstotal = [];

    /**
     * Prepare the frontend team statistics ranking page.
     *
     * Verifies the native StatsrankingteamsModel before loading teams,
     * configured statistic definitions, individual player statistics
     * and ranked team totals. Registers the component's presentation
     * JavaScript with Joomla's document after the data is ready.
     *
     * @return void
     * @throws \RuntimeException If the required ranking model is unavailable.
     */
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

        // Register assets with Joomla's WebAssetManager instead of the legacy
        // document addScript API; multiple view renders share one registry.
        $document = $this->getDocument();

        if ($document instanceof HtmlDocument) {
            $assets = $document->getWebAssetManager();
            $assetName = 'com_sportsmanagement.statsrankingteams';

            if (!$assets->assetExists('script', $assetName)) {
                $assets->registerScript(
                    $assetName,
                    'components/com_sportsmanagement/assets/js/smsportsmanagement.js',
                    ['version' => 'auto']
                );
            }

            $assets->useScript($assetName);
        }
    }
}
