<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\Database\DatabaseInterface;

final class SeasonTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_season', 'id', $db);
    }

    public function check()
    {
        $aliasSource = trim((string) $this->alias) === ''
            ? (string) $this->name
            : (string) $this->alias;
        $this->alias = OutputFilter::stringURLSafe($aliasSource);

        return parent::check();
    }
}
