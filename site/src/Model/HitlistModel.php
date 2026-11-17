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
use Joomla\CMS\Log\Log;
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
     * Validates the selected table and result cap without lossy PHP casts.
     * Database failures are logged by exception class and return an empty
     * result for the selected entity rather than aborting public rendering.
     *
     * @param array $config Reserved legacy query configuration.
     * @param mixed $max_hits Maximum hit-list rows (0 retains the legacy unlimited mode).
     * @param mixed $table Entity type drawn from the fixed HIT_TABLES allowlist.
     * @return void
     */
    public function getSportsmanagementHits($config = [], $max_hits = 0, $table = 'project'): void
    {
        if (!is_string($table)) {
            return;
        }

        $table = strtolower($table);

        if (!in_array($table, self::HIT_TABLES, true)) {
            return;
        }

        $limit = self::hitListLimit($max_hits);

        if ($limit === null) {
            return;
        }

        $label = Text::_('COM_SPORTSMANAGEMENT_HITLIST_' . strtoupper($table));

        try {
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

            $db->setQuery($query, 0, $limit);
            $records = $db->loadObjectList();
            self::$_success_text[$label] = is_array($records)
                ? array_values(array_filter($records, 'is_object'))
                : [];
        } catch (\Throwable $error) {
            Log::add(
                __METHOD__ . ': hit list database query failed (' . get_class($error) . ').',
                Log::ERROR,
                'com_sportsmanagement'
            );

            self::$_success_text[$label] = [];
        }
    }
    /**
     * Validate a legacy hit-list row limit before building a Joomla query.
     *
     * Accepts zero (historical no-limit selection) and positive integers or
     * zero-padded decimal strings, but rejects malformed request types and
     * partial numbers. Caps explicit limits at 500 to protect the frontend
     * from excessive allocations when imported settings are oversized.
     *
     * @param mixed $raw Joomla template hit-list limit.
     * @return int|null Allowed row count or null when invalid.
     */
    private static function hitListLimit(mixed $raw): ?int
    {
        if (is_int($raw)) {
            return $raw >= 0 ? min($raw, 500) : null;
        }

        if (!is_string($raw) || strlen($raw) > 64
            || preg_match('/^[0-9]+$/D', $raw) !== 1) {
            return null;
        }

        $digits = ltrim($raw, '0');

        if ($digits === '') {
            return 0;
        }

        if (strlen($digits) > 3 || (strlen($digits) === 3 && strcmp($digits, '500') > 0)) {
            return 500;
        }

        return (int) $digits;
    }
}
