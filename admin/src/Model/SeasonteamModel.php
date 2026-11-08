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
     * Authorise edits to season-team relations through Joomla's form model.
     *
     * Rejects requests without an administrator identity and delegates
     * record-level ACL checks to the native parent AdminModel. The former
     * message.<id> ACL lookup belongs to a different table and must not
     * grant permission to edit a season-team assignment.
     *
     * @param array $data Season-team record values.
     * @param string $key Key containing the relation identifier.
     * @return bool Whether the native Joomla ACL check permits editing.
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $identity = $this->administratorApplication()->getIdentity();

        if ($identity === null) {
            return false;
        }

        return parent::allowEdit($data, $key);
    }
}
