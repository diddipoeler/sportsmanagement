<?php
/**
 * Joomla 5/6 administrator season table.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\Database\DatabaseInterface;

final class SeasonTable extends SportsManagementTable
{
    /**
     * Initialise the native season table for Joomla's database driver.
     *
     * @param DatabaseInterface $db Selected SportsManagement connection.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_season', 'id', $db);
    }

    /**
     * Bind a season record while preserving the nullable image path.
     *
     * The Joomla media field can be empty; store that as SQL NULL. Reject
     * malformed picture input and values exceeding the new VARCHAR(250)
     * column rather than silently truncating a selected media reference.
     *
     * @param array|object $src Record values supplied by the administrator.
     * @param array|string $ignore Fields excluded from binding.
     * @return bool Whether the values were bound successfully.
     */
    public function bind($src, $ignore = [])
    {
        if (is_object($src)) {
            $src = get_object_vars($src);
        }

        if (is_array($src) && array_key_exists('picture', $src)) {
            $picture = $src['picture'];

            if ($picture !== null && !is_string($picture)) {
                $this->setError('Invalid season picture reference.');

                return false;
            }

            if (is_string($picture) && strlen($picture) > 250) {
                $this->setError('Season picture reference exceeds 250 bytes.');

                return false;
            }

            $src['picture'] = $picture === '' ? null : $picture;
        }

        return parent::bind($src, $ignore);
    }

    /**
     * Generate a URL-safe alias for the season record.
     *
     * @return bool Whether the underlying Joomla table passes validation.
     */
    public function check()
    {
        $aliasSource = trim((string) $this->alias) === ''
            ? (string) $this->name
            : (string) $this->alias;
        $this->alias = OutputFilter::stringURLSafe($aliasSource);

        return parent::check();
    }
}
