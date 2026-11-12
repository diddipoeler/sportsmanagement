<?php
/**
 * Verify safe Joomla 5/6 match-time formatting, extra-field SQL and club icon CSS bounds.
 *
 * Usage: php .github/tests/joomla56_matchtime_extra_fields_clubicons.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML {
    /** Standalone fixture for Joomla's date formatting API. */
    class HTMLHelper
    {
        /**
         * Return formatted time data while preserving the supplied date.
         *
         * @param string $date Valid original match timestamp.
         * @param string $format Valid Joomla date format setting.
         * @param string $timezone Requested timezone identifier.
         * @return string Stable date marker for assertions.
         */
        public static function date(string $date, string $format, string $timezone): string
        {
            return $format . '|' . $date . '|' . $timezone;
        }
    }
}

namespace Joomla\CMS\Language {
    /** Standalone translated string provider for match-time fixtures. */
    class Text
    {
        /**
         * Return the normal clock suffix for time output.
         *
         * @param string $key Joomla translation key.
         * @return string Stable string accepted by the formatter.
         */
        public static function _(string $key): string
        {
            return $key === 'COM_SPORTSMANAGEMENT_GLOBAL_CLOCK' ? '%1$s h' : $key;
        }
    }
}

namespace {
    \define('_JEXEC', 1);
    $root = \dirname(__DIR__, 2);

    require_once $root . '/site/src/Helper/MatchTimeHelper.php';

    $helper = \Diddipoeler\Component\SportsManagement\Site\Helper\MatchTimeHelper::class;

    foreach ([null, [], (object) ['unexpected' => true], '', 'not a date'] as $invalid) {
        $game = (object) ['match_date' => $invalid];

        if ($helper::format($game, [], ['time_format' => []]) !== '--&nbsp;:&nbsp;--') {
            throw new \RuntimeException('Invalid match dates must use the empty time placeholder.');
        }
    }

    $match = (object) ['match_date' => '2026-11-12 18:30:00'];

    if ($helper::format($match, [], ['time_format' => []]) !== 'H:i|2026-11-12 18:30:00|UTC') {
        throw new \RuntimeException('Invalid time format must fall back to H:i.');
    }

    if ($helper::format($match, [], ['time_format' => 'H:s']) !== 'H:s|2026-11-12 18:30:00|UTC') {
        throw new \RuntimeException('Valid time formats must remain supported.');
    }

    $admin = \file_get_contents($root . '/admin/src/Helper/ExtraFieldsReadHelper.php');

    if (!\is_string($admin)
        || !\str_contains($admin, '->bind(\':extraFieldItemId\', $itemId, ParameterType::INTEGER)')
        || !\str_contains($admin, '->bind(\':extraFieldTemplate\', $view, ParameterType::STRING)')
        || !\str_contains($admin, 'preg_match(\'/^[A-Za-z][A-Za-z0-9_-]*$/D\', $view)')
        || \str_contains($admin, '\' = \' . $itemId')) {
        throw new \RuntimeException('Administrator extra-field query validation has regressed.');
    }

    $module = \file_get_contents(
        $root . '/modules/mod_sportsmanagement_clubicons/src/Dispatcher/Dispatcher.php'
    );

    if (!\is_string($module)
        || !\str_contains($module, 'is_finite((float) $rawPercent)')
        || !\str_contains($module, 'min(400.0, max(-90.0, $percent))')
        || !\str_contains($module, 'min(1000, max(1, $height))')) {
        throw new \RuntimeException('Club icon layout CSS bounds have regressed.');
    }

    echo "Joomla 5/6 match times, extra-field SQL and Clubicons CSS bounds: OK\n";
}
