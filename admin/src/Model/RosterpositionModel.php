<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

/** Native Joomla 5/6 roster-position form model. */
final class RosterpositionModel extends SportsManagementAdminModel
{
    protected function prepareSportsManagementData(array $data): array
    {
        $data['players'] = min(11, max(1, (int) ($data['players'] ?? 11)));

        if (array_key_exists('short_name', $data)) {
            $shortName = strtoupper(trim((string) $data['short_name']));
            $data['short_name'] = in_array($shortName, ['HOME_POS', 'AWAY_POS'], true)
                ? $shortName
                : 'HOME_POS';
            $data['alias'] = $data['short_name'];
        }

        return parent::prepareSportsManagementData($data);
    }
}
