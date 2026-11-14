<?php
/**
 * Native Joomla 5/6 data and routing helper for the SportsManagement Project Map module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementProjectMap\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

final class ProjectMapHelper
{
    private const MAX_SEASONS = 50;
    private const MAX_FILTER_VALUES = 200;
    private const MAX_FILTER_BYTES = 4096;
    private const MAX_PROJECT_ROWS = 5000;

    /**
     * Assemble the complete project-map payload for the frontend template.
     * Loads eligible projects for the requested seasons and combines them
     * with map appearance, regional groupings and per-country links that
     * the JavaScript map renderer expects.
     * @param mixed $seasonIds Season identifiers from the module settings.
     * @param DatabaseInterface $db Active SportsManagement database connection.
     * @return array{projects:array,options:array} Project rows and map settings.
     */
    public function getMapData($seasonIds, DatabaseInterface $db): array
    {
        $projects = $this->getData($seasonIds, $db);

        return [
            'projects' => $projects,
            'options' => [
                'main_settings' => $this->getMainSettings(),
                'state_specific' => $this->createStateSpecific($projects),
                'regions' => $this->createRegions($projects),
                'locations' => (object) [],
                'labels' => (object) [],
            ],
        ];
    }

    /**
     * Fetch current top-level league projects for map countries.
     * Normalises season selectors, joins project, league, country and federation
     * records, and retains the latest project per country using a bounded
     * query that is compatible with MySQL ONLY_FULL_GROUP_BY.
     * Discards malformed result records before building country slugs or
     * accessing project properties in PHP 8.
     * @param mixed $seasonIds Season IDs or delimited legacy values.
     * @param DatabaseInterface $db Connection used for the project query.
     * @return array<int,object> Most recent matching project for each country.
     */
    public function getData($seasonIds, DatabaseInterface $db): array
    {
        $seasonIds = $this->normaliseSeasonIds($seasonIds);

        if ($seasonIds === []) {
            return [];
        }

        $query = $db->createQuery()
            ->select([
                $db->quoteName('pro.id'),
                $db->quoteName('pro.name'),
                $db->quoteName('pro.alias', 'project_alias'),
                $db->quoteName('le.name', 'liganame'),
                $db->quoteName('le.country'),
                $db->quoteName('le.picture', 'league_picture'),
                $db->quoteName('pro.picture', 'project_picture'),
                $db->quoteName('c.alpha2', 'country_alpha2'),
                $db->quoteName('c.name', 'country_name'),
                $db->quoteName('c.picture', 'country_picture'),
                $db->quoteName('c.federation', 'country_federation'),
                $db->quoteName('f.name', 'federation_name'),
                $db->quoteName('f.picture', 'federation_picture'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'pro'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_league', 'le') . ' ON ' . $db->quoteName('le.id') . ' = ' . $db->quoteName('pro.league_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_countries', 'c') . ' ON ' . $db->quoteName('c.alpha3') . ' = ' . $db->quoteName('le.country'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_federations', 'f') . ' ON ' . $db->quoteName('f.id') . ' = ' . $db->quoteName('c.federation'))
            ->where($db->quoteName('le.published_act_season') . ' = 1')
            ->where('(' . $db->quoteName('le.league_level') . ' = 1 OR ' . $db->quoteName('le.league_level') . ' = 21)')
            ->whereIn($db->quoteName('pro.season_id'), $seasonIds, ParameterType::INTEGER)
            ->order($db->quoteName('le.country') . ' ASC, ' . $db->quoteName('pro.id') . ' DESC');

        // Map only needs one project per country. Bound the intermediate
        // result set instead of loading an unbounded historical project list.
        $db->setQuery($query, 0, self::MAX_PROJECT_ROWS);

        // Grouping only by country while selecting unrelated project and league
        // columns is rejected by MySQL ONLY_FULL_GROUP_BY. Choose the highest
        // project ID for each country in PHP, keeping all selected columns from
        // the same project row instead of mixing MAX(id) with another row's name.
        $projectsByCountry = [];

        foreach ($db->loadObjectList() ?: [] as $project) {
            if (!is_object($project)) {
                continue;
            }

            $country = $project->country ?? null;
            $rawId = $project->id ?? null;
            $projectId = filter_var($rawId, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);

            if (!is_string($country) || trim($country) === '' || $projectId === false) {
                continue;
            }

            $country = trim($country);
            $alias = $project->project_alias ?? '';
            $project->project_slug = $projectId . ':' . (is_string($alias) ? $alias : '');

            if (!isset($projectsByCountry[$country])
                || $projectId > (int) ($projectsByCountry[$country]->id ?? 0)) {
                $projectsByCountry[$country] = $project;
            }
        }

        ksort($projectsByCountry);

        return array_values($projectsByCountry);
    }

    /**
     * Return the default appearance and navigation configuration for the map.
     * Defines colours, borders, hover behaviour, popup style and zoom options
     * as an array for the frontend JavaScript map implementation.
     * @return array<string,mixed> Default map renderer configuration.
     */
    public function getMainSettings(): array
    {
        return [
            'width' => 'responsive',
            'background_color' => '#FFFFFF',
            'background_transparent' => 'yes',
            'popups' => 'detect',
            'state_description' => 'State description',
            'state_color' => '#88A4BC',
            'state_hover_color' => '#3B729F',
            'border_size' => 1.5,
            'border_color' => '#ffffff',
            'all_states_inactive' => 'no',
            'all_states_zoomable' => 'no',
            'location_description' => 'Location description',
            'location_color' => '#FF0067',
            'location_opacity' => 0.8,
            'location_hover_opacity' => 1,
            'location_url' => '',
            'location_size' => 25,
            'location_type' => 'square',
            'location_border_color' => '#FFFFFF',
            'location_border' => 2,
            'location_hover_border' => 2.5,
            'all_locations_inactive' => 'no',
            'all_locations_hidden' => 'no',
            'label_color' => '#ffffff',
            'label_hover_color' => '#ffffff',
            'label_size' => 22,
            'label_font' => 'Arial',
            'hide_labels' => 'no',
            'manual_zoom' => 'no',
            'back_image' => 'no',
            'arrow_box' => 'no',
            'navigation_size' => '40',
            'navigation_color' => '#f7f7f7',
            'navigation_border_color' => '#636363',
            'initial_back' => 'no',
            'initial_zoom' => -1,
            'initial_zoom_solo' => 'no',
            'region_opacity' => 1,
            'region_hover_opacity' => 0.6,
            'zoom_out_incrementally' => 'yes',
            'zoom_percentage' => 0.99,
            'zoom_time' => 0.5,
            'popup_color' => 'white',
            'popup_opacity' => 0.9,
            'popup_shadow' => 1,
            'popup_corners' => 5,
            'popup_font' => '12px/1.5 Verdana, Arial, Helvetica, sans-serif',
            'popup_nocss' => 'no',
            'div' => 'map',
            'auto_load' => 'yes',
            'rotate' => '0',
            'url_new_tab' => 'no',
            'images_directory' => 'default',
            'import_labels' => 'no',
            'fade_time' => 0.1,
            'link_text' => 'View Website',
        ];
    }

    /**
     * Group project countries into their international federations.
     * Creates federation region metadata and deduplicates country codes.
     * Treats translated federation names as untrusted HTML text and escapes
     * their labels, just as country and league names are escaped elsewhere
     * in the module. Invalid image URLs are omitted from map popups.
     * @param array<int,object> $projects Country-specific map projects.
     * @return array<string,array> Region descriptions and country lists.
     */
    public function createRegions(array $projects): array
    {
        $regions = [];
        $states = [];

        foreach ($projects as $project) {
            $federationId = (string) ($project->country_federation ?? '');
            $country = (string) ($project->country_alpha2 ?? '');

            if ($federationId === '' || $country === '') {
                continue;
            }

            if (!isset($regions[$federationId])) {
                $regions[$federationId] = [
                    'name' => htmlspecialchars(
                        Text::_((string) ($project->federation_name ?? '')),
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        'UTF-8'
                    ),
                    'description' => ($federationImage = self::safeImageUrl($project->federation_picture ?? null)) !== ''
                        ? '<img src="' . htmlspecialchars($federationImage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" style="width: 75px" alt="">'
                        : '',
                    'states' => [],
                ];
            }

            $states[$federationId][$country] = true;
        }

        foreach ($states as $federationId => $countries) {
            $regions[$federationId]['states'] = array_keys($countries);
        }

        ksort($regions);

        return $regions;
    }

    /**
     * Build country-specific map labels, popups and ranking destinations.
     * Constructs Joomla ranking routes for each selected project, translates
     * country and competition labels, and escapes database text before
     * insertion into JavaScript-driven map HTML. Only permitted HTTP(S)
     * or safe local image references reach popup markup.
     * @param array<int,object> $projects Selected country projects.
     * @return array<string,array> Country settings indexed by code.
     */
    public function createStateSpecific(array $projects): array
    {
        $stateSpecific = [];

        foreach ($projects as $project) {
            $country = (string) ($project->country_alpha2 ?? '');

            if ($country === '') {
                continue;
            }

            $routeParameters = [
                'cfg_which_database' => 0,
                's' => 0,
                'p' => (string) ($project->project_slug ?? ''),
                'type' => 0,
                'r' => 0,
                'from' => 0,
                'to' => 0,
                'division' => 0,
                'Itemid' => -1,
            ];
            $link = SiteRouteHelper::view('ranking', $routeParameters);
            $countryImage = self::safeImageUrl($project->country_picture ?? null);
            $leagueImage = self::safeImageUrl($project->league_picture ?? null);
            $countryPicture = htmlspecialchars($countryImage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $leaguePicture = htmlspecialchars($leagueImage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            // Translation lookups may fall back to database-supplied names.
            // Always escape names inserted into the map's HTML popups.
            $countryName = htmlspecialchars(Text::_((string) ($project->country_name ?? '')), ENT_QUOTES, 'UTF-8');
            $leagueName = htmlspecialchars(Text::_((string) ($project->liganame ?? '')), ENT_QUOTES, 'UTF-8');
            $projectName = htmlspecialchars(Text::_((string) ($project->name ?? '')), ENT_QUOTES, 'UTF-8');

            $stateSpecific[$country] = [
                'name' => ($countryImage !== '' ? '<img src="' . $countryPicture . '" alt="">' : '') . $countryName,
                'image_url' => $leagueImage,
                'image_position' => 'manual',
                'image_size' => 0.2,
                'image_x' => 0.55,
                'image_y' => 0.4,
                'border_hover_color' => '#d13c12',
                'image_color' => '#e1ba7d',
                'description' => ($leagueImage !== '' ? '<img src="' . $leaguePicture . '" style="width: 50px" alt="">' : '')
                    . $leagueName . ' :<br>' . $projectName,
                'color' => 'default',
                'hover_color' => 'default',
                'url' => $link,
            ];
        }

        ksort($stateSpecific);

        return $stateSpecific;
    }

    /**
     * Validate a country, federation or league picture URL before map output.
     *
     * Allows HTTP(S) URLs without credentials and local image paths without
     * traversal, query fragments, encoded separators or control characters.
     *
     * @param mixed $raw Imported legacy image reference.
     * @return string Allowed image reference or an empty string.
     */
    private static function safeImageUrl(mixed $raw): string
    {
        if (!is_string($raw) || strlen($raw) > 2048) {
            return '';
        }

        $url = trim($raw);
        if ($url === '' || preg_match('/[\x00-\x20\x7F\\\\]/', $url)
            || str_starts_with($url, '//')) {
            return '';
        }

        if (preg_match('~^https?://~i', $url)) {
            $parts = parse_url($url);
            return filter_var($url, FILTER_VALIDATE_URL) !== false
                && is_array($parts)
                && !empty($parts['host'])
                && !isset($parts['user']) && !isset($parts['pass'])
                    ? $url
                    : '';
        }

        if (preg_match('~^[a-z][a-z0-9+.-]*:~i', $url)
            || strpbrk($url, '%?#') !== false
            || preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $url)) {
            return '';
        }

        return ltrim($url, '/');
    }

    /**
     * Encode a map option array for embedding in a JavaScript object.
     * Uses JSON_HEX escaping to protect special HTML characters and drops
     * the outer JSON braces for compatibility with the legacy map template.
     * @param array $data Map configuration data to encode.
     * @return string JSON object body, or an empty string on failure.
     */
    public function toJavascriptObjectBody(array $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        return is_string($json) && strlen($json) >= 2 ? substr($json, 1, -1) : '';
    }

    /**
     * Validate and limit legacy season-filter IDs before SQL binding.
     * Accepts numeric scalars or delimited strings, enforces input size and
     * item limits, rejects malformed/overflowed numbers, and returns a
     * deduplicated list of safe positive integers.
     * @param mixed $seasonIds Raw season filter input.
     * @return array<int> Valid unique season identifiers.
     */
    private function normaliseSeasonIds($seasonIds): array
    {
        if (is_int($seasonIds) || is_string($seasonIds)) {
            $seasonIds = [$seasonIds];
        }

        if (!is_array($seasonIds)) {
            return [];
        }

        $ids = [];

        foreach (array_slice($seasonIds, 0, self::MAX_FILTER_VALUES) as $entry) {
            if ((!is_int($entry) && !is_string($entry))
                || (is_string($entry) && strlen($entry) > self::MAX_FILTER_BYTES)) {
                continue;
            }

            $values = is_int($entry)
                ? [$entry]
                : (preg_split('/[\s,;]+/', $entry, -1, PREG_SPLIT_NO_EMPTY) ?: []);

            foreach ($values as $value) {
                if (is_int($value)) {
                    if ($value > 0) {
                        $ids[$value] = $value;

                        if (count($ids) >= self::MAX_SEASONS) {
                            return array_values($ids);
                        }
                    }
                    continue;
                }

                if (!preg_match('/^[0-9]+$/D', $value)) {
                    continue;
                }

                // Joomla module selections may store historical IDs with
                // leading zeros; normalize decimal strings before validating.
                $digits = ltrim($value, '0');

                if ($digits === '') {
                    continue;
                }

                $id = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

                if ($id !== false) {
                    $ids[$id] = $id;

                    if (count($ids) >= self::MAX_SEASONS) {
                        return array_values($ids);
                    }
                }
            }
        }

        return array_values($ids);
    }
}
