<?php
/**
 * Regression coverage for site match outcome, admin action-log and Top Tipper guards.
 *
 * Run from repository root with php .github/tests/joomla56_match_outcome_actionlog_toptipper.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\CMS\Language {
    /** Supply stable translated labels for standalone match and action-log tests. */
    final class Text
    {
        /**
         * Return the translation key so generated labels remain inspectable.
         *
         * @param string $key The Joomla translation identifier.
         * @return string Deterministic replacement text for standalone tests.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\Component\Actionlogs\Administrator\Model {
    /** Capture native administrator action-log writes without Joomla runtime. */
    class ActionlogModel
    {
        public static array $logs = [];

        /**
         * Record a requested Joomla action-log write for later assertions.
         *
         * @param array $messages Log rows emitted by the administrator helper.
         * @param string $message Translation used to identify the log entry.
         * @param string $context Component action-log context.
         * @param int $userId User to attribute the action to.
         * @return void
         */
        public function addLog(array $messages, string $message, string $context, int $userId): void
        {
            self::$logs[] = compact('messages', 'message', 'context', 'userId');
        }
    }
}

namespace {
    \define('_JEXEC', 1);
    $root = \dirname(__DIR__, 2);

    require_once $root . '/site/src/Helper/MatchResultHelper.php';
    require_once $root . '/admin/src/Helper/ActionLogHelper.php';

    $match = \Diddipoeler\Component\SportsManagement\Site\Helper\MatchResultHelper::class;
    $game = (object) [
        'projectteam1_id' => 10,
        'projectteam2_id' => 20,
        'team1_result' => '2',
        'team2_result' => '2',
    ];

    if ($match::outcome($game, 10) !== 0
        || $match::outcome($game, 20) !== 0
        || $match::outcome($game, 30) !== false
        || $match::renderOutcomeIcon($game, 30) !== '') {
        throw new \RuntimeException('Unrelated teams must not receive a draw icon.');
    }

    $game->team1_result = '3';
    $game->team2_result = '1';

    if ($match::outcome($game, 10) !== 1 || $match::outcome($game, 20) !== -1) {
        throw new \RuntimeException('Normal match victories and losses must be preserved.');
    }

    foreach (['invalid', '1junk', 'INF', '1e9999', [], null] as $invalid) {
        $game->team1_result = $invalid;

        if ($match::outcome($game, 10) !== false) {
            throw new \RuntimeException('Malformed match scores must not produce outcomes.');
        }
    }

    $action = \Diddipoeler\Component\SportsManagement\Administrator\Helper\ActionLogHelper::class;
    $user = (object) ['id' => 5];
    $transaction = ['id' => 77, 'name' => 'Test team'];

    foreach (['team.edit', '../team', 'team?task=delete', '', \str_repeat('a', 101)] as $invalid) {
        $action::record($user, $transaction, false, $invalid);
    }

    if (\Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel::$logs !== []) {
        throw new \RuntimeException('Invalid action log controllers must be rejected.');
    }

    $action::record($user, $transaction, true, 'team');

    $logs = \Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel::$logs;
    if (\count($logs) !== 1
        || $logs[0]['messages'][0]['itemlink'] !== 'index.php?option=com_sportsmanagement&task=team.edit&id=77'
        || $logs[0]['messages'][0]['username'] !== '') {
        throw new \RuntimeException('Valid action log links or absent username handling regressed.');
    }

    $dispatcher = \file_get_contents(
        $root . '/modules/mod_sportsmanagement_top_tipper/src/Dispatcher/Dispatcher.php'
    );

    if ($dispatcher === false
        || !\str_contains($dispatcher, "'predictionGame', 'predictionProjects', 'predictionProject'")
        || !\str_contains($dispatcher, 'array_key_exists($key, $payload)')
        || \str_contains($dispatcher, 'array_merge($data, $payload)')) {
        throw new \RuntimeException('Top Tipper must only copy approved template variables.');
    }

    echo "Site match outcomes, admin action-log and Top Tipper payload guards: OK\n";
}
