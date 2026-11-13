<?php
/**
 * Native Joomla 5/6 prediction member editing model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use DateTimeImmutable;
use DateTimeZone;

final class PredictionmemberModel extends PredictionuserModel
{
    /**
     * Save editable prediction-member profile and competition selections.
     *
     * Verifies the posted member and prediction-game identifiers against the
     * active selection, checks member-specific edit rights and respects the
     * configured permissions for profile, favourite team, champion and final
     * four changes. Validates selected team and group identifiers before
     * updating the member record through Joomla's database API. Posted
     * member, game, group and team IDs must be complete decimal values,
     * avoiding PHP 8 warnings and implicit casts of structured input.
     *
     * @param array<string,mixed> $post Submitted member form fields.
     * @return bool True when Joomla successfully updates the member row.
     * @throws \UnexpectedValueException If the submitted member target differs.
     * @throws \RuntimeException If the current user cannot edit the member.
     */
    public function saveMember(array $post): bool
    {
        $member = $this->getEditableMember();
        $memberId = $this->getSelectedMemberNumericId();
        $postedMemberId = self::positiveSubmittedId($post['member_id'] ?? $post['uid'] ?? 0);
        $postedPredictionId = self::positiveSubmittedId($post['prediction_id'] ?? 0);

        if ($memberId <= 0 || $postedMemberId !== $memberId || $postedPredictionId !== $this->predictionGameId) {
            throw new \UnexpectedValueException('Invalid prediction member target.');
        }
        if (!$this->canEditMember($member)) {
            throw new \RuntimeException('Prediction member edit is not allowed.', 403);
        }

        $config = $this->getEditConfig();
        $isPredictionAdmin = $this->isAllowedAdmin();
        $object = new \stdClass();
        $object->id = $memberId;
        $object->show_profile = $this->boolValue($post, 'show_profile', (int) ($member->show_profile ?? 0));
        $object->aliasName = !empty($config['allow_alias'])
            ? $this->cleanText($post['aliasName'] ?? ($member->aliasName ?? ''), 255)
            : (string) ($member->aliasName ?? '');
        $object->slogan = !empty($config['edit_slogan'])
            ? $this->cleanText($post['slogan'] ?? ($member->slogan ?? ''), 255)
            : (string) ($member->slogan ?? '');
        $object->admintipp = $this->boolValue($post, 'admintipp', (int) ($member->admintipp ?? 0));

        $object->reminder = !empty($config['edit_reminder'])
            ? $this->boolValue($post, 'reminder', (int) ($member->reminder ?? 0))
            : (int) ($member->reminder ?? 0);
        $object->receipt = !empty($config['edit_receipt'])
            ? $this->boolValue($post, 'receipt', (int) ($member->receipt ?? 0))
            : (int) ($member->receipt ?? 0);
        $object->picture = !empty($config['edit_avatar_upload'])
            ? $this->cleanPath($post['picture'] ?? ($member->picture ?? ''))
            : (string) ($member->picture ?? '');

        $object->registerDate = $isPredictionAdmin
            ? $this->normaliseRegisterDate($post, (string) ($member->pmRegisterDate ?? $member->registerDate ?? ''))
            : (string) ($member->pmRegisterDate ?? $member->registerDate ?? '');

        $groupId = (int) ($member->group_id ?? 0);
        if ($this->canChangeGroup()) {
            $candidate = self::positiveSubmittedId($post['group_id'] ?? $groupId);
            if ($this->isValidGroupId($candidate)) {
                $groupId = $candidate;
            }
        }
        $object->group_id = $groupId;

        $projects = $this->getPredictionProjects();
        $favPost = is_array($post['fav_team'] ?? null) ? $post['fav_team'] : [];
        $champPost = is_array($post['champ_tipp'] ?? null) ? $post['champ_tipp'] : [];
        $final4Post = [];
        for ($index = 1; $index <= 4; $index++) {
            $key = 'final4_tipp' . $index;
            $final4Post[$index] = is_array($post[$key] ?? null) ? $post[$key] : [];
        }
        $existingFav = $this->selectionMap((string) ($member->fav_team ?? ''), false);
        $existingChamp = $this->selectionMap((string) ($member->champ_tipp ?? ''), false);
        $existingFinal4 = $this->selectionMap((string) ($member->final4_tipp ?? ''), true);

        if (!empty($config['edit_favteam'])) {
            $fav = [];
            foreach ($projects as $project) {
                $projectId = (int) ($project->project_id ?? 0);
                if ($projectId <= 0) {
                    continue;
                }
                $candidate = self::positiveSubmittedId($favPost[$projectId] ?? 0);
                if ($this->isValidProjectTeamId($projectId, $candidate)) {
                    $fav[$projectId] = $candidate;
                } else {
                    $fav[$projectId] = (int) ($existingFav[$projectId] ?? 0);
                }
            }
            $object->fav_team = $this->serialiseSingleSelections($fav);
        } else {
            $object->fav_team = (string) ($member->fav_team ?? '');
        }

        $champ = [];
        $final4 = [];
        foreach ($projects as $project) {
            $projectId = (int) ($project->project_id ?? 0);
            if ($projectId <= 0) {
                continue;
            }

            $competitiveOpen = $this->isProjectOpenForCompetitiveEdits($project);
            $oldChamp = (int) ($existingChamp[$projectId] ?? 0);
            $oldFinal4 = array_values(array_filter(array_map('intval', (array) ($existingFinal4[$projectId] ?? []))));

            if ($competitiveOpen && !empty($project->champ)) {
                $candidate = self::positiveSubmittedId($champPost[$projectId] ?? $oldChamp);
                $champ[$projectId] = $this->isValidProjectTeamId($projectId, $candidate) ? $candidate : $oldChamp;
            } else {
                $champ[$projectId] = $oldChamp;
            }

            if ($competitiveOpen && !empty($config['show_final4_tip'])) {
                $selected = [];
                for ($index = 1; $index <= 4; $index++) {
                    $candidate = self::positiveSubmittedId($final4Post[$index][$projectId] ?? 0);
                    if ($candidate > 0 && $this->isValidProjectTeamId($projectId, $candidate)) {
                        $selected[] = $candidate;
                    }
                }
                $final4[$projectId] = array_values(array_unique(array_slice($selected, 0, 4)));
            } else {
                $final4[$projectId] = array_slice($oldFinal4, 0, 4);
            }
        }

        $object->champ_tipp = $this->serialiseSingleSelections($champ);
        $object->final4_tipp = $this->serialiseMultipleSelections($final4);

        return (bool) $this->getDatabase()->updateObject(
            '#__sportsmanagement_prediction_member',
            $object,
            'id'
        );
    }

    /**
     * Read a positive Joomla identifier from a submitted prediction form.
     *
     * Accepts only integer values or complete nonnegative decimal strings,
     * rejects arrays, floats, signs and integer overflow, and returns zero
     * for an invalid or empty identifier before any database write occurs.
     *
     * @param mixed $value Untrusted submitted ID or Joomla default.
     * @return int Positive identifier, or zero when invalid.
     */
    private static function positiveSubmittedId(mixed $value): int
    {
        if (!is_int($value) && (!is_string($value) || !preg_match('/^[0-9]+$/D', $value))) {
            return 0;
        }

        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? 0 : $id;
    }

    /**
     * Normalise a posted boolean option to the integer values zero or one.
     *
     * Keeps the existing value when the field is absent or has a non-scalar
     * value, preventing malformed form data from changing stored flags.
     *
     * @param array<string,mixed> $post Submitted form fields.
     * @param string $key Name of the boolean field.
     * @param int $fallback Current stored value.
     * @return int One for enabled and zero for disabled.
     */
    private function boolValue(array $post, string $key, int $fallback): int
    {
        if (!array_key_exists($key, $post)) {
            return $fallback ? 1 : 0;
        }
        if (!is_scalar($post[$key])) {
            return $fallback ? 1 : 0;
        }
        return ((int) $post[$key]) === 1 ? 1 : 0;
    }

    /**
     * Prepare a limited-length plain-text profile field for persistence.
     *
     * Rejects structured values, removes HTML tags and surrounding whitespace
     * and truncates the result to the allowed storage length.
     *
     * @param mixed $value Submitted text value.
     * @param int $maxLength Maximum stored text length in bytes.
     * @return string Sanitised profile text.
     */
    private function cleanText(mixed $value, int $maxLength): string
    {
        if (!is_scalar($value) && $value !== null) {
            return '';
        }
        $value = trim(strip_tags((string) $value));
        return substr($value, 0, $maxLength);
    }

    /**
     * Clean a user-configurable profile image reference.
     *
     * Rejects non-scalar data, removes markup, null bytes and surrounding
     * whitespace, and caps the stored string to 255 bytes.
     *
     * @param mixed $value Submitted picture path or reference.
     * @return string Cleaned picture reference.
     */
    private function cleanPath(mixed $value): string
    {
        if (!is_scalar($value) && $value !== null) {
            return '';
        }
        $value = str_replace("\0", '', trim(strip_tags((string) $value)));
        return substr($value, 0, 255);
    }

    /**
     * Parse an administrator-supplied prediction registration date and time.
     *
     * Combines separate date and clock fields, accepts a missing clock as
     * midnight and verifies the exact calendar date and time without allowing
     * PHP's automatic normalisation of impossible values.
     *
     * @param array<string,mixed> $post Submitted registration date/time fields.
     * @param string $fallback Existing registration timestamp.
     * @return string Valid canonical timestamp or the original timestamp.
     */
    private function normaliseRegisterDate(array $post, string $fallback): string
    {
        $dateValue = $post['registerDate'] ?? '';
        $timeValue = $post['registerTime'] ?? '';
        if ((!is_scalar($dateValue) && $dateValue !== null) || (!is_scalar($timeValue) && $timeValue !== null)) {
            return $fallback;
        }
        $date = trim((string) $dateValue);
        $time = trim((string) $timeValue);
        if ($date === '') {
            return $fallback;
        }
        if ($time === '') {
            $time = '00:00:00';
        } elseif (preg_match('/^\d{1,2}:\d{2}$/', $time)) {
            $time .= ':00';
        }

        // Do not let PHP normalise impossible dates or clock values.
        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $date)
            || !preg_match('/^([0-9]{1,2}):([0-9]{2})(?::([0-9]{2}))?$/D', $time, $parts)
        ) {
            return $fallback;
        }

        $canonicalTime = sprintf('%02d:%s:%s', (int) $parts[1], $parts[2], $parts[3] ?? '00');
        $expected = $date . ' ' . $canonicalTime;
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $expected, new DateTimeZone('UTC'));

        return $parsed instanceof DateTimeImmutable && $parsed->format('Y-m-d H:i:s') === $expected
            ? $expected
            : $fallback;
    }

    /**
     * Encode one selected team per prediction project for legacy storage.
     *
     * Skips invalid or empty identifiers and joins project/team pairs using
     * the comma and semicolon format expected by existing database records.
     *
     * @param array<int,int> $selections Team identifier indexed by project ID.
     * @return string Semicolon-separated project/team selections.
     */
    private function serialiseSingleSelections(array $selections): string
    {
        $parts = [];
        foreach ($selections as $projectId => $teamId) {
            $projectId = (int) $projectId;
            $teamId = (int) $teamId;
            if ($projectId > 0 && $teamId > 0) {
                $parts[] = $projectId . ',' . $teamId;
            }
        }
        return implode(';', $parts);
    }

    /**
     * Encode up to four distinct selected teams per prediction project.
     *
     * Normalises IDs, discards nonpositive team identifiers and limits each
     * project's selection to four entries in the historical storage format.
     *
     * @param array<int,array<int,int>> $selections Team IDs indexed by project.
     * @return string Semicolon-separated project/team pairs.
     */
    private function serialiseMultipleSelections(array $selections): string
    {
        $parts = [];
        foreach ($selections as $projectId => $teamIds) {
            $projectId = (int) $projectId;
            if ($projectId <= 0) {
                continue;
            }
            foreach (array_slice(array_values(array_unique(array_map('intval', (array) $teamIds))), 0, 4) as $teamId) {
                if ($teamId > 0) {
                    $parts[] = $projectId . ',' . $teamId;
                }
            }
        }
        return implode(';', $parts);
    }
}
