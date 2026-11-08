<?php
/**
 * Joomla 5/6 administrator legacy SportsManagement form model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
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
    /**
     * Resolve the legacy sample-record table using Joomla's core database.
     *
     * The compatibility table maps Joomla core session and asset tables,
     * not an external SportsManagement connection. Other table requests
     * continue through the native administrator model's factory.
     *
     * @param string $type Requested legacy table entity.
     * @param string $prefix Historical Joomla table prefix.
     * @param array $config Optional table construction settings.
     * @return mixed Joomla core table or the parent's resolved table.
     */
    public function getTable($type = 'sportsmanagement', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'sportsmanagement') === 0) {
            return new LegacySportsmanagementTable(
                Factory::getContainer()->get(DatabaseInterface::class)
            );
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Check editing rights for an existing legacy sample-record asset.
     *
     * Unlike season relation tables, LegacySportsmanagementTable actually
     * stores its ACL asset under com_sportsmanagement.message.<id>. Reuse
     * that legacy permission or the standard Joomla parent authorisation,
     * but never call authorise() on a missing administrator identity.
     *
     * @param array $data Legacy sample record values including its ID.
     * @param string $key Record identifier field to authorise.
     * @return bool Whether an authenticated user may edit the record.
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $identity = $this->administratorApplication()->getIdentity();

        if ($identity === null) {
            return false;
        }

        $id = (int) ($data[$key] ?? 0);

        return $identity->authorise(
            'core.edit',
            'com_sportsmanagement.message.' . $id
        ) || parent::allowEdit($data, $key);
    }
}
