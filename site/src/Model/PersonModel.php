<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;

final class PersonModel extends SportsManagementProjectModel
{
    public static int $projectid = 0;
    public static int $personid = 0;
    public static $person = null;
    public static $jsmdb = null;
    public static $jsmquery = null;
    public static $_inproject = null;
    public static int $cfg_which_database = 0;

    public int $teamplayerid = 0;
    public $teamplayer = null;
    public $_playerhistory = null;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$projectid = $this->projectId;
        self::$personid = $input->getInt('pid', 0);
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0);
        $this->teamplayerid = $input->getInt('pt', 0);

        self::$jsmdb = $this->getDatabase();
        self::$jsmquery = self::$jsmdb->getQuery(true);

        if (class_exists('sportsmanagementModelProject')) {
            \sportsmanagementModelProject::$projectid = self::$projectid;
            \sportsmanagementModelProject::$cfg_which_database = self::$cfg_which_database;
        }
    }

    public static function getReferee()
    {
        if (self::$projectid <= 0 || self::$personid <= 0) {
            return null;
        }

        $db = self::database();
        $query = $db->getQuery(true)
            ->select([
                'p.*',
                $db->quoteName('pr.id'),
                $db->quoteName('pr.notes', 'prnotes'),
                $db->quoteName('pr.picture'),
                $db->quoteName('pos.name', 'position_name'),
                "CONCAT_WS(':', p.id, p.alias) AS slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_project_referee', 'pr'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_person_id', 'o') . ' ON ' . $db->quoteName('o.id') . ' = ' . $db->quoteName('pr.person_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('o.person_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'pj')
                . ' ON ' . $db->quoteName('pj.id') . ' = ' . $db->quoteName('pr.project_id')
                . ' AND ' . $db->quoteName('pj.season_id') . ' = ' . $db->quoteName('o.season_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('pr.project_position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('pr.project_id') . ' = ' . self::$projectid)
            ->where($db->quoteName('pr.published') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('pj.published') . ' = 1')
            ->where($db->quoteName('o.person_id') . ' = ' . self::$personid);

        $db->setQuery($query, 0, 1);
        self::$_inproject = $db->loadObject();

        return self::$_inproject;
    }

    public static function getAllowed($config_editOwnPlayer)
    {
        $user = self::frontendApplication()->getIdentity();

        return self::_isAdmin($user) || self::_isOwnPlayer($user, $config_editOwnPlayer);
    }

    public static function _isAdmin($user)
    {
        if ((int) ($user->id ?? 0) <= 0) {
            return false;
        }

        if (class_exists('sportsmanagementModelProject')) {
            $project = \sportsmanagementModelProject::getProject();
            if ($project && \sportsmanagementModelProject::isUserProjectAdminOrEditor((int) $user->id, $project)) {
                return true;
            }
        }

        $option = self::frontendApplication()->getInput()->getCmd('option', 'com_sportsmanagement');

        return $user->authorise('person.edit', $option);
    }

    public static function _isOwnPlayer($user, $config_editOwnPlayer)
    {
        if (!$config_editOwnPlayer || (int) ($user->id ?? 0) <= 0) {
            return false;
        }

        $person = self::getPerson();

        return $person && (int) ($person->user_id ?? 0) === (int) $user->id;
    }

    public static function isContactDataVisible($config_showContactDataOnlyTeamMembers = [])
    {
        if (!$config_showContactDataOnlyTeamMembers) {
            return true;
        }

        $user = self::frontendApplication()->getIdentity();
        $userId = (int) ($user->id ?? 0);

        if (class_exists('sportsmanagementModelProject')) {
            $project = \sportsmanagementModelProject::getProject();
            if ($project && \sportsmanagementModelProject::isUserProjectAdminOrEditor($userId, $project)) {
                return true;
            }
        }

        if ($userId <= 0 || !class_exists('sportsmanagementModelPlayer')) {
            return false;
        }

        $projectTeamIds = self::_getProjectTeamIds4UserId($userId);
        $teamplayer = \sportsmanagementModelPlayer::getTeamPlayer();
        $projectTeamId = (int) ($teamplayer->projectteam_id ?? 0);

        return $projectTeamId > 0 && in_array($projectTeamId, $projectTeamIds, true);
    }

    public static function _getProjectTeamIds4UserId($userId = 0)
    {
        $userId = max(0, (int) $userId);
        if ($userId <= 0) {
            return [];
        }

        $db = self::database();
        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('st1.id'))
            ->from($db->quoteName('#__sportsmanagement_person', 'pr'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON ' . $db->quoteName('tp.person_id') . ' = ' . $db->quoteName('pr.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1')
                . ' ON ' . $db->quoteName('st1.team_id') . ' = ' . $db->quoteName('tp.team_id')
                . ' AND ' . $db->quoteName('st1.season_id') . ' = ' . $db->quoteName('tp.season_id'))
            ->where($db->quoteName('pr.user_id') . ' = ' . $userId)
            ->where($db->quoteName('pr.published') . ' = 1')
            ->where($db->quoteName('tp.published') . ' = 1')
            ->where($db->quoteName('tp.persontype') . ' IN (1,2)');

        try {
            $db->setQuery($query);
            return array_values(array_unique(array_map('intval', $db->loadColumn() ?: [])));
        } catch (\Throwable $e) {
            self::enqueueDatabaseError($e);
            return [];
        }
    }

    public function getRefereeHistory($order = 'ASC')
    {
        $personId = self::$personid;
        if ($personId <= 0) {
            return [];
        }

        $direction = strtoupper((string) $order) === 'DESC' ? 'DESC' : 'ASC';
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p.id', 'person_id'),
                $db->quoteName('pr.project_id'),
                $db->quoteName('p.firstname', 'fname'),
                $db->quoteName('p.lastname', 'lname'),
                $db->quoteName('pj.name', 'pname'),
                $db->quoteName('s.name', 'sname'),
                $db->quoteName('pos.name', 'position'),
                'COUNT(' . $db->quoteName('mr.id') . ') AS matchesCount',
            ])
            ->from($db->quoteName('#__sportsmanagement_match_referee', 'mr'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_referee', 'pr') . ' ON ' . $db->quoteName('pr.id') . ' = ' . $db->quoteName('mr.project_referee_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_person_id', 'o') . ' ON ' . $db->quoteName('o.id') . ' = ' . $db->quoteName('pr.person_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('o.person_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'pj')
                . ' ON ' . $db->quoteName('pj.id') . ' = ' . $db->quoteName('pr.project_id')
                . ' AND ' . $db->quoteName('pj.season_id') . ' = ' . $db->quoteName('o.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('pj.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('pj.league_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('pr.project_position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('p.id') . ' = ' . $personId)
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('pr.published') . ' = 1')
            ->where($db->quoteName('pj.published') . ' = 1')
            ->group([
                $db->quoteName('p.id'),
                $db->quoteName('pr.project_id'),
                $db->quoteName('p.firstname'),
                $db->quoteName('p.lastname'),
                $db->quoteName('pj.name'),
                $db->quoteName('s.name'),
                $db->quoteName('pos.name'),
                $db->quoteName('s.ordering'),
                $db->quoteName('l.ordering'),
            ])
            ->order([
                $db->quoteName('s.ordering') . ' ' . $direction,
                $db->quoteName('l.ordering') . ' ' . $direction,
                $db->quoteName('pj.name') . ' ' . $direction,
            ]);

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    public function getContactID($catid)
    {
        $person = self::getPerson();
        $categoryId = max(0, (int) $catid);
        $userId = (int) ($person->jl_user_id ?? 0);

        if ($categoryId <= 0 || $userId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__contact_details'))
            ->where($db->quoteName('user_id') . ' = ' . $userId)
            ->where($db->quoteName('catid') . ' = ' . $categoryId);
        $db->setQuery($query, 0, 1);

        return (int) $db->loadResult();
    }

    public static function getPerson($personid = 0, $cfg_which_database = 0, $inserthits = 0)
    {
        $personId = max(0, (int) $personid);
        if ($personId > 0) {
            self::$personid = $personId;
        } elseif (self::$personid <= 0) {
            self::$personid = self::frontendApplication()->getInput()->getInt('pid', 0);
        }

        $selector = max(0, (int) $cfg_which_database);
        if ($selector > 0 || self::$cfg_which_database === 0) {
            self::$cfg_which_database = $selector;
        } else {
            $selector = self::$cfg_which_database;
        }

        if (self::$personid <= 0) {
            self::$person = null;
            return null;
        }

        self::updateHits(self::$personid, $inserthits, $selector);

        $db = self::database($selector);
        $query = $db->getQuery(true)
            ->select([
                'p.*',
                "CONCAT_WS(':', p.id, p.alias) AS slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_person', 'p'))
            ->where($db->quoteName('p.id') . ' = ' . self::$personid);
        $db->setQuery($query, 0, 1);

        self::$person = $db->loadObject();
        return self::$person;
    }

    public static function updateHits($personid = 0, $inserthits = 0, $cfg_which_database = null)
    {
        $personId = max(0, (int) $personid);
        if (!$inserthits || $personId <= 0) {
            return;
        }

        $selector = $cfg_which_database === null
            ? self::$cfg_which_database
            : max(0, (int) $cfg_which_database);
        $db = self::database($selector);
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__sportsmanagement_person'))
            ->set($db->quoteName('hits') . ' = ' . $db->quoteName('hits') . ' + 1')
            ->where($db->quoteName('id') . ' = ' . $personId);
        $db->setQuery($query);
        $db->execute();
    }

    public function getAllEvents()
    {
        if (!class_exists('sportsmanagementModelPlayer')) {
            return [];
        }

        $history = \sportsmanagementModelPlayer::getPlayerHistory();
        $positionIds = [];

        foreach ($history ?: [] as $row) {
            $positionId = (int) ($row->position_id ?? 0);
            if ($positionId > 0) {
                $positionIds[$positionId] = $positionId;
            }
        }

        if (!$positionIds) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('et.*')
            ->from($db->quoteName('#__sportsmanagement_eventtype', 'et'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position_eventtype', 'pet') . ' ON ' . $db->quoteName('pet.eventtype_id') . ' = ' . $db->quoteName('et.id'))
            ->where($db->quoteName('et.published') . ' = 1')
            ->where($db->quoteName('pet.position_id') . ' IN (' . implode(',', array_values($positionIds)) . ')')
            ->order($db->quoteName('et.ordering') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getPlayerEvents($eventid, $projectid = null, $projectteamid = null, $show_events_as_sum = 1)
    {
        $eventId = max(0, (int) $eventid);
        if ($eventId <= 0 || self::$personid <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $aggregate = $show_events_as_sum ? 'SUM' : 'COUNT';
        $query = $db->getQuery(true)
            ->select($aggregate . '(' . $db->quoteName('me.event_sum') . ') AS total')
            ->from($db->quoteName('#__sportsmanagement_match_event', 'me'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp1') . ' ON ' . $db->quoteName('tp1.id') . ' = ' . $db->quoteName('me.teamplayer_id'))
            ->where($db->quoteName('me.event_type_id') . ' = ' . $eventId)
            ->where($db->quoteName('tp1.person_id') . ' = ' . self::$personid)
            ->group($db->quoteName('tp1.person_id'));

        $projectTeamId = max(0, (int) $projectteamid);
        if ($projectTeamId > 0) {
            $query->where($db->quoteName('me.projectteam_id') . ' = ' . $projectTeamId);
        }

        $db->setQuery($query);
        return (int) ($db->loadResult() ?: 0);
    }

    public function getPlayerChangedRecipients()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('u.email'))
            ->from($db->quoteName('#__users', 'u'))
            ->join('INNER', $db->quoteName('#__user_usergroup_map', 'map') . ' ON ' . $db->quoteName('map.user_id') . ' = ' . $db->quoteName('u.id'))
            ->join('INNER', $db->quoteName('#__usergroups', 'g') . ' ON ' . $db->quoteName('g.id') . ' = ' . $db->quoteName('map.group_id'))
            ->where($db->quoteName('u.block') . ' = 0')
            ->where($db->quoteName('u.email') . " <> ''")
            ->where($db->quoteName('g.title') . ' IN ('
                . implode(',', array_map([$db, 'quote'], ['Administrator', 'Super Users', 'Super Administrator']))
                . ')')
            ->order($db->quoteName('u.email') . ' ASC');
        $db->setQuery($query);

        return $db->loadColumn() ?: [];
    }

    public function sendMailTo($listOfRecipients, $subject, $message)
    {
        $recipients = self::normaliseRecipients($listOfRecipients);
        if (!$recipients) {
            return false;
        }

        try {
            $app = $this->siteApplication();
            $mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
            $configuration = $app->getConfig();
            $mailFrom = (string) $configuration->get('mailfrom', '');
            $fromName = (string) $configuration->get('fromname', '');

            if ($mailFrom !== '') {
                $mailer->setSender($mailFrom, $fromName);
            }

            foreach ($recipients as $recipient) {
                $mailer->addRecipient($recipient);
            }

            $mailer->setSubject((string) $subject);
            $mailer->setBody((string) $message);
            $mailer->send();
            return true;
        } catch (\Throwable $e) {
            $this->siteApplication()->enqueueMessage($e->getMessage(), 'error');
            return false;
        }
    }

    public function isEditAllowed($config_editOwnPlayer, $config_editAllowed)
    {
        $user = $this->siteApplication()->getIdentity();
        if ((int) ($user->id ?? 0) <= 0) {
            return false;
        }

        return self::_isAdmin($user)
            || ($config_editAllowed && self::_isOwnPlayer($user, $config_editOwnPlayer));
    }

    private static function frontendApplication(): SiteApplication
    {
        return Factory::getContainer()->get(SiteApplication::class);
    }

    private static function database(?int $selector = null): DatabaseInterface
    {
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);

        return SportsManagementDatabaseResolver::resolve(
            $joomlaDatabase,
            max(0, $selector ?? self::$cfg_which_database)
        );
    }

    private static function enqueueDatabaseError(\Throwable $e): void
    {
        self::frontendApplication()->enqueueMessage(
            Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
            'notice'
        );
    }

    private static function normaliseRecipients($value): array
    {
        $values = is_array($value) ? $value : preg_split('/[;,]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
        $recipients = [];

        foreach ($values ?: [] as $recipient) {
            $email = trim((string) $recipient);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $recipients[$email] = $email;
            }
        }

        return array_values($recipients);
    }
}
