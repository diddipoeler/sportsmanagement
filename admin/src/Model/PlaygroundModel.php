<?php
/**
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtraFieldsSaveHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Native Joomla 5/6 administrator model for playground editing and reads.
 */
final class PlaygroundModel extends SportsManagementAdminModel
{
    public static $playground = null;
    public static int $cfg_which_database = 0;

    private static ?DatabaseInterface $database = null;
    private static int $cachedPlaygroundId = 0;

    public function __construct(
        $config = [],
        ?MVCFactoryInterface $factory = null,
        ?FormFactoryInterface $formFactory = null
    ) {
        parent::__construct($config, $factory, $formFactory);

        $app = $this->administratorApplication();
        $input = $app->getInput();
        self::$cfg_which_database = max(
            0,
            $input->getInt(
                'cfg_which_database',
                (int) $app->getUserState('com_sportsmanagement.cfg_which_database', 0)
            )
        );

        try {
            self::$database = (new SportsManagementDatabaseResolver())->resolve(
                self::$cfg_which_database,
                $this->getDatabase()
            );
        } catch (\Throwable) {
            self::$database = null;
        }
    }

    public function getForm($data = [], $loadData = true)
    {
        $form = parent::getForm($data, $loadData);

        if ($form) {
            $mediaTool = (string) ComponentHelper::getParams('com_sportsmanagement')
                ->get('cfg_which_media_tool', 'media');

            if ($mediaTool !== '') {
                $form->setFieldAttribute('picture', 'type', $mediaTool);
            }
        }

        return $form;
    }

    public function getlogohistoryPlayground($playground_id = 0, $season_id = 0, $logoonly = false): array
    {
        $playgroundId = max(0, (int) $playground_id);
        $seasonId = max(0, (int) $season_id);
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('cl.id'),
                $db->quoteName('cl.playground_id'),
                $db->quoteName('cl.season_id'),
                $db->quoteName('cl.logo_big'),
                $db->quoteName('cl.modified'),
                $db->quoteName('cl.modified_by'),
                $db->quoteName('se.name', 'seasonname'),
            ])
            ->from($db->quoteName('#__sportsmanagement_playground_logos', 'cl'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season', 'se')
                . ' ON ' . $db->quoteName('se.id') . ' = ' . $db->quoteName('cl.season_id')
            )
            ->order($db->quoteName('se.name') . ' DESC');

        if ($playgroundId > 0) {
            $query->where($db->quoteName('cl.playground_id') . ' = ' . $playgroundId);
        }

        if ($seasonId > 0) {
            $query->where($db->quoteName('cl.season_id') . ' = ' . $seasonId);
        }

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (\Throwable) {
            return [];
        }

        if ($logoonly) {
            return array_values(
                array_filter(
                    array_map(static fn($row): string => (string) ($row->logo_big ?? ''), $rows)
                )
            );
        }

        return $rows;
    }

    public function getPlaygroundNotic($playground_id): array
    {
        $playgroundId = max(0, (int) $playground_id);
        if ($playgroundId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_playground_details'))
            ->where($db->quoteName('playground_id') . ' = ' . $playgroundId)
            ->order($db->quoteName('date_von') . ' DESC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    public function getAddressString(): ?string
    {
        $playground = self::getPlayground();

        if (!$playground) {
            return null;
        }

        $parts = [];
        foreach (['address', 'state'] as $field) {
            $value = trim((string) ($playground->{$field} ?? ''));
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        $city = trim((string) ($playground->city ?? $playground->location ?? ''));
        $zipcode = trim((string) ($playground->zipcode ?? ''));
        if ($city !== '') {
            $parts[] = trim($zipcode . ' ' . $city);
        }

        $country = trim((string) ($playground->country ?? ''));
        if ($country !== '') {
            if (!class_exists('JSMCountries', false)) {
                $countriesHelper = JPATH_SITE . '/components/com_sportsmanagement/helpers/countries.php';

                if (is_file($countriesHelper)) {
                    require_once $countriesHelper;
                }
            }

            $parts[] = class_exists('JSMCountries', false)
                ? (string) \JSMCountries::getShortCountryName($country)
                : $country;
        }

        return implode(', ', array_filter($parts, static fn($value): bool => $value !== ''));
    }

    public static function getPlayground($pgid = 0, $inserthits = 0)
    {
        $app = self::backendApplication();
        $input = $app->getInput();
        $playgroundId = max(0, (int) $pgid);

        if ($playgroundId <= 0) {
            $playgroundId = max(0, $input->getInt('pgid', $input->getInt('id', 0)));
        }

        if ($playgroundId <= 0) {
            return null;
        }

        self::updateHits($playgroundId, $inserthits);

        if (self::$playground !== null && self::$cachedPlaygroundId === $playgroundId) {
            return self::$playground;
        }

        $db = self::getStaticDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_playground'))
            ->where($db->quoteName('id') . ' = ' . $playgroundId);

        $db->setQuery($query, 0, 1);
        self::$playground = $db->loadObject();
        self::$cachedPlaygroundId = self::$playground ? $playgroundId : 0;

        return self::$playground;
    }

    public static function updateHits($pgid = 0, $inserthits = 0): void
    {
        $playgroundId = max(0, (int) $pgid);

        if (!$inserthits || $playgroundId <= 0) {
            return;
        }

        $db = self::getStaticDatabase();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__sportsmanagement_playground'))
            ->set($db->quoteName('hits') . ' = ' . $db->quoteName('hits') . ' + 1')
            ->where($db->quoteName('id') . ' = ' . $playgroundId);

        $db->setQuery($query);
        $db->execute();
    }

    public function getNextGames($project = 0, $pgid = 0, $played = 0, $allproject = 0): array
    {
        $projectId = max(0, (int) $project);
        $playgroundId = max(0, (int) $pgid);
        $playground = self::getPlayground($playgroundId);

        if (!$playground || (int) $playground->id <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.id'),
                $db->quoteName('m.match_date'),
                $db->quoteName('m.projectteam1_id'),
                $db->quoteName('m.projectteam2_id'),
                $db->quoteName('m.team1_result'),
                $db->quoteName('m.team2_result'),
                $db->quoteName('m.time_present'),
                $db->quoteName('p.name', 'project_name'),
                $db->quoteName('st1.team_id', 'team1'),
                $db->quoteName('st2.team_id', 'team2'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'tj')
                . ' ON ' . $db->quoteName('tj.id') . ' = ' . $db->quoteName('m.projectteam1_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'tj2')
                . ' ON ' . $db->quoteName('tj2.id') . ' = ' . $db->quoteName('m.projectteam2_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('tj.project_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st1')
                . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('tj.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st2')
                . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('tj2.team_id')
            )
            ->where($db->quoteName('m.playground_id') . ' = ' . (int) $playground->id)
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1')
            ->where(
                $db->quoteName('m.match_timestamp')
                . ($played ? ' < ' : ' > ')
                . time()
            )
            ->order($db->quoteName('m.match_date') . ' ASC');

        if ($projectId > 0 && !$allproject) {
            $query->where($db->quoteName('p.id') . ' = ' . $projectId);
        }

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];

            foreach ($rows as $row) {
                $row->time_present = self::formatTimePresent($row->time_present ?? null);
            }

            return $rows;
        } catch (\Throwable) {
            return [];
        }
    }

    protected function prepareSportsManagementData(array $data): array
    {
        $post = $this->administratorApplication()->getInput()->post->getArray();

        if (isset($post['copy_jform']['picture'])) {
            $data['picture'] = $post['copy_jform']['picture'];
        }

        if (!empty($data['picture'])) {
            $data['picture'] = MediaHelper::getCleanMediaFieldValue((string) $data['picture']);
        }

        $data['max_visitors'] = max(0, (int) ($data['max_visitors'] ?? 0));
        $data['max_visitors_int'] = max(0, (int) ($data['max_visitors_int'] ?? 0));

        foreach (['extended', 'extendeduser'] as $field) {
            if (isset($post[$field]) && is_array($post[$field])) {
                $registry = new Registry();
                $registry->loadArray($post[$field]);
                $data[$field] = (string) $registry;
            }
        }

        return $data;
    }

    protected function afterSportsManagementSave(array $data, int $id, bool $isNew): void
    {
        if ($id <= 0) {
            return;
        }

        self::$playground = null;
        self::$cachedPlaygroundId = 0;

        $app = $this->administratorApplication();
        $post = $app->getInput()->post->getArray();

        $this->storePlaygroundDetails($post, $id, $data);
        $this->storeLogoHistory($post, $id, $data);

        try {
            (new ExtraFieldsSaveHelper())->save($post, $id, $this->getDatabase());
        } catch (\Throwable) {
            // Extra-field persistence must not invalidate the saved playground.
        }

        $app->setUserState('com_sportsmanagement.playground_id', $id);
    }

    private function storePlaygroundDetails(array $post, int $playgroundId, array $data): void
    {
        $db = $this->getDatabase();
        $modified = (string) ($data['modified'] ?? Factory::getDate()->toSql());
        $modifiedBy = (int) ($data['modified_by'] ?? $this->administratorApplication()->getIdentity()->id);

        foreach ((array) ($post['date_von'] ?? []) as $key => $dateFrom) {
            $profile = new \stdClass();
            $profile->playground_id = $playgroundId;
            $profile->date_von = $this->normaliseLegacyDate($dateFrom);
            $profile->date_bis = $this->normaliseLegacyDate($post['date_bis'][$key] ?? '');
            $profile->name_visitors = (string) ($post['name_visitors'][$key] ?? '');
            $profile->notes = (string) ($post['notes'][$key] ?? '');
            $profile->max_visitors = max(0, (int) ($post['max_visitors'][$key] ?? 0));
            $profile->max_visitors_int = max(0, (int) ($post['max_visitors_int'][$key] ?? 0));
            $profile->timestamp_von = $this->legacyTimestamp($profile->date_von);
            $profile->timestamp_bis = $this->legacyTimestamp($profile->date_bis);
            $profile->modified = $modified;
            $profile->modified_by = $modifiedBy;

            try {
                $db->insertObject('#__sportsmanagement_playground_details', $profile);
            } catch (\Throwable) {
                // Preserve the successful main save if an optional history row is invalid.
            }
        }

        foreach ((array) ($post['change_id'] ?? []) as $key => $rawId) {
            $detailId = max(0, (int) $rawId);
            if ($detailId <= 0) {
                continue;
            }

            $profile = new \stdClass();
            $profile->id = $detailId;
            $profile->playground_id = $playgroundId;
            $profile->date_von = $this->normaliseLegacyDate(
                $this->postArrayValue((array) ($post['change_date_von'] ?? []), $key, $detailId)
            );
            $profile->date_bis = $this->normaliseLegacyDate(
                $this->postArrayValue((array) ($post['change_date_bis'] ?? []), $key, $detailId)
            );
            $profile->name_visitors = (string) $this->postArrayValue(
                (array) ($post['change_name_visitors'] ?? []),
                $key,
                $detailId,
                ''
            );
            $profile->notes = (string) $this->postArrayValue(
                (array) ($post['change_notes'] ?? []),
                $key,
                $detailId,
                ''
            );
            $profile->max_visitors = max(
                0,
                (int) $this->postArrayValue(
                    (array) ($post['change_max_visitors'] ?? []),
                    $key,
                    $detailId,
                    0
                )
            );
            $profile->max_visitors_int = max(
                0,
                (int) $this->postArrayValue(
                    (array) ($post['change_max_visitors_int'] ?? []),
                    $key,
                    $detailId,
                    0
                )
            );
            $profile->timestamp_von = $this->legacyTimestamp($profile->date_von);
            $profile->timestamp_bis = $this->legacyTimestamp($profile->date_bis);
            $profile->modified = $modified;
            $profile->modified_by = $modifiedBy;

            try {
                $db->updateObject('#__sportsmanagement_playground_details', $profile, 'id', true);
            } catch (\Throwable) {
                // Preserve the successful main save if an optional detail update fails.
            }
        }
    }

    private function storeLogoHistory(array $post, int $playgroundId, array $data): void
    {
        $seasonIds = array_values(
            array_unique(
                array_filter(
                    array_map('intval', (array) ($post['season_history'] ?? [])),
                    static fn(int $id): bool => $id > 0
                )
            )
        );

        if (!$seasonIds) {
            return;
        }

        $db = $this->getDatabase();
        $logo = (string) ($post['playground_logo_history'] ?? '');
        $modified = (string) ($data['modified'] ?? Factory::getDate()->toSql());
        $modifiedBy = (int) ($data['modified_by'] ?? $this->administratorApplication()->getIdentity()->id);

        foreach ($seasonIds as $seasonId) {
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__sportsmanagement_playground_logos'))
                ->where($db->quoteName('playground_id') . ' = ' . $playgroundId)
                ->where($db->quoteName('season_id') . ' = ' . $seasonId);
            $db->setQuery($query, 0, 1);
            $existingId = (int) $db->loadResult();

            $profile = new \stdClass();
            if ($existingId > 0) {
                $profile->id = $existingId;
            }
            $profile->playground_id = $playgroundId;
            $profile->season_id = $seasonId;
            $profile->logo_big = $logo;
            $profile->modified = $modified;
            $profile->modified_by = $modifiedBy;

            try {
                if ($existingId > 0) {
                    $db->updateObject('#__sportsmanagement_playground_logos', $profile, 'id', true);
                } else {
                    $db->insertObject('#__sportsmanagement_playground_logos', $profile);
                }
            } catch (\Throwable) {
                // Optional history must not invalidate the main playground save.
            }
        }
    }

    private function normaliseLegacyDate($value): string
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '00-00-0000' || $value === '0000-00-00') {
            return '0000-00-00';
        }

        $converted = SportsManagementDateHelper::convertDate($value, 0);

        return $converted !== '' ? $converted : $value;
    }

    private function legacyTimestamp(string $date): int
    {
        if ($date === '' || $date === '0000-00-00') {
            return 0;
        }

        return SportsManagementDateHelper::getTimestamp($date);
    }

    private function postArrayValue(array $values, $key, int $id, $default = '')
    {
        if (array_key_exists($id, $values)) {
            return $values[$id];
        }

        if (array_key_exists($key, $values)) {
            return $values[$key];
        }

        return $default;
    }

    private static function formatTimePresent($value)
    {
        if ($value === null) {
            return null;
        }

        $time = trim((string) $value);

        if ($time === '') {
            return '';
        }

        if (preg_match('/^(\d{1,2}):(\d{2})/', $time, $matches)) {
            return sprintf('%02d:%02d', (int) $matches[1], (int) $matches[2]);
        }

        $timestamp = strtotime($time);

        return $timestamp === false ? $time : date('H:i', $timestamp);
    }

    private static function backendApplication(): AdministratorApplication
    {
        return Factory::getContainer()->get(AdministratorApplication::class);
    }

    private static function getStaticDatabase(): DatabaseInterface
    {
        if (self::$database instanceof DatabaseInterface) {
            return self::$database;
        }

        $fallback = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$fallback instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement playground database connection is unavailable.');
        }

        self::$database = (new SportsManagementDatabaseResolver())->resolve(
            self::$cfg_which_database,
            $fallback
        );

        return self::$database;
    }
}
