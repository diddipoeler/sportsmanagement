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

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        BaseDatabaseModel::__construct($config, $factory);

        $this->app = SportsManagementSiteApplicationResolver::resolve();
        $this->user = $this->app->getIdentity();
        $this->jinput = $this->app->getInput();
        $this->option = $this->jinput->getCmd('option', 'com_sportsmanagement');
        $this->jsmdb = \sportsmanagementHelper::getDBConnection();
        $this->setDatabase($this->jsmdb);
        $this->query = $this->jsmdb->createQuery();
    }

    public function getDbo(): DatabaseInterface
    {
        return $this->jsmdb;
    }

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
