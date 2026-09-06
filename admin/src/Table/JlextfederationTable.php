<?php
/**
 * Joomla 5/6 administrator federation table.
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

final class JlextfederationTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_federations', 'id', $db);
    }

    public function check()
    {
        $alias = OutputFilter::stringURLSafe((string) ($this->name ?? ''));

        if (empty($this->alias) || $this->alias === $alias) {
            $this->alias = $alias;
        }

        return true;
    }
}
