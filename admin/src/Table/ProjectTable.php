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
        if (is_object($src)) {
            $src = get_object_vars($src);
        }

        if (!is_array($src)) {
            return parent::bind($src, $ignore);
        }

        foreach (['start_date', 'end_date'] as $field) {
            if (!array_key_exists($field, $src)) {
                continue;
            }

            $value = trim((string) $src[$field]);

            if ($value === '' || $value === '0000-00-00') {
                $src[$field] = null;
            }
        }

        // Optional select/list fields can arrive as an empty string from Joomla forms.
        // The project table stores these values as integer IDs with 0 meaning "not selected".
        foreach ([
            'category_id',
            'gcalendar_id',
            'agegroup_id',
            'sports_type_id',
            'editorgroup',
            'sb_catid',
        ] as $field) {
            if (array_key_exists($field, $src) && trim((string) $src[$field]) === '') {
                $src[$field] = 0;
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
