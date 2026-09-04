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
use Joomla\Registry\Registry;

final class ActSeasonHelper
{
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

        $databaseSelector = (int) $componentParams->get('cfg_which_database', 0);
        $db = $this->database($databaseSelector, $fallbackDatabase);
        $query = $db->getQuery(true)
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
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('pro.current_round'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_countries', 'co') . ' ON ' . $db->quoteName('co.alpha3') . ' = ' . $db->quoteName('le.country'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_federations', 'fed') . ' ON ' . $db->quoteName('fed.id') . ' = ' . $db->quoteName('co.federation'))
            ->where($db->quoteName('le.published_act_season') . ' = 1')
            ->where($db->quoteName('pro.season_id') . ' IN (' . implode(',', $ids) . ')')
            ->order($db->quoteName('le.country') . ' ASC, ' . $db->quoteName('pro.name') . ' ASC');

        try {
            $db->setQuery($query);
            $list = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
            $list = [];
        }

        $federations = [];
        $countries = [];
        foreach ($list as $row) {
            $row->project_slug = $this->slug((int) $row->id, (string) ($row->project_alias ?? ''));
            $row->roundcode = $this->slug((int) ($row->round_id ?? 0), (string) ($row->round_alias ?? ''));
            $row->database_selector = $databaseSelector;
            $row->country_label = Text::_((string) ($row->country_name ?: $row->country));
            $row->flag_html = $this->flagHtml($row, $componentParams);
            $fedId = (int) ($row->federation ?? 0);

            if ($fedId > 0 && !isset($federations[$fedId])) {
                $federations[$fedId] = (object) [
                    'id' => $fedId,
                    'name' => (string) ($row->federation_name ?: $fedId),
                ];
            }

            if (!isset($countries[$fedId][$row->country])) {
                $countries[$fedId][$row->country] = (object) [
                    'alpha3' => (string) $row->country,
                    'name' => $row->country_label,
                    'flag_html' => $row->flag_html,
                ];
            }
        }

        uasort(
            $federations,
            static fn(object $a, object $b): int => strcasecmp(Text::_($a->name), Text::_($b->name))
        );
        foreach ($countries as &$fedCountries) {
            uasort(
                $fedCountries,
                static fn(object $a, object $b): int => strcasecmp($a->name, $b->name)
            );
        }
        unset($fedCountries);

        return [
            'list' => $list,
            'federations' => $federations,
            'countriesByFederation' => $countries,
        ];
    }

    private function normaliseIds(mixed $ids): array
    {
        if (is_string($ids)) {
            $ids = preg_split('/[\s,;]+/', $ids, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        return array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static fn(int $id): bool => $id > 0
        )));
    }

    private function flagHtml(object $row, Registry $params): string
    {
        $alpha3 = strtoupper((string) ($row->country ?? ''));
        $alpha2 = strtolower((string) ($row->alpha2 ?? ''));
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

        return $path === ''
            ? ''
            : '<img src="' . htmlspecialchars(Uri::root() . ltrim($path, '/'), ENT_QUOTES, 'UTF-8')
                . '" alt="' . $label . '" title="' . $label . '" />';
    }

    private function slug(int $id, string $alias): string
    {
        return $id > 0 ? $id . ':' . $alias : '';
    }

    private function database(int $selector, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve($fallbackDatabase, $selector);
    }
}
