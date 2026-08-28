<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use RuntimeException;

/**
 * Native Joomla 5/6 facade for the XML import workflow.
 *
 * Read-only lookup operations used by the landing and selector layouts are
 * handled natively. The large historical parser/import engine is loaded only
 * for operations that have not yet been migrated.
 */
final class JlxmlimportModel extends BaseDatabaseModel
{
    public string $import_version = '';

    private ?object $legacyModel = null;

    public function getDataUpdateImportID(): int|false
    {
        $app = Factory::getApplication();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement');
        $projectId = (int) $app->getUserState($option . '.pid', 0);

        if ($projectId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('import_project_id'))
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);
        $importProjectId = $db->loadResult();

        return $importProjectId === null ? false : (int) $importProjectId;
    }

    public function getUserList(bool $isAdmin = false): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('username'),
            ])
            ->from($db->quoteName('#__users'))
            ->order($db->quoteName('username') . ' ASC');

        // The historical `usertype` column no longer exists in modern Joomla.
        // Current XML import callers request the complete user list, so keep
        // the argument for API compatibility without reintroducing that query.
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getTemplateList(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('master_template') . ' = 0')
            ->order($db->quoteName('name') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getNewClubList(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_club'))
            ->order($db->quoteName('name') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getNewClubListSelect(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
                $db->quoteName('country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_club'))
            ->order($db->quoteName('name') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getClubAndTeamList(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('c.id'),
                $db->quoteName('c.name', 'club_name'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('c.country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.club_id') . ' = ' . $db->quoteName('c.id')
            )
            ->order($db->quoteName('c.name') . ' ASC, ' . $db->quoteName('t.name') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getClubAndTeamListSelect(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('t.id', 'value'),
                'CONCAT('
                    . $db->quoteName('c.name') . ', '
                    . $db->quote(' - ') . ', '
                    . $db->quoteName('t.name') . ', '
                    . $db->quote(' (') . ', '
                    . $db->quoteName('t.info') . ', '
                    . $db->quote(')')
                    . ') AS ' . $db->quoteName('text'),
                $db->quoteName('t.club_id'),
                $db->quoteName('c.name', 'club_name'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('c.country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.club_id') . ' = ' . $db->quoteName('c.id')
            )
            ->order($db->quoteName('c.name') . ' ASC, ' . $db->quoteName('t.name') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getData(array $post = []): mixed
    {
        $result = $this->legacy()->getData($post);
        $this->syncLegacyState();

        return $result;
    }

    public function getDataUpdate(): mixed
    {
        $result = $this->legacy()->getDataUpdate();
        $this->syncLegacyState();

        return $result;
    }

    public function importData(array $post): mixed
    {
        $result = $this->legacy()->importData($post);
        $this->syncLegacyState();

        return $result;
    }

    public function getCountryByOldid(): array
    {
        return (array) $this->legacy()->getCountryByOldid();
    }

    private function legacy(): object
    {
        if ($this->legacyModel !== null) {
            return $this->legacyModel;
        }

        LegacyBootstrap::bootForView('jlxmlimport');
        $file = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/jlxmlimport.php';

        if (!class_exists('sportsmanagementModelJLXMLImport', false) && is_file($file)) {
            require_once $file;
        }

        if (!class_exists('sportsmanagementModelJLXMLImport', false)) {
            throw new RuntimeException('Legacy SportsManagement XML import engine not found.', 500);
        }

        try {
            $this->legacyModel = new \sportsmanagementModelJLXMLImport();
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'The remaining legacy XML import engine could not be initialised: ' . $e->getMessage(),
                500,
                $e
            );
        }

        $this->syncLegacyState();

        return $this->legacyModel;
    }

    private function syncLegacyState(): void
    {
        if ($this->legacyModel !== null && isset($this->legacyModel->import_version)) {
            $this->import_version = (string) $this->legacyModel->import_version;
        }
    }
}
