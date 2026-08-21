<?php
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

    private static function append(array &$parts, string $value): void
    {
        if ($value !== '') {
            $parts[] = $value;
        }
    }

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
