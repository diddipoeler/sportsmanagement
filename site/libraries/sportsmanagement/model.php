<?php
/**
 * SportsManagement legacy model compatibility classes.
 *
 * @package    Sportsmanagement
 * @subpackage libraries
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseInterface;

/**
 * Legacy administrator-style model base retained for old SportsManagement models.
 */
class JSMModelAdmin extends AdminModel
{
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->jsmapp    = Factory::getApplication();
        $this->jsmjinput = $this->jsmapp->getInput();
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        $this->jsmview   = $this->jsmjinput->getCmd('view');
    }

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
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->jsmapp    = Factory::getApplication();
        $this->jsmjinput = $this->jsmapp->getInput();
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        $this->jsmview   = $this->jsmjinput->getCmd('view');

        $this->jsmdb = $this->resolveDatabase();
        $this->setDatabase($this->jsmdb);
        $this->jsmquery     = $this->jsmdb->getQuery(true);
        $this->jsmsubquery1 = $this->jsmdb->getQuery(true);
        $this->jsmsubquery2 = $this->jsmdb->getQuery(true);
        $this->jsmsubquery3 = $this->jsmdb->getQuery(true);
    }

    private function resolveDatabase(): DatabaseInterface
    {
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $selector = $this->jsmjinput->getInt('cfg_which_database', 0) === 1 ? 1 : 0;

        return SportsManagementDatabaseResolver::resolve($joomlaDatabase, $selector);
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
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->jsmapp    = Factory::getApplication();
        $this->jsmjinput = $this->jsmapp->getInput();
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        $this->jsmview   = $this->jsmjinput->getCmd('view');

        $this->jsmdb = $this->resolveDatabase();
        $this->setDatabase($this->jsmdb);
        $this->jsmquery = $this->jsmdb->getQuery(true);

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

    private function resolveDatabase(): DatabaseInterface
    {
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $selector = $this->jsmjinput->getInt('cfg_which_database', 0) === 1 ? 1 : 0;

        return SportsManagementDatabaseResolver::resolve($joomlaDatabase, $selector);
    }
}
