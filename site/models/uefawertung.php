<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage uefawertung
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

class sportsmanagementModeluefawertung extends JSMModelLegacy
{
    public string $coefficientyear = '';

    public function __construct()
    {
        parent::__construct();

        $this->coefficientyear = $this->jsmjinput->post->getString('coefficientyear', '');

        if ($this->coefficientyear === '') {
            $this->coefficientyear = $this->jsmjinput->getString('coefficientyear', '');
        }
    }

    public function getcoefficientyears(): array
    {
        $this->jsmquery->clear()
            ->select('season AS id, season AS name')
            ->from('#__sportsmanagement_uefawertung')
            ->group('season')
            ->order('season DESC');

        try {
            $this->jsmdb->setQuery($this->jsmquery);

            return $this->jsmdb->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->jsmapp->enqueueMessage(
                Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
                'error'
            );
            $this->jsmapp->enqueueMessage(
                Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__),
                'error'
            );

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

        if (!$seasons) {
            return [];
        }

        $quotedSeasons = array_map(
            fn(string $season): string => $this->jsmdb->quote($season),
            $seasons
        );

        $this->jsmquery->clear()
            ->select('*')
            ->from('#__sportsmanagement_uefawertung')
            ->where('season IN (' . implode(',', $quotedSeasons) . ')')
            ->order('season ASC');

        $this->jsmdb->setQuery($this->jsmquery);
        $rows = $this->jsmdb->loadObjectList() ?: [];

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

        $this->jsmquery->clear()
            ->select('season')
            ->from('#__sportsmanagement_uefawertung')
            ->where('season <= ' . $this->jsmdb->quote($coefficientyear))
            ->group('season')
            ->order('season DESC')
            ->setLimit(5);

        $this->jsmdb->setQuery($this->jsmquery);

        return array_values(array_map('strval', $this->jsmdb->loadColumn() ?: []));
    }
}
