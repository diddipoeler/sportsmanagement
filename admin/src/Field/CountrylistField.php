<?php
/**
 * Joomla 5/6 country list form field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Language\Text;

final class CountrylistField extends SportsManagementListField
{
    protected $type = 'countrylist';

    /** Native Joomla Choices-based country selector with keyboard search. */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Render the country selector with automatic submission in list filters.
     * Preserves explicitly configured change handlers and only enables the
     * historical submit behaviour for supported administrator list views.
     * @return string Native Joomla list input markup.
     */
    protected function getInput(): string
    {
        $app = SportsManagementAdministratorApplicationResolver::resolve();
        $view = $app->getInput()->getCmd('view', '');
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

    /**
     * Load country codes and translated country names.
     * Uses the configured SportsManagement database and appends valid country
     * records to the inherited XML options.
     * @return array Joomla selection options.
     */
    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('alpha3', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_countries'))
            ->order($db->quoteName('name'));
        $db->setQuery($query);

        $options = [];

        $rows = $db->loadObjectList();
        foreach (is_array($rows) ? $rows : [] as $item) {
            if (!is_object($item)
                || (!is_int($item->value ?? null) && !is_string($item->value ?? null))
                || !is_string($item->text ?? null)) {
                continue;
            }

            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => Text::_((string) $item->text),
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
