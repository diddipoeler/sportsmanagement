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
     * @param bool $resolved Whether the connection has already been resolved.
     */
    public function __construct(DatabaseInterface $db, bool $resolved = false)
    {
        parent::__construct('#__sportsmanagement_season', 'id', $db, $resolved);
    }

    /**
     * Bind a season record while preserving the nullable image path.
     *
     * The Joomla media field can be empty; store that as SQL NULL. Reject
     * malformed picture input and values exceeding the new VARCHAR(250)
     * column rather than silently truncating a selected media reference.
     * Rejects invalid UTF-8 and control bytes before database binding.
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

            if (is_string($picture) && (preg_match('//u', $picture) !== 1
                || preg_match('/[\x00-\x1F\x7F]/', $picture) === 1)) {
                $this->setError('Invalid season picture encoding or control characters.');

                return false;
            }

            $src['picture'] = $picture === '' ? null : $picture;
        }

        return parent::bind($src, $ignore);
    }

    /**
     * Generate a URL-safe alias for the season record.
     * Rejects malformed non-string values before calling Joomla's output
     * filter, avoiding PHP 8 array conversion warnings. Uses the season
     * name when no alias is supplied and delegates remaining validation
     * to the shared native table implementation.
     *
     * @return bool Whether the underlying Joomla table passes validation.
     */
    public function check()
    {
        $alias = $this->alias ?? '';
        $name = $this->name ?? '';

        if (!is_string($alias) || !is_string($name)) {
            $this->setError('Invalid season name or alias.');

            return false;
        }

        $aliasSource = trim($alias) === '' ? $name : $alias;
        $this->alias = OutputFilter::stringURLSafe($aliasSource);

        return parent::check();
    }
}
