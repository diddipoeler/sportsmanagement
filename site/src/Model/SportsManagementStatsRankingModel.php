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

    public function setStatid($statid): void
    {
        $values = is_array($statid) ? $statid : explode('|', (string) $statid);
        $ids = [];

        foreach ($values as $value) {
            $id = (int) $value;
            if ($id === 0) {
                $this->stat_id = 0;
                return;
            }
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        $this->stat_id = $ids ? array_values($ids) : 0;
    }

    public function setOrder($order)
    {
        $value = strtolower((string) $order);
        if (in_array($value, ['asc', 'desc'], true)) {
            $this->order = $value;
        }

        return $this->order;
    }

    public function getTeamId(): int
    {
        return static::$teamid;
    }

    public function getPlayersStats($order = null): array
    {
        $stats = $this->getProjectUniqueStats();
        $rankingOrder = $this->resolveOrder($order);
        $results = [];

        foreach ($stats as $stat) {
            if (!is_object($stat) || !method_exists($stat, 'getPlayersRanking')) {
                continue;
            }
            $statId = (int) ($stat->id ?? 0);
            if ($statId <= 0) {
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

    public function getProjectUniqueStats(): array
    {
        $positionStats = $this->getProjectStats($this->stat_id, 0);
        $allStats = [];

        foreach ($positionStats as $stats) {
            foreach ((array) $stats as $stat) {
                if (!is_object($stat)) {
                    continue;
                }
                $statId = (int) ($stat->id ?? 0);
                if ($statId > 0) {
                    $allStats[$statId] = $stat;
                }
            }
        }

        return $allStats;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getLimitStart(): int
    {
        return $this->limitstart;
    }

    protected function resolveOrder($order): ?string
    {
        if ($order !== null && $order !== '') {
            $value = strtolower((string) $order);
            if (in_array($value, ['asc', 'desc'], true)) {
                return $value;
            }
        }

        return $this->order;
    }
}
