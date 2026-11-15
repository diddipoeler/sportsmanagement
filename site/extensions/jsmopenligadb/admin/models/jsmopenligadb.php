<?php
/**
 * SportsManagement OpenLigaDB legacy model compatibility bridge.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\OpenLigaDbPreviewService;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;

/** Backward-compatible model name delegating to the native preview service. */
class sportsmanagementModeljsmopenligadb extends BaseDatabaseModel
{
    public static $success_text = '';
    public $storeFailedColor = 'red';
    public $storeSuccessColor = 'green';
    public $existingInDbColor = 'orange';
    public $success_text_teams = '';
    public $success_text_results = '';

    /**
     * Retrieve the OpenLigaDB match URL associated with a project.
     * Delegates lookup to the native preview service after converting the project identifier to an integer.
     * @param mixed $projectid SportsManagement project ID.
     * @return string External match URL.
     */
    public function getMatchLink($projectid): string
    {
        return $this->service()->getProjectLink((int) $projectid);
    }

    /**
     * Load match preview rows for an OpenLigaDB project link.
     * Trims the input and returns an empty list for empty links; valid links are fetched through the namespaced service.
     * @param mixed $projectlink Remote project URL.
     * @return list<array<string,mixed>> Preview match rows.
     */
    public function getdata($projectlink): array
    {
        $link = trim((string) $projectlink);

        return $link === '' ? [] : $this->service()->fetchMatches($link);
    }

    /**
     * Construct the native OpenLigaDB preview service for legacy callers.
     * Validates the Joomla site application, resolves the database through the DI container and constructs the namespaced service.
     * @return OpenLigaDbPreviewService Configured preview service.
     * @throws \RuntimeException If no frontend application is available.
     */
    private function service(): OpenLigaDbPreviewService
    {
        $app = SportsManagementSiteApplicationResolver::resolve();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement OpenLigaDB legacy model requires the Joomla site application.', 500);
        }

        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        return new OpenLigaDbPreviewService($db);
    }
}
