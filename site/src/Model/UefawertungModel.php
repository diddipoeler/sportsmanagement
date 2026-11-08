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
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;

final class UefawertungModel extends SportsManagementProjectModel
{
    public string $coefficientyear = '';

    /**
     * Initialise the UEFA coefficient season selector from Joomla's request.
     * Uses the POST value for submitted filters and otherwise falls back to
     * the GET parameter while initialising the shared native project model.
     *
     * @param array $config Optional Joomla model configuration.
     * @param MVCFactoryInterface|null $factory Optional Joomla MVC factory.
     * @return void
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        $this->coefficientyear = $input->post->getString('coefficientyear', '');

        if ($this->coefficientyear === '') {
            $this->coefficientyear = $input->getString('coefficientyear', '');
        }
    }

    /**
     * Retrieve the seasons available in the stored UEFA coefficient table.
     * Reads grouped season identifiers in descending order, preserving the
     * season names needed by the native selection field. Database errors
     * are logged and produce an empty list for the frontend view.
     *
     * @return array<int, object> Available season ID and name records.
     */
    public function getcoefficientyears(): array
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
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

    /**
     * Resolve the five UEFA seasons preceding or including the selected year.
     * Delegates to the common bounded season query so column headers match
     * the exact seasons used by the coefficient totals.
     *
     * @param string $coefficientyear Selected upper season bound.
     * @return array<int, string> Ordered season labels for the table.
     */
    public function getSeasonNames($coefficientyear = ''): array
    {
        return $this->getCoefficientSeasons((string) $coefficientyear);
    }

    /**
     * Aggregate country coefficient points over the selected five-season window.
     * Loads only rows for the season identifiers returned by the bounded lookup,
     * groups points by country and season, fills missing seasons with zero
     * and sorts the total points descending for the native frontend table.
     *
     * @param string $coefficientyear Selected last season of the coefficient window.
     * @return array<int, object> Country rows with season points and total.
     */
    public function getcoefficientyearspoints($coefficientyear = ''): array
    {
        $seasons = $this->getCoefficientSeasons((string) $coefficientyear);

        if ($seasons === []) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_uefawertung'))
            ->whereIn($db->quoteName('season'), $seasons, ParameterType::STRING)
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

    /**
     * Look up the five most recent coefficient seasons up to a selected year.
     * Uses a bound database parameter and explicit five-row result limit;
     * an empty selector or database failure produces an empty season list.
     *
     * @param string $coefficientyear Maximum coefficient season name.
     * @return array<int, string> Up to five descending coefficient seasons.
     */
    private function getCoefficientSeasons(string $coefficientyear): array
    {
        if ($coefficientyear === '') {
            return [];
        }

        try {
            $db = $this->getDatabase();
            $query = $db->createQuery()
                ->select($db->quoteName('season'))
                ->from($db->quoteName('#__sportsmanagement_uefawertung'))
                ->where($db->quoteName('season') . ' <= :coefficientYear')
                ->group($db->quoteName('season'))
                ->order($db->quoteName('season') . ' DESC')
                ->bind(':coefficientYear', $coefficientyear, ParameterType::STRING);

            $db->setQuery($query, 0, 5);

            return array_values(array_map('strval', $db->loadColumn() ?: []));
        } catch (\Throwable $e) {
            $this->reportDatabaseError($e);

            return [];
        }
    }

    /**
     * Record a UEFA coefficient query failure without disclosing private SQL.
     * The full exception is written only to Joomla's error log and the
     * current site request receives a generic translated error message.
     *
     * @param \Throwable $e Database or query exception to record.
     * @return void
     */
    private function reportDatabaseError(\Throwable $e): void
    {
        // Log internal SQL and file details without exposing them to visitors.
        Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');
        $this->siteApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
    }
}
