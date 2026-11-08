<?php
/**
 * Native Joomla 5/6 administrator form model for training data assignments.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

final class TrainingsdataModel extends SportsManagementAdminModel
{
    /**
     * Authorise a training-data assignment through the native Joomla ACL.
     *
     * Rejects a missing administrator identity and delegates permission to
     * Joomla's parent form model. The previous message.<id> asset applies
     * only to the unrelated legacy example table, not training records.
     *
     * @param array $data Training-data assignment values.
     * @param string $key Field holding the selected assignment ID.
     * @return bool Whether Joomla permits editing the training assignment.
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
