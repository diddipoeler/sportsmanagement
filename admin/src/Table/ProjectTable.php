<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\Database\DatabaseInterface;

final class ProjectTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_project', 'id', $db);
    }

    public function bind($src, $ignore = [])
    {
        foreach (['start_date', 'end_date'] as $field) {
            if (is_array($src) && array_key_exists($field, $src)) {
                $value = trim((string) $src[$field]);

                if ($value === '' || $value === '0000-00-00') {
                    $src[$field] = null;
                }
            } elseif (is_object($src) && property_exists($src, $field)) {
                $value = trim((string) $src->{$field});

                if ($value === '' || $value === '0000-00-00') {
                    $src->{$field} = null;
                }
            }
        }

        return parent::bind($src, $ignore);
    }

    public function check()
    {
        $this->alias = OutputFilter::stringURLSafe((string) $this->name);

        return parent::check();
    }
}
