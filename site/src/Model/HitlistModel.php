<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class HitlistModel extends SportsManagementModel
{
    private const HIT_TABLES = ['project', 'club', 'team', 'person', 'playground'];

    public static int $cfg_which_database = 0;
    public static int $projectid = 0;
    public static array $_success_text = [];

    protected $_identifier = 'hitlist';
    public int $limitstart = 0;
    public int $limit = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$projectid = $input->getInt('p', 0);
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0);

        if (class_exists('sportsmanagementModelProject')) {
            \sportsmanagementModelProject::$projectid = self::$projectid;
        }
    }

    public function getSportsmanagementHits($config = [], $max_hits = 0, $table = 'project'): void
    {
        $table = strtolower((string) $table);

        if (!in_array($table, self::HIT_TABLES, true)) {
            return;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery();

        if ($table === 'person') {
            $query->select("CONCAT_WS(' - ', firstname, lastname) AS name, hits");
        } else {
            $query->select('name, hits');
        }

        $query->from($db->quoteName('#__sportsmanagement_' . $table))
            ->where($db->quoteName('hits') . ' != 0')
            ->order($db->quoteName('hits') . ' DESC');

        $limit = max(0, (int) $max_hits);
        $db->setQuery($query, 0, $limit);
        $rows = $db->loadObjectList() ?: [];

        self::$_success_text[Text::_('COM_SPORTSMANAGEMENT_HITLIST_' . strtoupper($table))] = $rows;
    }
}
