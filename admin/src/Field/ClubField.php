<?php
/**
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

final class ClubField extends SportsManagementListField
{
    protected $type = 'club';

    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('c.id', 'value'),
                $db->quoteName('c.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->order($db->quoteName('c.name') . ' ASC');

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
                'text' => (string) $item->text . ' (' . (int) $item->value . ')',
            ];
        }

        return array_merge($options, parent::getOptions());
    }
}
