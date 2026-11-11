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
     * Rejects partial numeric selectors and overflowing legacy settings.
     * Escapes the displayed text and optionally wraps it in a Joomla link.
     *
     * @param object $team Team record containing full and abbreviated names.
     * @param int|string $format Legacy team-name format selector.
     * @param string $link Optional destination URL.
     * @return string HTML-safe team name or linked name.
     */
    public static function team(object $team, int|string $format = 2, string $link = ''): string
    {
        $format = self::validatedFormat($format, 2, 2);
        $name = match ($format) {
            0 => self::firstNonEmpty($team->short_name ?? null, $team->name ?? null, $team->middle_name ?? null),
            1 => self::firstNonEmpty($team->middle_name ?? null, $team->name ?? null, $team->short_name ?? null),
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
     * safe display in native Joomla 5/6 views. Invalid array/object fields
     * are ignored instead of producing PHP 8 conversion warnings.
     * Malformed format selectors fall back to the default name layout.
     *
     * @param object $person Person record with firstname, nickname and lastname.
     * @param int|string $format Legacy person-name layout identifier.
     * @return string HTML-escaped display name.
     */
    public static function person(object $person, int|string $format = 0): string
    {
        // Imported person rows may contain unexpected arrays or objects.
        // Reuse the team's safe name-value normaliser rather than invoking
        // PHP 8 array-to-string conversion warnings in native templates.
        $first = self::firstNonEmpty($person->firstname ?? null);
        $nick = self::firstNonEmpty($person->nickname ?? null);
        $last = self::firstNonEmpty($person->lastname ?? null);
        $quotedNick = $nick !== '' ? '“' . $nick . '”' : '';
        $firstInitial = $first !== '' ? self::firstCharacter($first) . '.' : '';
        $lastInitial = $last !== '' ? self::firstCharacter($last) . '.' : '';

        $parts = match (self::validatedFormat($format, 18, 0)) {
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
     * Validate legacy name-format selectors without partial integer casts.
     *
     * A malformed numeric string such as "8junk" must not select format 8.
     * Accept only integers or digit-only strings within the supported
     * formatting range, retaining the caller's standard fallback otherwise.
     *
     * @param int|string $value Requested team or person name format.
     * @param int $maximum Highest supported format selector.
     * @param int $fallback Default for malformed or unsupported input.
     * @return int Validated name format index.
     */
    private static function validatedFormat(int|string $value, int $maximum, int $fallback): int
    {
        if (is_string($value)) {
            if (preg_match('/^[0-9]+$/D', $value) !== 1) {
                return $fallback;
            }

            $digits = ltrim($value, '0');
            $value = filter_var($digits === '' ? '0' : $digits, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 0],
            ]);

            if ($value === false) {
                return $fallback;
            }
        }

        return $value >= 0 && $value <= $maximum ? $value : $fallback;
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
     * Trims scalar or stringable candidates and returns the first nonempty
     * text, preserving the configured fallback order. Ignores malformed
     * arrays and objects rather than producing PHP 8 conversion warnings.
     *
     * @param mixed ...$values Name candidates ordered by preference.
     * @return string First nonempty name, or an empty string.
     */
    private static function firstNonEmpty(mixed ...$values): string
    {
        foreach ($values as $value) {
            // Legacy database results and template overrides can supply
            // malformed name fields. Ignore arrays and non-stringable
            // objects instead of emitting PHP 8 conversion warnings.
            if (!is_scalar($value) && !$value instanceof \Stringable) {
                continue;
            }

            $value = trim((string) $value);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }
}
