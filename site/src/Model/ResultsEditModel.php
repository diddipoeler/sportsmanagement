<?php
/**
 * Native Joomla 5/6 write model for the compact results edit form.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;
use Throwable;

/**
 * Joomla 5/6 write model for the compact results edit form.
 */
final class ResultsEditModel extends SportsManagementModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
    }

    public function saveShort(array $post = [], array $matchIds = []): bool
    {
        $app = $this->siteApplication();
        $input = $app->getInput();

        if (!$post) {
            $post = $input->post->getArray();
        }

        if (!$matchIds) {
            $matchIds = (array) $input->post->get('cid', [], 'array');
        }

        $matchIds = array_values(array_unique(array_filter(
            array_map('intval', $matchIds),
            static fn (int $id): bool => $id > 0
        )));

        if (!$matchIds) {
            return true;
        }

        if (!class_exists(SportsManagementDateHelper::class)) {
            require_once JPATH_ADMINISTRATOR
                . '/components/com_sportsmanagement/src/Helper/SportsManagementDateHelper.php';
        }

        $db = $this->getDatabase();
        $success = true;

        foreach ($matchIds as $matchId) {
            // Never trust a hidden POST flag to select the scoring algorithm:
            // each match inherits this rule from its persisted project.
            $useLegs = $this->matchUsesLegs($matchId);
            $currentDate = $this->getCurrentMatchDate($matchId);
            $record = (object) ['id' => $matchId];

            $matchDateKey = 'match_date' . $matchId;
            $matchTimeKey = 'match_time' . $matchId;
            $submittedDate = trim((string) ($post[$matchDateKey] ?? ''));
            $submittedTime = trim((string) ($post[$matchTimeKey] ?? ''));

            if ($submittedDate !== '' || $submittedTime !== '') {
                if ($submittedDate === '' && $currentDate !== '') {
                    $submittedDate = substr($currentDate, 0, 10);
                }
                if ($submittedTime === '') {
                    $submittedTime = strlen($currentDate) >= 16
                        ? substr($currentDate, 11, 5)
                        : '00:00';
                }

                $sqlDate = SportsManagementDateHelper::toSqlDate($submittedDate);
                if ($sqlDate !== null && $sqlDate !== '') {
                    $record->match_date = $sqlDate . ' ' . $this->normaliseTime($submittedTime) . ':00';

                    if ($currentDate !== '' && $record->match_date !== $currentDate) {
                        $app->enqueueMessage(
                            Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_ADMIN_CHANGE'),
                            'notice'
                        );
                    }
                }
            }

            $this->assignIfPresent($record, $post, 'match_number', $matchId);
            $this->assignIntIfPresent($record, $post, 'result_type', $matchId);
            $this->assignIntIfPresent($record, $post, 'match_result_type', $matchId);
            $this->assignIntIfPresent($record, $post, 'crowd', $matchId);

            // This is a results editor, not the match assignment editor. Never
            // accept injected round, division, team or content ownership fields.
            foreach ([
                'team1_single_matchpoint',
                'team2_single_matchpoint',
                'team1_single_sets',
                'team2_single_sets',
                'team1_single_games',
                'team2_single_games',
            ] as $field) {
                $this->assignIfPresent($record, $post, $field, $matchId);
            }

            $homeSplitsKey = 'team1_result_split' . $matchId;
            $awaySplitsKey = 'team2_result_split' . $matchId;
            $splitsSubmitted = array_key_exists($homeSplitsKey, $post) || array_key_exists($awaySplitsKey, $post);
            $homeSplits = $this->normaliseSplitValues($post[$homeSplitsKey] ?? []);
            $awaySplits = $this->normaliseSplitValues($post[$awaySplitsKey] ?? []);

            if ($useLegs && $splitsSubmitted) {
                // Only recalculate when the client actually submitted leg
                // scores. An unrelated update must not zero an existing result.
                $record->team1_result = 0;
                $record->team2_result = 0;

                $partCount = max(count($homeSplits), count($awaySplits));
                for ($index = 0; $index < $partCount; $index++) {
                    $home = $homeSplits[$index] ?? '';
                    $away = $awaySplits[$index] ?? '';

                    if (!is_numeric($home) || !is_numeric($away)) {
                        continue;
                    }

                    if ((float) $home > (float) $away) {
                        $record->team1_result++;
                    } elseif ((float) $home < (float) $away) {
                        $record->team2_result++;
                    } else {
                        $record->team1_result++;
                        $record->team2_result++;
                    }
                }
            } elseif (!$useLegs) {
                $this->assignNumericPair($record, $post, $matchId, 'team1_result', 'team2_result', true);
                $this->assignNumericPair($record, $post, $matchId, 'team1_result_ot', 'team2_result_ot');
                $this->assignNumericPair($record, $post, $matchId, 'team1_result_so', 'team2_result_so');
                $this->assignNumericPair($record, $post, $matchId, 'team1_legs', 'team2_legs');
            }

            if ($splitsSubmitted) {
                $record->team1_result_split = implode(';', $homeSplits);
                $record->team2_result_split = implode(';', $awaySplits);
            }

            try {
                $db->updateObject('#__sportsmanagement_match', $record, 'id', true);
                $app->enqueueMessage(
                    Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_SAVED', $matchId),
                    'notice'
                );
            } catch (Throwable $e) {
                // Keep driver details in the Joomla log rather than in public UI.
                Log::add(__METHOD__ . ' (match ' . $matchId . '): ' . $e->getMessage(), Log::ERROR, 'jsmerror');
                $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
                $success = false;
            }
        }

        return $success;
    }

    private function getCurrentMatchDate(int $matchId): string
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('match_date'))
            ->from($db->quoteName('#__sportsmanagement_match'))
            ->where($db->quoteName('id') . ' = :matchId')
            ->bind(':matchId', $matchId, ParameterType::INTEGER);

        try {
            $db->setQuery($query, 0, 1);
            return (string) ($db->loadResult() ?? '');
        } catch (Throwable) {
            return '';
        }
    }

    private function matchUsesLegs(int $matchId): bool
    {
        if ($matchId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('p.use_legs'))
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id')
            )
            ->where($db->quoteName('m.id') . ' = :matchId')
            ->bind(':matchId', $matchId, ParameterType::INTEGER);

        try {
            $db->setQuery($query, 0, 1);
            return (int) $db->loadResult() === 1;
        } catch (Throwable) {
            return false;
        }
    }

    private function normaliseTime(string $time): string
    {
        $time = trim($time);

        if (preg_match('/^(\d{1,2}):(\d{2})$/', $time, $matches)) {
            $hour = min(23, max(0, (int) $matches[1]));
            $minute = min(59, max(0, (int) $matches[2]));
            return sprintf('%02d:%02d', $hour, $minute);
        }

        if (preg_match('/^\d{1,2}$/', $time)) {
            return sprintf('%02d:00', min(23, max(0, (int) $time)));
        }

        return '00:00';
    }

    private function normaliseSplitValues($values): array
    {
        if (!is_array($values)) {
            return [];
        }

        return array_map(
            static fn ($value): string => trim((string) $value),
            array_values($values)
        );
    }

    private function assignNumericPair(
        object $record,
        array $post,
        int $matchId,
        string $homeField,
        string $awayField,
        bool $allowEmptyClear = false
    ): void {
        $homeKey = $homeField . $matchId;
        $awayKey = $awayField . $matchId;
        if (!array_key_exists($homeKey, $post) && !array_key_exists($awayKey, $post)) {
            return;
        }

        $home = $post[$homeKey] ?? null;
        $away = $post[$awayKey] ?? null;

        if (is_numeric($home) && is_numeric($away)) {
            $record->{$homeField} = $home;
            $record->{$awayField} = $away;
            return;
        }

        if ($allowEmptyClear && trim((string) $home) === '' && trim((string) $away) === '') {
            $record->{$homeField} = null;
            $record->{$awayField} = null;
        }
    }

    private function assignIfPresent(object $record, array $post, string $field, int $matchId): void
    {
        $key = $field . $matchId;
        if (array_key_exists($key, $post)) {
            $record->{$field} = $post[$key];
        }
    }

    private function assignIntIfPresent(object $record, array $post, string $field, int $matchId): void
    {
        $key = $field . $matchId;
        if (array_key_exists($key, $post)) {
            $record->{$field} = (int) $post[$key];
        }
    }

}
