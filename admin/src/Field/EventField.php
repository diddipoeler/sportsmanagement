<?php
/**
 * @version    4.24.00
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

    protected function getOptions(): array
    {
        $tablePrefix = preg_replace(
            '/[^A-Za-z0-9_]/',
            '',
            (string) ComponentHelper::getParams('com_sportsmanagement')->get(
                'cfg_which_database_table',
                'sportsmanagement'
            )
        );
        $tablePrefix = $tablePrefix !== '' ? $tablePrefix : 'sportsmanagement';

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
