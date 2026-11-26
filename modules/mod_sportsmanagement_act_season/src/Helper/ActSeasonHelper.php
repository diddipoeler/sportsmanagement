<?php
/**
 * Native Joomla 5/6 data helper for the current-season module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementActSeason\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class ActSeasonHelper
{
    /**
     * Load current-season projects and group their countries by federation.
     * Validates selected season IDs and the database configuration before
     * accessing the native resolver. An invalid explicit database selector
     * must not fall back to the default Joomla database. Uses bound SQL
     * parameters to load published projects in current-season leagues.
     * Builds translated labels, safe flags and project/round route segments.
     * @param mixed $seasonIds Season IDs or legacy selection values.
     * @param Registry $componentParams SportsManagement component options.
     * @param CMSApplicationInterface $app Joomla site application for messages.
     * @param DatabaseInterface $fallbackDatabase Default Joomla database.
     * @return array{list:array,federations:array,countriesByFederation:array} Season listing and federation groups.
     */
    public function getData(
        mixed $seasonIds,
        Registry $componentParams,
        CMSApplicationInterface $app,
        DatabaseInterface $fallbackDatabase
    ): array {
        $ids = $this->normaliseIds($seasonIds);
        if (!$ids) {
            return ['list' => [], 'federations' => [], 'countriesByFederation' => []];
        }

        $rawDatabaseSelector = $componentParams->get('cfg_which_database', 0);

        // A malformed external database selector must not be silently
        // converted to 0 (Joomla's main connection) and expose other data.
        if (!self::isValidDatabaseSelector($rawDatabaseSelector)) {
            return ['list' => [], 'federations' => [], 'countriesByFederation' => []];
        }

        $databaseSelector = $this->normaliseDatabaseSelector($rawDatabaseSelector);

        try {
            $db = $this->database($databaseSelector, $fallbackDatabase);
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('pro.id'),
                    $db->quoteName('pro.name'),
                    $db->quoteName('pro.alias', 'project_alias'),
                    $db->quoteName('le.name', 'liganame'),
                    $db->quoteName('le.country'),
                    $db->quoteName('le.picture', 'league_picture'),
                    $db->quoteName('pro.picture', 'project_picture'),
                    $db->quoteName('co.alpha2'),
                    $db->quoteName('co.name', 'country_name'),
                    $db->quoteName('co.picture', 'country_picture'),
                    $db->quoteName('co.federation'),
                    $db->quoteName('fed.name', 'federation_name'),
                    $db->quoteName('r.id', 'round_id'),
                    $db->quoteName('r.alias', 'round_alias'),
                ])
                ->from($db->quoteName('#__sportsmanagement_project', 'pro'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_league', 'le') . ' ON ' . $db->quoteName('le.id') . ' = ' . $db->quoteName('pro.league_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('pro.current_round') . ' AND ' . $db->quoteName('r.project_id') . ' = ' . $db->quoteName('pro.id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_countries', 'co') . ' ON ' . $db->quoteName('co.alpha3') . ' = ' . $db->quoteName('le.country'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_federations', 'fed') . ' ON ' . $db->quoteName('fed.id') . ' = ' . $db->quoteName('co.federation'))
                ->where($db->quoteName('le.published_act_season') . ' = 1')
                ->where($db->quoteName('pro.published') . ' = 1')
                ->whereIn($db->quoteName('pro.season_id'), $ids, ParameterType::INTEGER)
                ->order([
                    $db->quoteName('le.country') . ' ASC',
                    $db->quoteName('pro.name') . ' ASC',
                ]);

            $db->setQuery($query);
            $records = $db->loadObjectList();
            $list = is_array($records) ? $records : [];
        } catch (\Throwable $e) {
            \Joomla\CMS\Log\Log::add(__METHOD__ . ': ' . get_class($e), \Joomla\CMS\Log\Log::ERROR, 'jsmerror');
            $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
            $list = [];
        }

        $federations = [];
        $countries = [];
        $validRows = [];

        foreach ($list as $row) {
            // Older imports can contain incomplete records. Never let nested
            // values become "Array" in routes, flag markup or translation keys.
            if (!is_object($row)) {
                continue;
            }

            $projectId = self::positiveId($row->id ?? null);

            if ($projectId === 0) {
                continue;
            }

            $row->project_slug = $this->slug($projectId, self::plainText($row->project_alias ?? null));
            $row->roundcode = $this->slug(
                self::positiveId($row->round_id ?? null),
                self::plainText($row->round_alias ?? null)
            );
            $row->database_selector = $databaseSelector;
            $row->name = self::plainText($row->name ?? null);
            $row->country = strtoupper(self::plainText($row->country ?? null));
            $row->alpha2 = strtolower(self::plainText($row->alpha2 ?? null));
            $row->country_picture = self::plainText($row->country_picture ?? null);
            $countryName = self::plainText($row->country_name ?? null);
            $row->country_label = Text::_($countryName !== '' ? $countryName : $row->country);
            $row->flag_html = $this->flagHtml($row, $componentParams);
            $fedId = self::positiveId($row->federation ?? null);
            $row->federation = $fedId;

            if ($fedId > 0 && !isset($federations[$fedId])) {
                $name = self::plainText($row->federation_name ?? null);
                $federations[$fedId] = (object) [
                    'id' => $fedId,
                    'name' => $name !== '' ? $name : (string) $fedId,
                ];
            }

            $countryCode = $row->country;

            if ($countryCode !== '' && !isset($countries[$fedId][$countryCode])) {
                $countries[$fedId][$countryCode] = (object) [
                    'alpha3' => $countryCode,
                    'name' => $row->country_label,
                    'flag_html' => $row->flag_html,
                ];
            }

            $validRows[] = $row;
        }

        $list = $validRows;

        // Countries without a federation must remain visible in the tab layout.
        if (!empty($countries[0])) {
            $federations[0] = (object) [
                'id' => 0,
                'name' => Text::_('JNONE'),
            ];
        }

        uasort(
            $federations,
            static fn (object $a, object $b): int => strcasecmp(Text::_($a->name), Text::_($b->name))
        );
        foreach ($countries as &$fedCountries) {
            uasort(
                $fedCountries,
                static fn (object $a, object $b): int => strcasecmp($a->name, $b->name)
            );
        }
        unset($fedCountries);

        return [
            'list' => $list,
            'federations' => $federations,
            'countriesByFederation' => $countries,
        ];
    }

    /**
     * Verify a stored database selector without introducing a default value.
     *
     * Accepts only local/external modes zero and one, including bounded
     * zero-padded historical configuration values. Nested data, booleans,
     * negative values and overflowing numbers fail closed before any query
     * touches the default or a remote SportsManagement database.
     *
     * @param mixed $value Raw module database configuration.
     * @return bool Whether the configuration contains a valid selector.
     */
    private static function isValidDatabaseSelector(mixed $value): bool
    {
        return is_int($value)
            ? $value === 0 || $value === 1
            : is_string($value) && strlen($value) <= 64 && preg_match('/^0*[01]$/D', $value) === 1;
    }

    /**
     * Parse a positive SportsManagement record ID from legacy module data.
     *
     * Allows integer IDs and zero-padded decimal strings within PHP's range.
     * Rejects arrays, booleans, negative values, numeric suffixes and large
     * values instead of making unsafe PHP 8 integer casts in route segments.
     *
     * @param mixed $value Raw project, round or federation record ID.
     * @return int Positive ID, or zero for a malformed value.
     */
    private static function positiveId(mixed $value): int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : 0;
        }

        if (!is_string($value) || $value === '' || strlen($value) > 128
            || preg_match('/^[0-9]+$/D', $value) !== 1) {
            return 0;
        }

        $digits = ltrim($value, '0');

        if ($digits === '') {
            return 0;
        }

        $id = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? 0 : $id;
    }

    /**
     * Convert a bounded legacy text field into safe displayable text.
     *
     * Imports sometimes contain nested arrays or objects in nominally text
     * columns. Only strings and finite numeric scalar values are retained;
     * oversized values and control characters are discarded before Joomla
     * translation, flag rendering or URL alias construction.
     *
     * @param mixed $value Raw database display field.
     * @return string Trimmed printable text, or an empty string.
     */
    private static function plainText(mixed $value): string
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            return '';
        }

        if (is_float($value) && !is_finite($value)) {
            return '';
        }

        $value = trim((string) $value);

        return $value !== '' && strlen($value) <= 4096
            && preg_match('/[\\x00-\\x1F\\x7F]/', $value) !== 1
            ? $value : '';
    }

    /**
     * Validate the configured SportsManagement database selector.
     * Converts the two supported database modes, including legacy values
     * with leading zeros. The caller separately rejects invalid selectors.
     * @param mixed $rawSelector Raw database selector setting.
     * @return int Valid selector or zero for malformed input.
     */
    private function normaliseDatabaseSelector(mixed $rawSelector): int
    {
        return self::isValidDatabaseSelector($rawSelector) ? (int) $rawSelector : 0;
    }

    /**
     * Parse a deduplicated list of positive season identifiers.
     * Accepts numeric IDs or legacy delimited strings and rejects
     * nested, malformed or overflowing input before SQL binding.
     * @param mixed $ids Requested season IDs or delimited values.
     * @return array<int> Unique positive season identifiers.
     */
    private function normaliseIds(mixed $ids): array
    {
        if (is_string($ids)) {
            $ids = preg_split('/[\s,;]+/', $ids, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        // Invalid or nested configuration values must not silently become season 1.
        $normalised = [];
        foreach ($ids as $id) {
            if (!is_int($id) && (!is_string($id) || !preg_match('/^[0-9]+$/D', $id))) {
                continue;
            }

            if (is_string($id)) {
                $id = ltrim($id, '0');

                if ($id === '') {
                    continue;
                }
            }

            $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id !== false) {
                $normalised[$id] = $id;
            }
        }

        return array_values($normalised);
    }

    /**
     * Render a federation country's flag in the configured style.
     * Validates country codes before CSS or image rendering and escapes
     * country labels and URLs for safe HTML attribute output.
     * @param object $row Current-season country data.
     * @param Registry $params Flag display and placeholder options.
     * @return string Safe flag HTML or an empty string.
     */
    private function flagHtml(object $row, Registry $params): string
    {
        $alpha3 = strtoupper((string) ($row->country ?? ''));
        $alpha2 = strtolower((string) ($row->alpha2 ?? ''));

        // Country codes are used in CSS classes and image paths: accept ISO-style codes only.
        $alpha3 = preg_match('/^[A-Z]{3}$/D', $alpha3) ? $alpha3 : '';
        $alpha2 = preg_match('/^[a-z]{2}$/D', $alpha2) ? $alpha2 : '';
        $label = htmlspecialchars((string) ($row->country_label ?? $alpha3), ENT_QUOTES, 'UTF-8');

        if ((int) $params->get('cfg_flags_css', 0) === 1) {
            $cssCode = match ($alpha3) {
                'WAL' => 'gb-wls',
                'SCO' => 'gb-sct',
                'GBR' => 'gb-eng',
                default => $alpha2,
            };

            return $cssCode !== ''
                ? '<span class="fi fi-' . htmlspecialchars($cssCode, ENT_QUOTES, 'UTF-8')
                    . '" title="' . $label . '"></span>'
                : '';
        }

        $path = $alpha2 !== ''
            ? 'images/com_sportsmanagement/database/flags/' . $alpha2 . '.png'
            : (string) ($row->country_picture ?? $params->get('ph_flags', ''));

        if ($path === '') {
            return '';
        }

        // Reject untrusted schemes, protocol-relative URLs and traversal paths.
        // A value from a database or module configuration must never become a
        // javascript: URL or escape the site's image directory.
        $path = trim($path);
        if (preg_match('~^https?://~i', $path)) {
            $source = $path;
        } elseif (
            str_starts_with($path, '//')
            || str_starts_with($path, '/')
            || str_contains($path, '\\')
            || preg_match('~(?:^|/)\\.\\.(?:/|$)|[\\x00-\\x1F]|^[a-z][a-z0-9+.-]*:~i', $path)
        ) {
            return '';
        } else {
            $source = Uri::root() . $path;
        }

        return '<img src="' . htmlspecialchars($source, ENT_QUOTES, 'UTF-8')
            . '" alt="' . $label . '" title="' . $label . '" />';
    }

    /**
     * Create a Joomla-compatible numeric ID and alias route segment.
     * @param int $id Positive record identifier.
     * @param string $alias URL alias for the selected record.
     * @return string ID:alias segment or empty text for invalid IDs.
     */
    private function slug(int $id, string $alias): string
    {
        return $id > 0 ? $id . ':' . $alias : '';
    }

    /**
     * Resolve the requested component database via the native resolver.
     * @param int $selector Validated component database selector.
     * @param DatabaseInterface $fallbackDatabase Default Joomla database.
     * @return DatabaseInterface Effective SportsManagement connection.
     */
    private function database(int $selector, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve($fallbackDatabase, $selector);
    }
}
