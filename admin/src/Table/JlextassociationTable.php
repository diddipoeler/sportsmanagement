<?php
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
