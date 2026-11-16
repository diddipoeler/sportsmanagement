<?php
/**
 * Install missing SportsManagement sport types selected in component options.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Idempotent installer for the sports enabled in cfg_sport_types.
 *
 * Names are the exact language keys from admin/config.xml, rather than
 * generated from the option values: some option labels have different names.
 * All queries use Joomla's database service and bind values; a missing XML
 * definition does not prevent installation of the base sports-type record.
 */
final class ConfiguredSportTypeInstaller
{
    private const SPORT_NAMES = [
        'soccer' => 'COM_SPORTSMANAGEMENT_ST_SOCCER',
        'tablesoccer' => 'COM_SPORTSMANAGEMENT_ST_TABLESOCCER',
        'hockey' => 'COM_SPORTSMANAGEMENT_ST_HOCKEY',
        'floorball' => 'COM_SPORTSMANAGEMENT_ST_FLOORBALL',
        'skaterhockey' => 'COM_SPORTSMANAGEMENT_ST_SKATER_HOCKEY',
        'american_football' => 'COM_SPORTSMANAGEMENT_ST_AMERICAN_FOOTBALL',
        'icehockey' => 'COM_SPORTSMANAGEMENT_ST_ICEHOCKEY',
        'volleyball' => 'COM_SPORTSMANAGEMENT_ST_VOLLEYBALL',
        'korfball' => 'COM_SPORTSMANAGEMENT_ST_KORFBALL',
        'handball' => 'COM_SPORTSMANAGEMENT_ST_HANDBALL',
        'tennis' => 'COM_SPORTSMANAGEMENT_ST_TENNIS',
        'tabletennis' => 'COM_SPORTSMANAGEMENT_ST_TABLETENNIS',
        'basketball' => 'COM_SPORTSMANAGEMENT_ST_BASKETBALL',
        'australien_rules_football' => 'COM_SPORTSMANAGEMENT_ST_AUSTRALIEN_RULES_FOOTBALL',
        'dart' => 'COM_SPORTSMANAGEMENT_ST_DART',
        'waterpolo' => 'COM_SPORTSMANAGEMENT_ST_WATERPOLO',
        'small_bore_rifle_association' => 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION',
        'fistball' => 'COM_SPORTSMANAGEMENT_ST_FAUSTBALL',
        'golf_billard' => 'COM_SPORTSMANAGEMENT_ST_GOLF_BILLARD',
        'baseball' => 'COM_SPORTSMANAGEMENT_ST_BASEBALL',
    ];

    /**
     * Use the active Joomla database to locate and insert sport definitions.
     *
     * @param DatabaseInterface $database Joomla's selected component database.
     */
    public function __construct(private DatabaseInterface $database)
    {
    }

    /**
     * Install only the missing sports from the saved component configuration.
     *
     * Handles multiple-selection arrays, ignores unknown or malformed choices,
     * checks both the configured translation key and old generated key to avoid
     * duplicates, and inserts the minimal sports-type record for each absent
     * choice. Optional standard positions/events are seeded via the existing
     * database-tool model only when an XML structure is bundled for that sport.
     *
     * @param mixed $selected Value saved as cfg_sport_types in component options.
     * @param callable|null $seedDefaults Optional callback (name, id, token) for
     *     installing the sport's standard positions and events.
     * @return array<string, int> Newly installed sports keyed by option token.
     * @throws \RuntimeException When a sport row cannot be stored.
     */
    public function installMissing(mixed $selected, ?callable $seedDefaults = null): array
    {
        if (is_string($selected)) {
            $selected = [$selected];
        }

        if (!is_array($selected)) {
            return [];
        }

        $installed = [];

        foreach ($selected as $token) {
            if (!is_string($token) || !isset(self::SPORT_NAMES[$token])
                || array_key_exists($token, $installed)) {
                continue;
            }

            $name = self::SPORT_NAMES[$token];

            if ($this->findExistingSportId($token, $name) > 0) {
                continue;
            }

            $record = (object) [
                'name' => $name,
                'icon' => 'images/com_sportsmanagement/database/placeholders/placeholder_21.png',
            ];

            try {
                $saved = $this->database->insertObject('#__sportsmanagement_sports_type', $record);

                if ($saved === false) {
                    throw new \RuntimeException('SportsManagement sport insert failed.');
                }
            } catch (\Throwable $exception) {
                // A concurrent options save can install the same unique sport.
                // Treat it as successful only if a matching row now exists.
                if ($this->findExistingSportId($token, $name) > 0) {
                    continue;
                }

                throw new \RuntimeException('SportsManagement sport insert failed.', 0, $exception);
            }

            $id = (int) ($record->id ?? $this->database->insertid());

            if ($id <= 0) {
                $id = $this->findExistingSportId($token, $name);
            }

            if ($id <= 0) {
                throw new \RuntimeException('SportsManagement sport insert has no valid ID.');
            }

            $installed[$token] = $id;

            // Not every configurable sport ships with standard position XML.
            // Still install its base record; there is nothing further to seed.
            if ($seedDefaults !== null && is_file(
                JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sp_structur/' . $token . '.xml'
            )) {
                $seedDefaults($name, $id, $token);
            }
        }

        return $installed;
    }

    /**
     * Find a previously installed sport, including historical key variants.
     *
     * Legacy installers generated translation keys by uppercasing the option
     * token, which differs from the current labels for selected sports. Both
     * forms are checked so a configuration save never duplicates those rows.
     *
     * @param string $token Validated sport option value.
     * @param string $name Canonical translated sport name from config.xml.
     * @return int Positive existing sport ID, or zero when not installed.
     */
    private function findExistingSportId(string $token, string $name): int
    {
        foreach (array_unique([$name, 'COM_SPORTSMANAGEMENT_ST_' . strtoupper($token)]) as $candidate) {
            $query = $this->database->createQuery()
                ->select($this->database->quoteName('id'))
                ->from($this->database->quoteName('#__sportsmanagement_sports_type'))
                ->where($this->database->quoteName('name') . ' = :configuredSportName')
                ->bind(':configuredSportName', $candidate, ParameterType::STRING);

            $this->database->setQuery($query, 0, 1);
            $id = (int) $this->database->loadResult();

            if ($id > 0) {
                return $id;
            }
        }

        return 0;
    }
}
