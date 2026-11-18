<?php
/**
 * Joomla 5/6 native event type list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

final class EventtypelistField extends SportsManagementListField
{
    protected $type = 'eventtypelist';

    /**
     * Retrieve published event types in Joomla's configured ordering.
     *
     * Builds and executes a namespaced database query, translates event
     * labels, and retains core ListField options. When optional database
     * access fails, logs only the exception class rather than potentially
     * sensitive SQL text, credentials or filesystem paths.
     *
     * @return array<int,object> Published event list options.
     */
    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
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
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::NOTICE, 'jsmerror');
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
