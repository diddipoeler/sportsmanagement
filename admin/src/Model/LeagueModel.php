<?php
/**
 * Native Joomla 5/6 administrator model for leagues.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtraFieldsSaveHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Table\LeagueTable;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\Registry\Registry;

final class LeagueModel extends SportsManagementAdminModel
{
    public function getTable($type = 'League', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'League') === 0) {
            return new LeagueTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    public function getlogohistoryLeague($leagueId = 0, $seasonId = 0, $logoonly = false): array
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('cl') . '.*',
                $db->quoteName('se.name', 'seasonname'),
            ])
            ->from($db->quoteName('#__sportsmanagement_league_logos', 'cl'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season', 'se')
                . ' ON ' . $db->quoteName('se.id') . ' = ' . $db->quoteName('cl.season_id')
            )
            ->order($db->quoteName('se.name') . ' DESC');

        $leagueId = (int) $leagueId;
        $seasonId = (int) $seasonId;

        if ($leagueId > 0) {
            $query->where($db->quoteName('cl.league_id') . ' = ' . $leagueId);
        }

        if ($seasonId > 0) {
            $query->where($db->quoteName('se.id') . ' = ' . $seasonId);
        }

        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return [];
        }
    }

    /**
     * Return the configured administrator extra fields and the value for one league.
     *
     * @return array<object>
     */
    public function getExtraFields(int $leagueId): array
    {
        if ($leagueId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('ef') . '.*',
                $db->quoteName('ev.fieldvalue', 'fvalue'),
                $db->quoteName('ev.id', 'value_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_user_extra_fields', 'ef'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_user_extra_fields_values', 'ev')
                . ' ON ' . $db->quoteName('ef.id') . ' = ' . $db->quoteName('ev.field_id')
                . ' AND ' . $db->quoteName('ev.jl_id') . ' = ' . $leagueId
            )
            ->where($db->quoteName('ef.template_backend') . ' LIKE ' . $db->quote('league'))
            ->order($db->quoteName('ef.ordering') . ' ASC');

        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return [];
        }
    }

    public function saveshort(): bool
    {
        $input = $this->administratorApplication()->getInput();
        $ids = array_values(array_filter(array_map('intval', (array) $input->post->get('cid', [], 'array'))));
        $post = $input->post->getArray();
        $result = true;

        foreach ($ids as $id) {
            $table = $this->getTable();

            if (!$table->load($id)) {
                $result = false;
                continue;
            }

            $table->country = (string) ($post['country' . $id] ?? $table->country);
            $table->associations = (int) ($post['association' . $id] ?? $table->associations);
            $table->agegroup_id = (int) ($post['agegroup' . $id] ?? $table->agegroup_id);
            $table->published_act_season = (int) ($post['published_act_season' . $id] ?? $table->published_act_season);
            $table->champions_complete = (int) ($post['champions_complete' . $id] ?? $table->champions_complete);

            if (!$table->check() || !$table->store()) {
                $result = false;
            }
        }

        return $result;
    }

    protected function prepareSportsManagementData(array $data): array
    {
        $post = $this->administratorApplication()->getInput()->post->getArray();
        $request = isset($data['request']) && is_array($data['request']) ? $data['request'] : [];

        if ($request !== []) {
            $data['sports_type_id'] = (int) ($request['sports_type_id'] ?? $data['sports_type_id'] ?? 0);
            $data['agegroup_id'] = (int) ($request['agegroup_id'] ?? $data['agegroup_id'] ?? 0);
            unset($data['request']);
        }

        foreach (['extended', 'extendeduser'] as $group) {
            if (!isset($post[$group]) || !is_array($post[$group])) {
                continue;
            }

            $registry = new Registry();
            $registry->loadArray($post[$group]);
            $data[$group] = $registry->toString();
        }

        foreach (['founded', 'dissolved'] as $field) {
            $data = $this->normaliseLeagueDate($data, $field);
        }

        if (!empty($data['picture'])) {
            $data['picture'] = MediaHelper::getCleanMediaFieldValue((string) $data['picture']);
        }

        return $data;
    }

    protected function afterSportsManagementSave(array $data, int $id, bool $isNew): void
    {
        $app = $this->administratorApplication();
        $post = $app->getInput()->post->getArray();

        $this->storeLogoHistory($post, $id);

        try {
            (new ExtraFieldsSaveHelper())->save($post, $id, $this->getDatabase());
        } catch (\Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'warning');
        }

        $app->setUserState('com_sportsmanagement.league_id', $id);
    }

    private function normaliseLeagueDate(array $data, string $field): array
    {
        $value = trim((string) ($data[$field] ?? ''));

        if ($value === '' || $value === '0000-00-00') {
            $data[$field] = '0000-00-00';
            $data[$field . '_year'] = null;

            return $data;
        }

        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
            $converted = SportsManagementDateHelper::convertDate($value, 0);

            if ($converted !== '') {
                $value = $converted;
            }
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $timestamp = strtotime($value);

            if ($timestamp !== false) {
                $value = date('Y-m-d', $timestamp);
            }
        }

        $data[$field] = $value;
        $data[$field . '_year'] = preg_match('/^(\d{4})-\d{2}-\d{2}$/', $value, $matches)
            ? $matches[1]
            : ($data[$field . '_year'] ?? null);

        return $data;
    }

    private function storeLogoHistory(array $post, int $leagueId): void
    {
        if ($leagueId <= 0 || empty($post['season_history'])) {
            return;
        }

        $logo = MediaHelper::getCleanMediaFieldValue((string) ($post['league_logo_history'] ?? ''));
        $seasonIds = array_values(array_unique(array_filter(array_map('intval', (array) $post['season_history']))));

        if ($seasonIds === []) {
            return;
        }

        $db = $this->getDatabase();

        foreach ($seasonIds as $seasonId) {
            try {
                $query = $db->createQuery()
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__sportsmanagement_league_logos'))
                    ->where($db->quoteName('league_id') . ' = ' . $leagueId)
                    ->where($db->quoteName('season_id') . ' = ' . $seasonId);
                $db->setQuery($query, 0, 1);
                $existingId = (int) $db->loadResult();

                if ($existingId > 0) {
                    $db->updateObject(
                        '#__sportsmanagement_league_logos',
                        (object) [
                            'id' => $existingId,
                            'logo_big' => $logo,
                        ],
                        'id',
                        true
                    );
                    continue;
                }

                $db->insertObject(
                    '#__sportsmanagement_league_logos',
                    (object) [
                        'league_id' => $leagueId,
                        'season_id' => $seasonId,
                        'logo_big' => $logo,
                    ]
                );
            } catch (\Throwable $e) {
                $this->administratorApplication()->enqueueMessage($e->getMessage(), 'warning');
            }
        }
    }
}
