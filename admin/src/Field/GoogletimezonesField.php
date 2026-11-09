<?php
/**
 * Native Joomla 5/6 Google timezones field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use DateTimeImmutable;
use DateTimeZone;
use Joomla\CMS\Form\Field\ListField;

final class GoogletimezonesField extends ListField
{
    protected $type = 'Googletimezones';

    /**
     * Build translated-friendly timezone choices ordered by current UTC offset.
     *
     * Includes backward-compatible PHP timezone identifiers and retains
     * inherited Joomla options for administrator configuration forms.
     *
     * @return array<int,object> Ordered timezone selector options.
     */
    protected function getOptions(): array
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $zones = [];

        foreach (DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC) as $identifier) {
            try {
                $timezone = new DateTimeZone($identifier);
                $offset = $timezone->getOffset($now);
            } catch (\Throwable) {
                continue;
            }

            $zones[] = [
                'identifier' => $identifier,
                'offset' => $offset,
                'label' => $this->formatOffset($offset) . ' ' . str_replace('_', ' ', $identifier),
            ];
        }

        usort(
            $zones,
            static fn(array $left, array $right): int => [$left['offset'], $left['identifier']] <=> [$right['offset'], $right['identifier']]
        );

        $options = [
            (object) [
                'value' => '',
                'text' => '',
            ],
        ];

        foreach ($zones as $zone) {
            $options[] = (object) [
                'value' => (string) $zone['identifier'],
                'text' => (string) $zone['label'],
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }

    /**
     * Convert a signed UTC offset in seconds to the familiar hour/minute label.
     *
     * Handles negative, positive and half-hour offsets using integer math.
     *
     * @param int $seconds Signed timezone offset in seconds.
     * @return string Offset label such as (UTC+05:30).
     */
    private function formatOffset(int $seconds): string
    {
        $sign = $seconds < 0 ? '-' : '+';
        $seconds = abs($seconds);
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return sprintf('(UTC%s%02d:%02d)', $sign, $hours, $minutes);
    }
}
