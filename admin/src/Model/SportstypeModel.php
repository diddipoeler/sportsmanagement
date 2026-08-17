<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;
\defined('_JEXEC') or die;
/** Native Joomla 5/6 sports-type form model for standard CRUD. */
final class SportstypeModel extends SportsManagementAdminModel
{
    public function getSportstype(int $sportstypeId): ?object
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)->select('*')->from($db->quoteName('#__sportsmanagement_sports_type'))->where($db->quoteName('id') . ' = ' . $sportstypeId);
        $db->setQuery($query);
        return $db->loadObject() ?: null;
    }
}
