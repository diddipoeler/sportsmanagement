<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Table\Table;
use Joomla\Filesystem\File;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 administrator model for persons/players. */
final class PlayerModel extends SportsManagementAdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/forms');

        $form = $this->loadForm(
            'com_sportsmanagement.player',
            'player',
            ['control' => 'jform', 'load_data' => $loadData]
        );

        if (!$form) {
            return false;
        }

        $params = ComponentHelper::getParams('com_sportsmanagement');

        if ($form->getField('picture')) {
            $form->setFieldAttribute('picture', 'default', (string) $params->get('ph_player', ''));
            $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/persons');
            $form->setFieldAttribute('picture', 'type', (string) $params->get('cfg_which_media_tool', 0));
        }

        return $form;
    }

    public function getTable($type = 'player', $prefix = 'sportsmanagementTable', $config = [])
    {
        $config['dbo'] = $this->getDatabase();

        return Table::getInstance($type, $prefix, $config);
    }

    public function getAgeGroupID($age)
    {
        if (!is_numeric($age)) {
            return 0;
        }

        $age = max(0, (int) $age);
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_agegroup'))
            ->where($age . ' >= ' . $db->quoteName('age_from'))
            ->where($age . ' <= ' . $db->quoteName('age_to'));

        try {
            return (int) $db->setQuery($query, 0, 1)->loadResult();
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    public function getPerson($personId = 0, $seasonPersonId = 0, $insertHits = 0)
    {
        $personId = max(0, (int) $personId);
        $seasonPersonId = max(0, (int) $seasonPersonId);
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('p.*')
            ->from($db->quoteName('#__sportsmanagement_person', 'p'));

        if ($seasonPersonId > 0) {
            $query->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_person_id', 'sp')
                . ' ON sp.person_id = p.id'
            )->where('sp.id = ' . $seasonPersonId);
        } elseif ($personId > 0) {
            $query->where('p.id = ' . $personId);
        } else {
            return null;
        }

        try {
            return $db->setQuery($query, 0, 1)->loadObject();
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /** Update selected person rows from the compact administrator list. */
    public function saveshort(?array $ids = null, ?array $post = null): bool
    {
        $input = $this->administratorApplication()->getInput();
        $ids ??= (array) $input->post->get('cid', [], 'array');
        $post ??= $input->post->getArray();
        $ids = $this->normaliseIds($ids);

        if (!$ids) {
            return true;
        }

        $db = $this->getDatabase();
        $db->transactionStart();

        try {
            foreach ($ids as $id) {
                $row = (object) [
                    'id' => $id,
                    'firstname' => trim((string) ($post['firstname' . $id] ?? '')),
                    'lastname' => trim((string) ($post['lastname' . $id] ?? '')),
                    'nickname' => trim((string) ($post['nickname' . $id] ?? '')),
                    'knvbnr' => trim((string) ($post['knvbnr' . $id] ?? '')),
                    'country' => trim((string) ($post['country' . $id] ?? '')),
                    'position_id' => max(0, (int) ($post['position' . $id] ?? 0)),
                    'agegroup_id' => max(0, (int) ($post['agegroup' . $id] ?? 0)),
                ];

                foreach (['birthday', 'deathday'] as $field) {
                    $raw = trim((string) ($post[$field . $id] ?? ''));

                    if ($raw === '' || str_starts_with($raw, '0000-00-00')) {
                        continue;
                    }

                    [$date, $timestamp] = $this->normaliseDate($raw);

                    if ($date !== null) {
                        $row->{$field} = $date;
                        $row->{$field . '_timestamp'} = $timestamp;
                    }
                }

                $db->updateObject('#__sportsmanagement_person', $row, 'id');
            }

            $db->transactionCommit();
            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * Import persons from a semicolon separated CSV upload.
     *
     * CSV headers are explicitly mapped; request-controlled column names are
     * never interpolated into SQL.
     */
    public function importupload(array $post = [], ?array $file = null): bool
    {
        $input = $this->administratorApplication()->getInput();
        $file ??= (array) $input->files->get('fileToUpload', [], 'array');

        if (empty($file['name']) || empty($file['tmp_name']) || (int) ($file['error'] ?? 0) !== UPLOAD_ERR_OK) {
            $this->setError('No valid CSV upload was received.');
            return false;
        }

        $size = (int) ($file['size'] ?? @filesize((string) $file['tmp_name']));

        if ($size <= 0 || $size > 5 * 1024 * 1024) {
            $this->setError('The CSV upload is empty or exceeds 5 MB.');
            return false;
        }

        $safeName = preg_replace('/[^A-Za-z0-9._-]+/', '_', basename((string) $file['name']));

        if ($safeName === '' || strtolower(pathinfo($safeName, PATHINFO_EXTENSION)) !== 'csv') {
            $this->setError('Only CSV files are accepted.');
            return false;
        }

        $targetDir = JPATH_ROOT . '/tmp';

        if (!is_dir($targetDir) && !@mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            $this->setError('The Joomla temporary directory is unavailable.');
            return false;
        }

        $target = $targetDir . '/' . uniqid('jsm-players-', true) . '-' . $safeName;

        try {
            if (!File::upload((string) $file['tmp_name'], $target)) {
                $this->setError('Unable to store the uploaded CSV file.');
                return false;
            }

            return $this->importCsvFile($target);
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return false;
        } finally {
            if (is_file($target)) {
                @unlink($target);
            }
        }
    }

    /** Assign selected people as players, staff or referees. */
    public function storeAssign(array $post): bool
    {
        $app = $this->administratorApplication();
        $option = 'com_sportsmanagement';
        $projectId = max(0, (int) ($post['project_id'] ?? $app->getUserState($option . '.pid', 0)));
        $teamId = max(0, (int) ($post['team_id'] ?? $app->getUserState($option . '.team_id', 0)));
        $seasonId = max(0, (int) ($post['season_id'] ?? $app->getUserState($option . '.season_id', 0)));
        $personType = max(0, (int) ($post['persontype'] ?? 0));
        $ids = $this->normaliseIds((array) ($post['cid'] ?? []));

        if (!$ids || $seasonId <= 0 || !in_array($personType, [1, 2, 3], true)) {
            return true;
        }

        if ($personType !== 3 && $teamId <= 0) {
            $this->setError('A team is required for player or staff assignment.');
            return false;
        }

        $db = $this->getDatabase();
        $now = Factory::getDate()->toSql();
        $userId = (int) $app->getIdentity()->id;
        $db->transactionStart();

        try {
            foreach ($ids as $personId) {
                $person = $this->getPerson($personId);

                if (!$person) {
                    continue;
                }

                if ($personType === 3) {
                    $seasonPersonId = $this->ensureSeasonPerson(
                        $personId,
                        $seasonId,
                        3,
                        (string) ($person->picture ?? ''),
                        $now,
                        $userId
                    );
                    $this->ensureProjectReferee($projectId, $seasonPersonId, $now, $userId);
                    continue;
                }

                $this->ensureSeasonTeamPerson(
                    $personId,
                    $teamId,
                    $seasonId,
                    $personType,
                    (string) ($person->picture ?? ''),
                    $now,
                    $userId
                );

                if ($projectId > 0) {
                    $projectPositionId = $this->findProjectPosition($projectId, (int) ($person->position_id ?? 0));
                    $this->ensurePersonProjectPosition(
                        $personId,
                        $projectId,
                        $projectPositionId,
                        $personType,
                        $now,
                        $userId
                    );
                }
            }

            $db->transactionCommit();
            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());
            return false;
        }
    }

    protected function prepareSportsManagementData(array $data): array
    {
        $parts = [trim((string) ($data['firstname'] ?? '')), trim((string) ($data['lastname'] ?? ''))];

        if (empty($data['alias'])) {
            $data['alias'] = OutputFilter::stringURLSafe(implode(' ', $parts));
        } else {
            $data['alias'] = OutputFilter::stringURLSafe((string) $data['alias']);
        }

        foreach (['birthday', 'deathday'] as $field) {
            $raw = trim((string) ($data[$field] ?? ''));
            [$date, $timestamp] = $this->normaliseDate($raw);
            $data[$field] = $date;
            $data[$field . '_timestamp'] = $timestamp;
        }

        $input = $this->administratorApplication()->getInput();

        foreach (['extended', 'extendeduser'] as $field) {
            $values = $input->post->get($field, [], 'array');

            if ($values) {
                $registry = new Registry();
                $registry->loadArray($values);
                $data[$field] = $registry->toString();
            }
        }

        return $data;
    }

    private function importCsvFile(string $path): bool
    {
        $handle = @fopen($path, 'rb');

        if (!$handle) {
            $this->setError('Unable to read the uploaded CSV file.');
            return false;
        }

        $header = fgetcsv($handle, 0, ';');

        if (!is_array($header) || !$header) {
            fclose($handle);
            $this->setError('The CSV file does not contain a header row.');
            return false;
        }

        $headerMap = [
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'nickname' => 'nickname',
            'birthday' => 'birthday',
            'country' => 'country',
            'knvbnr' => 'knvbnr',
            'licence/registrationn' => 'knvbnr',
            'licence' => 'knvbnr',
            'gender' => 'gender',
            'email' => 'email',
            'phone' => 'phone',
            'mobile' => 'mobile',
            'sports_type_id' => 'sports_type_id',
            'position_id' => 'position_id',
            'agegroup_id' => 'agegroup_id',
        ];
        $columns = [];

        foreach ($header as $index => $name) {
            $normalised = strtolower(trim((string) $name));

            if (isset($headerMap[$normalised])) {
                $columns[(int) $index] = $headerMap[$normalised];
            }
        }

        if (!in_array('firstname', $columns, true) && !in_array('lastname', $columns, true)) {
            fclose($handle);
            $this->setError('The CSV header must contain firstname or lastname.');
            return false;
        }

        $db = $this->getDatabase();
        $db->transactionStart();

        try {
            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                $data = [];

                foreach ($columns as $index => $column) {
                    $data[$column] = trim((string) ($row[$index] ?? ''));
                }

                if (($data['firstname'] ?? '') === '' && ($data['lastname'] ?? '') === '') {
                    continue;
                }

                if ($this->personAlreadyExists($data)) {
                    continue;
                }

                if (!empty($data['birthday'])) {
                    [$date] = $this->normaliseDate((string) $data['birthday']);
                    $data['birthday'] = $date;
                }

                foreach (['gender', 'sports_type_id', 'position_id', 'agegroup_id'] as $integerField) {
                    if (array_key_exists($integerField, $data)) {
                        $data[$integerField] = max(0, (int) $data[$integerField]);
                    }
                }

                $data['alias'] = OutputFilter::stringURLSafe(
                    trim(($data['firstname'] ?? '') . ' ' . ($data['lastname'] ?? ''))
                );
                $data['published'] = 1;
                $db->insertObject('#__sportsmanagement_person', (object) $data);
            }

            fclose($handle);
            $db->transactionCommit();
            return true;
        } catch (\Throwable $e) {
            fclose($handle);
            $db->transactionRollback();
            throw $e;
        }
    }

    private function personAlreadyExists(array $data): bool
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('id')
            ->from($db->quoteName('#__sportsmanagement_person'));

        if (!empty($data['knvbnr'])) {
            $query->where($db->quoteName('knvbnr') . ' = ' . $db->quote((string) $data['knvbnr']));
        } else {
            $query->where($db->quoteName('firstname') . ' = ' . $db->quote((string) ($data['firstname'] ?? '')))
                ->where($db->quoteName('lastname') . ' = ' . $db->quote((string) ($data['lastname'] ?? '')));

            if (!empty($data['birthday'])) {
                [$date] = $this->normaliseDate((string) $data['birthday']);

                if ($date !== null) {
                    $query->where($db->quoteName('birthday') . ' = ' . $db->quote($date));
                }
            }
        }

        return (bool) $db->setQuery($query, 0, 1)->loadResult();
    }

    private function ensureSeasonTeamPerson(
        int $personId,
        int $teamId,
        int $seasonId,
        int $personType,
        string $picture,
        string $now,
        int $userId
    ): int {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('id')
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id'))
            ->where('person_id = ' . $personId)
            ->where('team_id = ' . $teamId)
            ->where('season_id = ' . $seasonId)
            ->where('persontype = ' . $personType);
        $id = (int) $db->setQuery($query, 0, 1)->loadResult();
        $row = (object) [
            'id' => $id,
            'person_id' => $personId,
            'team_id' => $teamId,
            'season_id' => $seasonId,
            'persontype' => $personType,
            'picture' => $picture,
            'active' => 1,
            'published' => 1,
            'modified' => $now,
            'modified_by' => $userId,
        ];

        if ($id > 0) {
            $db->updateObject('#__sportsmanagement_season_team_person_id', $row, 'id');
            return $id;
        }

        unset($row->id);
        $db->insertObject('#__sportsmanagement_season_team_person_id', $row);
        return (int) $db->insertid();
    }

    private function ensureSeasonPerson(
        int $personId,
        int $seasonId,
        int $personType,
        string $picture,
        string $now,
        int $userId
    ): int {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('id')
            ->from($db->quoteName('#__sportsmanagement_season_person_id'))
            ->where('person_id = ' . $personId)
            ->where('season_id = ' . $seasonId)
            ->where('persontype = ' . $personType);
        $id = (int) $db->setQuery($query, 0, 1)->loadResult();
        $row = (object) [
            'id' => $id,
            'person_id' => $personId,
            'team_id' => 0,
            'season_id' => $seasonId,
            'persontype' => $personType,
            'picture' => $picture,
            'published' => 1,
            'modified' => $now,
            'modified_by' => $userId,
        ];

        if ($id > 0) {
            $db->updateObject('#__sportsmanagement_season_person_id', $row, 'id');
            return $id;
        }

        unset($row->id);
        $db->insertObject('#__sportsmanagement_season_person_id', $row);
        return (int) $db->insertid();
    }

    private function ensureProjectReferee(int $projectId, int $seasonPersonId, string $now, int $userId): void
    {
        if ($projectId <= 0 || $seasonPersonId <= 0) {
            return;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('id')
            ->from($db->quoteName('#__sportsmanagement_project_referee'))
            ->where('project_id = ' . $projectId)
            ->where('person_id = ' . $seasonPersonId);
        $id = (int) $db->setQuery($query, 0, 1)->loadResult();
        $row = (object) [
            'id' => $id,
            'project_id' => $projectId,
            'person_id' => $seasonPersonId,
            'published' => 1,
            'modified' => $now,
            'modified_by' => $userId,
        ];

        if ($id > 0) {
            $db->updateObject('#__sportsmanagement_project_referee', $row, 'id');
        } else {
            unset($row->id);
            $db->insertObject('#__sportsmanagement_project_referee', $row);
        }
    }

    private function findProjectPosition(int $projectId, int $positionId): int
    {
        if ($projectId <= 0 || $positionId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('id')
            ->from($db->quoteName('#__sportsmanagement_project_position'))
            ->where('project_id = ' . $projectId)
            ->where('position_id = ' . $positionId);

        return (int) $db->setQuery($query, 0, 1)->loadResult();
    }

    private function ensurePersonProjectPosition(
        int $personId,
        int $projectId,
        int $projectPositionId,
        int $personType,
        string $now,
        int $userId
    ): void {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('id')
            ->from($db->quoteName('#__sportsmanagement_person_project_position'))
            ->where('person_id = ' . $personId)
            ->where('project_id = ' . $projectId)
            ->where('persontype = ' . $personType);
        $id = (int) $db->setQuery($query, 0, 1)->loadResult();
        $row = (object) [
            'id' => $id,
            'person_id' => $personId,
            'project_id' => $projectId,
            'project_position_id' => $projectPositionId,
            'persontype' => $personType,
            'published' => 1,
            'modified' => $now,
            'modified_by' => $userId,
        ];

        if ($id > 0) {
            $db->updateObject('#__sportsmanagement_person_project_position', $row, 'id');
        } else {
            unset($row->id);
            $db->insertObject('#__sportsmanagement_person_project_position', $row);
        }
    }

    private function normaliseDate(string $raw): array
    {
        $raw = trim($raw);

        if ($raw === '' || str_starts_with($raw, '0000-00-00')) {
            return [null, 0];
        }

        foreach (['!Y-m-d', '!d-m-Y', '!d.m.Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $raw);

            if ($date instanceof \DateTimeImmutable) {
                return [$date->format('Y-m-d'), $date->getTimestamp()];
            }
        }

        $timestamp = strtotime($raw);

        return $timestamp === false ? [null, 0] : [date('Y-m-d', $timestamp), $timestamp];
    }

    private function normaliseIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));
    }
}
