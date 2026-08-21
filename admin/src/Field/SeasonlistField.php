<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class SeasonlistField extends SportsManagementListField
{
    protected $type = 'seasonlist';

    protected function getInput(): string
    {
        $app = Factory::getApplication();
        $view = $app->getInput()->getCmd('view');
        $attributes = ['class="form-select"'];
        $size = trim((string) ($this->element['size'] ?? ''));

        if ($size !== '') {
            $attributes[] = 'size="' . htmlspecialchars($size, ENT_QUOTES, 'UTF-8') . '"';
        }

        if (in_array($view, ['clubs', 'projects'], true) && $this->name !== 'filter[copytoseason]') {
            $attributes[] = 'onchange="this.form.submit();"';
        } elseif ($view === 'project') {
            $attributes[] = 'onchange="setseasonname();"';
        }

        if (in_array($view, ['club', 'league', 'playground'], true)) {
            $attributes[] = 'multiple="multiple"';
        }

        return HTMLHelper::_(
            'select.genericlist',
            $this->getOptions(),
            $this->name,
            implode(' ', $attributes),
            'value',
            'text',
            $this->value,
            $this->id
        );
    }

    protected function getOptions(): array
    {
        $app = Factory::getApplication();
        $option = $app->getInput()->getCmd('option');
        $context = $option === 'com_modules' ? 'params' : 'request';
        $whichDatabase = $this->form->getValue('cfg_which_database', $context);
        $db = $this->getSportsManagementDatabase($whichDatabase);
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_season'))
            ->order($db->quoteName('name') . ' DESC');
        $db->setQuery($query);

        $placeholder = $this->name === 'filter[copytoseason]'
            ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_COPYTOSEASON_FILTER')
            : Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON_FILTER');
        $options = [HTMLHelper::_('select.option', '', $placeholder)];

        return array_merge($options, $db->loadObjectList() ?: []);
    }
}
