<?php
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
