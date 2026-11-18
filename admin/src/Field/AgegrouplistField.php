<?php
/**
 * Joomla 5/6 native age group list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

final class AgegrouplistField extends SportsManagementListField
{
    protected $type = 'agegrouplist';

    /**
     * Render the age-group filter with a default form-submission handler.
     *
     * Sets the onchange callback only when the XML field did not provide a
     * usable handler, preserving custom JavaScript and avoiding nested
     * value casts in PHP 8.
     *
     * @return string Joomla-generated age-group input HTML.
     */
    protected function getInput(): string
    {
        $onchange = $this->element['onchange'] ?? '';
        $onchange = is_string($onchange) || $onchange instanceof \Stringable
            ? (string) $onchange : '';

        if (trim($onchange) === '') {
            $this->element['onchange'] = 'this.form.submit();';
        }

        return parent::getInput();
    }

    /**
     * Load translated age groups with optional country suffixes.
     *
     * Uses the current component database, orders by group name and appends
     * the normal Joomla options required by administrator form selectors.
     *
     * @return array<int,object> Joomla and age-group select options.
     */
    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
                $db->quoteName('country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_agegroup'))
            ->order($db->quoteName('name'));
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $label = Text::_((string) $item->text);

            if (!empty($item->country)) {
                $label .= ' (' . (string) $item->country . ')';
            }

            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => $label,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
