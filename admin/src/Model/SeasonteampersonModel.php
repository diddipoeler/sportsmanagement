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
 * Native Joomla 5/6 administrator form model for a season team person assignment.
 */
final class SeasonteampersonModel extends SportsManagementAdminModel
{
    /**
     * Check editing permission for a season/team/person relationship.
     *
     * Prevents unauthenticated edits and delegates the component and
     * record-level decision to Joomla AdminModel. A legacy message.<id>
     * ACL rule does not apply to season-team-person relationships.
     *
     * @param array $data Relation data supplied by the edit form.
     * @param string $key Key that carries the relation record ID.
     * @return bool Whether the current identity can edit this relation.
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
