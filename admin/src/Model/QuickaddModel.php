<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * Native Joomla 5/6 helper model for the administrator quick-add lookups.
 *
 * The public method names retain the historic API used by integrations while
 * all database access goes through Joomla's query builder and component DB.
 */
final class QuickaddModel extends SportsManagementListModel
{
    public string $_identifier = 'quickadd';

    private int $quickTotal = 0;

    public function getNotAssignedPlayers($searchterm, $projectteam_id, $searchinfo = null): array
    {
        $db = $this->getDatabase();
        $query = $this->personSearchQuery($db, (string) $searchterm, $searchinfo)
            ->select($db->quoteName('pl.id', 'id2'));
        $subquery = $db->getQuery(true)
            ->select($db->quoteName('tp.person_id'))
            ->from($db->quoteName('#__sportsmanagement_team_player', 'tp'))
            ->where($db->quoteName('tp.projectteam_id') . ' = ' . (int) $projectteam_id)
            ->where($db->quoteName('tp.person_id') . ' = ' . $db->quoteName('pl.id'));
        $query->where('NOT EXISTS (' . $subquery . ')');

        return $this->loadQuickRows($db, $query, true);
    }

    public function getNotAssignedStaff($searchterm, $projectteam_id, $searchinfo = null): array
    {
        $db = $this->getDatabase();
        $query = $this->personSearchQuery($db, (string) $searchterm, $searchinfo);
        $subquery = $db->getQuery(true)
            ->select($db->quoteName('ts.person_id'))
            ->from($db->quoteName('#__sportsmanagement_team_staff', 'ts'))
            ->where($db->quoteName('ts.projectteam_id') . ' = ' . (int) $projectteam_id)
            ->where($db->quoteName('ts.person_id') . ' = ' . $db->quoteName('pl.id'));
        $query->where('NOT EXISTS (' . $subquery . ')');

        return $this->loadQuickRows($db, $query, true);
    }

    public function getNotAssignedReferees($searchterm, $projectid, $searchinfo = null): array
    {
        $db = $this->getDatabase();
        $query = $this->personSearchQuery($db, (string) $searchterm, $searchinfo);
        $subquery = $db->getQuery(true)
            ->select($db->quoteName('pr.person_id'))
            ->from($db->quoteName('#__sportsmanagement_project_referee', 'pr'))
            ->where($db->quoteName('pr.project_id') . ' = ' . (int) $projectid)
            ->where($db->quoteName('pr.person_id') . ' = ' . $db->quoteName('pl.id'));
        $query->where('NOT EXISTS (' . $subquery . ')');

        return $this->loadQuickRows($db, $query, true);
    }

    public function getNotAssignedTeams($searchterm, $projectid): array
    {
        $db = $this->getDatabase();
        $needle = '%' . $db->escape(trim((string) $searchterm), true) . '%';
        $quotedNeedle = $db->quote($needle, false);
        $numericId = filter_var($searchterm, FILTER_VALIDATE_INT);
        $search = [
            'LOWER(' . $db->quoteName('t.name') . ') LIKE LOWER(' . $quotedNeedle . ')',
            'LOWER(' . $db->quoteName('t.alias') . ') LIKE LOWER(' . $quotedNeedle . ')',
            'LOWER(' . $db->quoteName('t.short_name') . ') LIKE LOWER(' . $quotedNeedle . ')',
            'LOWER(' . $db->quoteName('t.middle_name') . ') LIKE LOWER(' . $quotedNeedle . ')',
        ];

        if ($numericId !== false) {
            $search[] = $db->quoteName('t.id') . ' = ' . (int) $numericId;
        }

        $subquery = $db->getQuery(true)
            ->select($db->quoteName('pt.team_id'))
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->where($db->quoteName('pt.project_id') . ' = ' . (int) $projectid);
        $query = $db->getQuery(true)
            ->select($db->quoteName('t') . '.*')
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->where('(' . implode(' OR ', $search) . ')')
            ->where($db->quoteName('t.id') . ' NOT IN (' . $subquery . ')')
            ->order($db->quoteName('t.name') . ' ASC');

        return $this->loadQuickRows($db, $query, false);
    }

    /**
     * Preserve the historical quick-add behaviour: when only a name is given,
     * create the person record. Team assignment itself was never implemented in
     * the legacy method and therefore remains outside this compatibility API.
     */
    public function addPlayer($projectteam_id, $personid, $name = null): bool
    {
        $personId = (int) $personid;

        if ($personId <= 0 && trim((string) $name) === '') {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_QUICKADD_CTRL_ADD_PLAYER_REQUIRES_ID_OR_NAME'));

            return false;
        }

        if ($personId <= 0) {
            $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
            $firstname = '';
            $nickname = '';
            $lastname = '';

            if (count($parts) === 1) {
                $firstname = ucfirst($parts[0]);
                $nickname = $parts[0];
                $lastname = '.';
            } elseif (count($parts) === 2) {
                $firstname = ucfirst($parts[0]);
                $nickname = $parts[1];
                $lastname = ucfirst($parts[1]);
            } elseif (count($parts) === 3) {
                $firstname = ucfirst($parts[0]);
                $nickname = $parts[1];
                $lastname = ucfirst($parts[2]);
            }

            $record = (object) [
                'firstname' => $firstname,
                'nickname' => $nickname,
                'lastname' => $lastname,
                'published' => 1,
            ];
            $db = $this->getDatabase();

            try {
                $db->insertObject('#__sportsmanagement_person', $record);
                $personId = (int) $db->insertid();
            } catch (\Throwable $e) {
                $this->setError($e->getMessage());

                return false;
            }
        }

        if ($personId <= 0) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_QUICKADD_CTRL_FAILED_ADDING_PERSON'));

            return false;
        }

        return true;
    }

    public function getTotal(): int
    {
        return $this->quickTotal;
    }

    private function personSearchQuery(DatabaseInterface $db, string $searchterm, $searchinfo)
    {
        $needle = '%' . $db->escape(trim($searchterm), true) . '%';
        $quotedNeedle = $db->quote($needle, false);
        $numericId = filter_var($searchterm, FILTER_VALIDATE_INT);
        $search = [
            'LOWER(CONCAT(' . $db->quoteName('pl.firstname') . ", ' ', " . $db->quoteName('pl.lastname') . ')) LIKE LOWER(' . $quotedNeedle . ')',
            'LOWER(' . $db->quoteName('pl.alias') . ') LIKE LOWER(' . $quotedNeedle . ')',
            'LOWER(' . $db->quoteName('pl.nickname') . ') LIKE LOWER(' . $quotedNeedle . ')',
        ];

        if ($numericId !== false) {
            $search[] = $db->quoteName('pl.id') . ' = ' . (int) $numericId;
        }

        $query = $db->getQuery(true)
            ->select($db->quoteName('pl') . '.*')
            ->from($db->quoteName('#__sportsmanagement_person', 'pl'))
            ->where('(' . implode(' OR ', $search) . ')')
            ->where($db->quoteName('pl.published') . ' = 1');

        if ($searchinfo !== null && trim((string) $searchinfo) !== '') {
            $query->where($db->quoteName('pl.info') . ' LIKE ' . $db->quote((string) $searchinfo));
        }

        return $query;
    }

    private function loadQuickRows(DatabaseInterface $db, $query, bool $personOrdering): array
    {
        if ($personOrdering) {
            [$ordering, $direction] = $this->personOrdering();
            $query->order($db->quoteName($ordering) . ' ' . $direction);

            if ($ordering !== 'pl.lastname') {
                $query->order($db->quoteName('pl.lastname') . ' ASC');
            }
        }

        $countQuery = clone $query;
        $countQuery->clear('select')->clear('order')->select('COUNT(*)');

        try {
            $db->setQuery($countQuery);
            $this->quickTotal = (int) $db->loadResult();

            $start = max(0, (int) $this->getState('list.start', $this->getState('limitstart', 0)));
            $limit = max(0, (int) $this->getState('list.limit', $this->getState('limit', 0)));
            $db->setQuery($query, $start, $limit);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            $this->quickTotal = 0;

            return [];
        }
    }

    private function personOrdering(): array
    {
        $app = Factory::getApplication();
        $requested = (string) $app->getUserStateFromRequest(
            'com_sportsmanagement.pl_filter_order',
            'filter_order',
            'pl.lastname',
            'cmd'
        );
        $direction = strtoupper((string) $app->getUserStateFromRequest(
            'com_sportsmanagement.pl_filter_order_Dir',
            'filter_order_Dir',
            'ASC',
            'word'
        ));
        $allowed = [
            'pl.lastname',
            'pl.firstname',
            'pl.nickname',
            'pl.alias',
            'pl.id',
        ];

        return [
            in_array($requested, $allowed, true) ? $requested : 'pl.lastname',
            $direction === 'DESC' ? 'DESC' : 'ASC',
        ];
    }
}
