<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use DateTimeImmutable;
use DateTimeZone;

final class PredictionmemberModel extends PredictionuserModel
{
    public function saveMember(array $post): bool
    {
        $member = $this->getEditableMember();
        $memberId = $this->getSelectedMemberNumericId();
        $postedMemberId = max(0, (int) ($post['member_id'] ?? $post['uid'] ?? 0));
        $postedPredictionId = max(0, (int) ($post['prediction_id'] ?? 0));

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
            $candidate = max(0, (int) ($post['group_id'] ?? $groupId));
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
                $candidate = max(0, (int) (($favPost[$projectId] ?? 0)));
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
                $candidate = max(0, (int) (($champPost[$projectId] ?? $oldChamp)));
                $champ[$projectId] = $this->isValidProjectTeamId($projectId, $candidate) ? $candidate : $oldChamp;
            } else {
                $champ[$projectId] = $oldChamp;
            }

            if ($competitiveOpen && !empty($config['show_final4_tip'])) {
                $selected = [];
                for ($index = 1; $index <= 4; $index++) {
                    $candidate = max(0, (int) (($final4Post[$index][$projectId] ?? 0)));
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

    private function cleanText(mixed $value, int $maxLength): string
    {
        if (!is_scalar($value) && $value !== null) {
            return '';
        }
        $value = trim(strip_tags((string) $value));
        return substr($value, 0, $maxLength);
    }

    private function cleanPath(mixed $value): string
    {
        if (!is_scalar($value) && $value !== null) {
            return '';
        }
        $value = str_replace("\0", '', trim(strip_tags((string) $value)));
        return substr($value, 0, 255);
    }

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

        try {
            $timezone = new DateTimeZone('UTC');
            $parsed = new DateTimeImmutable($date . ' ' . $time, $timezone);
            return $parsed->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return $fallback;
        }
    }

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
