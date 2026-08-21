<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

final class UserfieldsField extends SportsManagementListField
{
    protected $type = 'userfields';

    protected function getInput(): string
    {
        return HTMLHelper::_(
            'select.genericlist',
            $this->getOptions(),
            $this->name,
            'class="form-select" style="width:225px" size="1" onchange="this.form.submit();"',
            'value',
            'text',
            $this->value,
            $this->id
        );
    }

    protected function getOptions(): array
    {
        $view = Factory::getApplication()->getInput()->getCmd('view');
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_user_extra_fields'))
            ->order($db->quoteName('name'));

        if (in_array($view, ['projects', 'project'], true)) {
            $query->where($db->quoteName('template_backend') . ' = ' . $db->quote('project'));
        } elseif (in_array($view, ['teams', 'team'], true)) {
            $query->where($db->quoteName('template_backend') . ' = ' . $db->quote('team'));
        }

        $db->setQuery($query);

        return array_merge(parent::getOptions(), $db->loadObjectList() ?: []);
    }
}
