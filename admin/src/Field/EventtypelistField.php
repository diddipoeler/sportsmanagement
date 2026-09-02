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
use Joomla\CMS\Log\Log;

final class EventtypelistField extends SportsManagementListField
{
    protected $type = 'eventtypelist';

    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_eventtype'))
            ->where($db->quoteName('published') . ' = 1')
            ->order([
                $db->quoteName('ordering'),
                $db->quoteName('name'),
            ]);
        $db->setQuery($query);

        try {
            $items = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            Log::add($e->getMessage(), Log::NOTICE, 'jsmerror');
            $items = [];
        }

        $options = [];

        foreach ($items as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => Text::_((string) $item->text),
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
