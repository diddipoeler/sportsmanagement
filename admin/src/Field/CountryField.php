<?php
/**
 * Joomla 5/6 native country field.
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

final class CountryField extends SportsManagementListField
{
    protected $type = 'Country';

    /**
     * List installed countries as native Joomla administrator select options.
     *
     * Validates database records before building translated option labels,
     * skips missing or malformed ISO alpha-3 codes and returns inherited
     * options if the optional country lookup fails.
     *
     * @return array<int,object> Inherited and verified country options.
     */
    protected function getOptions(): array
    {
        $options = parent::getOptions();

        try {
            $db = $this->getSportsManagementDatabase();
            $query = $db->createQuery()
                ->select([$db->quoteName('alpha3', 'value'), $db->quoteName('name', 'text')])
                ->from($db->quoteName('#__sportsmanagement_countries'))
                ->order($db->quoteName('name'));
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (\Throwable $exception) {
            Log::add(
                __METHOD__ . ': country lookup failed (' . get_class($exception) . ').',
                Log::WARNING,
                'com_sportsmanagement'
            );

            return $options;
        }

        foreach ($rows as $item) {
            if (!is_object($item) || !is_string($item->value ?? null)) {
                continue;
            }

            $code = strtoupper(trim($item->value));

            if (preg_match('/^[A-Z]{3}$/D', $code) !== 1) {
                continue;
            }

            $name = $item->text ?? $code;
            $label = is_string($name) && $name !== '' ? $name : $code;
            $options[] = (object) [
                'value' => $code,
                'text' => Text::_($label),
            ];
        }

        return $options;
    }
}
