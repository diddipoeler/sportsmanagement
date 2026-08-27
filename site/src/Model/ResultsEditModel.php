<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
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
        $app = Factory::getApplication();
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
        $useLegs = (int) ($post['use_legs'] ?? 0) === 1;
        $success = true;

        foreach ($matchIds as $matchId) {
            $currentDate = $this->getCurrentMatchDate($matchId);
            $record = (object) [
                'id' => $matchId,
                'team1_result' => null,
                'team2_result' => null,
                'team1_legs' => null,
                'team2_legs' => null,
            ];

            $matchDateKey = 'match_date' . $matchId;
            $matchTimeKey = 'match_time' . $matchId;
            $submittedDate = trim((string) ($post[$matchDateKey] ?? ''));

            if ($submittedDate !== '') {
                $submittedTime = trim((string) ($post[$matchTimeKey] ?? ''));
                if ($submittedTime === '') {
                    $submittedTime = '00';
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

            $matchNumber = trim((string) ($post['match_number' . $matchId] ?? ''));
            if ($matchNumber !== '') {
                $record->match_number = $matchNumber;
            }

            $record->result_type = (int) ($post['result_type' . $matchId] ?? 0);
            $record->match_result_type = (int) ($post['match_result_type' . $matchId] ?? 0);
            $record->crowd = (int) ($post['crowd' . $matchId] ?? 0);

            $roundId = (int) ($post['round_id' . $matchId] ?? 0);
            if ($roundId > 0) {
                $record->round_id = $roundId;
            }

            $record->division_id = (int) ($post['division_id' . $matchId] ?? 0);
            $record->projectteam1_id = (int) ($post['projectteam1_id' . $matchId] ?? 0);
            $record->projectteam2_id = (int) ($post['projectteam2_id' . $matchId] ?? 0);

            foreach ([
                'team1_single_matchpoint',
                'team2_single_matchpoint',
                'team1_single_sets',
                'team2_single_sets',
                'team1_single_games',
                'team2_single_games',
            ] as $field) {
                $record->{$field} = $post[$field . $matchId] ?? 0;
            }

            $record->content_id = (int) ($post['content_id' . $matchId] ?? 0);

            $homeSplits = $this->normaliseSplitValues($post['team1_result_split' . $matchId] ?? []);
            $awaySplits = $this->normaliseSplitValues($post['team2_result_split' . $matchId] ?? []);

            if ($useLegs) {
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
            } else {
                $this->assignNumericPair($record, $post, $matchId, 'team1_result', 'team2_result');
                $this->assignNumericPair($record, $post, $matchId, 'team1_result_ot', 'team2_result_ot');
                $this->assignNumericPair($record, $post, $matchId, 'team1_result_so', 'team2_result_so');
                $this->assignNumericPair($record, $post, $matchId, 'team1_legs', 'team2_legs');
            }

            $record->team1_result_split = implode(';', $homeSplits);
            $record->team2_result_split = implode(';', $awaySplits);

            try {
                $db->updateObject('#__sportsmanagement_match', $record, 'id', true);
                $app->enqueueMessage(
                    Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_SAVED', $matchId),
                    'notice'
                );
            } catch (Throwable $e) {
                $app->enqueueMessage(
                    Text::sprintf(
                        'COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED',
                        $e->getCode(),
                        $e->getMessage()
                    ),
                    'error'
                );
                $success = false;
            }
        }

        return $success;
    }

    private function getCurrentMatchDate(int $matchId): string
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('match_date'))
            ->from($db->quoteName('#__sportsmanagement_match'))
            ->where($db->quoteName('id') . ' = ' . $matchId);

        try {
            $db->setQuery($query, 0, 1);
            return (string) ($db->loadResult() ?? '');
        } catch (Throwable) {
            return '';
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
        string $awayField
    ): void {
        $home = $post[$homeField . $matchId] ?? null;
        $away = $post[$awayField . $matchId] ?? null;

        if (is_numeric($home) && is_numeric($away)) {
            $record->{$homeField} = $home;
            $record->{$awayField} = $away;
        }
    }
}
