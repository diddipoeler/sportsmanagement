<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;
\defined('_JEXEC') or die;
use Joomla\Database\DatabaseInterface;
final class SportstypeTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_sports_type', 'id', $db);
    }
}
