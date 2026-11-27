<?php
/**
 * Joomla 5/6 multi-event field for administrator forms.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

final class EventsField extends SportsManagementListField
{
    protected $type = 'events';

    /**
     * Render the published events field as a multiple-selection control.
     *
     * Preserves the legacy ten-row display but permits custom form XML
     * sizes only when they are valid integers between one and fifty.
     * Joomla's parent field handles the final input HTML.
     *
     * @return string Joomla event multi-select HTML.
     */
    protected function getInput(): string
    {
        $this->element['multiple'] = 'true';
        $rawSize = $this->element['size'] ?? '10';
        $size = is_string($rawSize) || $rawSize instanceof \Stringable
            ? (string) $rawSize : '';
        $this->element['size'] = preg_match('/^[1-9][0-9]?$/D', $size) === 1
            && (int) $size <= 50 ? $size : '10';

        return parent::getInput();
    }

    /**
     * Build the Joomla administrator multiple-event selector options.
     *
     * Uses a validated component table prefix rather than coercing nested
     * or malformed settings into an SQL table name. Includes published
     * event IDs and translated labels alongside the inherited options.
     *
     * @return array<int,object> Default and published event-type options.
     */
    protected function getOptions(): array
    {
        $rawPrefix = ComponentHelper::getParams('com_sportsmanagement')
            ->get('cfg_which_database_table', 'sportsmanagement');
        $tablePrefix = is_string($rawPrefix) && strlen($rawPrefix) <= 64
            && preg_match('/^[A-Za-z0-9_]+$/D', $rawPrefix) === 1
                ? $rawPrefix : 'sportsmanagement';

        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('e.id', 'value'),
                $db->quoteName('e.name', 'text'),
            ])
            ->from($db->quoteName('#__' . $tablePrefix . '_eventtype', 'e'))
            ->where($db->quoteName('e.published') . ' = 1')
            ->order($db->quoteName('e.name') . ' ASC');

        $db->setQuery($query);

        $options = [
            (object) [
                'value' => '',
                'text' => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'),
            ],
        ];

        $rows = $db->loadObjectList();
        foreach (is_array($rows) ? $rows : [] as $item) {
            if (!is_object($item)
                || (!is_int($item->value ?? null) && !is_string($item->value ?? null))
                || !is_string($item->text ?? null)) {
                continue;
            }

            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => Text::_((string) $item->text) . ' (' . $item->value . ')',
            ];
        }

        return array_merge($options, parent::getOptions());
    }
}
