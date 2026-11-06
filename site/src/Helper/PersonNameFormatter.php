<?php
/**
 * Format person names using the historical SportsManagement name-format IDs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

final class PersonNameFormatter
{
    /**
     * Format a person's full name using legacy SportsManagement format IDs.
     * Supports prefixes, first names, nicknames, last names and initials
     * in the order expected by existing component templates.
     * @param string|null $prefix Optional title or name prefix.
     * @param string $firstName Person's first name.
     * @param string $nickName Optional nickname.
     * @param string $lastName Person's last name.
     * @param int|string $format Legacy name formatting variant.
     * @return string Formatted display name.
     */
    public static function format(
        ?string $prefix,
        string $firstName,
        string $nickName,
        string $lastName,
        int|string $format = 0
    ): string {
        $parts = [];
        $prefix = trim((string) $prefix);
        $firstName = trim($firstName);
        $nickName = trim($nickName);
        $lastName = trim($lastName);

        if ($prefix !== '') {
            $parts[] = $prefix;
        }

        switch ((int) $format) {
            case 1:
                self::append($parts, $lastName !== '' ? $lastName . ',' : '');
                self::append($parts, $nickName !== '' ? "'" . $nickName . "'" : '');
                self::append($parts, $firstName);
                break;

            case 2:
                self::append($parts, $lastName !== '' ? $lastName . ',' : '');
                self::append($parts, $firstName);
                self::append($parts, $nickName !== '' ? "'" . $nickName . "'" : '');
                break;

            case 3:
                self::append($parts, $firstName);
                self::append($parts, $lastName);
                break;

            case 4:
                self::append($parts, $lastName !== '' ? $lastName . ',' : '');
                self::append($parts, $firstName);
                break;

            case 5:
                self::append($parts, $nickName !== '' ? "'" . $nickName . "' -" : '');
                self::append($parts, $firstName);
                self::append($parts, $lastName);
                break;

            case 6:
                self::append($parts, $nickName !== '' ? "'" . $nickName . "' -" : '');
                self::append($parts, $lastName !== '' ? $lastName . ',' : '');
                self::append($parts, $firstName);
                break;

            case 7:
                self::append($parts, $firstName);
                self::append($parts, $lastName);
                self::append($parts, $nickName !== '' ? '(' . $nickName . ')' : '');
                break;

            case 8:
                self::append($parts, self::initial($firstName));
                self::append($parts, $lastName);
                break;

            case 9:
                self::append($parts, $lastName !== '' ? $lastName . ',' : '');
                self::append($parts, self::initial($firstName));
                break;

            case 10:
                self::append($parts, $lastName);
                break;

            case 11:
                self::append($parts, $firstName);
                self::append($parts, $nickName !== '' ? "'" . $nickName . "'" : '');
                self::append($parts, self::initial($lastName));
                break;

            case 12:
                self::append($parts, $nickName);
                break;

            case 13:
                self::append($parts, $firstName);
                self::append($parts, self::initial($lastName));
                break;

            case 14:
                self::append($parts, $lastName);
                self::append($parts, $firstName);
                break;

            case 15:
            case 16:
                self::append($parts, $lastName);
                self::append($parts, $lastName !== '' ? '<br \\>' : '');
                self::append($parts, $firstName);
                break;

            case 17:
                self::append($parts, $lastName !== '' ? $lastName . ',' : '');
                self::append($parts, $firstName);
                self::append($parts, $nickName !== '' ? "'" . $nickName . "'" : '');
                break;

            case 18:
                self::append($parts, self::initial($lastName));
                self::append($parts, self::initial($firstName));
                break;

            case 0:
            default:
                self::append($parts, $firstName);
                self::append($parts, $nickName !== '' ? "'" . $nickName . "'" : '');
                self::append($parts, $lastName);
                break;
        }

        return implode(' ', $parts);
    }

    /**
     * Append a nonempty name fragment to the ordered output parts.
     * @param array<int,string> $parts Mutable formatted-name fragments.
     * @param string $value Text fragment to add.
     * @return void
     */
    private static function append(array &$parts, string $value): void
    {
        if ($value !== '') {
            $parts[] = $value;
        }
    }

    /**
     * Return the first character of a name with a trailing period.
     * Uses UTF-8-aware extraction when the mbstring extension is present.
     * @param string $value First or last name.
     * @return string Initial with period or empty text.
     */
    private static function initial(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, 1, 'UTF-8') . '.';
        }

        return $value[0] . '.';
    }
}
