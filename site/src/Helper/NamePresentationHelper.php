<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/**
 * Joomla 5/6-safe team and person name formatting used by site layouts.
 */
final class NamePresentationHelper
{
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

    private static function firstCharacter(string $value): string
    {
        return function_exists('mb_substr') ? mb_substr($value, 0, 1) : substr($value, 0, 1);
    }

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
