<?php
/**
 * Joomla 5/6 administrator individual-sport form model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Service\IndividualMatchAdminService;
use Diddipoeler\Component\SportsManagement\Administrator\Service\IndividualMatchWriteService;
use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchSingleTable;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/** Joomla 5/6 compatibility form model for individual-match rows. */
final class JlextindividualsportModel extends SportsManagementAdminModel
{
    public function getTable($type = 'MatchSingle', $prefix = 'Administrator', $config = [])
    {
        return new MatchSingleTable($this->getDatabase());
    }

    /** @return array{0:int,1:int} inserted, failed */
    public function generatematchsingles(): array
    {
        $app = $this->administratorApplication();
        return (new IndividualMatchAdminService($this->getDatabase()))->generateSingles(
            $app->getInput()->post->getArray(),
            (int) $app->getIdentity()->id,
            Factory::getDate()->toSql()
        );
    }

    public function addmatch(array $post = []): bool
    {
        return (new IndividualMatchAdminService($this->getDatabase()))->addMatch($post);
    }

    public function saveshort(): bool
    {
        $app = $this->administratorApplication();
        $input = $app->getInput();
        $ids = (array) $input->post->get('cid', [], 'array');
        return (new IndividualMatchWriteService($this->getDatabase()))->saveShort(
            $input->post->getArray(),
            $ids,
            (int) $app->getIdentity()->id,
            Factory::getDate()->toSql()
        );
    }

    public function delete(&$pks): bool
    {
        return (new IndividualMatchAdminService($this->getDatabase()))->deleteSingles((array) $pks);
    }

    /**
     * Backwards-compatible static reader kept for third-party templates.
     * New component code uses IndividualMatchReadService instead.
     */
    public static function getmatchsingle_rowshome(
        $projectId = 0,
        $projectTeamId = 0,
        $seasonTeamPersonId = 0,
        $matchType = 'SINGLE',
        $homeAway = 'HOME'
    ): array {
        $projectId = (int) $projectId;
        $projectTeamId = (int) $projectTeamId;
        $seasonTeamPersonId = (int) $seasonTeamPersonId;

        if ($projectId <= 0 || $projectTeamId <= 0 || $seasonTeamPersonId <= 0) {
            return [];
        }

        $db = self::sportsDatabase();
        $side = strtoupper((string) $homeAway) === 'AWAY' ? 2 : 1;
        $matchType = (string) $matchType;
        $query = $db->createQuery()
            ->select($db->quoteName('ms') . '.*')
            ->from($db->quoteName('#__sportsmanagement_match_single', 'ms'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('ms.round_id')
            )
            ->where($db->quoteName('r.project_id') . ' = :projectId')
            ->where($db->quoteName('ms.projectteam' . $side . '_id') . ' = :projectTeamId')
            ->where($db->quoteName('ms.teamplayer' . $side . '_id') . ' = :seasonTeamPersonId')
            ->where($db->quoteName('ms.match_type') . ' = :matchType')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER)
            ->bind(':seasonTeamPersonId', $seasonTeamPersonId, ParameterType::INTEGER)
            ->bind(':matchType', $matchType, ParameterType::STRING);

        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    private static function sportsDatabase(): DatabaseInterface
    {
        return (new SportsManagementDatabaseResolver())->resolve();
    }
}
