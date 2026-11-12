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
    /**
     * Load and alphabetize administrator country selector options.
     *
     * Selects registered ISO-3166 alpha-3 country codes, discards malformed
     * database records, including nested/invalid code or name values,
     * and returns translated Joomla option objects.
     * Database and iterator errors are logged privately and produce an
     * empty selector. Repeated ISO codes appear only once.
     *
     * @param DatabaseInterface $db Current Joomla database connection.
     * @return array<int,object> Sorted country selection options.
     */
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
            $rows = $db->loadAssocList();

            if (!is_iterable($rows)) {
                return [];
            }

            $options = [];
            $seenCodes = [];

            foreach ($rows as $country) {
                // Older imports can leave malformed or nested country values.
                // Avoid PHP 8 array-to-string warnings during option rendering.
                if (!is_array($country)) {
                    continue;
                }

                $rawCode = $country['alpha3'] ?? null;

                if (!is_string($rawCode)) {
                    continue;
                }

                $code = strtoupper(trim($rawCode));

                if (!preg_match('/^[A-Z]{3}$/D', $code) || isset($seenCodes[$code])) {
                    continue;
                }

                $seenCodes[$code] = true;
                $name = $country['name'] ?? $code;
                $label = is_string($name) && trim($name) !== '' ? $name : $code;
                $option = HTMLHelper::_('select.option', $code, Text::_($label));

                // Joomla options normally carry scalar strings. An invalid
                // template override must not trigger array-to-string warnings
                // or silently turn a country into an "Array" option label.
                if (!is_object($option)
                    || !isset($option->text, $option->value)
                    || !is_string($option->text)
                    || !is_string($option->value)
                    || $option->value !== $code) {
                    throw new \UnexpectedValueException('Malformed country selection option');
                }

                $options[] = $option;
            }

            usort(
                $options,
                static fn ($left, $right): int => strnatcasecmp((string) $left->text, (string) $right->text)
            );

            return $options;
        } catch (\Throwable $e) {
            // Iterators and malformed sorting keys can fail after loading.
            // Never expose a partial selector or break the administrator page.
            self::reportDatabaseError($e);

            return [];
        }
    }

    /**
     * Resolve a two-letter ISO country code to the configured alpha-3 code.
     *
     * Validates the input before querying and verifies the stored result.
     * Unsupported codes, invalid result types and database errors return
     * the configured fallback without PHP 8 conversion warnings.
     *
     * @param DatabaseInterface $db Current Joomla database connection.
     * @param string $iso2 ISO alpha-2 country identifier.
     * @param string $fallback Alpha-3 code used when the lookup fails.
     * @return string Resolved alpha-3 code or the fallback value.
     */
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
            $rawAlpha3 = $db->loadResult();
            $alpha3 = is_string($rawAlpha3) ? strtoupper(trim($rawAlpha3)) : '';
        } catch (\Throwable $e) {
            self::reportDatabaseError($e);
            return $fallback;
        }

        return preg_match('/^[A-Z]{3}$/D', $alpha3) ? $alpha3 : $fallback;
    }

    /**
     * Render an escaped flag icon for an ISO alpha-3 country identifier.
     *
     * Retrieves the saved country, honours the component CSS flag setting,
     * and otherwise builds a validated image URL for the flag graphic.
     * Invalid country identifiers or missing records return no markup.
     * Malformed optional country or fallback picture fields are ignored
     * rather than being converted from arrays under PHP 8.
     * Reject malformed country row types and configuration toggles.
     *
     * @param DatabaseInterface $db Current Joomla database connection.
     * @param string $countryCode ISO alpha-3 country identifier.
     * @return string Escaped flag HTML, or empty text if unavailable.
     */
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

        if (!is_object($country)) {
            return '';
        }

        $params = ComponentHelper::getParams('com_sportsmanagement');
        $rawAlpha2 = $country->alpha2 ?? '';
        $alpha2 = is_string($rawAlpha2) ? strtolower(trim($rawAlpha2)) : '';
        $alpha2 = preg_match('/^[a-z]{2}$/D', $alpha2) ? $alpha2 : '';
        $rawName = $country->name ?? null;
        $label = Text::_(is_string($rawName) && trim($rawName) !== '' ? $rawName : $countryCode);

        $cssFlagMode = $params->get('cfg_flags_css', 0);

        if ($cssFlagMode === 1 || $cssFlagMode === '1' || $cssFlagMode === true) {
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

        $rawPicture = $country->picture ?? null;
        $fallbackPicture = $params->get('ph_flags', '');
        $path = $alpha2 !== ''
            ? 'images/com_sportsmanagement/database/flags/' . $alpha2 . '.png'
            : (is_string($rawPicture) && trim($rawPicture) !== ''
                ? $rawPicture
                : (is_string($fallbackPicture) ? $fallbackPicture : ''));
        $url = self::imageUrl($path);

        return $url !== ''
            ? '<img src="' . self::escape($url)
                . '" alt="' . self::escape($label) . '" title="' . self::escape($label) . '" />'
            : '';
    }

    /**
     * Log country lookup failures without exposing database details in HTML.
     *
     * The exception class is sufficient for categorising failures without
     * leaking SQL queries, credentials or private connection endpoints.
     *
     * @param \Throwable $e Country database failure to log.
     * @return void
     */
    private static function reportDatabaseError(\Throwable $e): void
    {
        Log::add(__METHOD__ . ': country query failed (' . get_class($e) . ').', Log::ERROR, 'com_sportsmanagement');
    }

    /**
     * Construct an allowed flag URL from a stored flag image path.
     *
     * Rejects URL credentials, unsupported schemes, path traversal, control
     * characters, encoded schemes and backslash-based paths before resolving a site-relative
     * URL against the Joomla root.
     *
     * @param string $value Stored flag path or HTTP(S) URL.
     * @return string Allowed image URL, or empty text for unsafe input.
     */
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

        // Decode and validate each layer, including paths that reveal ../
        // only after several rounds of URL decoding. Reject undecoded
        // escapes if the bounded loop is exhausted.
        for ($depth = 0; $depth < 8; $depth++) {
            if (preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $decoded)
            || str_starts_with($decoded, '//')
            || preg_match('~^[a-z][a-z0-9+.-]*:~i', $decoded)
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

        // Nested percent escapes remaining after eight rounds could be
        // interpreted by a proxy or server in a subsequent decoding pass.
        if (preg_match('/%[0-9a-f]{2}/i', $decoded) === 1
            || preg_match('~(?:^|/)\\.{1,2}(?:/|$)~', $decoded)
            || str_starts_with($decoded, '//')
            || preg_match('~^[a-z][a-z0-9+.-]*:~i', $decoded)
            || preg_match('/[\\x00-\\x1F\\x7F]/', $decoded)
            || str_contains($decoded, chr(92))
            || str_contains($decoded, '?') || str_contains($decoded, '#')) {
            return '';
        }

        return rtrim(Uri::root(), '/') . '/' . $path;
    }

    /**
     * Encode flag image URLs and accessible labels as safe HTML attributes.
     *
     * @param string $value Untrusted image URL or country display name.
     * @return string UTF-8 HTML-escaped attribute value.
     */
    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
