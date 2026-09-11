<?php
/**
 * Native Joomla 5/6 model entry point for SportsManagement XML exports.
 *
 * The export data collectors still live in the legacy model for now. This
 * class lets Joomla's MVCFactory resolve the model natively and replaces the
 * legacy constructor bootstrap with Joomla 5/6 application/database APIs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
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
 * Joomla 5/6 MVCFactory model for the historical JL XML exporter.
 *
 * The inherited export routines are migrated incrementally. Keeping them
 * behind this native entry point removes the legacy model loader dependency
 * from the administrator controller immediately.
 */
class JlxmlexportsModel extends \sportsmanagementModelJLXMLExports
{
    /** @var object Joomla administrator application used by legacy collectors. */
    public $app;

    /** @var object|null Current Joomla identity used to build the export path. */
    public $user;

    /** @var object Joomla input object used by the legacy export routines. */
    public $jinput;

    public string $option = 'com_sportsmanagement';

    public DatabaseInterface $jsmdb;

    /** @var object Joomla database query used by the inherited collectors. */
    public $query;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        // Deliberately bypass the legacy model constructor. It still uses
        // Factory::getUser(), $app->input and setDbo()/getDbo().
        BaseDatabaseModel::__construct($config, $factory);

        $this->app = SportsManagementAdministratorApplicationResolver::resolve();
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

    /**
     * Compatibility for inherited collectors that still call getDbo().
     * New code must use the injected database/query properties instead.
     */
    public function getDbo(): DatabaseInterface
    {
        return $this->jsmdb;
    }
}
