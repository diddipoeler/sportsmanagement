<?php
/**
 * SportsManagement OpenLigaDB administrator preview view.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Service\OpenLigaDbPreviewService;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Database\DatabaseInterface;

class sportsmanagementViewjsmopenligadb extends sportsmanagementView
{
    /** @var array{matches:int,teams:int,playgrounds:int,goals:int} */
    public array $previewSummary = [
        'matches' => 0,
        'teams' => 0,
        'playgrounds' => 0,
        'goals' => 0,
    ];

    public string $projectlink = '';

    public function init(): void
    {
        $this->projectid = $this->jinput->getInt('pid', 0);

        if (!$this->projectid) {
            $this->projectid = (int) $this->app->getUserState($this->option . '.pid', 0);
        }

        if (!$this->projectid) {
            return;
        }

        try {
            $service = $this->previewService();
            $this->projectlink = $service->getProjectLink((int) $this->projectid);

            if ($this->projectlink !== '') {
                $this->previewSummary = $service->summarize($service->fetchMatches($this->projectlink));
            }
        } catch (\Throwable $exception) {
            Log::add($exception->getMessage(), Log::WARNING, 'jsmerror');
            $this->app->enqueueMessage($exception->getMessage(), 'warning');
        }
    }

    protected function addToolbar(): void
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_JSMOPENLIGADB_TITLE');
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=projects');
        parent::addToolbar();
    }

    private function previewService(): OpenLigaDbPreviewService
    {
        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        return new OpenLigaDbPreviewService($db);
    }
}
