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
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseFactory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Joomla 5/6 model facade for the remaining historical JoomLeague import engine.
 *
 * Small local-database operations and the external database preflight live
 * natively here. Only the old table-conversion engine remains behind the
 * explicit legacy boundary.
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

        if (method_exists($database, 'disconnect')) {
            $database->disconnect();
        }

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
        return $this->legacy()->importjoomleaguenew($importstep, $sportsTypeId);
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
