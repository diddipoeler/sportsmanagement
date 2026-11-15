<?php
/**
 * Joomla 5/6 compatibility model for the historic SportsManagement frontend root view.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright (C) 2013-2026 Fussball in Europa
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\LegacySportsmanagementTable;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\Database\DatabaseInterface;

/**
 * sportsmanagementModelsportsmanagement
 *
 * Historic site model retained for compatibility with legacy callers.
 */
class sportsmanagementModelsportsmanagement extends ItemModel
{
    /**
     * Resolve the validated Joomla frontend application for the legacy root model.
     *
     * Uses the shared Joomla 5/6 site resolver instead of the removed static
     * application factory. Loads that resolver explicitly for older template
     * overrides which still include this model without the component dispatcher,
     * and rejects requests when its dependency cannot be found.
     *
     * @return CMSApplicationInterface Active Joomla site application.
     * @throws \RuntimeException If the resolver file or site service is unavailable.
     */
    private static function joomlaApplication(): CMSApplicationInterface
    {
        if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
            $file = JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php';

            if (!is_file($file)) {
                throw new \RuntimeException('SportsManagement site application resolver is missing.', 500);
            }

            require_once $file;
        }

        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Retrieve Joomla's typed database service for the legacy table adapter.
     *
     * Reads DatabaseInterface from the public dependency container; unlike
     * the historical static database accessor, this works in Joomla 5/6 and
     * rejects unexpected services before constructing database tables.
     *
     * @return DatabaseInterface Active Joomla database connection.
     * @throws \RuntimeException If Joomla did not provide a database service.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement frontend database service is unavailable.', 500);
        }

        return $database;
    }

    /** @var object|null */
    protected $item;

    /**
     * Create the administrator-backed SportsManagement table for legacy callers.
     *
     * Autoloads the native table bridge when necessary and injects Joomla's
     * validated database service. Historical type, prefix and configuration
     * arguments remain accepted for existing template overrides.
     *
     * @param string $type Legacy table type (retained for signature compatibility).
     * @param string $prefix Legacy table class prefix.
     * @param array $config Legacy table options.
     * @return LegacySportsmanagementTable Database-aware native table bridge.
     * @throws \RuntimeException When the native table class is unavailable.
     */
    public function getTable($type = 'sportsmanagement', $prefix = 'sportsmanagementTable', $config = array())
    {
        if (!class_exists(LegacySportsmanagementTable::class)) {
            $nativeTable = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/LegacySportsmanagementTable.php';

            if (is_file($nativeTable)) {
                require_once $nativeTable;
            }
        }

        if (!class_exists(LegacySportsmanagementTable::class)) {
            throw new \RuntimeException('SportsManagement legacy table bridge could not be loaded.', 500);
        }

        return new LegacySportsmanagementTable(self::joomlaDatabase());
    }

    /**
     * Read the item previously stored by the legacy frontend model.
     *
     * Preserves the old getItem() contract for template overrides without
     * reintroducing the deprecated Joomla table and model factories.
     *
     * @param int|null $pk Unused historical primary-key selector.
     * @return object|null Previously prepared item, if any.
     */
    public function getItem($pk = null)
    {
        return $this->item;
    }

    /**
     * Populate model state from the validated Joomla site request.
     *
     * Reads the integer record selector and component parameters from the
     * active frontend application, then lets Joomla initialise inherited state.
     * This avoids the removed static Factory application accessor.
     *
     * @return void
     */
    protected function populateState()
    {
        $app = self::joomlaApplication();
        $input = $app->getInput();

        $this->setState('message.id', $input->getInt('id'));
        $this->setState('params', $app->getParams());

        parent::populateState();
    }
}
