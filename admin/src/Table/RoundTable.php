<?php
/**
 * Joomla 5/6 table implementation for SportsManagement rounds.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\Database\DatabaseInterface;

final class RoundTable extends SportsManagementTable
{
    /**
     * Initialise the round table for the selected Joomla database driver.
     *
     * @param DatabaseInterface $db Selected SportsManagement connection.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_round', 'id', $db);
    }

    /**
     * Bind edited round fields, including nullable notes and SQL dates.
     *
     * Converts form date values to SQL dates and timestamps. Empty notes
     * are stored as NULL, while malformed structured editor values are
     * rejected before Joomla attempts a database write. Notes exceeding
     * the SQL TEXT byte limit and non-string date values are also rejected.
     *
     * @param array|object $src Submitted round record values.
     * @param array|string $ignore Fields excluded from binding.
     * @return bool Whether the record values were bound successfully.
     */
    public function bind($src, $ignore = [])
    {
        if (is_object($src)) {
            $src = get_object_vars($src);
        }

        if (!is_array($src)) {
            return parent::bind($src, $ignore);
        }

        if (array_key_exists('notes', $src)) {
            if ($src['notes'] !== null && !is_string($src['notes'])) {
                $this->setError('Invalid round notes.');

                return false;
            }

            if (is_string($src['notes']) && strlen($src['notes']) > 65535) {
                $this->setError('Round notes exceed the SQL TEXT column capacity.');

                return false;
            }

            if ($src['notes'] === '') {
                $src['notes'] = null;
            }
        }

        $dateFields = [
            'round_date_first' => 'rdatefirst_timestamp',
            'round_date_last' => 'rdatelast_timestamp',
        ];

        foreach ($dateFields as $field => $timestampField) {
            if (!array_key_exists($field, $src)) {
                continue;
            }

            if ($src[$field] !== null && !is_string($src[$field])) {
                $this->setError('Invalid round date value.');

                return false;
            }

            $src[$field] = SportsManagementDateHelper::toSqlDate($src[$field] ?? '');
            $src[$timestampField] = $src[$field] === null
                ? 0
                : SportsManagementDateHelper::getTimestamp($src[$field]);
        }

        return parent::bind($src, $ignore);
    }

    /**
     * Derive a safe URL alias from the round's name when none is present.
     *
     * @return bool Whether the round record passes the table check.
     */
    public function check()
    {
        $this->alias = OutputFilter::stringURLSafe(
            empty($this->alias) ? (string) $this->name : (string) $this->alias
        );

        return true;
    }
}
