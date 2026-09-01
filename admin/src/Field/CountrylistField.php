<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

final class CountrylistField extends SportsManagementListField
{
    protected $type = 'countrylist';

    protected function getInput(): string
    {
        $view = Factory::getApplication()->getInput()->getCmd('view', '');
        $autoSubmitViews = [
            'clubs',
            'projects',
            'leagues',
            'projectteams',
            'jlextassociations',
            'teams',
        ];

        if (in_array($view, $autoSubmitViews, true) && trim((string) ($this->element['onchange'] ?? '')) === '') {
            $this->element['onchange'] = 'this.form.submit();';
        }

        return parent::getInput();
    }

    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('alpha3', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_countries'))
            ->order($db->quoteName('name'));
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => Text::_((string) $item->text),
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
