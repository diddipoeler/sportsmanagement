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
 * Native Joomla 5/6 administrator form model for a season person assignment.
 */
final class SeasonpersonModel extends SportsManagementAdminModel
{
    /**
     * Authorise changes to a person-season assignment in Joomla's model.
     *
     * Requires an active administrator identity and defers the actual
     * record ACL decision to Joomla's form model, rather than inspecting
     * the unrelated legacy message.<id> permission.
     *
     * @param array $data Person-season record fields.
     * @param string $key Name of the record identifier field.
     * @return bool Whether Joomla permits editing this assignment.
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
