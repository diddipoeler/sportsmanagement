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
 * Local operations plus JoomLeague source preparation steps 0-9 live natively
 * here. Only the actual table-copy/conversion engine from step 10 onward remains
 * behind the explicit legacy boundary.
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
            return $this->prepareJoomLeagueImport((int) $sportsTypeId);
        }

        if ($step >= 1 && $step <= 6) {
            return $this->prepareJoomLeaguePositionRelations($step, (int) $sportsTypeId);
        }

        if ($step === 7) {
            return $this->repairJoomLeagueEventTimes((int) $sportsTypeId);
        }

        if ($step === 8 || $step === 9) {
            return $this->advanceJoomLeaguePreparationStep($step, (int) $sportsTypeId);
        }

        return $this->legacy()->importjoomleaguenew($importstep, $sportsTypeId);
    }

    /** Native equivalent of the historical import step 0. */
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

        return $this->finishNativeStep(0, $sportsTypeId, $started, implode('', $messages), 'JL-Update:');
    }

    /**
     * Native equivalents of historical steps 1-6 which populate project position
     * relations in the source JoomLeague database before the table copy starts.
     */
    private function prepareJoomLeaguePositionRelations(int $step, int $sportsTypeId): array
    {
        $started = microtime(true);
        $database = $this->createJoomLeagueDatabase();
        $prefix = $database->getPrefix();
        $tableSuffix = '';
        $rows = [];

        try {
            $query = $database->createQuery();

            switch ($step) {
                case 1:
                    $tableSuffix = 'joomleague_project_referee';
                    $query->select([
                        $database->quoteName('pr.id'),
                        $database->quoteName('pr.project_id'),
                        $database->quoteName('pr.position_id'),
                        $database->quoteName('pp.id', 'project_position_id'),
                    ])
                        ->from($database->quoteName($prefix . $tableSuffix, 'pr'))
                        ->join(
                            'INNER',
                            $database->quoteName($prefix . 'joomleague_project_position', 'pp')
                            . ' ON ' . $database->quoteName('pp.project_id') . ' = ' . $database->quoteName('pr.project_id')
                            . ' AND ' . $database->quoteName('pp.position_id') . ' = ' . $database->quoteName('pr.position_id')
                        )
                        ->where($database->quoteName('pr.position_id') . ' <> 0');
                    break;

                case 2:
                case 3:
                    $tableSuffix = $step === 2 ? 'joomleague_team_staff' : 'joomleague_team_player';
                    $query->select([
                        $database->quoteName('member.id'),
                        $database->quoteName('member.position_id'),
                        $database->quoteName('pt.project_id'),
                    ])
                        ->from($database->quoteName($prefix . $tableSuffix, 'member'))
                        ->join(
                            'INNER',
                            $database->quoteName($prefix . 'joomleague_project_team', 'pt')
                            . ' ON ' . $database->quoteName('pt.id') . ' = ' . $database->quoteName('member.projectteam_id')
                        )
                        ->where($database->quoteName('member.project_position_id') . ' = 0')
                        ->where($database->quoteName('member.position_id') . ' <> 0');
                    break;

                case 4:
                case 5:
                case 6:
                    $tableSuffix = match ($step) {
                        4 => 'joomleague_match_player',
                        5 => 'joomleague_match_staff',
                        default => 'joomleague_match_referee',
                    };
                    $select = [
                        $database->quoteName('member.id'),
                        $database->quoteName('member.position_id'),
                        $database->quoteName('r.project_id'),
                    ];

                    if ($step === 6) {
                        $select[] = $database->quoteName('member.referee_id');
                    }

                    $query->select($select)
                        ->from($database->quoteName($prefix . $tableSuffix, 'member'))
                        ->join(
                            'INNER',
                            $database->quoteName($prefix . 'joomleague_match', 'm')
                            . ' ON ' . $database->quoteName('m.id') . ' = ' . $database->quoteName('member.match_id')
                        )
                        ->join(
                            'INNER',
                            $database->quoteName($prefix . 'joomleague_round', 'r')
                            . ' ON ' . $database->quoteName('r.id') . ' = ' . $database->quoteName('m.round_id')
                        )
                        ->where($database->quoteName('member.position_id') . ' <> 0')
                        ->where($database->quoteName('member.project_position_id') . ' = 0');
                    break;
            }

            $database->setQuery($query);
            $rows = $database->loadObjectList() ?: [];

            foreach ($rows as $row) {
                $projectPositionId = $step === 1
                    ? (int) ($row->project_position_id ?? 0)
                    : $this->resolveJoomLeagueProjectPosition(
                        $database,
                        $prefix,
                        (int) ($row->project_id ?? 0),
                        (int) ($row->position_id ?? 0)
                    );

                $update = (object) [
                    'id' => (int) $row->id,
                    'project_position_id' => $projectPositionId,
                ];

                if ($step === 2 || $step === 3) {
                    $update->published = 1;
                }

                if ($step === 6) {
                    $update->project_referee_id = (int) ($row->referee_id ?? 0);
                }

                $database->updateObject($prefix . $tableSuffix, $update, 'id');
            }

            $message = $this->legacyStatusLine($tableSuffix, true, 'aktualisiert');
        } catch (\Throwable $exception) {
            Log::add(__METHOD__ . ': ' . $exception->getMessage(), Log::ERROR, 'jsmerror');
            $message = $this->legacyStatusLine(
                $tableSuffix !== '' ? $tableSuffix : 'joomleague',
                false,
                'nicht aktualisiert (' . $exception->getMessage() . ')'
            );
        }

        $this->disconnectDatabase($database);
        $resultKey = $step === 6 ? 'Tabellenkopie:' : 'JL-Update:';

        return $this->finishNativeStep($step, $sportsTypeId, $started, $message, $resultKey);
    }

    /** Native equivalent of historical step 7. */
    private function repairJoomLeagueEventTimes(int $sportsTypeId): array
    {
        $started = microtime(true);
        $database = $this->createJoomLeagueDatabase();
        $tableSuffix = 'joomleague_match_event';
        $table = $database->getPrefix() . $tableSuffix;
        $empty = '';
        $replacement = '1';

        try {
            $query = $database->createQuery()
                ->update($database->quoteName($table))
                ->set($database->quoteName('event_time') . ' = :replacement')
                ->where($database->quoteName('event_time') . ' = :emptyTime')
                ->bind(':replacement', $replacement, ParameterType::STRING)
                ->bind(':emptyTime', $empty, ParameterType::STRING);
            $database->setQuery($query);
            $database->execute();
            $message = $this->legacyStatusLine($tableSuffix, true, 'aktualisiert zum ändern gefunden');
        } catch (\Throwable $exception) {
            Log::add(__METHOD__ . ': ' . $exception->getMessage(), Log::ERROR, 'jsmerror');
            $message = $this->legacyStatusLine(
                $tableSuffix,
                false,
                'nicht aktualisiert (' . $exception->getMessage() . ')'
            );
        }

        $this->disconnectDatabase($database);

        return $this->finishNativeStep(7, $sportsTypeId, $started, $message, 'Tabellenkopie:');
    }

    /** Historical steps 8 and 9 intentionally performed no data changes. */
    private function advanceJoomLeaguePreparationStep(int $step, int $sportsTypeId): array
    {
        return $this->finishNativeStep($step, $sportsTypeId, microtime(true), '', 'Tabellenkopie:');
    }

    private function resolveJoomLeagueProjectPosition(
        DatabaseInterface $database,
        string $prefix,
        int $projectId,
        int $positionId
    ): int {
        if ($projectId <= 0 || $positionId <= 0) {
            return 0;
        }

        $query = $database->createQuery()
            ->select($database->quoteName('id'))
            ->from($database->quoteName($prefix . 'joomleague_project_position'))
            ->where($database->quoteName('position_id') . ' = :positionId')
            ->where($database->quoteName('project_id') . ' = :projectId')
            ->bind(':positionId', $positionId, ParameterType::INTEGER)
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $database->setQuery($query, 0, 1);

        return (int) ($database->loadResult() ?: 0);
    }

    private function finishNativeStep(
        int $step,
        int $sportsTypeId,
        float $started,
        string $message,
        string $resultKey
    ): array {
        $input = SportsManagementAdministratorApplicationResolver::resolve()->getInput();
        $input->set('filter_sports_type', $sportsTypeId);
        $input->set('jl_table_import_step', $step + 1);

        return [
            'Laufzeit:' => Text::sprintf(
                'This page was created in %1$s seconds',
                number_format(microtime(true) - $started, 4, '.', '')
            ),
            $resultKey => $message,
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
