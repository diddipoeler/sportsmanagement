<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Crypt\Crypt;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class JsmgcalendarTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_gcalendar', 'id', $db);
    }

    public function bind($array, $ignore = '')
    {
        if (isset($array['params']) && is_array($array['params'])) {
            $params = new Registry();
            $params->loadArray($array['params']);
            $array['params'] = $params->toString();
        }

        return parent::bind($array, $ignore);
    }

    public function load($keys = null, $reset = true)
    {
        $result = parent::load($keys, $reset);

        if ($result && !empty($this->password)) {
            $this->password = (new Crypt())->decrypt((string) $this->password);
        }

        return $result;
    }

    public function store($updateNulls = false)
    {
        $plainPassword = (string) ($this->password ?? '');

        if ($plainPassword !== '') {
            $this->password = (new Crypt())->encrypt($plainPassword);
        }

        try {
            return parent::store($updateNulls);
        } finally {
            $this->password = $plainPassword;
        }
    }
}
