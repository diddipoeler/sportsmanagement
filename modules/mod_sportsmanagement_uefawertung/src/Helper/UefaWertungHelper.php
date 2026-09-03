<?php
/**
 * Joomla 5/6 data helper for the SportsManagement UEFA ranking module.
 *
 * @version    3.8.0
 * @author     diddipoeler
 * @copyright  (C) 2015-2026
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementUefaWertung\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class UefaWertungHelper
{
    /**
     * @return array{seasons:array<int,string>,rankings:array<int,array{country:string,points:array<string,float>,total:float}>}
     */
    public function getData(
        Registry $params,
        CMSApplicationInterface $app,
        DatabaseInterface $fallbackDatabase
    ): array {
        $seasonId = (int) $params->get('s', 0);

        if ($seasonId <= 0) {
            return ['seasons' => [], 'rankings' => []];
        }

        $db = SportsManagementDatabaseResolver::resolve(
            $fallbackDatabase,
            (int) $params->get('cfg_which_database', 0)
        );

        try {
            $seasonName = $this->seasonName($db, $seasonId);

            if ($seasonName === '') {
                return ['seasons' => [], 'rankings' => []];
            }

            $seasons = $this->rankingSeasons($db, $seasonName);

            if ($seasons === []) {
                return ['seasons' => [], 'rankings' => []];
            }

            $rankings = $this->rankings($db, $seasons);

            return [
                'seasons' => $seasons,
                'rankings' => $rankings,
            ];
        } catch (\Throwable $error) {
            $app->enqueueMessage($error->getMessage(), 'error');

            return ['seasons' => [], 'rankings' => []];
        }
    }

    private function seasonName(DatabaseInterface $db, int $seasonId): string
    {
        $query = $db->getQuery(true)
            ->select($db->quoteName('name'))
            ->from($db->quoteName('#__sportsmanagement_season'))
            ->where($db->quoteName('id') . ' = ' . $seasonId);
        $db->setQuery($query, 0, 1);

        return trim((string) $db->loadResult());
    }

    /** @return array<int,string> */
    private function rankingSeasons(DatabaseInterface $db, string $seasonName): array
    {
        $query = $db->getQuery(true)
            ->select($db->quoteName('season'))
            ->from($db->quoteName('#__sportsmanagement_uefawertung'))
            ->where($db->quoteName('season') . ' <= ' . $db->quote($seasonName))
            ->group($db->quoteName('season'))
            ->order($db->quoteName('season') . ' DESC');
        $db->setQuery($query, 0, 5);
        $seasons = array_values(array_filter(
            array_map('strval', $db->loadColumn() ?: []),
            static fn(string $value): bool => $value !== ''
        ));

        sort($seasons, SORT_NATURAL);

        return $seasons;
    }

    /**
     * @param array<int,string> $seasons
     * @return array<int,array{country:string,points:array<string,float>,total:float}>
     */
    private function rankings(DatabaseInterface $db, array $seasons): array
    {
        $quotedSeasons = array_map([$db, 'quote'], $seasons);
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('country'),
                $db->quoteName('season'),
                $db->quoteName('points'),
            ])
            ->from($db->quoteName('#__sportsmanagement_uefawertung'))
            ->where($db->quoteName('season') . ' IN (' . implode(',', $quotedSeasons) . ')')
            ->order([
                $db->quoteName('country') . ' ASC',
                $db->quoteName('season') . ' ASC',
            ]);
        $db->setQuery($query);

        $countries = [];

        foreach ($db->loadObjectList() ?: [] as $row) {
            $country = trim((string) ($row->country ?? ''));
            $season = trim((string) ($row->season ?? ''));

            if ($country === '' || !in_array($season, $seasons, true)) {
                continue;
            }

            $countries[$country][$season] = (float) ($row->points ?? 0);
        }

        $rankings = [];

        foreach ($countries as $country => $points) {
            if (count(array_intersect_key(array_flip($seasons), $points)) !== count($seasons)) {
                continue;
            }

            $orderedPoints = [];
            $total = 0.0;

            foreach ($seasons as $season) {
                $value = (float) ($points[$season] ?? 0);
                $orderedPoints[$season] = $value;
                $total += $value;
            }

            $rankings[] = [
                'country' => $country,
                'points' => $orderedPoints,
                'total' => $total,
            ];
        }

        usort(
            $rankings,
            static function (array $left, array $right): int {
                $total = $right['total'] <=> $left['total'];

                return $total !== 0 ? $total : strcasecmp($left['country'], $right['country']);
            }
        );

        return $rankings;
    }
}
