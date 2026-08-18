<?php
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/**
 * Read-only ranking facade shared by site views and modules.
 */
final class RankingEngine
{
    public function __construct(private readonly DatabaseInterface $db)
    {
    }

    /**
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
