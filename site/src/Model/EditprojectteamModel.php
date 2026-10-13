<?php
/**
 * Joomla 5/6 frontend model for editing project teams.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\ProjectteamTable;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Database\ParameterType;

/** Joomla 5/6 frontend model for editing project teams. */
final class EditprojectteamModel extends AdminModel
{
    public $latitude = null;
    public $longitude = null;
    public $name = 'editprojectteam';

    private ?ProjectteamTable $projectTeam = null;

    /**
     * Save only editable form fields against an existing project-team row.
     * Never trust readonly project_id/team_id or other POST identity fields.
     */
    public function updItem($data): bool
    {
        $data = (array) $data;
        $id = self::positiveId($data['id'] ?? null);
        $postedProjectTeamId = self::positiveId($data['ptid'] ?? null);
        $projectId = self::positiveId($data['p'] ?? null);

        if ($id <= 0
            || (array_key_exists('ptid', $data) && $postedProjectTeamId !== $id)) {
            $this->setError(Text::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }

        $table = $this->getTable('projectteam');

        if (!$table->load($id)
            || (int) $table->id !== $id
            || ($projectId > 0 && (int) $table->project_id !== $projectId)) {
            $this->setError(Text::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }

        // Native Joomla forms post under jform; historical top-level fields
        // remain supported, but neither can alter relation identity.
        $fields = [
            'admin', 'standard_playground', 'is_in_score', 'start_points',
            'reason', 'use_finally', 'matches_finally', 'points_finally',
            'neg_points_finally', 'penalty_points', 'won_finally',
            'draws_finally', 'lost_finally', 'homegoals_finally',
            'guestgoals_finally', 'diffgoals_finally', 'picture',
            'cr_picture', 'trikot_home', 'trikot_away', 'notes',
        ];
        $allowed = array_fill_keys($fields, true);
        $submitted = array_intersect_key($data, $allowed);

        foreach ([$data['request'] ?? null, $data['jform'] ?? null] as $nested) {
            if (is_array($nested)) {
                $submitted = array_replace($submitted, array_intersect_key($nested, $allowed));
            }
        }

        foreach ($submitted as $value) {
            if ($value !== null && !is_scalar($value)) {
                $this->setError(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
                return false;
            }
        }

        $update = ['id' => $id] + $submitted;
        $seasonTeamId = (int) $table->team_id;
        $db = $this->getDatabase();

        try {
            $db->transactionStart();

            if (!$table->bind($update) || !$table->check() || !$table->store()) {
                throw new \RuntimeException((string) $table->getError());
            }

            // Table::store() already saves this project-team row. Only mirror
            // its picture to the season team, never bulk-update other projects.
            if ($seasonTeamId > 0 && array_key_exists('picture', $submitted)) {
                $db->updateObject(
                    '#__sportsmanagement_season_team_id',
                    (object) [
                        'id' => $seasonTeamId,
                        'picture' => (string) $submitted['picture'],
                    ],
                    'id'
                );
            }

            $db->transactionCommit();
            $this->projectTeam = $table;

            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');
            $this->setError(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));

            return false;
        }
    }

    /** Do not coerce malformed, negative or overflowing IDs into keys. */
    private static function positiveId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $raw = trim((string) $value);

        if ($raw === '' || !ctype_digit($raw)) {
            return 0;
        }

        $decimal = ltrim($raw, '0');
        $decimal = $decimal === '' ? '0' : $decimal;
        $max = (string) PHP_INT_MAX;

        if (strlen($decimal) > strlen($max)
            || (strlen($decimal) === strlen($max) && strcmp($decimal, $max) > 0)) {
            return 0;
        }

        return (int) $decimal;
    }

    public function getTeamInfo(int $projectTeamId): ?object
    {
        if ($projectTeamId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('t.id'),
                $db->quoteName('t.name'),
                $db->quoteName('t.short_name'),
                $db->quoteName('t.middle_name'),
                $db->quoteName('t.picture'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id')
            )
            ->where($db->quoteName('pt.id') . ' = :projectTeamId')
            ->bind(':projectTeamId', $projectTeamId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    public function getTable($type = 'projectteam', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'projectteam') === 0) {
            return new ProjectteamTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    public function getForm($data = [], $loadData = true)
    {
        FormHelper::addFormPath(JPATH_SITE . '/components/com_sportsmanagement/models/forms');
        FormHelper::addFieldPrefix('Diddipoeler\\Component\\SportsManagement\\Administrator\\Field');

        $form = $this->loadForm(
            'com_sportsmanagement.' . $this->name,
            $this->name,
            ['load_data' => $loadData]
        );

        return $form ?: false;
    }

    protected function loadFormData()
    {
        $app = $this->siteApplication();
        $data = $app->getUserState('com_sportsmanagement.edit.' . $this->name . '.data', []);

        return empty($data) ? $this->getData() : $data;
    }

    public function getData(): ProjectteamTable
    {
        if ($this->projectTeam === null) {
            $projectTeamId = $this->siteApplication()->getInput()->getInt('ptid', 0);
            $this->projectTeam = $this->getTable('projectteam');

            if ($projectTeamId > 0) {
                $this->projectTeam->load($projectTeamId);
            }
        }

        return $this->projectTeam;
    }

    private function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }
}
