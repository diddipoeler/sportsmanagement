<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

final class TemplatelistField extends SportsManagementListField
{
    protected $type = 'templatelist';

    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('master_template') . ' = 0')
            ->order($db->quoteName('name'));
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $options[] = HTMLHelper::_('select.option', $item->value, $item->text);
        }

        return array_merge(parent::getOptions(), $options);
    }
}
