<?php
/**
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
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

    /**
     * Initialise hit-list model state from the frontend Joomla request.
     *
     * Calls the native base model constructor, reads the selected project
     * and database IDs as filtered integers and mirrors the project ID
     * into the optional historical model for legacy templates.
     *
     * @param array $config Joomla model configuration.
     * @param MVCFactoryInterface|null $factory Optional MVC factory.
     */
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

    /**
     * Load view counts for an explicitly allowed SportsManagement entity.
     *
     * Limits table selection to the fixed project/club/team/person/playground
     * allowlist to prevent untrusted table names entering SQL. Orders
     * nonzero hit counts descending and stores the resulting objects under
     * their translated entity label for the legacy hit-list view.
     *
     * @param array $config Reserved legacy query configuration.
     * @param int $max_hits Maximum number of rows; zero uses Joomla's no-limit convention.
     * @param string $table Entity type drawn from HIT_TABLES.
     * @return void
     */
    public function getSportsmanagementHits($config = [], $max_hits = 0, $table = 'project'): void
    {
        $table = strtolower((string) $table);

        if (!in_array($table, self::HIT_TABLES, true)) {
            return;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery();

        if ($table === 'person') {
            $query->select([
                "CONCAT_WS(' - ', " . $db->quoteName('firstname') . ', ' . $db->quoteName('lastname') . ') AS ' . $db->quoteName('name'),
                $db->quoteName('hits'),
            ]);
        } else {
            $query->select([
                $db->quoteName('name'),
                $db->quoteName('hits'),
            ]);
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
