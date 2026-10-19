<?php
/**
 * Native Joomla 5/6 administrator country presentation helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

/**
 * Builds translated country presentation data without loading the legacy JSMCountries helper.
 */
final class CountryOptionsHelper
{
    public static function getOptions(DatabaseInterface $db): array
    {
        try {
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('alpha3'),
                    $db->quoteName('name'),
                ])
                ->from($db->quoteName('#__sportsmanagement_countries'));
            $db->setQuery($query);
            $rows = $db->loadAssocList() ?: [];
        } catch (\Throwable $e) {
            self::reportDatabaseError($e);
            return [];
        }

        $options = [];

        foreach ($rows as $country) {
            $code = strtoupper(trim((string) ($country['alpha3'] ?? '')));

            if (!preg_match('/^[A-Z]{3}$/D', $code)) {
                continue;
            }

            $options[] = HTMLHelper::_(
                'select.option',
                $code,
                Text::_((string) ($country['name'] ?? $code))
            );
        }

        usort(
            $options,
            static fn ($left, $right): int => strnatcasecmp((string) $left->text, (string) $right->text)
        );

        return $options;
    }

    public static function iso2To3(DatabaseInterface $db, string $iso2, string $fallback = 'DEU'): string
    {
        $iso2 = strtoupper(trim($iso2));

        if (!preg_match('/^[A-Z]{2}$/D', $iso2)) {
            return $fallback;
        }

        try {
            $query = $db->createQuery()
                ->select($db->quoteName('alpha3'))
                ->from($db->quoteName('#__sportsmanagement_countries'))
                ->where($db->quoteName('alpha2') . ' = ' . $db->quote($iso2));
            $db->setQuery($query, 0, 1);
            $alpha3 = strtoupper(trim((string) $db->loadResult()));
        } catch (\Throwable $e) {
            self::reportDatabaseError($e);
            return $fallback;
        }

        return preg_match('/^[A-Z]{3}$/D', $alpha3) ? $alpha3 : $fallback;
    }

    public static function getFlag(DatabaseInterface $db, string $countryCode): string
    {
        $countryCode = strtoupper(trim($countryCode));

        if (!preg_match('/^[A-Z]{3}$/D', $countryCode)) {
            return '';
        }

        try {
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('alpha2'),
                    $db->quoteName('name'),
                    $db->quoteName('picture'),
                ])
                ->from($db->quoteName('#__sportsmanagement_countries'))
                ->where($db->quoteName('alpha3') . ' = ' . $db->quote($countryCode));
            $db->setQuery($query, 0, 1);
            $country = $db->loadObject();
        } catch (\Throwable $e) {
            self::reportDatabaseError($e);
            return '';
        }

        if (!$country) {
            return '';
        }

        $params = ComponentHelper::getParams('com_sportsmanagement');
        $alpha2 = strtolower(trim((string) ($country->alpha2 ?? '')));
        $alpha2 = preg_match('/^[a-z]{2}$/D', $alpha2) ? $alpha2 : '';
        $label = Text::_((string) ($country->name ?? $countryCode));

        if ((int) $params->get('cfg_flags_css', 0) === 1) {
            $cssCode = match ($countryCode) {
                'WAL' => 'gb-wls',
                'SCO' => 'gb-sct',
                'GBR' => 'gb-eng',
                default => $alpha2,
            };

            return $cssCode !== ''
                ? '<span class="fi fi-' . self::escape($cssCode) . '" title="' . self::escape($label) . '"></span>'
                : '';
        }

        $path = $alpha2 !== ''
            ? 'images/com_sportsmanagement/database/flags/' . $alpha2 . '.png'
            : (string) ($country->picture ?? $params->get('ph_flags', ''));
        $url = self::imageUrl($path);

        return $url !== ''
            ? '<img src="' . self::escape($url)
                . '" alt="' . self::escape($label) . '" title="' . self::escape($label) . '" />'
            : '';
    }

    /** Keep optional administrator country lookups from exposing SQL details. */
    private static function reportDatabaseError(\Throwable $e): void
    {
        Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'com_sportsmanagement');
    }

    /** Build only safe HTTP(S) or site-relative flag URLs. */
    private static function imageUrl(string $value): string
    {
        $value = trim($value);

        if ($value === '' || preg_match('/[\x00-\x1F\x7F]/', $value)
            || str_contains($value, chr(92)) || str_starts_with($value, '//')) {
            return '';
        }

        if (preg_match('~^https?://~i', $value)) {
            $parts = parse_url($value);

            return filter_var($value, FILTER_VALIDATE_URL) !== false
                && is_array($parts)
                && !empty($parts['host'])
                && !isset($parts['user'])
                && !isset($parts['pass'])
                    ? $value
                    : '';
        }

        if (preg_match('~^[a-z][a-z0-9+.-]*:~i', $value)) {
            return '';
        }

        $path = ltrim($value, '/');
        $decoded = $path;

        for ($depth = 0; $depth < 3; $depth++) {
            if (preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $decoded)
                || preg_match('/[\x00-\x1F\x7F]/', $decoded)
                || str_contains($decoded, chr(92))
                || str_contains($decoded, '?') || str_contains($decoded, '#')) {
                return '';
            }

            $next = rawurldecode($decoded);
            if ($next === $decoded) {
                break;
            }
            $decoded = $next;
        }

        return rtrim(Uri::root(), '/') . '/' . $path;
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
