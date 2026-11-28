<?php
/**
 * Joomla 5/6-native league selector used by administrator list filters.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\Database\ParameterType;

final class LeagueField extends SportsManagementListField
{
    protected $type = 'League';

    /** Native Joomla searchable list layout; retains the original option values. */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Build league options using the administrator's country and association.
     *
     * Reads Joomla POST and saved-user-state filters, validates complete
     * scalars before database access and binds both filters through the Joomla
     * query builder. Malformed legacy values return inherited options only
     * rather than widening a query to leagues outside the intended scope.
     *
     * @return array<int,object> Filtered league IDs/names and parent options.
     */
    protected function getOptions(): array
    {
        $app = SportsManagementAdministratorApplicationResolver::resolve();
        $input = $app->getInput();
        $filter = $input->post->get('filter', [], 'array');

        if (!is_array($filter)) {
            return parent::getOptions();
        }

        $country = self::countryFilter(
            $filter['search_nation'] ?? $app->getUserState('com_sportsmanagement.projects_search_nation', '')
        );
        $association = self::associationFilter(
            $filter['search_associations_leagues']
                ?? $app->getUserState('com_sportsmanagement.projects_search_associations_leagues', 0)
        );

        if ($country === null || $association === null) {
            return parent::getOptions();
        }

        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_league'))
            ->order($db->quoteName('name') . ' ASC');

        if ($country !== '') {
            $query->where($db->quoteName('country') . ' = :country')
                ->bind(':country', $country, ParameterType::STRING);
        }

        if ($association > 0) {
            $query->where($db->quoteName('associations') . ' = :association')
                ->bind(':association', $association, ParameterType::INTEGER);
        }

        $db->setQuery($query);
        $options = [];

        foreach ($db->loadObjectList() ?: [] as $league) {
            $options[] = (object) [
                'value' => (string) $league->value,
                'text' => (string) $league->text,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }

    /**
     * Validate a country selection from an administrator filter.
     *
     * Accepts printable UTF-8 country codes and stored country names up
     * to 64 bytes. Rejects arrays, control characters and malformed text
     * rather than silently discarding a requested SQL country constraint.
     *
     * @param mixed $value Raw POST or saved country setting.
     * @return string|null Trimmed country, or null for invalid settings.
     */
    private static function countryFilter(mixed $value): ?string
    {
        if (!is_string($value) || strlen($value) > 64
            || preg_match('/[\\x00-\\x1F\\x7F]/', $value) === 1
            || preg_match('//u', $value) !== 1) {
            return null;
        }

        return trim($value);
    }

    /**
     * Parse a whole nonnegative association identifier without lossy casts.
     *
     * Supports stored decimal strings with leading zeroes, and treats an
     * unset or empty selection as zero (no filter). Rejects signs, boolean
     * values, arrays, partial numbers and integer-overflowing IDs.
     *
     * @param mixed $value Raw POST or saved association selector.
     * @return int|null Association ID, zero for no filter, null if malformed.
     */
    private static function associationFilter(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }

        if (!is_string($value) || strlen($value) > 64
            || preg_match('/^[0-9]+$/D', $value) !== 1) {
            return null;
        }

        $digits = ltrim($value, '0');
        $result = filter_var($digits === '' ? '0' : $digits, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0],
        ]);

        return $result === false ? null : $result;
    }
}
