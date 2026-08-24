<?php
namespace Diddipoeler\Module\SportsManagementProjectMap\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

final class ProjectMapHelper
{
    public function getMapData($seasonIds, DatabaseInterface $db): array
    {
        $projects = $this->getData($seasonIds, $db);

        return [
            'projects' => $projects,
            'javascript' => 'var simplemaps_worldmap_mapdata = ' . json_encode([
                'main_settings' => $this->getMainSettings(),
                'state_specific' => $this->createStateSpecific($projects),
                'regions' => $this->createRegions($projects),
                'locations' => (object) [],
                'labels' => (object) [],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';',
        ];
    }

    public function getData($seasonIds, DatabaseInterface $db): array
    {
        $seasonIds = $this->normaliseSeasonIds($seasonIds);

        if ($seasonIds === []) {
            return [];
        }

        $query = $db->getQuery(true)
            ->select([
                'MAX(pro.id) AS id',
                $db->quoteName('pro.name'),
                "CONCAT_WS(':', pro.id, pro.alias) AS project_slug",
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
            ->where($db->quoteName('pro.season_id') . ' IN (' . implode(',', $seasonIds) . ')')
            ->group($db->quoteName('le.country'))
            ->order($db->quoteName('le.country') . ' ASC, ' . $db->quoteName('pro.name') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

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
                    'name' => (string) ($project->federation_name ?? ''),
                    'description' => '<img src="' . htmlspecialchars((string) ($project->federation_picture ?? ''), ENT_QUOTES, 'UTF-8') . '" style="width: 75px" alt="">',
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
            $countryPicture = htmlspecialchars((string) ($project->country_picture ?? ''), ENT_QUOTES, 'UTF-8');
            $leaguePicture = htmlspecialchars((string) ($project->league_picture ?? ''), ENT_QUOTES, 'UTF-8');

            $stateSpecific[$country] = [
                'name' => '<img src="' . $countryPicture . '" alt="">' . Text::_((string) ($project->country_name ?? '')),
                'image_url' => (string) ($project->league_picture ?? ''),
                'image_position' => 'manual',
                'image_size' => 0.2,
                'image_x' => 0.55,
                'image_y' => 0.4,
                'border_hover_color' => '#d13c12',
                'image_color' => '#e1ba7d',
                'description' => '<img src="' . $leaguePicture . '" style="width: 50px" alt="">' . Text::_((string) ($project->liganame ?? '')) . ' :<br>' . Text::_((string) ($project->name ?? '')),
                'color' => 'default',
                'hover_color' => 'default',
                'url' => $link,
            ];
        }

        ksort($stateSpecific);

        return $stateSpecific;
    }

    public function toJavascriptObjectBody(array $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        return is_string($json) && strlen($json) >= 2 ? substr($json, 1, -1) : '';
    }

    private function normaliseSeasonIds($seasonIds): array
    {
        if (!is_array($seasonIds)) {
            $seasonIds = preg_split('/\s*,\s*/', trim((string) $seasonIds), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        $seasonIds = array_values(array_unique(array_filter(array_map('intval', $seasonIds), static fn (int $id): bool => $id > 0)));

        return $seasonIds;
    }
}
