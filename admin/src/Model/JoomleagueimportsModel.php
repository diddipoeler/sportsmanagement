<?php
/**
 * Joomla 5/6 administrator JoomLeague import facade model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseFactory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Joomla 5/6 model facade for the remaining historical JoomLeague import engine.
 *
 * Small local-database operations, the external database preflight and the
 * preparation step live natively here. Only the remaining table-conversion
 * engine stays behind the explicit legacy boundary.
 */
final class JoomleagueimportsModel extends BaseDatabaseModel
{
    private ?object $legacyModel = null;

    /**
     * Verify the configured JoomLeague database and add fields required by the
     * historical conversion engine when importing from older JoomLeague data.
     */
    public function check_database(): int
    {
        $database = null;

        try {
            $database = $this->createJoomLeagueDatabase();
        } catch (\Throwable $exception) {
            Log::add(
                __METHOD__ . ': JoomLeague database connection failed: ' . $exception->getMessage(),
                Log::ERROR,
                'jsmerror'
            );

            return 1;
        }

        $schema = [
            'joomleague_division' => [
                'tree_id' => "INT(11) NOT NULL DEFAULT '0'",
            ],
            'joomleague_match_player' => [
                'position_id' => "INT(11) NOT NULL DEFAULT '0'",
                'project_position_id' => "INT(11) NOT NULL DEFAULT '0'",
            ],
            'joomleague_match_referee' => [
                'referee_id' => "INT(11) NOT NULL DEFAULT '0'",
                'position_id' => "INT(11) NOT NULL DEFAULT '0'",
                'project_position_id' => "INT(11) NOT NULL DEFAULT '0'",
            ],
            'joomleague_match_staff' => [
                'staff_id' => "INT(11) NOT NULL DEFAULT '0'",
                'position_id' => "INT(11) NOT NULL DEFAULT '0'",
                'project_position_id' => "INT(11) NOT NULL DEFAULT '0'",
            ],
            'joomleague_project_referee' => [
                'position_id' => "INT(11) NOT NULL DEFAULT '0'",
            ],
            'joomleague_team_player' => [
                'position_id' => "INT(11) NOT NULL DEFAULT '0'",
                'project_position_id' => "INT(11) NOT NULL DEFAULT '0'",
            ],
            'joomleague_team_staff' => [
                'position_id' => "INT(11) NOT NULL DEFAULT '0'",
                'project_position_id' => "INT(11) NOT NULL DEFAULT '0'",
            ],
            'joomleague_team_trainingdata' => [
                'team_id_in_project' => "INT(11) NOT NULL DEFAULT '0'",
            ],
            'joomleague_project_team' => [
                'mark' => 'INT(11) DEFAULT NULL',
            ],
            'joomleague_project' => [
                'serveroffset' => "VARCHAR(6) NOT NULL DEFAULT '-01:00'",
                'tree_id' => "INT(11) NOT NULL DEFAULT '0'",
                'admin' => "INT(11) NOT NULL DEFAULT '0'",
                'editor' => "INT(11) NOT NULL DEFAULT '0'",
            ],
        ];
        $errors = 0;
        $prefix = $database->getPrefix();

        foreach ($schema as $tableSuffix => $fields) {
            $table = $prefix . $tableSuffix;

            try {
                $columns = $database->getTableColumns($table, false);
            } catch (\Throwable $exception) {
                $errors++;
                Log::add(
                    __METHOD__ . ': unable to inspect ' . $table . ': ' . $exception->getMessage(),
                    Log::ERROR,
                    'jsmerror'
                );
                continue;
            }

            foreach ($fields as $field => $definition) {
                if (array_key_exists($field, $columns)) {
                    continue;
                }

                $sql = 'ALTER TABLE ' . $database->quoteName($table)
                    . ' ADD ' . $database->quoteName($field) . ' ' . $definition;

                try {
                    $database->setQuery($sql);
                    $database->execute();
                } catch (\Throwable $exception) {
                    $errors++;
                    Log::add(
                        __METHOD__ . ': unable to add ' . $table . '.' . $field . ': ' . $exception->getMessage(),
                        Log::ERROR,
                        'jsmerror'
                    );
                }
            }
        }

        $this->disconnectDatabase($database);

        return $errors;
    }

    public function get_info_fields(): array
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('info'),
                $db->quoteName('agegroup_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team'))
            ->where($db->quoteName('info') . ' <> :emptyInfo')
            ->group([
                $db->quoteName('info'),
                $db->quoteName('agegroup_id'),
            ])
            ->bind(':emptyInfo', '', ParameterType::STRING);
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function joomleaguesetagegroup(): int
    {
        $post = SportsManagementAdministratorApplicationResolver::resolve()->getInput()->post->getArray();
        $agegroups = (array) ($post['agegroup'] ?? []);
        $db = $this->getDatabase();
        $updated = 0;

        foreach ($agegroups as $info => $agegroupId) {
            $info = (string) $info;
            $agegroupId = (int) $agegroupId;

            if ($info === '') {
                continue;
            }

            $query = $db->createQuery()
                ->update($db->quoteName('#__sportsmanagement_team'))
                ->set($db->quoteName('agegroup_id') . ' = :agegroupId')
                ->where($db->quoteName('info') . ' = :teamInfo')
                ->bind(':agegroupId', $agegroupId, ParameterType::INTEGER)
                ->bind(':teamInfo', $info, ParameterType::STRING);
            $db->setQuery($query);
            $db->execute();
            $updated++;
        }

        return $updated;
    }

    public function importjoomleaguenew($importstep = 0, $sportsTypeId = 0)
    {
        $step = (int) $importstep;

        if ($step === 0) {
            return $this->prepareJoomLeagueImport($sportsTypeId);
        }

        return $this->legacy()->importjoomleaguenew($importstep, $sportsTypeId);
    }

    /**
     * Native equivalent of the historical import step 0.
     *
     * The source tables receive the indexes needed by later joins and old
     * unpublished person rows are enabled before conversion starts.
     */
    private function prepareJoomLeagueImport(int $sportsTypeId): array
    {
        $started = microtime(true);
        $app = SportsManagementAdministratorApplicationResolver::resolve();
        $input = $app->getInput();
        $input->set('filter_sports_type', $sportsTypeId);
        $database = $this->createJoomLeagueDatabase();
        $prefix = $database->getPrefix();
        $messages = [];

        $indexes = [
            'joomleague_match_player' => 'match_id',
            'joomleague_match_staff' => 'match_id',
            'joomleague_match_referee' => 'match_id',
            'joomleague_match' => 'round_id',
        ];

        foreach ($indexes as $tableSuffix => $column) {
            $table = $prefix . $tableSuffix;
            $status = $this->ensureIndex($database, $table, $column);
            $messages[] = $this->legacyStatusLine($tableSuffix, $status['success'], $status['message']);
        }

        $personTable = $prefix . 'joomleague_person';
        $published = 1;
        $unpublished = 0;

        try {
            $query = $database->createQuery()
                ->update($database->quoteName($personTable))
                ->set($database->quoteName('published') . ' = :published')
                ->where($database->quoteName('published') . ' = :unpublished')
                ->bind(':published', $published, ParameterType::INTEGER)
                ->bind(':unpublished', $unpublished, ParameterType::INTEGER);
            $database->setQuery($query);
            $database->execute();
            $messages[] = $this->legacyStatusLine('joomleague_person', true, 'aktualisiert');
        } catch (\Throwable $exception) {
            Log::add(__METHOD__ . ': ' . $exception->getMessage(), Log::ERROR, 'jsmerror');
            $messages[] = $this->legacyStatusLine(
                'joomleague_person',
                false,
                'nicht aktualisiert (' . $exception->getMessage() . ')'
            );
        }

        $this->disconnectDatabase($database);
        $input->set('jl_table_import_step', 1);

        return [
            'Laufzeit:' => Text::sprintf(
                'This page was created in %1$s seconds',
                number_format(microtime(true) - $started, 4, '.', '')
            ),
            'JL-Update:' => implode('', $messages),
        ];
    }

    /** @return array{success:bool,message:string} */
    private function ensureIndex(DatabaseInterface $database, string $table, string $column): array
    {
        try {
            if (method_exists($database, 'getTableKeys')) {
                foreach ((array) $database->getTableKeys($table) as $key) {
                    $keyName = strtolower((string) ($key->Key_name ?? $key->key_name ?? $key->INDEX_NAME ?? ''));
                    $columnName = strtolower((string) ($key->Column_name ?? $key->column_name ?? $key->COLUMN_NAME ?? ''));

                    if ($keyName === strtolower($column) || $columnName === strtolower($column)) {
                        return ['success' => true, 'message' => 'aktualisiert'];
                    }
                }
            }

            $sql = 'ALTER TABLE ' . $database->quoteName($table)
                . ' ADD INDEX ' . $database->quoteName($column)
                . ' (' . $database->quoteName($column) . ')';
            $database->setQuery($sql);
            $database->execute();

            return ['success' => true, 'message' => 'aktualisiert'];
        } catch (\Throwable $exception) {
            Log::add(__METHOD__ . ': ' . $exception->getMessage(), Log::ERROR, 'jsmerror');

            return [
                'success' => false,
                'message' => 'nicht aktualisiert (' . $exception->getMessage() . ')',
            ];
        }
    }

    private function legacyStatusLine(string $table, bool $success, string $message): string
    {
        $color = $success ? 'green' : 'red';

        return '<span style="color:' . $color . '"><strong>Daten in der Tabelle: ( __'
            . htmlspecialchars($table, ENT_QUOTES, 'UTF-8') . ' ) '
            . htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
            . '!</strong></span><br />';
    }

    private function createJoomLeagueDatabase(): DatabaseInterface
    {
        $app = SportsManagementAdministratorApplicationResolver::resolve();
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $driver = trim((string) $params->get('jl_dbtype', ''));

        if ($driver === '') {
            $driver = (string) $app->get('dbtype', 'mysqli');
        }

        $driver = match (strtolower($driver)) {
            'mysql' => 'mysqli',
            'postgresql' => 'pgsql',
            default => strtolower($driver),
        };

        $factory = new DatabaseFactory();

        return $factory->getDriver($driver, [
            'host' => (string) ($params->get('jl_host') ?: $app->get('host', 'localhost')),
            'user' => (string) ($params->get('jl_user') ?: $app->get('user', '')),
            'password' => (string) ($params->get('jl_password') ?: $app->get('password', '')),
            'database' => (string) ($params->get('jl_db') ?: $app->get('db', '')),
            'prefix' => (string) $params->get('jl_dbprefix', ''),
            'select' => true,
        ]);
    }

    private function disconnectDatabase(DatabaseInterface $database): void
    {
        if (method_exists($database, 'disconnect')) {
            $database->disconnect();
        }
    }

    private function legacy(): object
    {
        if ($this->legacyModel !== null) {
            return $this->legacyModel;
        }

        LegacyBootstrap::boot();
        $legacyFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/joomleagueimports.php';

        if (!class_exists('sportsmanagementModeljoomleagueimports', false) && is_file($legacyFile)) {
            require_once $legacyFile;
        }

        if (!class_exists('sportsmanagementModeljoomleagueimports', false)) {
            throw new \RuntimeException('Legacy JoomLeague import engine is unavailable.', 500);
        }

        $this->legacyModel = new \sportsmanagementModeljoomleagueimports(['ignore_request' => true]);

        return $this->legacyModel;
    }
}
