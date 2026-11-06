<?php
/**
 * Joomla 5/6 administrator controller for project-team records.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectteamModel;
use Diddipoeler\Component\SportsManagement\Administrator\Service\FinderRelationNotifier;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/** Native Joomla 5/6 form controller for a project-team record. */
final class ProjectteamController extends SportsManagementFormController
{
    /**
     * Replace selected project-team assignments in the administrator.
     * Requires a valid POST token and edit permission, snapshots the old
     * Finder relations, updates selected season-team references and
     * notifies Joomla Finder after successful changes.
     * @return void
     * @throws \RuntimeException If the token, permission or model is invalid.
     */
    public function storechangeteams(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $identity = $this->app->getIdentity();

        if ($identity === null || !$identity->authorise('core.edit', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $model = $this->getModel('Projectteam', 'Administrator', ['ignore_request' => true]);

        if (!$model instanceof ProjectteamModel) {
            throw new \RuntimeException('ProjectteamModel is unavailable.', 500);
        }

        $projectTeamIds = $this->normaliseIds(
            $this->app->getInput()->post->get('oldteamid', [], 'array')
        );
        $notifier = new FinderRelationNotifier($this->database());
        $before = $notifier->projectTeamEntitiesForRows($projectTeamIds);
        $ok = $this->replaceSelectedTeams($model);

        if ($ok) {
            $notifier->notify($before, $notifier->projectTeamEntitiesForRows($projectTeamIds));
        } else {
            $this->app->enqueueMessage(
                $model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
                'warning'
            );
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
    }

    /**
     * Resolve the native project-team editor model via Joomla MVC.
     * Preserves additional caller settings while defaulting to request-
     * independent model loading for project-team write operations.
     * @param string $name Requested project-team model name.
     * @param string $prefix Administrator MVC namespace.
     * @param array $config Additional model factory configuration.
     * @return mixed Resolved model or parent failure result.
     */
    public function getModel($name = 'Projectteam', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, array_replace(['ignore_request' => true], $config));
    }

    /**
     * Replace project-team relations with selected season-team IDs.
     * Reads validated project-team IDs from POST, resolves each selected
     * team in the project's season and stores the new association.
     * @param ProjectteamModel $model Native project-team model.
     * @return bool True if all eligible team assignments succeeded.
     */
    private function replaceSelectedTeams(ProjectteamModel $model): bool
    {
        $input = $this->app->getInput();
        $oldIds = $this->normaliseIds($input->post->get('oldteamid', [], 'array'));
        $newIds = (array) $input->post->get('newteamid', [], 'array');
        $db = $this->database();

        foreach ($oldIds as $projectTeamId) {
            $selectedRelationId = (int) ($newIds[$projectTeamId] ?? 0);

            if ($selectedRelationId <= 0) {
                continue;
            }

            $query = $db->createQuery()
                ->select([
                    $db->quoteName('st.season_id'),
                    $db->quoteName('t.name', 'old_name'),
                ])
                ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                    . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
                )
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_team', 't')
                    . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id')
                )
                ->where($db->quoteName('pt.id') . ' = :currentProjectTeamId')
                ->bind(':currentProjectTeamId', $projectTeamId, ParameterType::INTEGER);

            try {
                $db->setQuery($query, 0, 1);
                $current = $db->loadObject();

                if (!$current) {
                    continue;
                }

                $selection = $this->resolveSeasonTeamSelection(
                    $model,
                    $selectedRelationId,
                    (int) $current->season_id
                );

                if (!$selection) {
                    return false;
                }

                $db->updateObject(
                    '#__sportsmanagement_project_team',
                    (object) [
                        'id' => $projectTeamId,
                        'team_id' => (int) $selection->season_team_id,
                    ],
                    'id'
                );

                $this->app->enqueueMessage(
                    Text::sprintf(
                        'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAM_MODEL_ASSIGNED_OLD_TEAMNAME',
                        (string) $current->old_name,
                        (string) $selection->team_name
                    ),
                    'notice'
                );
            } catch (\Throwable $e) {
                $model->setError($e->getMessage());
                return false;
            }
        }

        return true;
    }

    /**
     * Resolve a selected team relation for the required season.
     * Supports a season-team ID and the legacy raw team-ID fallback,
     * creating a missing association for the expected season if needed.
     * @param ProjectteamModel $model Native project-team model for errors.
     * @param int $selectedId Selected team or season-team relation ID.
     * @param int $expectedSeasonId Season required by the project.
     * @return object|null Selected season-team data or null on failure.
     */
    private function resolveSeasonTeamSelection(
        ProjectteamModel $model,
        int $selectedId,
        int $expectedSeasonId
    ): ?object {
        if ($selectedId <= 0 || $expectedSeasonId <= 0) {
            return null;
        }

        $db = $this->database();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('st.id', 'season_team_id'),
                $db->quoteName('st.team_id'),
                $db->quoteName('st.season_id'),
                $db->quoteName('t.name', 'team_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_season_team_id', 'st'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id')
            )
            ->where($db->quoteName('st.id') . ' = :selectedSeasonTeamId')
            ->bind(':selectedSeasonTeamId', $selectedId, ParameterType::INTEGER);

        try {
            $db->setQuery($query, 0, 1);
            $selection = $db->loadObject();

            if ($selection) {
                if ((int) $selection->season_id !== $expectedSeasonId) {
                    $selection->season_team_id = $this->ensureSeasonTeam(
                        $model,
                        (int) $selection->team_id,
                        $expectedSeasonId
                    );
                }

                return (int) $selection->season_team_id > 0 ? $selection : null;
            }

            // Compatibility fallback for callers that still submit a raw team id.
            $seasonTeamId = $this->ensureSeasonTeam($model, $selectedId, $expectedSeasonId);

            if ($seasonTeamId <= 0) {
                return null;
            }

            $nameQuery = $db->createQuery()
                ->select($db->quoteName('name'))
                ->from($db->quoteName('#__sportsmanagement_team'))
                ->where($db->quoteName('id') . ' = :selectedTeamNameId')
                ->bind(':selectedTeamNameId', $selectedId, ParameterType::INTEGER);
            $db->setQuery($nameQuery, 0, 1);

            return (object) [
                'season_team_id' => $seasonTeamId,
                'team_id' => $selectedId,
                'season_id' => $expectedSeasonId,
                'team_name' => (string) $db->loadResult(),
            ];
        } catch (\Throwable $e) {
            $model->setError($e->getMessage());
            return null;
        }
    }

    /**
     * Find or create a team association for the requested season.
     * Uses bound integer parameters to query existing season-team rows.
     * @param ProjectteamModel $model Native project-team model for errors.
     * @param int $teamId Raw team identifier.
     * @param int $seasonId Required season identifier.
     * @return int Season-team association ID, or zero on failure.
     */
    private function ensureSeasonTeam(ProjectteamModel $model, int $teamId, int $seasonId): int
    {
        if ($teamId <= 0 || $seasonId <= 0) {
            return 0;
        }

        $db = $this->database();
        $query = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_season_team_id'))
            ->where($db->quoteName('team_id') . ' = :seasonTeamId')
            ->where($db->quoteName('season_id') . ' = :seasonId')
            ->bind(':seasonTeamId', $teamId, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER);

        try {
            $db->setQuery($query, 0, 1);
            $seasonTeamId = (int) $db->loadResult();

            if ($seasonTeamId > 0) {
                return $seasonTeamId;
            }

            $db->insertObject(
                '#__sportsmanagement_season_team_id',
                (object) ['team_id' => $teamId, 'season_id' => $seasonId]
            );

            return (int) $db->insertid();
        } catch (\Throwable $e) {
            $model->setError($e->getMessage());
            return 0;
        }
    }

    /**
     * Resolve the SportsManagement database for project-team updates.
     * Uses the selected database option from Joomla request or user state.
     * @return DatabaseInterface Resolved component database connection.
     */
    private function database(): DatabaseInterface
    {
        $app = $this->app;
        $input = $app->getInput();
        $selector = $input->getInt(
            'cfg_which_database',
            (int) $app->getUserState('com_sportsmanagement.cfg_which_database', 0)
        );

        return (new SportsManagementDatabaseResolver())->resolve(
            $selector,
            self::joomlaDatabase()
        );
    }

    /**
     * Get the native Joomla database interface from its container.
     * @return DatabaseInterface Primary Joomla database connection.
     * @throws \RuntimeException If the container has no valid database service.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement project-team database service is unavailable.', 500);
        }

        return $database;
    }

    /**
     * Validate selected project-team relation identifiers from POST.
     * Keeps up to 500 distinct positive decimal integers, rejecting
     * malformed and overflowing values rather than coercing them.
     * @param mixed $ids Raw selected project-team identifiers.
     * @return array<int> Valid unique project-team row IDs.
     */
    private function normaliseIds(mixed $ids): array
    {
        if (!is_array($ids)) {
            return [];
        }

        $valid = [];

        foreach (array_slice($ids, 0, 500) as $value) {
            if (!is_int($value) && !is_string($value)) {
                continue;
            }

            $raw = trim((string) $value);

            if (strlen($raw) > 20 || preg_match('/^[0-9]+$/D', $raw) !== 1) {
                continue;
            }

            $digits = ltrim($raw, '0');

            if ($digits === '') {
                continue;
            }

            $id = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($id !== false) {
                $valid[$id] = $id;
            }
        }

        return array_values($valid);
    }
}
