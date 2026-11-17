<?php
/**
 * Base model for native Joomla 5/6 SportsManagement statistics rankings.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

abstract class SportsManagementStatsRankingModel extends SportsManagementProjectModel
{
    public static int $divisionid = 0;
    public static int $teamid = 0;
    public static int $cfg_which_database = 0;
    public static int $projectid = 0;

    public $_total = null;
    public $_pagination = null;
    public ?string $order = null;
    public $stat_id = 0;

    protected int $limit = 0;
    protected int $limitstart = 0;
    protected string $statsTemplate = 'statsranking';

    /**
     * Initialise the Joomla statistics model and its selected ranking scope.
     *
     * Reads the active project, division, team, database, paging and statistic
     * selectors from the site application. Loads display limits from the
     * selected template and retains the legacy static view context while
     * delegating service setup to Joomla's MVC model constructor.
     *
     * @param array<string,mixed> $config Native Joomla model configuration.
     * @param MVCFactoryInterface|null $factory Optional Joomla MVC factory.
     * @return void
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        static::$projectid = $this->projectId;
        static::$divisionid = $this->divisionId;
        static::$teamid = $input->getInt('tid', 0);
        static::$cfg_which_database = $input->getInt('cfg_which_database', 0);

        $this->setStatid($input->get('sid', 0, 'raw'));
        $templateConfig = $this->getTemplateConfig($this->statsTemplate);
        $defaultLimit = $this->stat_id !== 0
            ? (int) ($templateConfig['max_stats'] ?? 20)
            : (int) ($templateConfig['count_stats'] ?? 5);

        $this->limit = max(0, $input->getInt('limit', $defaultLimit));
        $this->limitstart = max(0, $input->getInt('limitstart', 0));
        $this->setOrder($input->getCmd('order', ''));
    }

    /**
     * Interpret the historic statistic selector from a URL or saved filter.
     *
     * Accepts positive integer IDs in an array or pipe-separated string,
     * deduplicates the result and bounds it to 100 entries. A deliberate
     * zero means "all statistics" as in the previous Joomla model. Partial
     * numeric strings, nested values, booleans and integer overflow cannot
     * be coerced into a different statistic identifier.
     *
     * @param mixed $statid Raw selected statistic IDs or zero for all.
     * @return void
     */
    public function setStatid($statid): void
    {
        if (!is_array($statid) && !is_string($statid) && !is_int($statid)) {
            $this->stat_id = 0;
            return;
        }

        $values = is_array($statid) ? $statid : explode('|', (string) $statid);
        $ids = [];

        foreach ($values as $value) {
            $id = self::normaliseStatisticId($value);

            if ($id === 0) {
                $this->stat_id = 0;
                return;
            }

            if ($id === null) {
                continue;
            }

            $ids[$id] = $id;

            if (count($ids) >= 100) {
                break;
            }
        }

        $this->stat_id = $ids ? array_values($ids) : 0;
    }

    /**
     * Record a supported ranking sort direction from frontend input.
     *
     * Normalises ASCII case but leaves the current selection unchanged for
     * unsupported values or structured input, avoiding PHP 8 conversion
     * warnings when callers pass malformed Joomla request parameters.
     *
     * @param mixed $order Requested ascending or descending sort direction.
     * @return string|null Current accepted direction, or null when not set.
     */
    public function setOrder($order)
    {
        if (is_string($order)) {
            $value = strtolower($order);

            if (in_array($value, ['asc', 'desc'], true)) {
                $this->order = $value;
            }
        }

        return $this->order;
    }

    /**
     * Return the selected team identifier for legacy ranking view overrides.
     *
     * The static context is retained for templates that read the selected
     * team directly from the active model rather than Joomla model state.
     *
     * @return int Selected team ID or zero for the unfiltered view.
     */
    public function getTeamId(): int
    {
        return static::$teamid;
    }

    /**
     * Load the player ranking rows for every configured project statistic.
     *
     * Passes the selected project, division, team and pagination to each
     * statistic's ranking provider, ignoring malformed provider objects or
     * IDs without exposing PHP warnings to visitors.
     *
     * @param mixed $order Optional supported sort direction.
     * @return array<int,mixed> Player ranking rows keyed by statistic ID.
     */
    public function getPlayersStats($order = null): array
    {
        $stats = $this->getProjectUniqueStats();
        $rankingOrder = $this->resolveOrder($order);
        $results = [];

        foreach ($stats as $stat) {
            if (!is_object($stat) || !is_callable([$stat, 'getPlayersRanking'])) {
                continue;
            }

            $statId = self::normaliseStatisticId($stat->id ?? null);

            if ($statId === null || $statId <= 0) {
                continue;
            }

            $results[$statId] = $stat->getPlayersRanking(
                static::$projectid,
                static::$divisionid,
                static::$teamid,
                $this->limit,
                $this->limitstart,
                $rankingOrder
            );
        }

        return $results;
    }

    /**
     * Collect unique and valid statistic providers from the current project.
     *
     * Filters the provider groups returned by the native project model,
     * retaining each positive statistic ID only once and ignoring corrupt
     * or noniterable database results instead of casting them into objects.
     *
     * @return array<int,object> Valid statistic providers indexed by ID.
     */
    public function getProjectUniqueStats(): array
    {
        $positionStats = $this->getProjectStats($this->stat_id, 0);
        $allStats = [];

        if (!is_iterable($positionStats)) {
            return [];
        }

        foreach ($positionStats as $stats) {
            foreach ((array) $stats as $stat) {
                if (!is_object($stat)) {
                    continue;
                }

                $statId = self::normaliseStatisticId($stat->id ?? null);

                if ($statId !== null && $statId > 0) {
                    $allStats[$statId] = $stat;
                }
            }
        }

        return $allStats;
    }

    /**
     * Return the maximum number of statistic records selected for this page.
     *
     * Provides the current Joomla pagination limit to legacy ranking views.
     *
     * @return int Nonnegative record limit.
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Return the first record offset for the active ranking page.
     *
     * The value comes from Joomla's validated paging input, as recorded
     * during model construction.
     *
     * @return int Nonnegative pagination offset.
     */
    public function getLimitStart(): int
    {
        return $this->limitstart;
    }

    /**
     * Resolve an optional per-query sort order or use the model selection.
     *
     * Accepts only the two supported direction names and refuses structured
     * input. Invalid overrides do not replace the saved Joomla page order.
     *
     * @param mixed $order Optional query-specific sort direction.
     * @return string|null Effective direction or null when not configured.
     */
    protected function resolveOrder($order): ?string
    {
        if (is_string($order) && $order !== '') {
            $value = strtolower($order);

            if (in_array($value, ['asc', 'desc'], true)) {
                return $value;
            }
        }

        return $this->order;
    }

    /**
     * Validate an unsigned numeric statistic ID without loose PHP casts.
     *
     * Supports Joomla's integer IDs and zero-padded decimal strings.
     * Rejects numeric text with suffixes, nested values and integers
     * exceeding PHP_INT_MAX before they can alter the query selection.
     *
     * @param mixed $value Candidate statistic identifier.
     * @return int|null Normalised nonnegative ID or null when malformed.
     */
    protected static function normaliseStatisticId(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }

        if (!is_string($value) || strlen($value) > 64
            || preg_match('/^[0-9]+$/D', $value) !== 1) {
            return null;
        }

        $digits = ltrim($value, '0');

        if ($digits === '') {
            return 0;
        }

        $max = (string) PHP_INT_MAX;

        if (strlen($digits) > strlen($max)
            || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
            return null;
        }

        return (int) $digits;
    }
}
