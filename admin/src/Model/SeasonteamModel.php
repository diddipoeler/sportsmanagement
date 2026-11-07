<?php
/**
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

/**
 * Native Joomla 5/6 administrator form model for a season team assignment.
 */
final class SeasonteamModel extends SportsManagementAdminModel
{
    /**
     * Allow season-team editing only when the administrator is authorised.
     *
     * @param array $data Selected season-team record.
     * @param string $key Record ID field.
     * @return bool Whether the current user may edit the relation.
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $id = (int) ($data[$key] ?? 0);

        $identity = $this->administratorApplication()->getIdentity();

        return ($identity !== null && $identity->authorise(
            'core.edit',
            'com_sportsmanagement.message.' . $id
        )) || parent::allowEdit($data, $key);
    }
}
