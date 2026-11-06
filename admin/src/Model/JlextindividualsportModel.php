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
    /**
     * Create the native single-match table for individual sports.
     * @param string $type Requested table type.
     * @param string $prefix Joomla table prefix.
     * @param array $config Additional table options.
     * @return MatchSingleTable Table bound to the component database.
     */
    public function getTable($type = 'MatchSingle', $prefix = 'Administrator', $config = [])
    {
        return new MatchSingleTable($this->getDatabase());
    }

    /**
     * Generate individual match rows through the native admin service.
     *
     * @return array{0:int,1:int} Numbers of inserted and failed matches.
     */
    public function generatematchsingles(): array
    {
        $app = $this->administratorApplication();
        return (new IndividualMatchAdminService($this->getDatabase()))->generateSingles(
            $app->getInput()->post->getArray(),
            (int) ($app->getIdentity()?->id ?? 0),
            Factory::getDate()->toSql()
        );
    }

    /**
     * Create an individual match through the native admin service.
     * @param array $post Submitted individual match values.
     * @return bool Whether a match was added.
     */
    public function addmatch(array $post = []): bool
    {
        return (new IndividualMatchAdminService($this->getDatabase()))->addMatch($post);
    }

    /**
     * Persist short-form individual match changes from POST.
     * Delegates to the native write service and records the current user.
     * @return bool Whether the changes were saved.
     */
    public function saveshort(): bool
    {
        $app = $this->administratorApplication();
        $input = $app->getInput();
        $ids = (array) $input->post->get('cid', [], 'array');
        return (new IndividualMatchWriteService($this->getDatabase()))->saveShort(
            $input->post->getArray(),
            $ids,
            (int) ($app->getIdentity()?->id ?? 0),
            Factory::getDate()->toSql()
        );
    }

    /**
     * Delete selected individual matches via the native service.
     * @param array $pks Selected match identifiers.
     * @return bool Whether the matches were deleted.
     */
    public function delete(&$pks): bool
    {
        return (new IndividualMatchAdminService($this->getDatabase()))->deleteSingles((array) $pks);
    }

    /**
     * Read individual match rows for a player's selected team and project.
     *
     * Backwards-compatible reader for external templates; component code
     * should prefer IndividualMatchReadService.
     *
     * @param int $projectId Project identifier.
     * @param int $projectTeamId Project-team identifier.
     * @param int $seasonTeamPersonId Player membership identifier.
     * @param string $matchType Individual match type.
     * @param string $homeAway Which team side to select.
     * @return array<int,object> Individual match records.
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

    /**
     * Resolve the component database for compatibility match readers.
     * @return DatabaseInterface Selected SportsManagement database.
     */
    private static function sportsDatabase(): DatabaseInterface
    {
        return (new SportsManagementDatabaseResolver())->resolve();
    }
}
