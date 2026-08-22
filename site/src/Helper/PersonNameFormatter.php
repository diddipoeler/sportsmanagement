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
            case 1: // Lastname, 'Nickname' Firstname
                self::append($parts, $lastName !== '' ? $lastName . ',' : '');
                self::append($parts, $nickName !== '' ? "'" . $nickName . "'" : '');
                self::append($parts, $firstName);
                break;

            case 2: // Lastname, Firstname 'Nickname'
                self::append($parts, $lastName !== '' ? $lastName . ',' : '');
                self::append($parts, $firstName);
                self::append($parts, $nickName !== '' ? "'" . $nickName . "'" : '');
                break;

            case 3: // Firstname Lastname
                self::append($parts, $firstName);
                self::append($parts, $lastName);
                break;

            case 4: // Lastname, Firstname
                self::append($parts, $lastName !== '' ? $lastName . ',' : '');
                self::append($parts, $firstName);
                break;

            case 5: // 'Nickname' - Firstname Lastname
                self::append($parts, $nickName !== '' ? "'" . $nickName . "' -" : '');
                self::append($parts, $firstName);
                self::append($parts, $lastName);
                break;

            case 6: // 'Nickname' - Lastname, Firstname
                self::append($parts, $nickName !== '' ? "'" . $nickName . "' -" : '');
                self::append($parts, $lastName !== '' ? $lastName . ',' : '');
                self::append($parts, $firstName);
                break;

            case 7: // Firstname Lastname (Nickname)
                self::append($parts, $firstName);
                self::append($parts, $lastName);
                self::append($parts, $nickName !== '' ? '(' . $nickName . ')' : '');
                break;

            case 8: // F. Lastname
                self::append($parts, self::initial($firstName));
                self::append($parts, $lastName);
                break;

            case 9: // Lastname, F.
                self::append($parts, $lastName !== '' ? $lastName . ',' : '');
                self::append($parts, self::initial($firstName));
                break;

            case 10: // Lastname
                self::append($parts, $lastName);
                break;

            case 11: // Firstname 'Nickname' L.
                self::append($parts, $firstName);
                self::append($parts, $nickName !== '' ? "'" . $nickName . "'" : '');
                self::append($parts, self::initial($lastName));
                break;

            case 12: // Nickname
                self::append($parts, $nickName);
                break;

            case 13: // Firstname L.
                self::append($parts, $firstName);
                self::append($parts, self::initial($lastName));
                break;

            case 0:
            default: // Firstname 'Nickname' Lastname
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

        return mb_substr($value, 0, 1) . '.';
    }
}
