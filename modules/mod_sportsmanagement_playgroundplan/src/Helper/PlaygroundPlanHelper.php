<?php
/**
 * Native Joomla 5/6 data helper for the SportsManagement Playground Plan module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementPlaygroundPlan\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class PlaygroundPlanHelper
{
    /**
     * Retrieve the upcoming playground fixtures for the module.
     *
     * Selects projects and grounds, queries published upcoming matches, and resolves the venue, logos and route data. Errors are logged and an empty list is returned.
     *
     * @param Registry $params Module filters and display settings.
     * @param CMSApplicationInterface $app Current Joomla application.
     * @param object $module Joomla module object.
     * @return array<int, object> Enriched fixture records or an empty list.
     */
    public function getData(Registry $params, CMSApplicationInterface $app, object $module): array
    {
        try {
            if (!$app->isClient('site')) {
                throw new \RuntimeException('SportsManagement PlaygroundPlan requires the Joomla site application.', 500);
            }

            $projectSelection = $params->get('projects', []);
            $playgroundSelection = $params->get('playground', []);
            $projectIds = $this->ids($projectSelection);
            $playgroundIds = $this->ids($playgroundSelection);

            // Explicitly configured but malformed selectors must not make
            // this module query and display all projects or playgrounds.
            if (($this->hasSelection($projectSelection) && $projectIds === [])
                || ($this->hasSelection($playgroundSelection) && $playgroundIds === [])) {
                return [];
            }

            $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);

            if (!$joomlaDatabase instanceof DatabaseInterface) {
                throw new \RuntimeException('SportsManagement PlaygroundPlan database service is unavailable.', 500);
            }

            $db = $this->database($params, $joomlaDatabase);
            $rawTeamField = $params->get('teamformat', 'middle_name');
            $teamField = $this->teamField(is_string($rawTeamField) ? $rawTeamField : 'middle_name');
            $rawLogoField = $params->get('show_picture', 'logo_big');
            $logoField = $this->logoField(is_string($rawLogoField) ? $rawLogoField : 'logo_big');
            $limit = self::positiveLimit($params->get('maxmatches', 7), 7, 100);

            $query = $db->createQuery()
                ->select([
                    'm.match_date',
                    'p.id AS project_id',
                    'p.alias AS project_alias',
                    'p.name AS project_name',
                    'p.season_id',
                    'lg.name AS league_name',
                    'st1.team_id AS team1',
                    'st2.team_id AS team2',
                    't1.' . $teamField . ' AS team1_name',
                    't2.' . $teamField . ' AS team2_name',
                    'c1.' . $logoField . ' AS team1_logo',
                    'c2.' . $logoField . ' AS team2_logo',
                    'pl.id AS playground_id',
                    'pl.alias AS playground_alias',
                    'pl.name AS playground_name',
                    'pl.picture AS playground_picture',
                    'pltd.id AS team_playground_id',
                    'pltd.alias AS team_playground_alias',
                    'pltd.name AS team_playground_name',
                    'pltd.picture AS playground_team_picture',
                    'plcd.id AS club_playground_id',
                    'plcd.alias AS club_playground_alias',
                    'plcd.name AS club_playground_name',
                    'plcd.picture AS playground_club_picture',
                ])
                ->from('#__sportsmanagement_match AS m')
                ->join('INNER', '#__sportsmanagement_project_team AS pt1 ON pt1.id = m.projectteam1_id')
                ->join('INNER', '#__sportsmanagement_project_team AS pt2 ON pt2.id = m.projectteam2_id AND pt2.project_id = pt1.project_id')
                ->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt1.project_id')
                ->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id AND st1.season_id = p.season_id')
                ->join('INNER', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id AND st2.season_id = p.season_id')
                ->join('INNER', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id')
                ->join('INNER', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id')
                ->join('INNER', '#__sportsmanagement_club AS c1 ON c1.id = t1.club_id')
                ->join('LEFT', '#__sportsmanagement_club AS c2 ON c2.id = t2.club_id')
                ->join('INNER', '#__sportsmanagement_league AS lg ON lg.id = p.league_id')
                ->join('LEFT', '#__sportsmanagement_playground AS plcd ON c1.standard_playground = plcd.id')
                ->join('LEFT', '#__sportsmanagement_playground AS pltd ON pt1.standard_playground = pltd.id')
                ->join('LEFT', '#__sportsmanagement_playground AS pl ON m.playground_id = pl.id')
                ->where('m.match_date > CURRENT_TIMESTAMP')
                ->where('m.published = 1')
                ->where('p.published = 1')
                ->order('m.match_date ASC');

            if ($projectIds) {
                $projectPlaceholders = $query->bindArray($projectIds, ParameterType::INTEGER);
                $query->where('p.id IN (' . implode(',', $projectPlaceholders) . ')');
            }

            if ($playgroundIds) {
                $matchPlaygroundPlaceholders = $query->bindArray($playgroundIds, ParameterType::INTEGER);
                $teamPlaygroundPlaceholders = $query->bindArray($playgroundIds, ParameterType::INTEGER);
                $clubPlaygroundPlaceholders = $query->bindArray($playgroundIds, ParameterType::INTEGER);
                $query->where(
                    '(m.playground_id IN (' . implode(',', $matchPlaygroundPlaceholders) . ')'
                    . ' OR (pt1.standard_playground IN (' . implode(',', $teamPlaygroundPlaceholders) . ') AND m.playground_id IS NULL)'
                    . ' OR (c1.standard_playground IN (' . implode(',', $clubPlaygroundPlaceholders) . ') AND m.playground_id IS NULL AND pt1.standard_playground IS NULL))'
                );
            }

            $db->setQuery($query, 0, $limit);
            $rows = $db->loadObjectList() ?: [];
            $cfg = self::nonNegativeInteger($params->get('cfg_which_database', 0));
            $season = self::nonNegativeInteger($params->get('s', 0));
            $showLink = self::nonNegativeInteger($params->get('show_playground_link', 1)) === 1;
            $showLogos = self::nonNegativeInteger($params->get('show_club_logo', 1)) === 1;

            foreach ($rows as $row) {
                $row->project_slug = $this->slug((int) $row->project_id, (string) $row->project_alias);

                [$playgroundId, $playgroundAlias, $playgroundName, $playgroundPicture] = $this->playground($row);
                $row->display_playground_name = $playgroundName;
                $row->display_playground_picture = $playgroundPicture;
                $row->display_playground_slug = $this->slug($playgroundId, $playgroundAlias);
                $row->playground_link = '';

                if ($showLink && $playgroundId > 0) {
                    $row->playground_link = SiteRouteHelper::view('playground', [
                        'cfg_which_database' => $cfg,
                        's' => $season ?: (int) ($row->season_id ?? 0),
                        'p' => $row->project_slug,
                        'pgid' => $row->display_playground_slug,
                    ]);
                }

                if ($showLogos) {
                    $row->team1_logo = $this->logo((string) ($row->team1_logo ?? ''), $logoField);
                    $row->team2_logo = $this->logo((string) ($row->team2_logo ?? ''), $logoField);
                } else {
                    $row->team1_logo = '';
                    $row->team2_logo = '';
                }
            }

            return $rows;
        } catch (\Throwable $exception) {
            // Keep SQL and service exception details out of public pages.
            Log::add(__METHOD__ . ': ' . $exception->getMessage(), Log::ERROR, 'jsmerror');
            $app->enqueueMessage('SportsManagement playground fixtures could not be loaded.', 'error');

            return [];
        }
    }

    /**
     * Select the configured SportsManagement database connection.
     *
     * Delegates the database selector to the Joomla-aware resolver, using the provided connection as a fallback.
     *
     * @param Registry $params Database configuration.
     * @param DatabaseInterface $fallbackDatabase Default Joomla database.
     * @return DatabaseInterface Database selected for fixture queries.
     */
    private function database(Registry $params, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve(
            $fallbackDatabase,
            self::nonNegativeInteger($params->get('cfg_which_database', 0))
        );
    }

    /**
     * Only accept nonnegative decimal IDs, including zero-padded legacy
     * settings, without PHP's surprising array or boolean-to-integer casts.
     */
    /**
     * Convert a configured numeric selector to a nonnegative integer.
     *
     * Rejects arrays, boolean values, partial numbers and integer overflows; accepts zero-padded decimal strings.
     *
     * @param mixed $value Raw configured numeric value.
     * @return int Nonnegative integer or zero for invalid input.
     */
    private static function nonNegativeInteger(mixed $value): int
    {
        if (!is_int($value) && (!is_string($value) || !preg_match('/^[0-9]+$/D', $value))) {
            return 0;
        }

        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $number === false ? 0 : $number;
    }

    /** Retain a bounded default for malformed fixture display limits. */
    /**
     * Clamp the maximum number of upcoming fixtures.
     *
     * Accepts positive integer values and zero-padded decimal strings, retaining a bounded default for malformed values.
     *
     * @param mixed $value Raw configured maximum.
     * @param int $default Fallback limit.
     * @param int $maximum Maximum number of records allowed.
     * @return int Bounded positive fixture limit.
     */
    private static function positiveLimit(mixed $value, int $default, int $maximum): int
    {
        if (!is_int($value) && (!is_string($value) || !preg_match('/^[0-9]+$/D', $value))) {
            return $default;
        }

        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $number === false ? $default : min($maximum, max(1, $number));
    }

    /**
     * Choose a team logo or the component placeholder.
     *
     * Uses the supplied image path when available, otherwise selects the matching configured placeholder size.
     *
     * @param string $path Team image path.
     * @param string $logoField Configured logo size field.
     * @return string Team logo or fallback path.
     */
    private function logo(string $path, string $logoField): string
    {
        if ($path !== '') {
            return $path;
        }

        $componentParams = ComponentHelper::getParams('com_sportsmanagement');

        return (string) match ($logoField) {
            'logo_small' => $componentParams->get('ph_logo_small', ''),
            'logo_middle' => $componentParams->get('ph_logo_medium', ''),
            default => $componentParams->get('ph_logo_big', ''),
        };
    }

    /**
     * Determine the relevant playground for a fixture.
     *
     * Prefers the match-specific playground, then the project team's ground and finally the club ground.
     *
     * @param object $row Fixture record containing playground fields.
     * @return array{int, string, string, string} Ground ID, alias, name and image path.
     */
    private function playground(object $row): array
    {
        if ((int) ($row->playground_id ?? 0) > 0) {
            return [
                (int) $row->playground_id,
                (string) ($row->playground_alias ?? ''),
                (string) ($row->playground_name ?? ''),
                (string) ($row->playground_picture ?? ''),
            ];
        }

        if ((int) ($row->team_playground_id ?? 0) > 0) {
            return [
                (int) $row->team_playground_id,
                (string) ($row->team_playground_alias ?? ''),
                (string) ($row->team_playground_name ?? ''),
                (string) ($row->playground_team_picture ?? ''),
            ];
        }

        if ((int) ($row->club_playground_id ?? 0) > 0) {
            return [
                (int) $row->club_playground_id,
                (string) ($row->club_playground_alias ?? ''),
                (string) ($row->club_playground_name ?? ''),
                (string) ($row->playground_club_picture ?? ''),
            ];
        }

        return [0, '', '', ''];
    }

    /**
     * Select an allowlisted team name column for SQL.
     *
     * Rejects unknown configuration fields before composing a dynamic SELECT clause.
     *
     * @param string $value Requested team name column.
     * @return string Safe SQL column name.
     */
    private function teamField(string $value): string
    {
        return in_array($value, ['name', 'middle_name', 'short_name'], true)
            ? $value
            : 'middle_name';
    }

    /**
     * Select an allowlisted club logo column for SQL.
     *
     * Rejects unknown configuration fields before composing a dynamic SELECT clause.
     *
     * @param string $value Requested club image column.
     * @return string Safe SQL column name.
     */
    private function logoField(string $value): string
    {
        return in_array($value, ['logo_small', 'logo_middle', 'logo_big'], true)
            ? $value
            : 'logo_big';
    }

    /**
     * Build a Joomla ID-and-alias slug for a record.
     *
     * Omits the alias suffix when the database record has no alias.
     *
     * @param int $id Record identifier.
     * @param string $alias Optional URL alias.
     * @return string Numeric ID or ID:alias slug.
     */
    private function slug(int $id, string $alias): string
    {
        $alias = trim($alias);

        return $alias === '' ? (string) $id : $id . ':' . $alias;
    }

    /**
     * Check whether a project or playground selection was explicitly set.
     *
     * Distinguishes blank and zero-valued selectors from malformed entries that must not broaden a query.
     *
     * @param mixed $value Scalar or array-valued module selection.
     * @return bool True when the selector contains an explicit entry.
     */
    private function hasSelection(mixed $value): bool
    {
        $values = is_array($value) ? $value : [$value];

        foreach ($values as $entry) {
            if (!is_string($entry) && !is_int($entry)) {
                return true;
            }

            $text = trim((string) $entry);

            if ($text !== '' && $text !== '0') {
                return true;
            }
        }

        return false;
    }

    /** @return array<int,int> */
    /**
     * Parse the allowed positive IDs from Joomla module selectors.
     *
     * Accepts scalar or array selectors, comma-separated entries and ID:alias slugs; rejects malformed or overflowing IDs and removes duplicates.
     *
     * @param mixed $value Raw selection from module parameters.
     * @return array<int, int> Unique validated positive IDs.
     */
    private function ids(mixed $value): array
    {
        $values = is_array($value) ? $value : [$value];
        $ids = [];

        foreach ($values as $entry) {
            if (!is_string($entry) && !is_int($entry)) {
                continue;
            }

            foreach (preg_split('/[\\s,;]+/', (string) $entry, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $candidate) {
                // Module selectors can return Joomla ID:alias values.
                // Reject malformed partial IDs and integer overflow.
                if (!preg_match('/^([0-9]+)(?::[A-Za-z0-9_-]+)?$/D', $candidate, $match)) {
                    continue;
                }

                $digits = ltrim($match[1], '0');

                if ($digits === '') {
                    continue;
                }

                $id = filter_var($digits, FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1],
                ]);

                if ($id !== false) {
                    $ids[$id] = $id;
                }
            }
        }

        return array_values($ids);
    }
}
