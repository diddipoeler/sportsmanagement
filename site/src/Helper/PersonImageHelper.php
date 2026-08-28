<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

/**
 * Person image defaults and URL resolution used by native site views.
 */
final class PersonImageHelper
{
    public static function placeholder(): string
    {
        return trim((string) ComponentHelper::getParams('com_sportsmanagement')->get('ph_player', ''));
    }

    public static function resolve(string ...$candidates): string
    {
        $placeholder = self::placeholder();

        foreach ($candidates as $candidate) {
            $candidate = trim($candidate);

            if ($candidate !== '' && $candidate !== $placeholder) {
                return $candidate;
            }
        }

        return $placeholder;
    }

    public static function url(string $picture): string
    {
        $picture = trim($picture);

        if ($picture === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $picture)) {
            return $picture;
        }

        $base = \defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')
            ? trim((string) COM_SPORTSMANAGEMENT_PICTURE_SERVER)
            : Uri::root();

        if ($base === '') {
            $base = Uri::root();
        }

        return rtrim($base, '/') . '/' . ltrim($picture, '/');
    }
}
