<?php
/**
 * Native Joomla 5/6 table for SportsManagement playgrounds.
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

final class PlaygroundTable extends SportsManagementTable
{
    /**
     * Initialise the venue table on the selected database connection.
     * @param DatabaseInterface $db Active database service.
     * @param bool $resolved Whether the caller has already resolved the connection.
     */
    public function __construct(DatabaseInterface $db, bool $resolved = false)
    {
        parent::__construct('#__sportsmanagement_playground', 'id', $db, $resolved);
    }

    /**
     * Require a venue name and normalise its URL alias before persistence.
     * @return bool Whether the venue passes table validation.
     */
    public function check()
    {
        if (trim((string) $this->name) === '') {
            $this->setError(Text::_('ERROR NAME REQUIRED'));

            return false;
        }

        $this->alias = OutputFilter::stringURLSafe((string) ($this->alias ?: $this->name));

        return parent::check();
    }
}
