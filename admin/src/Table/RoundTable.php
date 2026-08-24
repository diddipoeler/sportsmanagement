<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\Database\DatabaseInterface;

final class RoundTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_round', 'id', $db);
    }

    public function bind($src, $ignore = [])
    {
        if (is_object($src)) {
            $src = get_object_vars($src);
        }

        if (!is_array($src)) {
            return parent::bind($src, $ignore);
        }

        $dateFields = [
            'round_date_first' => 'rdatefirst_timestamp',
            'round_date_last' => 'rdatelast_timestamp',
        ];

        foreach ($dateFields as $field => $timestampField) {
            if (!array_key_exists($field, $src)) {
                continue;
            }

            $src[$field] = SportsManagementDateHelper::toSqlDate((string) $src[$field]);
            $src[$timestampField] = $src[$field] === null
                ? 0
                : SportsManagementDateHelper::getTimestamp($src[$field]);
        }

        return parent::bind($src, $ignore);
    }

    public function check()
    {
        $this->alias = OutputFilter::stringURLSafe(
            empty($this->alias) ? (string) $this->name : (string) $this->alias
        );

        return true;
    }
}
