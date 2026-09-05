<?php
/**
 * Native Joomla 5/6 prediction points model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

final class PredictionpointsModel extends PredictionresultsModel
{
    public function recalculatePoints(array $config): int
    {
        $project = $this->getResultsProject();
        if (!$project) {
            return 0;
        }

        $roundId = $this->getSelectedRoundId($config);
        if ($roundId <= 0) {
            return 0;
        }

        $rows = $this->getPredictionMembersResultsList((int) $project->project_id, $roundId, $roundId);
        $db = $this->getDatabase();
        $updated = 0;

        foreach ($rows as $row) {
            if ((int) ($row->prID ?? 0) <= 0 || !$this->hasPlayedResult($row)) {
                continue;
            }

            $normalised = $this->normaliseResultForScoring($row, (int) ($config['use_match_result'] ?? 0));
            $newPoints = $this->calculateScore($project, $normalised);
            $classification = $this->classifyPrediction($project, $normalised);
            $newTipp = $classification['tipp'];

            $oldTipp = $row->prTipp === null ? null : (string) $row->prTipp;
            $newTippString = $newTipp === null ? null : (string) $newTipp;
            $changed = (int) ($row->prPoints ?? 0) !== $newPoints
                || (int) ($row->prTop ?? 0) !== $classification['top']
                || (int) ($row->prDiff ?? 0) !== $classification['diff']
                || (int) ($row->prTend ?? 0) !== $classification['tend']
                || $oldTipp !== $newTippString;

            if (!$changed) {
                continue;
            }

            $object = new \stdClass();
            $object->id = (int) $row->prID;
            $object->tipp_home = $row->prHomeTipp;
            $object->tipp_away = $row->prAwayTipp;
            $object->tipp = $newTipp;
            $object->joker = $row->prJoker;
            $object->points = $newPoints;
            $object->top = $classification['top'];
            $object->diff = $classification['diff'];
            $object->tend = $classification['tend'];

            if ($db->updateObject('#__sportsmanagement_prediction_result', $object, 'id')) {
                $updated++;
            }
        }

        return $updated;
    }
}
