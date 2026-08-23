<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

final class TreetomatchTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_treeto_match', 'id', $db);
    }
}
