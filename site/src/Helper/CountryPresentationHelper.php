<?php
/**
 * Country presentation helpers for native site views.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

final class CountryPresentationHelper
{
    private static array $countries = [];

    /**
     * Translate a validated country record into its displayed name.
     * Ignore malformed legacy name columns rather than casting arrays to text.
     * @param string $countryCode Three-letter country lookup identifier.
     * @return string Translated label or empty text.
     */
    public static function name(string $countryCode): string
    {
        $country = self::country($countryCode);

        $rawName = $country->name ?? null;

        return is_string($rawName) && trim($rawName) !== '' ? Text::_($rawName) : '';
    }

    /**
     * Render a safe country flag and optional image layout attributes.
     * Handle CSS flags, image URLs and malformed optional country fields.
     * @param string $countryCode Country or FIFA lookup code.
     * @param string $attributes Allowed legacy image attributes.
     * @return string Escaped flag HTML or empty text.
     */
    public static function flag(string $countryCode, string $attributes = ''): string
    {
        $country = self::country($countryCode);
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $rawName = $country->name ?? null;
        $countryName = is_string($rawName) && trim($rawName) !== '' ? Text::_($rawName) : '';
        $rawIso2 = $country->alpha2 ?? '';
        $iso2 = is_string($rawIso2) ? strtolower(trim($rawIso2)) : '';
        $iso2 = preg_match('/^[a-z]{2}$/D', $iso2) ? $iso2 : '';

        if ((int) $params->get('cfg_flags_css', 0) === 1) {
            $cssCode = match (strtoupper($countryCode)) {
                'WAL' => 'gb-wls',
                'SCO' => 'gb-sct',
                'GBR' => 'gb-eng',
                default => $iso2,
            };

            return $cssCode !== '' ? '<span class="fi fi-' . self::escape($cssCode) . '"></span>' : '';
        }

        $rawPicture = $country->picture ?? null;
        $src = $iso2 !== ''
            ? 'images/com_sportsmanagement/database/flags/' . $iso2 . '.png'
            : (is_string($rawPicture) ? $rawPicture : '');

        if ($src === '') {
            $fallback = $params->get('ph_flags', '');
            $src = is_string($fallback) ? $fallback : '';
        }

        $source = self::safeFlagSource($src);

        if ($source === '') {
            return '';
        }

        $extra = self::safeImageAttributes($attributes);

        return '<img src="' . self::escape($source)
            . '" alt="' . self::escape($countryName)
            . '" title="' . self::escape($countryName) . '"'
            . ($extra !== '' ? ' ' . $extra : '') . ' />';
    }

    /**
     * Resolve a stored flag path without allowing traversal or unsafe schemes.
     * Validate all URL decoding layers and reject encoded traversal segments.
     * @param string $source Stored country flag image URL or path.
     * @return string Validated URL or empty string.
     */
    private static function safeFlagSource(string $source): string
    {
        $source = trim($source);

        if ($source === ''
            || preg_match('/[\x00-\x1F\x7F]/', $source)
            || str_contains($source, chr(92))
            || str_starts_with($source, '//')) {
            return '';
        }

        if (preg_match('~^https?://~i', $source)) {
            $parts = parse_url($source);

            return filter_var($source, FILTER_VALIDATE_URL) !== false
                && is_array($parts)
                && !empty($parts['host'])
                && !isset($parts['user'])
                && !isset($parts['pass'])
                    ? $source
                    : '';
        }

        if (preg_match('~^[a-z][a-z0-9+.-]*:~i', $source)) {
            return '';
        }

        $path = ltrim($source, '/');
        $decoded = $path;

        // A nested encoded ../ can emerge only after the final decoding
        // pass; validate that final layer as well and reject leftover escapes.
        for ($depth = 0; $depth < 8; $depth++) {
            if (preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $decoded)
                || preg_match('/[\x00-\x1F\x7F]/', $decoded)
                || str_contains($decoded, chr(92))
                || str_contains($decoded, '?')
                || str_contains($decoded, '#')) {
                return '';
            }

            $next = rawurldecode($decoded);

            if ($next === $decoded) {
                break;
            }

            $decoded = $next;
        }

        if (preg_match('/%[0-9a-f]{2}/i', $decoded) === 1
            || preg_match('~(?:^|/)\\.{1,2}(?:/|$)~', $decoded)
            || preg_match('/[\\x00-\\x1F\\x7F]/', $decoded)
            || str_contains($decoded, chr(92))
            || str_contains($decoded, '?') || str_contains($decoded, '#')) {
            return '';
        }

        return rtrim(Uri::root(), '/') . '/' . $path;
    }

    /**
     * Allowlist safe HTML image attributes supplied by legacy templates.
     * Drop event handlers and escape permitted attribute values.
     * @param string $attributes Raw optional HTML attributes.
     * @return string Escaped allowlisted attributes.
     */
    private static function safeImageAttributes(string $attributes): string
    {
        $clean = [];

        preg_match_all('/([A-Za-z][A-Za-z0-9_-]*)\s*=\s*(["\x27])(.*?)\2/s', $attributes, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $name = strtolower($match[1]);

            if (!in_array($name, ['class', 'style', 'width', 'height', 'loading', 'decoding', 'id', 'role', 'title'], true)
                && !preg_match('/^aria-[a-z0-9_-]+$/D', $name)) {
                continue;
            }

            $value = $match[3];

            if (preg_match('/[\x00-\x1F\x7F]/', $value)) {
                continue;
            }

            $clean[] = $name . '="' . self::escape($value) . '"';
        }

        return implode(' ', $clean);
    }

    /**
     * Load and sort translated country selector options for site views.
     * Validate three-letter codes and disregard malformed database rows.
     * @param DatabaseInterface $db Selected Joomla database.
     * @return array<int,object> Sorted country option objects.
     */
    public static function options(DatabaseInterface $db): array
    {
        try {
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('alpha3'),
                    $db->quoteName('name'),
                ])
                ->from($db->quoteName('#__sportsmanagement_countries'));
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'com_sportsmanagement');
            return [];
        }

        $options = [];
        foreach ($rows as $country) {
            // Imported option tables may contain arrays or other malformed
            // rows. Do not inspect properties on non-object records.
            if (!is_object($country)) {
                continue;
            }

            $rawCode = $country->alpha3 ?? null;
            $code = is_string($rawCode) ? strtoupper(trim($rawCode)) : '';
            if (preg_match('/^[A-Z]{3}$/D', $code) !== 1) {
                continue;
            }

            $rawName = $country->name ?? null;
            $label = is_string($rawName) && trim($rawName) !== '' ? $rawName : $code;
            $options[] = HTMLHelper::_('select.option', $code, Text::_($label));
        }

        usort(
            $options,
            static fn (object $a, object $b): int => strnatcasecmp(
                (string) ($a->text ?? ''),
                (string) ($b->text ?? '')
            )
        );

        return $options;
    }

    /**
     * Render an escaped postal address from legacy location fields.
     * Insert translated country labels and safely generated flag markup.
     * @param string $name Location display name.
     * @param string $address Street address.
     * @param string $state Region or state.
     * @param string $zipcode Postal code.
     * @param string $location City name.
     * @param string $country Country lookup identifier.
     * @param string $format Translated address layout key.
     * @return string HTML-safe postal address, or empty text.
     */
    public static function address(
        string $name = '',
        string $address = '',
        string $state = '',
        string $zipcode = '',
        string $location = '',
        string $country = '',
        string $format = 'COM_SPORTSMANAGEMENT_CLUBINFO_ADDRESS_FORM'
    ): string {
        if ($address === '' && $state === '' && $zipcode === '' && $location === '') {
            return '';
        }

        $output = Text::_($format);
        $output = str_replace('%NAME%', '<span itemprop="name">' . self::escape($name) . '</span>', $output);
        $output = str_replace(
            '%ADDRESS%',
            '<div itemprop="address" itemscope itemtype="https://schema.org/PostalAddress"><span itemprop="streetAddress">'
                . self::escape($address) . '</span>',
            $output
        );
        $output = str_replace('%STATE%', '<span itemprop="addressRegion">' . self::escape($state) . '</span>', $output);
        $output = str_replace('%ZIPCODE%', '<span itemprop="postalCode">' . self::escape($zipcode) . '</span>', $output);
        $output = str_replace('%LOCATION%', '<span itemprop="addressLocality">' . self::escape($location) . '</span>', $output);
        $output = str_replace('%FLAG%', self::flag($country), $output);
        $output = str_replace('%COUNTRY%', self::escape(self::name($country)), $output);

        return $output . '</div>&nbsp;';
    }

    /**
     * Load and cache a country record for the active frontend database.
     * Log database failures privately and cache missing country records.
     * Reject malformed country codes before invoking Joomla database services.
     * @param string $countryCode Country or FIFA lookup identifier.
     * @return object|null Saved country or null when unavailable.
     */
    private static function country(string $countryCode): ?object
    {
        $countryCode = strtoupper(trim($countryCode));

        // ISO alpha-3 and FIFA country identifiers are three letters.
        // Avoid unnecessary database lookups for malformed site input.
        if (preg_match('/^[A-Z]{3}$/D', $countryCode) !== 1) {
            return null;
        }

        $cacheKey = null;

        try {
            $app = SportsManagementSiteApplicationResolver::resolve();
            $databaseSelector = $app->getInput()->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
            $cacheKey = $databaseSelector . ':' . $countryCode;

            if (array_key_exists($cacheKey, self::$countries)) {
                return self::$countries[$cacheKey];
            }

            /** @var DatabaseInterface $joomlaDatabase */
            $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
            $db = SportsManagementDatabaseResolver::resolve($joomlaDatabase, $databaseSelector);
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('alpha2'),
                    $db->quoteName('alpha3'),
                    $db->quoteName('name'),
                    $db->quoteName('picture'),
                ])
                ->from($db->quoteName('#__sportsmanagement_countries'))
                ->where(
                    '(' . $db->quoteName('alpha3') . ' = ' . $db->quote($countryCode)
                    . ' OR ' . $db->quoteName('fifa') . ' = ' . $db->quote($countryCode) . ')'
                );

            $db->setQuery($query, 0, 1);
            self::$countries[$cacheKey] = $db->loadObject() ?: null;
        } catch (\Throwable $e) {
            // Country labels and flags are optional presentation data. Do not
            // break the whole player/club page or expose SQL errors in HTML.
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'com_sportsmanagement');

            if ($cacheKey !== null) {
                self::$countries[$cacheKey] = null;
            }

            return null;
        }

        return self::$countries[$cacheKey];
    }

    /**
     * Escape arbitrary country names and URLs for HTML attributes.
     * @param string $value Raw HTML attribute or text value.
     * @return string HTML-escaped UTF-8 text.
     */
    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
