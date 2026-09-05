<?php
/**
 * Joomla 5/6 administrator model for clubs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtraFieldsSaveHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\LocationHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\RemoteImageDownloadHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\Language\Text;

final class ClubModel extends SportsManagementAdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        $form = parent::getForm($data, $loadData);

        if (!$form) {
            return false;
        }

        $params = ComponentHelper::getParams('com_sportsmanagement');
        $mediaTool = trim((string) $params->get('cfg_which_media_tool', 'media')) ?: 'media';

        if (!(bool) $params->get('show_team_community', 0)) {
            $form->setFieldAttribute('merge_teams', 'type', 'hidden');
        }

        foreach (['logo_small', 'logo_middle', 'logo_big', 'trikot_home', 'trikot_away'] as $fieldName) {
            if ($form->getField($fieldName)) {
                $form->setFieldAttribute($fieldName, 'type', $mediaTool);
            }
        }

        if ((bool) $params->get('cfg_use_plz_table', 0)) {
            $clubId = (int) $form->getValue('id');
            $country = '';

            if ($clubId > 0) {
                $table = $this->getTable();

                if ($table->load($clubId)) {
                    $country = (string) ($table->country ?? '');
                }
            }

            if ($country !== '' && $this->countryHasPostalCodes($country)) {
                $form->setFieldAttribute('zipcode', 'type', 'dependsql');
                $form->setFieldAttribute('zipcode', 'size', '10');
                $form->setFieldAttribute('location', 'type', 'dependsql');
                $form->setFieldAttribute('location', 'size', '10');
            }
        }

        try {
            $tableName = $this->getDatabase()->getPrefix() . 'sportsmanagement_club';

            foreach ($this->getDatabase()->getTableColumns($tableName, true) as $fieldName => $type) {
                if (!$form->getField((string) $fieldName)) {
                    continue;
                }

                if (preg_match('/varchar\s*\(\s*(\d+)\s*\)/i', (string) $type, $match)) {
                    $form->setFieldAttribute((string) $fieldName, 'size', (string) (int) $match[1]);
                }
            }
        } catch (\Throwable) {
            // Dynamic input sizes are only a UI enhancement.
        }

        return $form;
    }

    public function getlogohistory($club_id = 0, $season_id = 0, $team_id = 0, $logoonly = false)
    {
        $app = $this->administratorApplication();
        $db = $this->getDatabase();
        $clubId = (int) $club_id;
        $seasonId = (int) $season_id;
        $teamId = (int) $team_id;
        $query = $db->createQuery()
            ->select('cl.*, se.name AS seasonname')
            ->from($db->quoteName('#__sportsmanagement_club_logos', 'cl'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season', 'se') . ' ON se.id = cl.season_id');

        if ($teamId > 0) {
            $query->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON c.id = cl.club_id')
                ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON t.club_id = c.id')
                ->where('t.id = ' . $teamId);
        }

        if ($clubId > 0) {
            $query->where('cl.club_id = ' . $clubId);
        }

        if ($seasonId > 0) {
            $query->where('se.id = ' . $seasonId);
        }

        $query->order('seasonname DESC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (\Throwable $e) {
            $app->enqueueMessage(__METHOD__ . ' ' . $e->getMessage(), 'error');
            return [];
        }
    }

    public function getuserextrafieldvalue($club_id = 0, $fieldtext = '')
    {
        $clubId = (int) $club_id;
        $fieldText = trim((string) $fieldtext);

        if ($clubId <= 0 || $fieldText === '') {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('uefv.fieldvalue'))
            ->from($db->quoteName('#__sportsmanagement_user_extra_fields_values', 'uefv'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_user_extra_fields', 'uef') . ' ON uef.id = uefv.field_id'
            )
            ->where($db->quoteName('uefv.jl_id') . ' = ' . $clubId)
            ->where($db->quoteName('uef.name') . ' LIKE ' . $db->quote('%' . $fieldText . '%'))
            ->where($db->quoteName('uef.template_backend') . ' = ' . $db->quote('club'));

        try {
            $db->setQuery($query);
            return $db->loadResult();
        } catch (\Throwable $e) {
            $this->administratorApplication()->enqueueMessage(
                Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
                'notice'
            );
            return false;
        }
    }

    public function saveshort(): bool
    {
        $app = $this->administratorApplication();
        $input = $app->getInput();
        $date = Factory::getDate();
        $user = $app->getIdentity();
        $ids = array_values(array_filter(array_map('intval', (array) $input->post->get('cid', [], 'array'))));
        $post = $input->post->getArray();

        if (!$ids) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_SAVE_NO_SELECT'));
            return false;
        }

        foreach ($ids as $id) {
            $table = $this->getTable();

            if (!$table->load($id)) {
                return false;
            }

            $table->zipcode = (string) ($post['zipcode' . $id] ?? $table->zipcode ?? '');
            $table->location = (string) ($post['location' . $id] ?? $table->location ?? '');
            $table->address = (string) ($post['address' . $id] ?? $table->address ?? '');
            $table->country = (string) ($post['country' . $id] ?? $table->country ?? '');
            $table->founded_year = (string) ($post['founded_year' . $id] ?? $table->founded_year ?? '');
            $table->unique_id = (string) ($post['unique_id' . $id] ?? $table->unique_id ?? '');
            $table->new_club_id = (int) ($post['new_club_id' . $id] ?? $table->new_club_id ?? 0);
            $table->name = trim((string) ($post['club_name' . $id] ?? $table->name ?? ''));
            $table->alias = OutputFilter::stringURLSafe($table->name);
            $table->modified = $date->toSql();
            $table->modified_by = (int) $user->id;

            $this->applyCoordinates($table);

            if (!$table->check() || !$table->store()) {
                return false;
            }
        }

        return true;
    }

    public function teamsofclub($club_id)
    {
        $clubId = (int) $club_id;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select(['t.id', 't.name', 't.club_id', 't.short_name'])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->where($db->quoteName('t.club_id') . ' = ' . $clubId)
            ->order($db->quoteName('t.name') . ' ASC');
        try {
            $db->setQuery($query);
            return $db->loadObjectList();
        } catch (\Throwable $e) {
            $this->administratorApplication()->enqueueMessage(
                Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
                'notice'
            );
            return false;
        }
    }

    protected function prepareSportsManagementData(array $data): array
    {
        $app = $this->administratorApplication();
        $post = $app->getInput()->post->getArray();
        $params = ComponentHelper::getParams('com_sportsmanagement');

        if (isset($post['copy_jform']) && is_array($post['copy_jform'])) {
            foreach (['logo_big', 'logo_middle', 'logo_small', 'trikot_home', 'trikot_away'] as $fieldName) {
                if (array_key_exists($fieldName, $post['copy_jform'])) {
                    $data[$fieldName] = $post['copy_jform'][$fieldName];
                }
            }
        }

        if (!empty($data['link_logo_big'])) {
            $downloaded = $this->downloadClubLogo((string) $data['link_logo_big']);

            if ($downloaded !== '') {
                $data['logo_big'] = $downloaded;
            }
        }

        $defaults = [
            'logo_big' => (string) $params->get('ph_logo_big', ''),
            'logo_middle' => (string) $params->get('ph_logo_medium', ''),
            'logo_small' => (string) $params->get('ph_logo_small', ''),
        ];

        foreach ($defaults as $fieldName => $defaultValue) {
            if (empty($data[$fieldName])) {
                $data[$fieldName] = $defaultValue;
            } else {
                $data[$fieldName] = MediaHelper::getCleanMediaFieldValue((string) $data[$fieldName]);
            }
        }

        foreach (['trikot_home', 'trikot_away'] as $fieldName) {
            if (!empty($data[$fieldName])) {
                $data[$fieldName] = MediaHelper::getCleanMediaFieldValue((string) $data[$fieldName]);
            }
        }

        $data = $this->normaliseClubDate($data, 'founded');
        $data = $this->normaliseClubDate($data, 'dissolved');

        if (empty($data['founded_year'])) {
            $data['founded_year'] = 'kein';
        }

        return $data;
    }

    protected function afterSportsManagementSave(array $data, int $id, bool $isNew): void
    {
        $app = $this->administratorApplication();
        $post = $app->getInput()->post->getArray();

        $this->updateSubmittedTeams($post);
        $this->storeLogoHistory($post, $id);

        try {
            (new ExtraFieldsSaveHelper())->save($post, $id, $this->getDatabase());
        } catch (\Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'warning');
        }

        $app->setUserState('com_sportsmanagement.club_id', $id);
    }

    private function countryHasPostalCodes(string $country): bool
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_countries_plz', 'a'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_countries', 'c') . ' ON c.alpha2 = a.country_code')
            ->where($db->quoteName('c.alpha3') . ' = ' . $db->quote($country));
        $db->setQuery($query);

        return (int) $db->loadResult() > 0;
    }

    private function applyCoordinates(object $table): void
    {
        $parts = [];

        if (trim((string) ($table->address ?? '')) !== '') {
            $parts[] = trim((string) $table->address);
        }

        $location = trim((string) ($table->location ?? ''));
        $zipcode = trim((string) ($table->zipcode ?? ''));

        if ($location !== '') {
            $parts[] = trim($zipcode . ' ' . $location);
        }

        if (trim((string) ($table->country ?? '')) !== '') {
            $parts[] = trim((string) $table->country);
        }

        if (!$parts) {
            return;
        }

        $coords = (new LocationHelper())->resolve(implode(', ', $parts));

        if (isset($coords['latitude'])) {
            $table->latitude = $coords['latitude'];
        }

        if (isset($coords['longitude'])) {
            $table->longitude = $coords['longitude'];
        }
    }

    private function downloadClubLogo(string $url): string
    {
        $parts = parse_url($url);

        if (!is_array($parts) || !in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)) {
            return '';
        }

        $basename = basename((string) ($parts['path'] ?? ''));
        $basename = preg_replace('/[^A-Za-z0-9._-]/', '_', $basename) ?: '';

        if ($basename === '' || $basename === '.' || $basename === '..') {
            return '';
        }

        $extension = strtolower((string) pathinfo($basename, PATHINFO_EXTENSION));

        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return '';
        }

        $relativePath = 'images/com_sportsmanagement/database/clubs/large/' . $basename;
        $absolutePath = JPATH_ROOT . '/' . $relativePath;
        $maxBytes = max(
            1,
            (int) ComponentHelper::getParams('com_sportsmanagement')->get('image_max_size', 120)
        ) * 1024;

        if (!(new RemoteImageDownloadHelper())->download($url, $absolutePath, $maxBytes)) {
            return '';
        }

        return $relativePath;
    }

    private function normaliseClubDate(array $data, string $field): array
    {
        $value = trim((string) ($data[$field] ?? ''));

        if ($value === '' || $value === '0000-00-00') {
            $data[$field] = '0000-00-00';
            return $data;
        }

        $converted = SportsManagementDateHelper::convertDate($value, 0);

        if ($converted !== '') {
            $value = $converted;
        }

        $data[$field] = $value;
        $timestamp = strtotime($value);

        if ($timestamp !== false) {
            $data[$field . '_year'] = date('Y', $timestamp);
            $data[$field . '_timestamp'] = SportsManagementDateHelper::getTimestamp($value);
        }

        return $data;
    }

    private function updateSubmittedTeams(array $post): void
    {
        if (!isset($post['team_id']) || !is_array($post['team_id'])) {
            return;
        }

        $db = $this->getDatabase();

        foreach ($post['team_id'] as $key => $value) {
            $teamId = (int) $value;

            if ($teamId <= 0) {
                continue;
            }

            $teamName = trim((string) ($post['team_value_id'][$key] ?? ''));
            $shortName = trim((string) ($post['team_short_name'][$key] ?? ''));
            $clubId = (int) ($post['club_value_id'][$key] ?? 0);

            $object = (object) [
                'id' => $teamId,
                'name' => $teamName,
                'short_name' => $shortName,
                'club_id' => $clubId,
                'alias' => OutputFilter::stringURLSafe($teamName),
            ];

            $db->updateObject('#__sportsmanagement_team', $object, 'id', true);
        }
    }

    private function storeLogoHistory(array $post, int $clubId): void
    {
        if ($clubId <= 0 || !isset($post['season_history']) || !is_array($post['season_history'])) {
            return;
        }

        $logo = (string) ($post['logo_big_history'] ?? '');
        $modified = Factory::getDate()->toSql();
        $modifiedBy = (int) $this->administratorApplication()->getIdentity()->id;
        $db = $this->getDatabase();

        foreach ($post['season_history'] as $seasonId) {
            $seasonId = (int) $seasonId;

            if ($seasonId <= 0) {
                continue;
            }

            $query = $db->createQuery()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__sportsmanagement_club_logos'))
                ->where($db->quoteName('club_id') . ' = ' . $clubId)
                ->where($db->quoteName('season_id') . ' = ' . $seasonId);
            $db->setQuery($query);
            $existingId = (int) $db->loadResult();

            if ($existingId > 0) {
                $object = (object) [
                    'id' => $existingId,
                    'logo_big' => $logo,
                    'modified' => $modified,
                    'modified_by' => $modifiedBy,
                ];
                $db->updateObject('#__sportsmanagement_club_logos', $object, 'id', true);
                continue;
            }

            $object = (object) [
                'club_id' => $clubId,
                'season_id' => $seasonId,
                'logo_big' => $logo,
                'modified' => $modified,
                'modified_by' => $modifiedBy,
            ];
            $db->insertObject('#__sportsmanagement_club_logos', $object);
        }
    }
}
