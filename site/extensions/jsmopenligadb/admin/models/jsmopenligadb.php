<?php
/**
 * SportsManagement OpenLigaDB legacy model compatibility bridge.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Service\OpenLigaDbPreviewService;
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

    public function getMatchLink($projectid): string
    {
        return $this->service()->getProjectLink((int) $projectid);
    }

    /** @return list<array<string,mixed>> */
    public function getdata($projectlink): array
    {
        $link = trim((string) $projectlink);

        return $link === '' ? [] : $this->service()->fetchMatches($link);
    }

    private function service(): OpenLigaDbPreviewService
    {
        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        return new OpenLigaDbPreviewService($db);
    }
}
