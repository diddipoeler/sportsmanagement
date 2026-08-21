<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\Database\DatabaseInterface;

final class RoundTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_round', 'id', $db);
    }

    public function check()
    {
        $this->alias = OutputFilter::stringURLSafe(
            empty($this->alias) ? (string) $this->name : (string) $this->alias
        );

        return true;
    }
}
