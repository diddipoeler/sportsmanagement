<?php
/**
 * Native Joomla 5/6 event-type form model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;

/** Native Joomla 5/6 event-type form model. */
final class EventtypeModel extends SportsManagementAdminModel
{
    /**
     * Build the native Joomla event-type form with safe media field settings.
     *
     * Retains the configured event icon directory and an optional default
     * icon, while accepting only a well-formed Joomla media-field type.
     * Malformed settings saved by older versions cannot cause PHP casts of
     * arrays or become arbitrary form field class names.
     *
     * @param array $data Existing or posted event-type field values.
     * @param bool $loadData Whether Joomla should load the saved record.
     * @return mixed Configured Joomla form or false if loading failed.
     */
    public function getForm($data = [], $loadData = true)
    {
        $form = parent::getForm($data, $loadData);

        if (!$form) {
            return false;
        }

        $params = ComponentHelper::getParams('com_sportsmanagement');
        $configuredMediaType = $params->get('cfg_which_media_tool', 'media');
        $mediaType = is_string($configuredMediaType) ? trim($configuredMediaType) : '';

        // Joomla form types are identifiers, not executable expressions.
        // Never cast legacy arrays, objects or invalid names to a field type.
        if (!preg_match('/^[A-Za-z][A-Za-z0-9_-]{0,63}$/D', $mediaType)) {
            $mediaType = 'media';
        }

        $rawIcon = $params->get('ph_icon', '');
        $defaultIcon = is_string($rawIcon) ? $rawIcon : '';

        $form->setFieldAttribute('icon', 'default', $defaultIcon);
        $form->setFieldAttribute('icon', 'directory', 'com_sportsmanagement/database/events');
        $form->setFieldAttribute('icon', 'type', $mediaType);

        return $form;
    }
}
