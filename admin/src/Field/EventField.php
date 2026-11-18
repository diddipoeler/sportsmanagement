<?php
/**
 * Joomla 5/6 single-event field for administrator forms.
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

final class EventField extends SportsManagementListField
{
    protected $type = 'event';

    /**
     * Load published event types from the selected Joomla component database.
     *
     * Reads the configured table prefix only if it is a complete, bounded
     * SQL identifier. Invalid or nested PHP 8 values use the standard
     * SportsManagement table. Combines translated event labels with the
     * inherited Joomla form options.
     *
     * @return array<int,object> Published event choices and default options.
     */
    protected function getOptions(): array
    {
        $rawPrefix = ComponentHelper::getParams('com_sportsmanagement')
            ->get('cfg_which_database_table', 'sportsmanagement');
        $tablePrefix = is_string($rawPrefix) && strlen($rawPrefix) <= 64
            && preg_match('/^[A-Za-z0-9_]+$/D', $rawPrefix) === 1
                ? $rawPrefix : 'sportsmanagement';

        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
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

        foreach ($db->loadObjectList() ?: [] as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => Text::_((string) $item->text) . ' (' . $item->value . ')',
            ];
        }

        return array_merge($options, parent::getOptions());
    }
}
