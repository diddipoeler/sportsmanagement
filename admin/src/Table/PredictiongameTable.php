<?php
/**
 * Native Joomla 5/6 table implementation for prediction games.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

final class PredictiongameTable extends SportsManagementTable
{
    /**
     * Initialise the native Joomla table for tip game definitions.
     * Registers the storage table and primary key with the shared table
     * implementation using the injected Joomla database driver.
     * @param DatabaseInterface $db Active SportsManagement database connection.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_prediction_game', 'id', $db);
    }

    /**
     * Validate the tip game name and prepare its Joomla URL alias.
     * Rejects missing or non-string names and malformed alias values, then
     * generates a default URL-safe alias while preserving a custom alias.
     * @return bool Whether the name and alias pass validation.
     */
    public function check()
    {
        $rawName = $this->name ?? '';
        $name = is_string($rawName) ? trim($rawName) : '';

        if ($name === '') {
            $this->setError(Text::_('CHECK FAILED - Empty name of prediction game'));

            return false;
        }

        if (isset($this->alias) && !is_string($this->alias)) {
            $this->setError(Text::_('CHECK FAILED'));

            return false;
        }

        $alias = OutputFilter::stringURLSafe($name);

        if (empty($this->alias) || $this->alias === $alias) {
            $this->alias = $alias;
        }

        return true;
    }
}
