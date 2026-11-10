<?php
/**
 * Team and person name presentation helper for native Joomla 5/6 site views.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/**
 * Joomla 5/6-safe team and person name formatting used by site layouts.
 */
final class NamePresentationHelper
{
    /**
     * Format a team's display name using the configured legacy name style.
     *
     * Prefers short, middle or full names according to the requested
     * format and falls back to the next available name when necessary.
     * Escapes the displayed text and optionally wraps it in a Joomla link.
     *
     * @param object $team Team record containing full and abbreviated names.
     * @param int|string $format Legacy team-name format selector.
     * @param string $link Optional destination URL.
     * @return string HTML-safe team name or linked name.
     */
    public static function team(object $team, int|string $format = 2, string $link = ''): string
    {
        $format = (int) $format;
        $name = match ($format) {
            0 => self::firstNonEmpty($team->short_name ?? null, $team->name ?? null),
            1 => self::firstNonEmpty($team->middle_name ?? null, $team->name ?? null),
            default => self::firstNonEmpty($team->name ?? null, $team->middle_name ?? null, $team->short_name ?? null),
        };

        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

        return $link !== '' ? HTMLHelper::link($link, $name) : $name;
    }

    /**
     * Render a person's name using the configured SportsManagement style.
     *
     * Supports first/last name ordering, quoted nicknames and initial
     * variants, omits empty name parts and escapes the final text for
     * safe display in native Joomla 5/6 views.
     *
     * @param object $person Person record with firstname, nickname and lastname.
     * @param int|string $format Legacy person-name layout identifier.
     * @return string HTML-escaped display name.
     */
    public static function person(object $person, int|string $format = 0): string
    {
        $first = trim((string) ($person->firstname ?? ''));
        $nick = trim((string) ($person->nickname ?? ''));
        $last = trim((string) ($person->lastname ?? ''));
        $quotedNick = $nick !== '' ? '“' . $nick . '”' : '';
        $firstInitial = $first !== '' ? self::firstCharacter($first) . '.' : '';
        $lastInitial = $last !== '' ? self::firstCharacter($last) . '.' : '';

        $parts = match ((int) $format) {
            1 => [$last, $quotedNick, $first],
            2 => [$last, $first, $quotedNick],
            3 => [$first, $last],
            4 => [$last, $first],
            5 => [$quotedNick, $first, $last],
            6 => [$quotedNick, $last, $first],
            7 => [$first, $last, $quotedNick],
            8 => [$firstInitial, $last],
            9 => [$last, $firstInitial],
            10 => [$last],
            11 => [$first, $nick, $last],
            12 => [$nick],
            13 => [$first, $lastInitial],
            14 => [$lastInitial, $first],
            15 => [$last, $first],
            16 => [$first, $last],
            17 => [$last, $first, $nick],
            18 => [$last, $firstInitial],
            default => [$first, $quotedNick, $last],
        };

        $value = implode(' ', array_filter($parts, static fn ($part): bool => trim((string) $part) !== ''));

        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Read the first character of a name for initial-based formats.
     *
     * Uses mbstring when available; otherwise a Unicode-aware PCRE match
     * retains the entire first character (including non-ASCII initials).
     * The byte fallback is only used for invalid UTF-8 input.
     *
     * @param string $value Name text from which to take an initial.
     * @return string First available character, or an empty string.
     */
    private static function firstCharacter(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, 1, 'UTF-8');
        }

        if (preg_match('/^./us', $value, $matches) === 1) {
            return $matches[0];
        }

        return substr($value, 0, 1);
    }

    /**
     * Select the first usable text value from a list of name candidates.
     *
     * Trims each candidate and returns its text as soon as it is
     * nonempty, preserving the configured fallback order.
     *
     * @param mixed ...$values Name candidates ordered by preference.
     * @return string First nonempty name, or an empty string.
     */
    private static function firstNonEmpty(mixed ...$values): string
    {
        foreach ($values as $value) {
            $value = trim((string) $value);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }
}
