<?php
/**
 * Joomla 5/6 administrator association table.
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

final class JlextassociationTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_associations', 'id', $db);
    }

    public function check()
    {
        $this->alias = OutputFilter::stringURLSafe((string) ($this->name ?? ''));

        return true;
    }
}
