<?php
/**
 * Isolated Joomla 5/6 regression checks for site age and live-match rendering.
 *
 * No Joomla installation is needed: lightweight CMS stubs validate that
 * invalid birthdays are rejected and translated live labels are HTML-safe.
 *
 * Usage: php .github/tests/joomla56-site-age-match-time.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\HTML {
    final class HTMLHelper
    {
        /**
         * Return a stable time string for live-match rendering tests.
         *
         * @param string $date Source fixture date.
         * @param string $format Configured display format.
         * @param string $timezone Requested timezone.
         * @return string Stable fixture time.
         */
        public static function date(string $date, string $format, string $timezone): string
        {
            return '12:00';
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return configured translation text unmodified for escaping assertions.
         *
         * @param string $key Translation key or fixture text.
         * @return string Translation fixture.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace {
    \define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Helper/PersonAgeHelper.php';
    require_once dirname(__DIR__, 2) . '/site/src/Helper/MatchTimeHelper.php';

    $age = \Diddipoeler\Component\SportsManagement\Site\Helper\PersonAgeHelper::class;
    $time = \Diddipoeler\Component\SportsManagement\Site\Helper\MatchTimeHelper::class;

    foreach (['2025-02-29', '2026-13-01', '2026-04-31', '0000-00-00'] as $date) {
        if ($age::calculate($date, '2026-11-11') !== '-') {
            throw new \RuntimeException('Invalid frontend birthday was accepted: ' . $date);
        }
    }

    if ($age::calculate('2024-02-29', '2025-02-28') !== 0
        || $age::calculate('2024-02-29', '2025-03-01') !== 1) {
        throw new \RuntimeException('Leap-day age calculation changed.');
    }

    $game = (object) [
        'match_date' => date('Y-m-d H:i:s', time() - 60),
        'team1_result' => 1,
        'team2_result' => 1,
    ];
    $project = (object) [
        'game_parts' => 1,
        'game_regular_time' => 90,
        'halftime' => 0,
        'allow_add_time' => false,
    ];
    $config = [
        'mark_now_playing' => true,
        'mark_now_playing_alt_text' => 'A "B" & C',
        'mark_now_playing_alt_actual_time' => 'Minute %MINUTE%',
        'mark_now_playing_text' => '<script>unsafe</script>',
    ];
    $result = $time::format($game, $config, [], $project);

    if (!str_contains($result, 'A &quot;B&quot; &amp; C')
        || !str_contains($result, '&lt;script&gt;unsafe&lt;/script&gt;')
        || str_contains($result, '&amp;quot;')
        || str_contains($result, '<script>')) {
        throw new \RuntimeException('Live-match labels were not escaped exactly once.');
    }

    echo "Joomla 5/6 site age and match-time regressions passed.\n";
}
