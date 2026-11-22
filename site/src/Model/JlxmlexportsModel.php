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

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
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

    /**
     * Resolve the active Joomla frontend application for XML exports.
     *
     * Use the component's site application resolver rather than accessing
     * legacy global application methods while preparing export requests.
     *
     * @return CMSApplicationInterface Current frontend application.
     */
    private static function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Initialise the legacy-compatible XML export model through Joomla MVC.
     *
     * Set up the configured Joomla model, capture the site identity and
     * request input, resolve the selected SportsManagement database and
     * create a query builder for the historical XML export engine.
     *
     * @param array $config MVC model configuration.
     * @param MVCFactoryInterface|null $factory Joomla MVC factory if supplied.
     * @return void
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        BaseDatabaseModel::__construct($config, $factory);

        $this->app = self::siteApplication();
        $this->user = $this->app->getIdentity();
        $this->jinput = $this->app->getInput();
        $this->option = $this->jinput->getCmd('option', 'com_sportsmanagement');
        $this->jsmdb = SportsManagementDatabaseResolver::resolve(
            $this->getDatabase(),
            max(0, $this->jinput->getInt('cfg_which_database', 0))
        );
        $this->setDatabase($this->jsmdb);
        $this->query = $this->jsmdb->createQuery();
    }

    /**
     * Expose the selected database to legacy XML export collectors.
     *
     * Preserve the historical getDbo() contract for the parent exporter
     * while using the native Joomla DatabaseInterface implementation.
     *
     * @return DatabaseInterface Configured SportsManagement database.
     */
    public function getDbo(): DatabaseInterface
    {
        return $this->jsmdb;
    }

    /**
     * Export the selected project through the legacy XML export engine.
     *
     * Map the frontend project's p request parameter to the older pid
     * parameter when necessary, then delegate actual export collection
     * to the inherited model for backward-compatible result formatting.
     *
     * @return mixed Data or export result produced by the parent model.
     */
    public function exportData()
    {
        if ($this->jinput->getInt('pid', 0) <= 0) {
            $projectId = $this->jinput->getInt('p', 0);

            if ($projectId > 0) {
                $this->jinput->set('pid', $projectId);
            }
        }

        return parent::exportData();
    }

    /**
     * Stream an XML export as an HTTP download response.
     *
     * Generate a safe project-based attachment filename, emit XML content
     * and no-cache response headers, clear the active output buffer when
     * present and write the supplied XML document to the response body.
     *
     * @param mixed $data XML document produced by the export collector.
     * @param mixed $table Historical export table identifier.
     * @return void The XML response is written directly to output.
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

    /**
     * Build a safe download filename for the selected project and table.
     *
     * Read the positive project identifier, query its name using a bound
     * parameter, append the optional table label, normalise the result
     * using Joomla's URL-safe filter and add a timestamp and .jlg suffix.
     *
     * @param string $table Optional export table label.
     * @return string Sanitised timestamped XML export attachment filename.
     */
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
