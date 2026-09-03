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
    public function getForm($data = [], $loadData = true)
    {
        $form = parent::getForm($data, $loadData);

        if (!$form) {
            return false;
        }

        $params = ComponentHelper::getParams('com_sportsmanagement');
        $mediaType = trim((string) $params->get('cfg_which_media_tool', 'media'));

        if ($mediaType === '' || ctype_digit($mediaType)) {
            $mediaType = 'media';
        }

        $form->setFieldAttribute('icon', 'default', (string) $params->get('ph_icon', ''));
        $form->setFieldAttribute('icon', 'directory', 'com_sportsmanagement/database/events');
        $form->setFieldAttribute('icon', 'type', $mediaType);

        return $form;
    }
}
