<?php
/**
 * Joomla 5/6 administrator association form model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Table\JlextassociationTable;

/** Native Joomla 5/6 administrator form model for associations. */
final class JlextassociationModel extends SportsManagementAdminModel
{
    /**
     * Resolve the native association table through Joomla MVC.
     * @param string $type Requested association table name.
     * @param string $prefix Legacy Joomla table namespace.
     * @param array $config Optional table settings.
     * @return mixed Association table or parent result.
     */
    public function getTable($type = 'jlextassociation', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'jlextassociation') === 0) {
            return new JlextassociationTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Normalise association founding and dissolution dates for storage.
     * Converts valid configured dates and derives founding/dissolution
     * year and timestamp values for the existing administrator schema.
     * @param array $data Submitted association form values.
     * @return array Prepared association record data.
     */
    protected function prepareSportsManagementData(array $data): array
    {
        $founded = trim((string) ($data['founded'] ?? ''));
        $dissolved = trim((string) ($data['dissolved'] ?? ''));

        if ($founded !== '' && $founded !== '0000-00-00') {
            $founded = SportsManagementDateHelper::convertDate($founded, 0);
        }

        if ($dissolved !== '' && $dissolved !== '0000-00-00') {
            $dissolved = SportsManagementDateHelper::convertDate($dissolved, 0);
        }

        $data['founded'] = $founded !== '' ? $founded : '0000-00-00';
        $data['dissolved'] = $dissolved !== '' ? $dissolved : '0000-00-00';

        if ($data['founded'] !== '0000-00-00') {
            $data['founded_year'] = date('Y', strtotime($data['founded']));
            $data['founded_timestamp'] = SportsManagementDateHelper::getTimestamp($data['founded']);
        } elseif (empty($data['founded_year'])) {
            $data['founded_year'] = 'kein';
        }

        if ($data['dissolved'] !== '0000-00-00') {
            $data['dissolved_year'] = date('Y', strtotime($data['dissolved']));
            $data['dissolved_timestamp'] = SportsManagementDateHelper::getTimestamp($data['dissolved']);
        }

        return $data;
    }
}
