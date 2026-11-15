<?php
/**
 * SportsManagement OpenLigaDB administrator preview view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

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

    /**
     * Initialise the selected project's OpenLigaDB preview.
     * Finds the project ID in request or Joomla state, retrieves the native match preview and summary, and logs remote failures without disclosing details.
     * @return void Updates the view's preview properties.
     */
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
            // Exceptions may contain database paths or remote HTTP details.
            $this->app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'warning');
        }
    }

    /**
     * Prepare the OpenLigaDB preview administrator toolbar.
     * Sets the translated title, adds a back link to projects and preserves the parent's toolbar configuration.
     * @return void
     */
    protected function addToolbar(): void
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_JSMOPENLIGADB_TITLE');
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=projects');
        parent::addToolbar();
    }

    /**
     * Create the namespaced OpenLigaDB preview service.
     * Loads the Joomla DatabaseInterface through dependency injection and passes it to the native preview implementation.
     * @return OpenLigaDbPreviewService Configured remote preview service.
     */
    private function previewService(): OpenLigaDbPreviewService
    {
        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        return new OpenLigaDbPreviewService($db);
    }
}
