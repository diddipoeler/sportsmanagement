<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use DateTimeImmutable;
use DateTimeZone;
use Joomla\CMS\Form\Field\ListField;

final class GoogletimezonesField extends ListField
{
    protected $type = 'Googletimezones';

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

    private function formatOffset(int $seconds): string
    {
        $sign = $seconds < 0 ? '-' : '+';
        $seconds = abs($seconds);
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return sprintf('(UTC%s%02d:%02d)', $sign, $hours, $minutes);
    }
}
