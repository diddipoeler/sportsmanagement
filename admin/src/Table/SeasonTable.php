<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
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
        if (!$this->alias) {
            $this->alias = $this->name;
        }

        $this->alias = OutputFilter::stringURLSafe((string) $this->alias);

        if (trim(str_replace('-', '', (string) $this->alias)) === '') {
            $this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
        }

        return parent::check();
    }
}
