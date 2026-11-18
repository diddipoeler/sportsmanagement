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
    /**
     * Recalculate stored prediction scores for the selected project round.
     *
     * Requires prediction administrator permission even when called outside
     * the controller, retrieves completed match predictions and applies
     * the configured result and scoring rules. Updates only rows whose
     * points or classification changed, reducing unnecessary database writes.
     * Handles malformed legacy scoring rows and structured old score fields
     * without PHP 8 conversion warnings or unrelated record updates.
     *
     * @param array $config Scoring options, including use_match_result.
     * @return int Number of prediction-result rows successfully updated.
     * @throws \RuntimeException If prediction administration is not allowed.
     */
    public function recalculatePoints(array $config): int
    {
        // Enforce permissions in the model as well as the controller: other
        // callers must not be able to recalculate scores without admin rights.
        if (!$this->isAllowedAdmin()) {
            throw new \RuntimeException('Not authorised to recalculate prediction points.', 403);
        }

        $project = $this->getResultsProject();
        if (!$project) {
            return 0;
        }

        $roundId = $this->getSelectedRoundId($config);
        if ($roundId <= 0) {
            return 0;
        }

        $projectId = self::predictionRowId($project->project_id ?? null);

        if ($projectId === null) {
            return 0;
        }

        $rows = $this->getPredictionMembersResultsList($projectId, $roundId, $roundId);

        if (!is_iterable($rows)) {
            return 0;
        }

        $db = $this->getDatabase();
        $updated = 0;
        $rawUseMatchResult = $config['use_match_result'] ?? 0;
        $useMatchResult = $rawUseMatchResult === 1 || $rawUseMatchResult === '1' ? 1 : 0;

        foreach ($rows as $row) {
            if (!is_object($row)) {
                continue;
            }

            $predictionId = self::predictionRowId($row->prID ?? null);

            if ($predictionId === null
                || !self::safePredictionFields($row)
                || !$this->hasPlayedResult($row)) {
                continue;
            }

            $normalised = $this->normaliseResultForScoring($row, $useMatchResult);
            $newPoints = $this->calculateScore($project, $normalised);
            $classification = $this->classifyPrediction($project, $normalised);
            $newTipp = $classification['tipp'];

            $oldTipp = $row->prTipp ?? null;
            $oldTipp = $oldTipp === null ? null : (string) $oldTipp;
            $newTippString = $newTipp === null ? null : (string) $newTipp;
            $changed = self::predictionScore($row->prPoints ?? 0) !== $newPoints
                || self::predictionScore($row->prTop ?? 0) !== $classification['top']
                || self::predictionScore($row->prDiff ?? 0) !== $classification['diff']
                || self::predictionScore($row->prTend ?? 0) !== $classification['tend']
                || $oldTipp !== $newTippString;

            if (!$changed) {
                continue;
            }

            $object = new \stdClass();
            $object->id = $predictionId;
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

    /**
     * Validate a stored prediction or project primary key without loose casts.
     *
     * Accepts positive integers and zero-padded decimal strings. Prevents
     * malformed, nested or overflowing records from updating a different row.
     *
     * @param mixed $value Raw database primary key.
     * @return int|null Positive ID or null for invalid input.
     */
    private static function predictionRowId(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        if (!is_string($value) || strlen($value) > 64
            || preg_match('/^[0-9]+$/D', $value) !== 1) {
            return null;
        }

        $digits = ltrim($value, '0');
        $max = (string) PHP_INT_MAX;

        if ($digits === '' || strlen($digits) > strlen($max)
            || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
            return null;
        }

        return (int) $digits;
    }

    /**
     * Check that scoring inputs can be read without PHP 8 type warnings.
     *
     * Old Joomla results can contain arrays from corrupt serialised fields
     * or invalid values from imported records. Skip those rows before
     * scoring or writing, while accepting null and ordinary scalar scores.
     *
     * @param object $row One stored prediction result.
     * @return bool Whether all scoring inputs are safe scalars or null.
     */
    private static function safePredictionFields(object $row): bool
    {
        foreach ([
            'prHomeTipp', 'prAwayTipp', 'prJoker', 'prTipp',
            'prPoints', 'prTop', 'prDiff', 'prTend',
        ] as $field) {
            $value = $row->{$field} ?? null;

            if ($value === null) {
                continue;
            }

            if ((!is_string($value) && !is_int($value) && !is_float($value))
                || (is_float($value) && !is_finite($value))
                || (is_string($value) && (strlen($value) > 128
                    || preg_match('/[\x00-\x1F\x7F]/', $value) === 1))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Parse the stored integer scoring categories without PHP type coercion.
     *
     * Treats malformed or overflowing legacy category values as unmatched
     * rather than converting arrays to 1 or partial strings to an ID.
     *
     * @param mixed $value Stored points or classification count.
     * @return int|null Numeric score, or null when malformed.
     */
    private static function predictionScore(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (!is_string($value) || strlen($value) > 64
            || preg_match('/^-?[0-9]+$/D', $value) !== 1) {
            return null;
        }

        $digits = ltrim(ltrim($value, '-'), '0');
        $digits = $digits === '' ? '0' : $digits;
        $negative = str_starts_with($value, '-') && $digits !== '0';
        $max = $negative ? substr((string) PHP_INT_MIN, 1) : (string) PHP_INT_MAX;

        if (strlen($digits) > strlen($max)
            || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
            return null;
        }

        return (int) (($negative ? '-' : '') . $digits);
    }
}
