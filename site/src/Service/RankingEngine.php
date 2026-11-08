<?php
/**
 * Native Joomla 5/6 read-only ranking facade shared by site views and modules.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/**
 * Read-only ranking facade shared by site views and modules.
 */
final class RankingEngine
{
    /**
     * Create the read-only ranking engine with its database dependency.
     * Keeps the database instance immutable so the loader and calculator operate on the same Joomla 5/6 connection.
     *
     * @param DatabaseInterface $db Joomla database driver used by the ranking data loader.
     * @return void
     */
    public function __construct(private readonly DatabaseInterface $db)
    {
    }

    /**
     * Calculate the table for a project, optionally limited to a division and rounds.
     *
     * Loads project, team, match and display settings through RankingDataLoader,
     * calculates the ordered team rows and returns an empty result for missing projects.
     *
     * @param int $projectId Project whose table is requested.
     * @param int $divisionId Optional division filter, zero for all divisions.
     * @param int $fromRoundId Optional first round identifier.
     * @param int $toRoundId Optional last round identifier.
     * @return array{project: object, ranking: array<int, RankingRow>, config: array, colors: array, matches: int}
     */
    public function calculate(
        int $projectId,
        int $divisionId = 0,
        int $fromRoundId = 0,
        int $toRoundId = 0
    ): array {
        $loader = new RankingDataLoader($this->db);
        $data = $loader->load($projectId, $divisionId, $fromRoundId, $toRoundId);
        if (!$data['project']) {
            return ['project' => (object) [], 'ranking' => [], 'config' => [], 'colors' => [], 'matches' => 0];
        }

        $calculator = new RankingCalculator();
        $ranking = $calculator->calculate(
            $data['teams'],
            $data['matches'],
            $data['project'],
            $data['config']
        );

        return [
            'project' => $data['project'],
            'ranking' => $ranking,
            'config' => $data['config'],
            'colors' => $data['colors'],
            'matches' => count($data['matches']),
        ];
    }
}
