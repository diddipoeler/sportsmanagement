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
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
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

    /**
     * Initialise the playground model and configured database and form services.
     *
     * @param mixed $config config value.
     * @param ?MVCFactoryInterface $factory factory value.
     * @param ?FormFactoryInterface $formFactory formFactory value.
     */
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

    /**
     * Load the playground edit form with an approved picture field type.
     *
     * Retains the Joomla Media form for empty or unsupported configured
     * types instead of allowing obsolete values to instantiate unavailable
     * field classes. The playground XML definition already provides the
     * image handler's safe targetfolder="playgrounds" for imageselect mode.
     *
     * @param array<string,mixed> $data Existing playground values.
     * @param bool $loadData Whether to populate persisted edit values.
     * @return mixed Joomla playground form or false if loading failed.
     */
    public function getForm($data = [], $loadData = true)
    {
        $form = parent::getForm($data, $loadData);

        if ($form) {
            $mediaTool = trim((string) ComponentHelper::getParams('com_sportsmanagement')
                ->get('cfg_which_media_tool', 'media'));

            if (!in_array($mediaTool, ['media', 'imageselect'], true)) {
                $mediaTool = 'media';
            }

            $form->setFieldAttribute('picture', 'type', $mediaTool);
        }

        return $form;
    }

    /**
     * Load playground logo history for the selected season.
     *
     * @param mixed $playground_id playground id value.
     * @param mixed $season_id season id value.
     * @param mixed $logoonly logoonly value.
     * @return array
     */
    public function getlogohistoryPlayground($playground_id = 0, $season_id = 0, $logoonly = false): array
    {
        $playgroundId = max(0, (int) $playground_id);
        $seasonId = max(0, (int) $season_id);
        $db = $this->getDatabase();

        $query = $db->createQuery()
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
            $query->where($db->quoteName('cl.playground_id') . ' = :playgroundId')
                ->bind(':playgroundId', $playgroundId, ParameterType::INTEGER);
        }

        if ($seasonId > 0) {
            $query->where($db->quoteName('cl.season_id') . ' = :seasonId')
                ->bind(':seasonId', $seasonId, ParameterType::INTEGER);
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

    /**
     * Load stored playground description and notice records.
     *
     * @param mixed $playground_id playground id value.
     * @return array
     */
    public function getPlaygroundNotic($playground_id): array
    {
        $playgroundId = max(0, (int) $playground_id);
        if ($playgroundId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_playground_details'))
            ->where($db->quoteName('playground_id') . ' = :playgroundId')
            ->bind(':playgroundId', $playgroundId, ParameterType::INTEGER)
            ->order($db->quoteName('date_von') . ' DESC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Build a display address from current playground metadata.
     * @return string|null
     */
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

    /**
     * Load and cache playground details by identifier.
     *
     * @param mixed $pgid pgid value.
     * @param mixed $inserthits inserthits value.
     * @return object|null
     */
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
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_playground'))
            ->where($db->quoteName('id') . ' = :playgroundId')
            ->bind(':playgroundId', $playgroundId, ParameterType::INTEGER);

        $db->setQuery($query, 0, 1);
        self::$playground = $db->loadObject();
        self::$cachedPlaygroundId = self::$playground ? $playgroundId : 0;

        return self::$playground;
    }

    /**
     * Increment playground view counts when requested.
     *
     * @param mixed $pgid pgid value.
     * @param mixed $inserthits inserthits value.
     * @return void
     */
    public static function updateHits($pgid = 0, $inserthits = 0): void
    {
        $playgroundId = max(0, (int) $pgid);

        if (!$inserthits || $playgroundId <= 0) {
            return;
        }

        $db = self::getStaticDatabase();
        $query = $db->createQuery()
            ->update($db->quoteName('#__sportsmanagement_playground'))
            ->set($db->quoteName('hits') . ' = ' . $db->quoteName('hits') . ' + 1')
            ->where($db->quoteName('id') . ' = :playgroundId')
            ->bind(':playgroundId', $playgroundId, ParameterType::INTEGER);

        $db->setQuery($query);
        $db->execute();
    }

    /**
     * List future or past matches at the selected playground.
     *
     * @param mixed $project project value.
     * @param mixed $pgid pgid value.
     * @param mixed $played played value.
     * @param mixed $allproject allproject value.
     * @return array
     */
    public function getNextGames($project = 0, $pgid = 0, $played = 0, $allproject = 0): array
    {
        $projectId = max(0, (int) $project);
        $playgroundId = max(0, (int) $pgid);
        $playground = self::getPlayground($playgroundId);

        if (!$playground || (int) $playground->id <= 0) {
            return [];
        }

        $matchPlaygroundId = (int) $playground->id;
        $matchTimestamp = time();
        $db = $this->getDatabase();
        $query = $db->createQuery()
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
            ->where($db->quoteName('m.playground_id') . ' = :playgroundId')
            ->bind(':playgroundId', $matchPlaygroundId, ParameterType::INTEGER)
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1')
            ->where(
                $db->quoteName('m.match_timestamp')
                . ($played ? ' < ' : ' > ')
                . ' :matchTimestamp'
            )
            ->bind(':matchTimestamp', $matchTimestamp, ParameterType::INTEGER)
            ->order($db->quoteName('m.match_date') . ' ASC');

        if ($projectId > 0 && !$allproject) {
            $query->where($db->quoteName('p.id') . ' = :projectId')
                ->bind(':projectId', $projectId, ParameterType::INTEGER);
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

    /**
     * Normalise submitted playground media and other save fields.
     *
     * @param array $data data value.
     * @return array
     */
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

    /**
     * Persist playground details, logos and extended data after saving.
     *
     * @param array $data data value.
     * @param int $id id value.
     * @param bool $isNew isNew value.
     * @return void
     */
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

    /**
     * Store date-scoped playground notes and opening information.
     *
     * @param array $post post value.
     * @param int $playgroundId playgroundId value.
     * @param array $data data value.
     * @return void
     */
    private function storePlaygroundDetails(array $post, int $playgroundId, array $data): void
    {
        $db = $this->getDatabase();
        $modified = (string) ($data['modified'] ?? (new Date('now', new \DateTimeZone('UTC')))->toSql());
        $modifiedBy = (int) ($data['modified_by'] ?? ($this->administratorApplication()->getIdentity()?->id ?? 0));

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

    /**
     * Store seasonal playground logo history.
     *
     * @param array $post post value.
     * @param int $playgroundId playgroundId value.
     * @param array $data data value.
     * @return void
     */
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
        $modified = (string) ($data['modified'] ?? (new Date('now', new \DateTimeZone('UTC')))->toSql());
        $modifiedBy = (int) ($data['modified_by'] ?? ($this->administratorApplication()->getIdentity()?->id ?? 0));

        foreach ($seasonIds as $seasonId) {
            $query = $db->createQuery()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__sportsmanagement_playground_logos'))
                ->where($db->quoteName('playground_id') . ' = :playgroundId')
                ->bind(':playgroundId', $playgroundId, ParameterType::INTEGER)
                ->where($db->quoteName('season_id') . ' = :seasonId')
                ->bind(':seasonId', $seasonId, ParameterType::INTEGER);
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

    /**
     * Convert legacy date values into a database-friendly representation.
     *
     * @param mixed $value value value.
     * @return string
     */
    private function normaliseLegacyDate($value): string
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '00-00-0000' || $value === '0000-00-00') {
            return '0000-00-00';
        }

        $converted = SportsManagementDateHelper::convertDate($value, 0);

        return $converted !== '' ? $converted : $value;
    }

    /**
     * Convert a valid legacy date to its Unix timestamp.
     *
     * @param string $date date value.
     * @return int
     */
    private function legacyTimestamp(string $date): int
    {
        if ($date === '' || $date === '0000-00-00') {
            return 0;
        }

        return SportsManagementDateHelper::getTimestamp($date);
    }

    /**
     * Read per-row input by record ID or original input index.
     *
     * @param array $values values value.
     * @param mixed $key key value.
     * @param int $id id value.
     * @param mixed $default default value.
     * @return mixed
     */
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

    /**
     * Format a supplied time value for legacy playground display.
     *
     * @param mixed $value value value.
     * @return mixed
     */
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

    /**
     * Resolve the Joomla administrator application.
     * @return CMSApplicationInterface
     */
    private static function backendApplication(): CMSApplicationInterface
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

    /**
     * Resolve and validate Joomla's primary database service.
     * @return DatabaseInterface
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement playground database service is unavailable.', 500);
        }

        return $database;
    }

    /**
     * Reuse or resolve the active playground database connection.
     * @return DatabaseInterface
     */
    private static function getStaticDatabase(): DatabaseInterface
    {
        if (self::$database instanceof DatabaseInterface) {
            return self::$database;
        }

        self::$database = (new SportsManagementDatabaseResolver())->resolve(
            self::$cfg_which_database,
            self::joomlaDatabase()
        );

        return self::$database;
    }
}
