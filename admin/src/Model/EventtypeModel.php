<?php
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

        $mediaType = ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_media_tool', 'media');
        $form->setFieldAttribute('icon', 'default', ComponentHelper::getParams('com_sportsmanagement')->get('ph_icon', ''));
        $form->setFieldAttribute('icon', 'directory', 'com_sportsmanagement/database/events');

        if ($mediaType) {
            $form->setFieldAttribute('icon', 'type', (string) $mediaType);
        }

        return $form;
    }
}
