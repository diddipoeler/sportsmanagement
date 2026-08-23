<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\LegacySportsmanagementTable;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Native Joomla 5/6 administrator form model for the legacy SportsManagement sample record.
 */
final class SportsmanagementModel extends SportsManagementAdminModel
{
    public function getTable($type = 'sportsmanagement', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'sportsmanagement') === 0) {
            return new LegacySportsmanagementTable(
                Factory::getContainer()->get(DatabaseInterface::class)
            );
        }

        return parent::getTable($type, $prefix, $config);
    }

    protected function allowEdit($data = [], $key = 'id')
    {
        $id = (int) ($data[$key] ?? 0);

        return Factory::getApplication()->getIdentity()->authorise(
            'core.edit',
            'com_sportsmanagement.message.' . $id
        ) || parent::allowEdit($data, $key);
    }
}
