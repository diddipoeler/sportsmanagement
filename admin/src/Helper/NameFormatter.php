<?php
/**
 * Joomla 5/6 SportsManagement administrator helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

/**
 * Joomla 5/6-native person-name formatter.
 *
 * Formats 0-16 intentionally preserve the historical
 * sportsmanagementHelper::formatName() output contract.
 */
final class NameFormatter
{
    /**
     * Format administrator person names using the configured legacy layout.
     *
     * Combines optional prefixes, first names, nicknames and surnames in the
     * ordering selected by SportsManagement formats 0–18. Preserves the
     * historical line-break and punctuation output for existing templates.
     *
     * @param string|null $prefix Optional title or name prefix.
     * @param string $firstName Given name of the person.
     * @param string $nickName Optional nickname.
     * @param string $lastName Family name of the person.
     * @param int $format Legacy formatting variant identifier.
     * @return string Display name formatted for administrator views.
     */
    public static function format(
        ?string $prefix,
        string $firstName,
        string $nickName,
        string $lastName,
        int $format
    ): string {
        $name = [];

        if ($prefix) {
            $name[] = $prefix;
        }

        switch ($format) {
            case 0:
                self::append($name, $firstName);
                self::append($name, $nickName !== '' ? "'" . $nickName . "'" : '');
                self::append($name, $lastName);
                break;

            case 1:
                self::append($name, $lastName !== '' ? $lastName . ',' : '');
                self::append($name, $nickName !== '' ? "'" . $nickName . "'" : '');
                self::append($name, $firstName);
                break;

            case 2:
                self::append($name, $lastName !== '' ? $lastName . ',' : '');
                self::append($name, $firstName);
                self::append($name, $nickName !== '' ? "'" . $nickName . "'" : '');
                break;

            case 3:
                self::append($name, $firstName);
                self::append($name, $lastName);
                break;

            case 4:
                self::append($name, $lastName !== '' ? $lastName . ',' : '');
                self::append($name, $firstName);
                break;

            case 5:
                self::append($name, $nickName !== '' ? "'" . $nickName . "' - " : '');
                self::append($name, $firstName);
                self::append($name, $lastName);
                break;

            case 6:
                self::append($name, $nickName !== '' ? "'" . $nickName . "' - " : '');
                self::append($name, $lastName !== '' ? $lastName . ',' : '');
                self::append($name, $firstName);
                break;

            case 7:
                self::append($name, $firstName);
                self::append($name, $lastName);
                self::append($name, $nickName !== '' ? '(' . $nickName . ')' : '');
                break;

            case 8:
                self::append($name, self::initial($firstName));
                self::append($name, $lastName);
                break;

            case 9:
                self::append($name, $lastName !== '' ? $lastName . ',' : '');
                self::append($name, self::initial($firstName));
                break;

            case 10:
                self::append($name, $lastName);
                break;

            case 11:
                self::append($name, $firstName);
                self::append($name, $nickName !== '' ? "'" . $nickName . "'" : '');
                self::append($name, self::initial($lastName));
                break;

            case 12:
                self::append($name, $nickName);
                break;

            case 13:
                self::append($name, $firstName);
                self::append($name, self::initial($lastName));
                break;

            case 14:
                self::append($name, $lastName);
                self::append($name, $firstName);
                break;

            case 15:
                self::append($name, $lastName);
                self::append($name, $lastName !== '' ? '<br \\>' : '');
                self::append($name, $firstName);
                break;

            case 16:
                // Preserve the historical implementation, which produced the
                // same order as format 15 despite the field label saying the reverse.
                self::append($name, $lastName);
                self::append($name, $lastName !== '' ? '<br \\>' : '');
                self::append($name, $firstName);
                break;

            case 17:
                // The configured label duplicates format 2; keep that useful output
                // instead of the historical empty result for this newer option.
                self::append($name, $lastName !== '' ? $lastName . ',' : '');
                self::append($name, $firstName);
                self::append($name, $nickName !== '' ? "'" . $nickName . "'" : '');
                break;

            case 18:
                // The German label is "N. V.": surname and first-name initials.
                self::append($name, self::initial($lastName));
                self::append($name, self::initial($firstName));
                break;

            default:
                self::append($name, $firstName);
                self::append($name, $lastName);
                break;
        }

        return implode(' ', $name);
    }

    /**
     * Append a nonempty name component to the ordered output collection.
     *
     * Avoids introducing empty separators when optional name fields are
     * absent, while preserving punctuation already present in the value.
     *
     * @param array<int,string> $parts Ordered name components, mutated in place.
     * @param string $value Component to add when nonempty.
     * @return void
     */
    private static function append(array &$parts, string $value): void
    {
        if ($value !== '') {
            $parts[] = $value;
        }
    }

    /**
     * Extract the first Unicode character of a name and append a period.
     *
     * Prefers mbstring where installed, falls back to a Unicode-aware regex,
     * and retains the original byte fallback only for malformed input.
     *
     * @param string $value Given or family name to abbreviate.
     * @return string First character and period, or empty text for no name.
     */
    private static function initial(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, 1, 'UTF-8') . '.';
        }

        if (preg_match('/^./us', $value, $matches) === 1) {
            return $matches[0] . '.';
        }

        return $value[0] . '.';
    }
}
