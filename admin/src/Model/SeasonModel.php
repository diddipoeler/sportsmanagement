<?php
/**
 * Joomla 5/6 administrator model for seasons.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

final class SeasonModel extends SportsManagementAdminModel
{
    public function saveshortpersons()
    {
        $app = $this->administratorApplication();
        $post = $app->getInput()->post->getArray();
        $personIds = array_values(array_unique(array_filter(array_map('intval', (array) ($post['cid'] ?? [])))));
        $seasonId = (int) ($post['season_id'] ?? 0);
        $projectId = (int) ($post['project_id'] ?? 0);
        $personType = (int) ($post['persontype'] ?? 0);
        $whichView = (string) ($post['whichview'] ?? '');
        $modified = Factory::getDate()->toSql();
        $modifiedBy = (int) $app->getIdentity()->id;

        if (!$personIds || $seasonId <= 0) {
            return '';
        }

        $db = $this->getDatabase();

        foreach ($personIds as $personId) {
            $transactionStarted = false;

            try {
                $db->transactionStart();
                $transactionStarted = true;
                $seasonPersonId = $this->ensureSeasonPerson($personId, $seasonId, $modified, $modifiedBy);

                if ($whichView === 'teamplayers' && $projectId > 0) {
                    $positionId = (int) ($post['position' . $personId] ?? 0);
                    $this->ensureProjectPersonPosition(
                        $personId,
                        $projectId,
                        $positionId,
                        $personType,
                        $modified,
                        $modifiedBy
                    );
                }

                if ($personType === 3 && $seasonPersonId > 0) {
                    $object = (object) [
                        'id' => $seasonPersonId,
                        'modified' => $modified,
                        'modified_by' => $modifiedBy,
                        'persontype' => 3,
                        'published' => 1,
                    ];
                    $db->updateObject('#__sportsmanagement_season_person_id', $object, 'id', true);

                    if ($projectId > 0) {
                        $this->ensureProjectReferee($seasonPersonId, $projectId, $modified, $modifiedBy);
                    }
                } elseif ($personType !== 3) {
                    $teamId = $this->resolveTeamId($post['team_id'] ?? 0, $personId);
                    $this->ensureSeasonTeamPerson(
                        $personId,
                        $seasonId,
                        $teamId,
                        $personType > 0 ? $personType : 1,
                        $modified,
                        $modifiedBy
                    );
                }

                $db->transactionCommit();
            } catch (\Throwable $e) {
                if ($transactionStarted) {
                    try {
                        $db->transactionRollback();
                    } catch (\Throwable) {
                        // Preserve the original person update error.
                    }
                }

                $app->enqueueMessage(
                    Text::sprintf(
                        'COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED',
                        $e->getCode(),
                        $e->getMessage()
                    ),
                    'error'
                );
            }
        }

        return '';
    }

    public function saveshortteams()
    {
        $app = $this->administratorApplication();
        $post = $app->getInput()->post->getArray();
        $teamIds = array_values(array_unique(array_filter(array_map('intval', (array) ($post['cid'] ?? [])))));
        $seasonId = (int) ($post['season_id'] ?? 0);

        if (!$teamIds || $seasonId <= 0) {
            return true;
        }

        $db = $this->getDatabase();
        $modified = Factory::getDate()->toSql();
        $modifiedBy = (int) $app->getIdentity()->id;
        $result = true;

        foreach ($teamIds as $teamId) {
            try {
                $query = $db->getQuery(true)
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__sportsmanagement_season_team_id'))
                    ->where($db->quoteName('team_id') . ' = ' . $teamId)
                    ->where($db->quoteName('season_id') . ' = ' . $seasonId);
                $db->setQuery($query);

                if ((int) $db->loadResult() > 0) {
                    continue;
                }

                $query = $db->getQuery(true)
                    ->insert($db->quoteName('#__sportsmanagement_season_team_id'))
                    ->columns([
                        $db->quoteName('team_id'),
                        $db->quoteName('season_id'),
                        $db->quoteName('modified'),
                        $db->quoteName('modified_by'),
                    ])
                    ->values(implode(', ', [
                        $teamId,
                        $seasonId,
                        $db->quote($modified),
                        $modifiedBy,
                    ]));
                $db->setQuery($query)->execute();
            } catch (\Throwable $e) {
                $result = false;
                $app->enqueueMessage(
                    Text::sprintf(
                        'COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED',
                        $e->getCode(),
                        $e->getMessage()
                    ),
                    'error'
                );
            }
        }

        return $result;
    }

    private function ensureSeasonPerson(
        int $personId,
        int $seasonId,
        string $modified,
        int $modifiedBy
    ): int {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_season_person_id'))
            ->where($db->quoteName('person_id') . ' = ' . $personId)
            ->where($db->quoteName('season_id') . ' = ' . $seasonId);
        $db->setQuery($query);
        $id = (int) $db->loadResult();

        if ($id > 0) {
            return $id;
        }

        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__sportsmanagement_season_person_id'))
            ->columns([
                $db->quoteName('person_id'),
                $db->quoteName('season_id'),
                $db->quoteName('modified'),
                $db->quoteName('modified_by'),
            ])
            ->values(implode(', ', [
                $personId,
                $seasonId,
                $db->quote($modified),
                $modifiedBy,
            ]));
        $db->setQuery($query)->execute();

        $db->setQuery(
            $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__sportsmanagement_season_person_id'))
                ->where($db->quoteName('person_id') . ' = ' . $personId)
                ->where($db->quoteName('season_id') . ' = ' . $seasonId)
        );

        return (int) $db->loadResult();
    }

    private function ensureProjectPersonPosition(
        int $personId,
        int $projectId,
        int $positionId,
        int $personType,
        string $modified,
        int $modifiedBy
    ): void {
        $db = $this->getDatabase();
        $projectPositionId = 0;

        if ($positionId > 0) {
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__sportsmanagement_project_position'))
                ->where($db->quoteName('project_id') . ' = ' . $projectId)
                ->where($db->quoteName('position_id') . ' = ' . $positionId);
            $db->setQuery($query);
            $projectPositionId = (int) $db->loadResult();
        }

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_person_project_position'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->where($db->quoteName('person_id') . ' = ' . $personId);

        if ($projectPositionId > 0) {
            $query->where($db->quoteName('project_position_id') . ' = ' . $projectPositionId);
        }

        $db->setQuery($query);
        $id = (int) $db->loadResult();

        $object = (object) [
            'project_id' => $projectId,
            'person_id' => $personId,
            'published' => 1,
            'project_position_id' => $projectPositionId,
            'persontype' => $personType,
            'modified' => $modified,
            'modified_by' => $modifiedBy,
        ];

        if ($id > 0) {
            $object->id = $id;
            $db->updateObject('#__sportsmanagement_person_project_position', $object, 'id', true);
            return;
        }

        $db->insertObject('#__sportsmanagement_person_project_position', $object);
    }

    private function ensureProjectReferee(
        int $seasonPersonId,
        int $projectId,
        string $modified,
        int $modifiedBy
    ): void {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_project_referee'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->where($db->quoteName('person_id') . ' = ' . $seasonPersonId);
        $db->setQuery($query);

        if ((int) $db->loadResult() > 0) {
            return;
        }

        $object = (object) [
            'project_id' => $projectId,
            'person_id' => $seasonPersonId,
            'published' => 1,
            'modified' => $modified,
            'modified_by' => $modifiedBy,
        ];
        $db->insertObject('#__sportsmanagement_project_referee', $object);
    }

    private function ensureSeasonTeamPerson(
        int $personId,
        int $seasonId,
        int $teamId,
        int $personType,
        string $modified,
        int $modifiedBy
    ): void {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id'))
            ->where($db->quoteName('person_id') . ' = ' . $personId)
            ->where($db->quoteName('season_id') . ' = ' . $seasonId)
            ->where($db->quoteName('team_id') . ' = ' . $teamId)
            ->where($db->quoteName('persontype') . ' = ' . $personType);
        $db->setQuery($query);
        $id = (int) $db->loadResult();

        $object = (object) [
            'person_id' => $personId,
            'season_id' => $seasonId,
            'team_id' => $teamId,
            'published' => 1,
            'persontype' => $personType,
            'modified' => $modified,
            'modified_by' => $modifiedBy,
        ];

        if ($id > 0) {
            $object->id = $id;
            $db->updateObject('#__sportsmanagement_season_team_person_id', $object, 'id', true);
            return;
        }

        $db->insertObject('#__sportsmanagement_season_team_person_id', $object);
    }

    private function resolveTeamId($teamInput, int $personId): int
    {
        if (!is_array($teamInput)) {
            return (int) $teamInput;
        }

        if (isset($teamInput[$personId]) && is_scalar($teamInput[$personId])) {
            return (int) $teamInput[$personId];
        }

        foreach ($teamInput as $value) {
            if (is_scalar($value)) {
                return (int) $value;
            }
        }

        return 0;
    }
}
