<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictiongameTable;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 administrator form model for prediction games.
 */
final class PredictiongameModel extends SportsManagementAdminModel
{
    public static int $seasonid = 0;

    public function getForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/forms');

        return $this->loadForm(
            'com_sportsmanagement.predictiongame',
            'predictiongame',
            ['control' => 'jform', 'load_data' => $loadData]
        );
    }

    public function getTable($type = 'predictiongame', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'predictiongame') === 0) {
            return new PredictiongameTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    public function getScript(): string
    {
        return 'administrator/components/com_sportsmanagement/models/forms/sportsmanagement.js';
    }

    protected function afterSportsManagementSave(array $data, int $id, bool $isNew): void
    {
        $data['id'] = $id;

        if ($isNew) {
            Factory::getApplication()->enqueueMessage(
                Text::plural('COM_SPORTSMANAGEMENT_N_ITEMS_CREATED', $id),
                'message'
            );
        }

        $this->storePredictionAdmins($data);
        $this->storePredictionProjects($data);
    }

    public function storePredictionAdmins($data)
    {
        $ids = isset($data['user_ids']) && is_array($data['user_ids'])
            ? $data['user_ids']
            : [];

        $result = $this->syncRelation(
            '#__sportsmanagement_prediction_admin',
            'user_id',
            (int) ($data['id'] ?? 0),
            $ids
        );

        if ($result) {
            Factory::getApplication()->enqueueMessage('Admins zum Tippspiel gespeichert', 'notice');
        }

        return $result;
    }

    public function storePredictionProjects($data)
    {
        $ids = isset($data['project_ids']) && is_array($data['project_ids'])
            ? $data['project_ids']
            : [];

        $result = $this->syncRelation(
            '#__sportsmanagement_prediction_project',
            'project_id',
            (int) ($data['id'] ?? 0),
            $ids
        );

        if ($result) {
            Factory::getApplication()->enqueueMessage('Projekte zum Tippspiel gespeichert', 'notice');
        }

        return $result;
    }

    public function import(): void
    {
    }

    public function getPredictionGame($id = 0)
    {
        $predictionId = (int) $id;

        if ($predictionId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_prediction_game'))
            ->where($db->quoteName('id') . ' = ' . $predictionId);

        try {
            $db->setQuery($query);

            return $db->loadObject() ?: false;
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }

    public function getPredictionProjectIDs($prediction_id = 0)
    {
        $predictionId = (int) $prediction_id;
        self::$seasonid = 0;

        if ($predictionId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('project_id'))
            ->from($db->quoteName('#__sportsmanagement_prediction_project'))
            ->where($db->quoteName('prediction_id') . ' = ' . $predictionId)
            ->order($db->quoteName('id') . ' ASC');

        try {
            $db->setQuery($query);
            $projectIds = array_values(array_filter(array_map('intval', $db->loadColumn() ?: [])));

            if ($projectIds) {
                $query = $db->getQuery(true)
                    ->select($db->quoteName('season_id'))
                    ->from($db->quoteName('#__sportsmanagement_project'))
                    ->where($db->quoteName('id') . ' = ' . (int) end($projectIds));
                $db->setQuery($query);
                self::$seasonid = (int) $db->loadResult();
            }

            return $projectIds;
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return [];
        }
    }

    public function deletePredictionAdmins($cid = [])
    {
        return $this->deleteRelations('#__sportsmanagement_prediction_admin', (array) $cid);
    }

    public function deletePredictionProjects($cid = [])
    {
        return $this->deleteRelations('#__sportsmanagement_prediction_project', (array) $cid);
    }

    public function deletePredictionMembers($cid = [])
    {
        return $this->deleteRelations('#__sportsmanagement_prediction_member', (array) $cid);
    }

    public function deletePredictionResults($cid = [])
    {
        return $this->deleteRelations('#__sportsmanagement_prediction_result', (array) $cid);
    }

    /**
     * Rebuild points for all prediction project results belonging to the selected games.
     */
    public function rebuildPredictionProjectSPoints($cid)
    {
        $predictionIds = $this->normaliseIds((array) $cid);

        if (!$predictionIds) {
            return true;
        }

        $db = $this->getDatabase();

        try {
            foreach ($predictionIds as $predictionId) {
                $query = $db->getQuery(true)
                    ->select($db->quoteName('pp') . '.*')
                    ->from($db->quoteName('#__sportsmanagement_prediction_project', 'pp'))
                    ->where($db->quoteName('pp.prediction_id') . ' = ' . $predictionId);
                $db->setQuery($query);
                $predictionProjects = $db->loadObjectList() ?: [];

                foreach ($predictionProjects as $predictionProject) {
                    $query = $db->getQuery(true)
                        ->select([
                            $db->quoteName('pr') . '.*',
                            $db->quoteName('m.team1_result'),
                            $db->quoteName('m.team2_result'),
                            $db->quoteName('m.team1_result_decision'),
                            $db->quoteName('m.team2_result_decision'),
                        ])
                        ->from($db->quoteName('#__sportsmanagement_prediction_result', 'pr'))
                        ->join(
                            'LEFT',
                            $db->quoteName('#__sportsmanagement_match', 'm')
                            . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('pr.match_id')
                        )
                        ->where($db->quoteName('pr.prediction_id') . ' = ' . $predictionId)
                        ->where(
                            $db->quoteName('pr.project_id')
                            . ' = ' . (int) $predictionProject->project_id
                        );
                    $db->setQuery($query);
                    $results = $db->loadObjectList() ?: [];

                    foreach ($results as $predictionResult) {
                        [$tip, $points, $top, $diff, $tend] = $this->calculatePredictionPoints(
                            $predictionProject,
                            $predictionResult
                        );

                        $query = $db->getQuery(true)
                            ->update($db->quoteName('#__sportsmanagement_prediction_result'))
                            ->set([
                                $db->quoteName('tipp') . ' = ' . $this->nullableSqlValue($tip),
                                $db->quoteName('points') . ' = ' . $this->nullableSqlValue($points),
                                $db->quoteName('top') . ' = ' . $this->nullableSqlValue($top),
                                $db->quoteName('diff') . ' = ' . $this->nullableSqlValue($diff),
                                $db->quoteName('tend') . ' = ' . $this->nullableSqlValue($tend),
                            ])
                            ->where($db->quoteName('id') . ' = ' . (int) $predictionResult->id);
                        $db->setQuery($query)->execute();
                    }
                }
            }
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        return true;
    }

    private function syncRelation(string $table, string $relationColumn, int $predictionId, array $ids): bool
    {
        if ($predictionId <= 0) {
            return false;
        }

        $ids = $this->normaliseIds($ids);
        $db = $this->getDatabase();

        try {
            $query = $db->getQuery(true)
                ->delete($db->quoteName($table))
                ->where($db->quoteName('prediction_id') . ' = ' . $predictionId);

            if ($ids) {
                $query->where($db->quoteName($relationColumn) . ' NOT IN (' . implode(',', $ids) . ')');
            }

            $db->setQuery($query)->execute();

            if (!$ids) {
                return true;
            }

            $query = $db->getQuery(true)
                ->select($db->quoteName($relationColumn))
                ->from($db->quoteName($table))
                ->where($db->quoteName('prediction_id') . ' = ' . $predictionId);
            $db->setQuery($query);
            $existing = array_map('intval', $db->loadColumn() ?: []);

            foreach (array_diff($ids, $existing) as $relationId) {
                $record = (object) [
                    'prediction_id' => $predictionId,
                    $relationColumn => (int) $relationId,
                ];
                $db->insertObject($table, $record);
            }
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        return true;
    }

    private function deleteRelations(string $table, array $predictionIds): bool
    {
        $predictionIds = $this->normaliseIds($predictionIds);

        if (!$predictionIds) {
            return true;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->delete($db->quoteName($table))
            ->where($db->quoteName('prediction_id') . ' IN (' . implode(',', $predictionIds) . ')');

        try {
            $db->setQuery($query)->execute();

            return true;
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }

    private function normaliseIds(array $ids): array
    {
        return array_values(
            array_unique(
                array_filter(
                    array_map('intval', $ids),
                    static fn (int $id): bool => $id > 0
                )
            )
        );
    }

    private function calculatePredictionPoints(object $predictionProject, object $predictionResult): array
    {
        $resultHome = $predictionResult->team1_result;
        $resultAway = $predictionResult->team2_result;
        $tipHome = $predictionResult->tipp_home;
        $tipAway = $predictionResult->tipp_away;
        $joker = $predictionResult->joker;

        if ($tipHome > $tipAway) {
            $tip = '1';
        } elseif ($tipHome < $tipAway) {
            $tip = '2';
        } elseif ($tipHome !== null && $tipAway !== null) {
            $tip = '0';
        } else {
            $tip = null;
        }

        $points = null;
        $top = null;
        $diff = null;
        $tend = null;

        if ($tipHome === null || $tipAway === null) {
            return [$tip, $points, $top, $diff, $tend];
        }

        if ((int) $predictionProject->mode === 1) {
            return [$tip, $tip, $top, $diff, $tend];
        }

        $suffix = $joker ? '_joker' : '';

        if ($resultHome == $tipHome && $resultAway == $tipAway) {
            $points = $predictionProject->{'points_correct_result' . $suffix};
            $top = 1;
        } elseif (
            $resultHome == $resultAway
            && ($resultHome - $resultAway) == ($tipHome - $tipAway)
        ) {
            $points = $predictionProject->{'points_correct_draw' . $suffix};
            $diff = 1;
        } elseif (($resultHome - $resultAway) == ($tipHome - $tipAway)) {
            $points = $predictionProject->{'points_correct_diff' . $suffix};
            $diff = 1;
        } elseif (
            (($resultHome - $resultAway) > 0 && ($tipHome - $tipAway) > 0)
            || (($resultHome - $resultAway) < 0 && ($tipHome - $tipAway) < 0)
        ) {
            $points = $predictionProject->{'points_correct_tendence' . $suffix};
            $tend = 1;
        } else {
            $points = $predictionProject->{'points_tipp' . $suffix};
        }

        return [$tip, $points, $top, $diff, $tend];
    }

    private function nullableSqlValue($value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        return $this->getDatabase()->quote((string) $value);
    }
}
