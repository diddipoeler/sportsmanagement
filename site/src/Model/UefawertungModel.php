<?php
/**
 * Native Joomla 5/6 model for the UEFA coefficient view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class UefawertungModel extends SportsManagementProjectModel
{
    public string $coefficientyear = '';

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        $this->coefficientyear = $input->post->getString('coefficientyear', '');

        if ($this->coefficientyear === '') {
            $this->coefficientyear = $input->getString('coefficientyear', '');
        }
    }

    public function getcoefficientyears(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('season', 'id'),
                $db->quoteName('season', 'name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_uefawertung'))
            ->group($db->quoteName('season'))
            ->order($db->quoteName('season') . ' DESC');

        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->reportDatabaseError($e);

            return [];
        }
    }

    public function getSeasonNames($coefficientyear = ''): array
    {
        return $this->getCoefficientSeasons((string) $coefficientyear);
    }

    public function getcoefficientyearspoints($coefficientyear = ''): array
    {
        $seasons = $this->getCoefficientSeasons((string) $coefficientyear);

        if ($seasons === []) {
            return [];
        }

        $db = $this->getDatabase();
        $quotedSeasons = array_map(
            static fn(string $season): string => $db->quote($season),
            $seasons
        );
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_uefawertung'))
            ->where($db->quoteName('season') . ' IN (' . implode(',', $quotedSeasons) . ')')
            ->order($db->quoteName('season') . ' ASC');

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->reportDatabaseError($e);

            return [];
        }

        $byCountry = [];

        foreach ($rows as $row) {
            $country = (string) ($row->country ?? '');
            $season = (string) ($row->season ?? '');

            if ($country === '' || $season === '') {
                continue;
            }

            $byCountry[$country][$season] = (float) ($row->points ?? 0);
        }

        $ranking = [];

        foreach ($byCountry as $country => $pointsBySeason) {
            $entry = new \stdClass();
            $entry->team = $country;
            $total = 0.0;

            foreach ($seasons as $season) {
                $points = (float) ($pointsBySeason[$season] ?? 0);
                $entry->{$season} = $points;
                $total += $points;
            }

            $entry->total = $total;
            $ranking[] = $entry;
        }

        usort(
            $ranking,
            static fn(object $first, object $second): int => ((float) $second->total) <=> ((float) $first->total)
        );

        return $ranking;
    }

    private function getCoefficientSeasons(string $coefficientyear): array
    {
        if ($coefficientyear === '') {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('season'))
            ->from($db->quoteName('#__sportsmanagement_uefawertung'))
            ->where($db->quoteName('season') . ' <= ' . $db->quote($coefficientyear))
            ->group($db->quoteName('season'))
            ->order($db->quoteName('season') . ' DESC');

        $db->setQuery($query, 0, 5);

        return array_values(array_map('strval', $db->loadColumn() ?: []));
    }

    private function reportDatabaseError(\Throwable $e): void
    {
        $app = $this->siteApplication();
        $app->enqueueMessage(
            Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
            'error'
        );
        $app->enqueueMessage(
            Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__),
            'error'
        );
    }
}
