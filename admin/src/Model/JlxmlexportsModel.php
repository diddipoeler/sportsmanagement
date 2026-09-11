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
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

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
        $this->setDatabase($this->jsmdb);
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

    /**
     * Joomla 5/6 download response without the legacy app->input/jimport path.
     */
    public function downloadXml($data, $table)
    {
        $filename = $this->buildDownloadFilename((string) $table);

        header('Content-Type: text/xml; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');

        if (ob_get_level() > 0) {
            ob_clean();
        }

        echo (string) $data;
    }

    private function buildDownloadFilename(string $table = ''): string
    {
        $projectId = max(0, $this->jinput->getInt('pid', 0));
        $name = 'sportsmanagement-export';

        if ($projectId > 0) {
            $query = $this->jsmdb->createQuery()
                ->select($this->jsmdb->quoteName('name'))
                ->from($this->jsmdb->quoteName('#__sportsmanagement_project'))
                ->where($this->jsmdb->quoteName('id') . ' = :projectId')
                ->bind(':projectId', $projectId, ParameterType::INTEGER);
            $this->jsmdb->setQuery($query, 0, 1);
            $projectName = trim((string) $this->jsmdb->loadResult());

            if ($projectName !== '') {
                $name = $projectName;
            }
        }

        if ($table !== '') {
            $name .= '-' . $table;
        }

        $safeName = OutputFilter::stringURLSafe($name) ?: 'sportsmanagement-export';

        return $safeName . '-' . date('ymd-His') . '.jlg';
    }
}
