<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Format person names using the historical SportsManagement name-format IDs.
 */
final class PersonNameFormatter
{
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
