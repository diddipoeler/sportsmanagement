<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectteamModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

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

        $ok = $this->replaceSelectedTeams($model);

        if (!$ok) {
            $this->app->enqueueMessage($model->getError() ?: Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'warning');
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
        $oldIds = array_values(array_unique(array_filter(
            array_map('intval', (array) $input->post->get('oldteamid', [], 'array')),
            static fn (int $id): bool => $id > 0
        )));
        $newIds = (array) $input->post->get('newteamid', [], 'array');
        $db = $model->getDatabase();

        foreach ($oldIds as $projectTeamId) {
            $newTeamId = (int) ($newIds[$projectTeamId] ?? 0);

            if ($newTeamId <= 0) {
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

                $seasonTeamId = $this->ensureSeasonTeam($model, $newTeamId, (int) $current->season_id);

                if ($seasonTeamId <= 0) {
                    return false;
                }

                $db->updateObject(
                    '#__sportsmanagement_project_team',
                    (object) ['id' => $projectTeamId, 'team_id' => $seasonTeamId],
                    'id'
                );

                $nameQuery = $db->getQuery(true)
                    ->select($db->quoteName('name'))
                    ->from($db->quoteName('#__sportsmanagement_team'))
                    ->where($db->quoteName('id') . ' = ' . $newTeamId);
                $db->setQuery($nameQuery, 0, 1);
                $newName = (string) $db->loadResult();

                $this->app->enqueueMessage(
                    Text::sprintf(
                        'COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAM_MODEL_ASSIGNED_OLD_TEAMNAME',
                        (string) $current->old_name,
                        $newName
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

    private function ensureSeasonTeam(ProjectteamModel $model, int $teamId, int $seasonId): int
    {
        if ($teamId <= 0 || $seasonId <= 0) {
            return 0;
        }

        $db = $model->getDatabase();
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
}
