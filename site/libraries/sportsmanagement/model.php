<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
  * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * SportsManagement legacy model compatibility classes.
 *
 * @package    Sportsmanagement
 * @subpackage libraries
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseInterface;

trait JSMJoomlaServices
{
    /**
     * Resolve the Joomla frontend application service from the dependency injection container for the legacy model bases.
     * @return SiteApplication Joomla site application.
     */
    private static function joomlaSiteApplication(): SiteApplication
    {
        /** @var SiteApplication $application */
        $application = Factory::getContainer()->get(SiteApplication::class);

        return $application;
    }

    /**
     * Resolve the Joomla database service from the dependency injection container for legacy database selection.
     * @return DatabaseInterface Joomla database connection.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        return $database;
    }
}

/**
 * Legacy administrator-style model base retained for old SportsManagement models.
 */
class JSMModelAdmin extends AdminModel
{
    use JSMJoomlaServices;
    /**
     * Initialise the administrator-style legacy model and capture the frontend application, request input, component option and view.
     * @param array $config Joomla model configuration.
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        $app = self::joomlaSiteApplication();
        $this->jsmapp    = $app;
        $this->jsmjinput = $this->jsmapp->getInput();
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        $this->jsmview   = $this->jsmjinput->getCmd('view');
    }

    /**
     * Provide the legacy form-loading compatibility hook. This base implementation intentionally does not construct or return a form.
     * @param array $data Form data.
     * @param bool $loadData Whether to load existing data.
     * @return void
     */
    public function getForm($data = array(), $loadData = true)
    {
    }
}

/**
 * Legacy list-model base.
 *
 * Database selection is deliberately routed through the same resolver used by
 * the namespaced Joomla 5/6 models. This preserves the historical external-DB
 * selector without loading sportsmanagementHelper or using deprecated setDbo().
 */
class JSMModelList extends ListModel
{
    use JSMJoomlaServices;

    /**
     * Initialise the legacy list model, capture the request context, resolve and bind the selected SportsManagement database and allocate its reusable query builders.
     * @param array $config Joomla model configuration.
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        $app = self::joomlaSiteApplication();
        $this->jsmapp    = $app;
        $this->jsmjinput = $this->jsmapp->getInput();
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        $this->jsmview   = $this->jsmjinput->getCmd('view');

        $this->jsmdb = $this->resolveDatabase();
        $this->setDatabase($this->jsmdb);
        $this->jsmquery     = $this->jsmdb->createQuery();
        $this->jsmsubquery1 = $this->jsmdb->createQuery();
        $this->jsmsubquery2 = $this->jsmdb->createQuery();
        $this->jsmsubquery3 = $this->jsmdb->createQuery();
    }

    /**
     * Select the primary or external SportsManagement database according to the integer request selector and delegate connection resolution to the shared database resolver.
     * @return DatabaseInterface Selected database connection.
     */
    private function resolveDatabase(): DatabaseInterface
    {
        $selector = $this->jsmjinput->getInt('cfg_which_database', 0) === 1 ? 1 : 0;

        return SportsManagementDatabaseResolver::resolve(self::joomlaDatabase(), $selector);
    }
}

/**
 * Legacy database-model base.
 *
 * The custom SportsManagement MVCFactory can still rebind a model after
 * construction. Initial construction now follows the same resolver path too,
 * so direct legacy instantiation no longer depends on getDBConnection().
 */
class JSMModelLegacy extends BaseDatabaseModel
{
    use JSMJoomlaServices;

    /**
     * Initialise the legacy database model with the frontend request context and selected database, allocate a query builder and register the message-queue and database loggers.
     * @param array $config Joomla model configuration.
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        $app = self::joomlaSiteApplication();
        $this->jsmapp    = $app;
        $this->jsmjinput = $this->jsmapp->getInput();
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        $this->jsmview   = $this->jsmjinput->getCmd('view');

        $this->jsmdb = $this->resolveDatabase();
        $this->setDatabase($this->jsmdb);
        $this->jsmquery = $this->jsmdb->createQuery();

        Log::addLogger(array('logger' => 'messagequeue'), Log::ALL, array('jsmerror'));
        Log::addLogger(
            array('logger' => 'database', 'db_table' => '#__sportsmanagement_log_entries'),
            Log::ALL,
            array('dblog')
        );
        Log::addLogger(
            array('logger' => 'database', 'db_table' => '#__sportsmanagement_log_entries'),
            Log::ALL,
            array('dbperformance')
        );
    }

    /**
     * Resolve the selected SportsManagement database using the legacy request selector and the shared database resolver.
     * @return DatabaseInterface Selected database connection.
     */
    private function resolveDatabase(): DatabaseInterface
    {
        $selector = $this->jsmjinput->getInt('cfg_which_database', 0) === 1 ? 1 : 0;

        return SportsManagementDatabaseResolver::resolve(self::joomlaDatabase(), $selector);
    }
}
