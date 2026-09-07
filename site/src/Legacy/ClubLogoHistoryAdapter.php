<?php
/**
 * Joomla 5/6 compatibility adapter for legacy ranking logo-history calls.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Legacy;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\ClubinfoModel;

/**
 * Bridges the historical getlogohistory(clubId, seasonId, teamId) contract to
 * the native ClubinfoModel::getLogoHistory(clubId, seasonId) API.
 */
final class ClubLogoHistoryAdapter
{
    /** @var array<int, int> Team id => club id. */
    private array $clubIdsByTeam = [];

    public function __construct(
        private ClubinfoModel $model,
        array $teams = []
    ) {
        foreach ($teams as $team) {
            if (!is_object($team)) {
                continue;
            }

            $teamId = (int) ($team->id ?? 0);
            $clubId = (int) ($team->club_id ?? 0);

            if ($teamId > 0 && $clubId > 0) {
                $this->clubIdsByTeam[$teamId] = $clubId;
            }
        }
    }

    public function getLogoHistory($clubId = 0, $seasonId = 0, $teamId = 0): array
    {
        $clubId = max(0, (int) $clubId);
        $seasonId = max(0, (int) $seasonId);
        $teamId = max(0, (int) $teamId);

        if ($clubId <= 0 && $teamId > 0) {
            $clubId = $this->clubIdsByTeam[$teamId] ?? 0;
        }

        if ($clubId <= 0) {
            return [];
        }

        return $this->model->getLogoHistory($clubId, $seasonId);
    }
}
