<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

final class ProjectdivisionField extends SportsManagementListField
{
    protected $type = 'Projectdivision';

    protected function getOptions(): array
    {
        $projectId = Factory::getApplication()->input->getInt('pid', 0)
            ?: (int) Factory::getApplication()->getUserState('com_sportsmanagement.pid', 0);
        if ($projectId <= 0) return parent::getOptions();
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id', 'value'), $db->quoteName('name', 'text')])
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->order($db->quoteName('name') . ' ASC');
        $db->setQuery($query);
        $options = [];
        foreach ($db->loadObjectList() ?: [] as $item) $options[] = HTMLHelper::_('select.option', $item->value, $item->text);
        return array_merge(parent::getOptions(), $options);
    }
}
