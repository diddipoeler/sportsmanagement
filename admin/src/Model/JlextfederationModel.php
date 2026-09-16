<?php
/**
 * Joomla 5/6 administrator federation form model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Table\JlextfederationTable;

/** Native Joomla 5/6 administrator form model for federations. */
final class JlextfederationModel extends SportsManagementAdminModel
{
    public function getTable($type = 'jlextfederation', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'jlextfederation') === 0) {
            return new JlextfederationTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

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
