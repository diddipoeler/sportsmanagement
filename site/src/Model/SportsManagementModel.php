<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;

abstract class SportsManagementModel extends BaseDatabaseModel
{
    private ?int $databaseSelectorOverride = null;

    /**
     * Explicitly select the SportsManagement database for non-menu consumers
     * such as modules. Calling this after MVCFactory creation rebinds the model
     * to the requested connection instead of relying on URL input state.
     */
    public function setDatabaseSelector(int $selector): void
    {
        $this->databaseSelectorOverride = max(0, $selector);

        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $this->setDatabase($joomlaDatabase);
    }

    public function setDatabase(DatabaseInterface $db): void
    {
        if (!class_exists('sportsmanagementHelper')) {
            $helperFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';

            if (is_file($helperFile)) {
                require_once $helperFile;
            }
        }

        try {
            if (class_exists('sportsmanagementHelper')) {
                $databaseSelector = $this->databaseSelectorOverride
                    ?? Factory::getApplication()->getInput()->getInt('cfg_which_database', 0);
                $sportsManagementDb = \sportsmanagementHelper::getDBConnection(true, $databaseSelector);

                if ($sportsManagementDb instanceof DatabaseInterface) {
                    parent::setDatabase($sportsManagementDb);

                    return;
                }
            }
        } catch (\Throwable) {
            // Keep Joomla's injected database connection as a safe fallback.
        }

        parent::setDatabase($db);
    }
}
