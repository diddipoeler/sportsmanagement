<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectteamModel;
use Diddipoeler\Component\SportsManagement\Administrator\Service\FinderRelationNotifier;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;

/** Native Joomla 5/6 form controller for a project-team record. */
final class ProjectteamController extends SportsManagementFormController
{
    public function storechangeteams(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        if (!$this->app->getIdentity()->authorise('core.edit', 'com_sportsmanagement')) {
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

    public function getModel($name = 'Projectteam', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }

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

            $query = $db->getQuery(true)
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
                ->where($db->quoteName('pt.id') . ' = ' . $projectTeamId);

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

    private function resolveSeasonTeamSelection(
        ProjectteamModel $model,
        int $selectedId,
        int $expectedSeasonId
    ): ?object {
        if ($selectedId <= 0 || $expectedSeasonId <= 0) {
            return null;
        }

        $db = $this->database();
        $query = $db->getQuery(true)
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
            ->where($db->quoteName('st.id') . ' = ' . $selectedId);

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

            $nameQuery = $db->getQuery(true)
                ->select($db->quoteName('name'))
                ->from($db->quoteName('#__sportsmanagement_team'))
                ->where($db->quoteName('id') . ' = ' . $selectedId);
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

    private function ensureSeasonTeam(ProjectteamModel $model, int $teamId, int $seasonId): int
    {
        if ($teamId <= 0 || $seasonId <= 0) {
            return 0;
        }

        $db = $this->database();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_season_team_id'))
            ->where($db->quoteName('team_id') . ' = ' . $teamId)
            ->where($db->quoteName('season_id') . ' = ' . $seasonId);

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

    private function database(): DatabaseInterface
    {
        $input = $this->app->getInput();
        $selector = $input->getInt(
            'cfg_which_database',
            (int) $this->app->getUserState('com_sportsmanagement.cfg_which_database', 0)
        );

        return (new SportsManagementDatabaseResolver())->resolve(
            $selector,
            Factory::getContainer()->get(DatabaseInterface::class)
        );
    }

    private function normaliseIds(mixed $ids): array
    {
        return array_values(array_unique(array_filter(
            array_map('intval', (array) $ids),
            static fn (int $id): bool => $id > 0
        )));
    }
}
