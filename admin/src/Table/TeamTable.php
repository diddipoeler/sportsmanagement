<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

final class TeamTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_team', 'id', $db);
    }

    public function check()
    {
        $this->name = trim((string) $this->name);

        if ($this->name === '') {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_REQUIRED'));
            return false;
        }

        if (trim((string) $this->middle_name) === '') {
            $parts = preg_split('/\s+/', $this->name) ?: [$this->name];
            $this->middle_name = substr((string) $parts[0], 0, 20);
        }

        if (trim((string) $this->short_name) === '') {
            $parts = preg_split('/\s+/', $this->name) ?: [$this->name];
            $this->short_name = substr((string) $parts[0], 0, 2);
        }

        $this->alias = OutputFilter::stringURLSafe($this->name);

        return parent::check();
    }
}
