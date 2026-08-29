<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailerFactoryInterface;

final class PredictiontipModel extends PredictionentryModel
{
    public function saveTips(array $post): bool
    {
        $member = $this->getEntryMember();
        $memberId = $this->getSelectedMemberNumericId();
        $memberUserId = (int) ($member->user_id ?? 0);
        $projectId = $this->getProjectId();
        $roundId = $this->getRoundId();
        $project = $this->getEntryProject();

        if (!$project || !$this->canActAsEntryMember($member)) {
            throw new \RuntimeException('Prediction entry is not allowed for this member.', 403);
        }

        $postedPredictionId = $this->scalarInt($post['prediction_id'] ?? 0);
        $postedMemberId = $this->scalarInt($post['member_id'] ?? 0);
        $postedProjectId = $this->scalarInt($post['pj'] ?? 0);
        $postedRoundId = $this->scalarInt($post['r'] ?? 0);

        if ($postedPredictionId !== $this->predictionGameId
            || $postedMemberId !== $memberId
            || $postedProjectId !== $projectId
            || $postedRoundId !== $roundId
            || $memberUserId <= 0) {
            throw new \UnexpectedValueException('Invalid prediction entry target.');
        }

        $matches = $this->getEntryMatches();
        $mode = (int) ($project->mode ?? 0);
        $homes = is_array($post['homes'] ?? null) ? $post['homes'] : [];
        $aways = is_array($post['aways'] ?? null) ? $post['aways'] : [];
        $tipps = is_array($post['tipps'] ?? null) ? $post['tipps'] : [];
        $jokers = is_array($post['jokers'] ?? null) ? $post['jokers'] : [];

        $changes = [];
        $proposedJokerCount = $this->getMemberProjectJokerCount();
        foreach ($matches as $match) {
            if (empty($match->editable)) {
                continue;
            }

            $matchId = (int) $match->id;
            $existingJoker = !empty($match->joker) ? 1 : 0;
            $change = [
                'match' => $match,
                'delete' => false,
                'tipp' => null,
                'home' => null,
                'away' => null,
                'joker' => 0,
            ];

            if ($mode === 0) {
                $hasHome = array_key_exists($matchId, $homes);
                $hasAway = array_key_exists($matchId, $aways);
                if (!$hasHome && !$hasAway) {
                    continue;
                }
                if (!$hasHome || !$hasAway) {
                    throw new \UnexpectedValueException('Incomplete score prediction.');
                }
                $home = $this->nullableScore($homes[$matchId]);
                $away = $this->nullableScore($aways[$matchId]);
                if ($home === null && $away === null) {
                    $change['delete'] = true;
                } elseif ($home === null || $away === null) {
                    throw new \UnexpectedValueException('Incomplete score prediction.');
                } else {
                    $change['home'] = $home;
                    $change['away'] = $away;
                    $change['tipp'] = $home > $away ? 1 : ($home < $away ? 2 : 0);
                    $change['joker'] = !empty($project->joker) && $this->checkboxValue($jokers[$matchId] ?? null) ? 1 : 0;
                }
            } else {
                if (!array_key_exists($matchId, $tipps)) {
                    continue;
                }
                $tipp = $this->nullableToto($tipps[$matchId]);
                if ($tipp === null) {
                    $change['delete'] = true;
                } else {
                    $change['tipp'] = $tipp;
                }
            }

            $proposedJokerCount -= $existingJoker;
            $proposedJokerCount += $change['delete'] ? 0 : (int) $change['joker'];
            $changes[$matchId] = $change;
        }

        $jokerLimit = max(0, (int) ($project->joker_limit ?? 0));
        if (!empty($project->joker) && $jokerLimit > 0 && $proposedJokerCount > $jokerLimit) {
            throw new \UnexpectedValueException(Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_CHECK_JOKERS_COUNT'));
        }

        $actorId = (int) $this->siteApplication()->getIdentity()->id;
        $modified = Factory::getDate()->toSql();
        $db = $this->getDatabase();
        $changed = false;

        foreach ($changes as $change) {
            $match = $change['match'];
            $existingId = (int) ($match->prediction_result_id ?? 0);

            if ($change['delete']) {
                if ($existingId > 0) {
                    $query = $db->getQuery(true)
                        ->delete($db->quoteName('#__sportsmanagement_prediction_result'))
                        ->where($db->quoteName('id') . ' = ' . $existingId)
                        ->where($db->quoteName('prediction_id') . ' = ' . $this->predictionGameId)
                        ->where($db->quoteName('user_id') . ' = ' . $memberUserId)
                        ->where($db->quoteName('project_id') . ' = ' . $projectId)
                        ->where($db->quoteName('match_id') . ' = ' . (int) $match->id);
                    $db->setQuery($query)->execute();
                    $changed = true;
                }
                continue;
            }

            $object = new \stdClass();
            $object->prediction_id = $this->predictionGameId;
            $object->user_id = $memberUserId;
            $object->project_id = $projectId;
            $object->match_id = (int) $match->id;
            $object->tipp = $change['tipp'];
            $object->tipp_home = $change['home'];
            $object->tipp_away = $change['away'];
            $object->joker = (int) $change['joker'];
            $object->points = null;
            $object->top = null;
            $object->diff = null;
            $object->tend = null;
            $object->modified_by = $actorId;
            $object->modified = $modified;

            if ($existingId > 0) {
                $object->id = $existingId;
                if (!$db->updateObject('#__sportsmanagement_prediction_result', $object, 'id', true)) {
                    return false;
                }
            } else {
                if (!$db->insertObject('#__sportsmanagement_prediction_result', $object)) {
                    return false;
                }
            }
            $changed = true;
        }

        if ($this->isRoundExtrasEditable($matches)) {
            $this->saveRoundExtras($post, $project, $memberUserId, $projectId, $roundId, $actorId, $modified);
            $changed = true;
        }

        if ($changed) {
            $memberUpdate = new \stdClass();
            $memberUpdate->id = $memberId;
            $memberUpdate->last_tipp = $modified;
            $memberUpdate->modified = $modified;
            $memberUpdate->modified_by = $actorId;
            if (!$db->updateObject('#__sportsmanagement_prediction_member', $memberUpdate, 'id', true)) {
                return false;
            }

            if (!empty($member->receipt)) {
                $this->sendTipReceipt($member, $project, $this->getEntryMatches());
            }
        }

        return true;
    }

    private function saveRoundExtras(
        array $post,
        object $project,
        int $memberUserId,
        int $projectId,
        int $roundId,
        int $actorId,
        string $modified
    ): void {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_prediction_result_round'))
            ->where($db->quoteName('prediction_id') . ' = ' . $this->predictionGameId)
            ->where($db->quoteName('user_id') . ' = ' . $memberUserId)
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->where($db->quoteName('round_id') . ' = ' . $roundId);
        $db->setQuery($query, 0, 1);
        $existingId = (int) $db->loadResult();

        $object = new \stdClass();
        $object->prediction_id = $this->predictionGameId;
        $object->user_id = $memberUserId;
        $object->project_id = $projectId;
        $object->round_id = $roundId;
        $object->goals = !empty($project->use_goals) ? $this->nonNegativeInt($post['goals'] ?? 0, 999) : null;
        $object->penalties = !empty($project->use_penalties) ? $this->nonNegativeInt($post['penalties'] ?? 0, 999) : null;
        $object->yellow_cards = !empty($project->use_cards) ? $this->nonNegativeInt($post['yellow_cards'] ?? 0, 999) : null;
        $object->yellow_red_cards = !empty($project->use_cards) ? $this->nonNegativeInt($post['yellow_red_cards'] ?? 0, 999) : null;
        $object->red_cards = !empty($project->use_cards) ? $this->nonNegativeInt($post['red_cards'] ?? 0, 999) : null;
        $object->modified_by = $actorId;
        $object->modified = $modified;

        if ($existingId > 0) {
            $object->id = $existingId;
            $db->updateObject('#__sportsmanagement_prediction_result_round', $object, 'id', true);
        } else {
            $db->insertObject('#__sportsmanagement_prediction_result_round', $object);
        }
    }

    private function sendTipReceipt(object $member, object $project, array $matches): bool
    {
        $userId = (int) ($member->user_id ?? 0);
        if ($userId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('email'), $db->quoteName('name')])
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('id') . ' = ' . $userId)
            ->where($db->quoteName('block') . ' = 0');
        $db->setQuery($query, 0, 1);
        $user = $db->loadObject();
        if (!$user || empty($user->email)) {
            return false;
        }

        try {
            $mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
            $config = Factory::getConfig();
            $mailFrom = (string) $config->get('mailfrom');
            $fromName = (string) $config->get('fromname');
            $mailer->setSender([$mailFrom, $fromName]);
            $recipients = [(string) $user->email];
            if (!empty($this->getEntryConfig()['send_admin_user_tipentry']) && $mailFrom !== '') {
                $recipients[] = $mailFrom;
            }
            $mailer->addRecipient(array_values(array_unique($recipients)));
            $mailer->isHtml(true);
            $mailer->setSubject(Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_MAIL_TITLE'));

            $body = '<p>' . htmlspecialchars((string) $user->name, ENT_QUOTES, 'UTF-8') . '</p>';
            $body .= '<h3>' . htmlspecialchars((string) ($project->projectName ?? ''), ENT_QUOTES, 'UTF-8') . '</h3>';
            $body .= '<table><thead><tr><th>' . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_MATCH') . '</th><th>'
                . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_YOURS') . '</th></tr></thead><tbody>';
            foreach ($matches as $match) {
                $tip = (int) ($project->mode ?? 0) === 0
                    ? (($match->tipp_home ?? '') . ':' . ($match->tipp_away ?? ''))
                    : (string) ($match->tipp ?? '');
                $body .= '<tr><td>'
                    . htmlspecialchars((string) $match->home_display_name, ENT_QUOTES, 'UTF-8') . ' - '
                    . htmlspecialchars((string) $match->away_display_name, ENT_QUOTES, 'UTF-8')
                    . '</td><td>' . htmlspecialchars($tip, ENT_QUOTES, 'UTF-8') . '</td></tr>';
            }
            $body .= '</tbody></table>';
            $mailer->setBody($body);
            return $mailer->send() === true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function scalarInt(mixed $value): int
    {
        return is_scalar($value) ? max(0, (int) $value) : 0;
    }

    private function nullableScore(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_scalar($value) || !preg_match('/^\d{1,2}$/', trim((string) $value))) {
            throw new \UnexpectedValueException('Invalid score prediction.');
        }
        return min(99, max(0, (int) $value));
    }

    private function nullableToto(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_scalar($value) || !in_array((string) $value, ['0', '1', '2'], true)) {
            throw new \UnexpectedValueException('Invalid toto prediction.');
        }
        return (int) $value;
    }

    private function checkboxValue(mixed $value): bool
    {
        return is_scalar($value) && in_array((string) $value, ['1', 'on', 'true'], true);
    }

    private function nonNegativeInt(mixed $value, int $max): int
    {
        if (!is_scalar($value) || !preg_match('/^\d+$/', trim((string) $value))) {
            return 0;
        }
        return min($max, max(0, (int) $value));
    }
}
