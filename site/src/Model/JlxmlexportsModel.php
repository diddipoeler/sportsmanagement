<?php
/**
 * Native Joomla 5/6 frontend model entry point for SportsManagement XML exports.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;

if (!class_exists('sportsmanagementHelper', false)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';
}

if (!class_exists('sportsmanagementModelJLXMLExports', false)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/jlxmlexports.php';
}

/**
 * Site MVCFactory adapter for the historical XML export collectors.
 *
 * This keeps the existing frontend export URL functional while the shared
 * export engine is moved out of the administrator legacy model incrementally.
 */
class JlxmlexportsModel extends \sportsmanagementModelJLXMLExports
{
    public $app;
    public $user;
    public $jinput;
    public string $option = 'com_sportsmanagement';
    public DatabaseInterface $jsmdb;
    public $query;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        BaseDatabaseModel::__construct($config, $factory);

        $this->app = SportsManagementSiteApplicationResolver::resolve();
        $this->user = $this->app->getIdentity();
        $this->jinput = $this->app->getInput();
        $this->option = $this->jinput->getCmd('option', 'com_sportsmanagement');
        $this->jsmdb = \sportsmanagementHelper::getDBConnection();

        if (method_exists($this, 'setDatabase')) {
            $this->setDatabase($this->jsmdb);
        } else {
            parent::setDbo($this->jsmdb);
        }

        $this->query = $this->jsmdb->createQuery();
    }

    public function getDbo(): DatabaseInterface
    {
        return $this->jsmdb;
    }
}
