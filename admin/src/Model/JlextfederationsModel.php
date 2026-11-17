<?php
/**
 * Joomla 5/6 administrator federations list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\CountryOptionsHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;

/** Native Joomla 5/6 administrator list model for federations. */
final class JlextfederationsModel extends SportsManagementListModel
{
    /**
     * Register supported federation list columns and sorting options.
     * @param array $config Joomla list model configuration.
     * @param MVCFactoryInterface|null $factory Optional MVC factory.
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'objassoc.name', 'name',
            'objassoc.short_name', 'short_name',
            'objassoc.alias', 'alias',
            'objassoc.id', 'id',
            'objassoc.ordering', 'ordering',
            'objassoc.picture', 'picture',
            'objassoc.flag_maps', 'flag_maps',
            'objassoc.assocflag', 'assocflag',
            'objassoc.published', 'published', 'state',
            'objassoc.modified', 'modified',
            'objassoc.modified_by', 'modified_by',
            'objassoc.checked_out', 'checked_out',
            'objassoc.checked_out_time', 'checked_out_time',
            'objassoc.country', 'country', 'search_nation',
        ];

        parent::__construct($config, $factory);
    }

    /**
     * Build country dropdown choices for the native federation list.
     *
     * Loads registered ISO-3 countries and includes codes already stored
     * in federation records, so existing associations remain filterable
     * when the countries table is incomplete. Ignores malformed records
     * and duplicate codes without breaking the administrator view.
     *
     * @return array<int,object> Sorted ISO-3 country options.
     */
    public function getCountryOptions(): array
    {
        $db = $this->getDatabase();
        $options = [];
        $seen = [];

        foreach (CountryOptionsHelper::getOptions($db) as $option) {
            if (!is_object($option) || !is_string($option->value ?? null)
                || !is_string($option->text ?? null)) {
                continue;
            }

            $code = strtoupper(trim($option->value));

            if (preg_match('/^[A-Z]{3}$/D', $code) !== 1 || isset($seen[$code])) {
                continue;
            }

            $seen[$code] = true;
            $options[] = (object) ['value' => $code, 'text' => $option->text];
        }

        try {
            $query = $db->createQuery()
                ->select('DISTINCT ' . $db->quoteName('country'))
                ->from($db->quoteName('#__sportsmanagement_federations'))
                ->where($db->quoteName('country') . ' IS NOT NULL')
                ->order($db->quoteName('country') . ' ASC');
            $db->setQuery($query);
            $storedCountries = $db->loadColumn();

            if (is_array($storedCountries)) {
                foreach ($storedCountries as $rawCode) {
                    if (!is_string($rawCode)) {
                        continue;
                    }

                    $code = strtoupper(trim($rawCode));

                    if (preg_match('/^[A-Z]{3}$/D', $code) !== 1 || isset($seen[$code])) {
                        continue;
                    }

                    $seen[$code] = true;
                    $options[] = (object) ['value' => $code, 'text' => $code];
                }
            }
        } catch (\Throwable $exception) {
            Log::add(
                __METHOD__ . ': federation country choices failed (' . get_class($exception) . ').',
                Log::WARNING,
                'com_sportsmanagement'
            );
        }

        usort(
            $options,
            static fn (object $left, object $right): int => strnatcasecmp($left->text, $right->text)
        );

        return $options;
    }

    /**
     * Populate country, search and publication filters through Joomla ListModel.
     *
     * The native filter form now supplies filter[search_nation], so Joomla
     * manages session persistence, pagination resets and filter state.
     * Do not read the removed flat filter_search_nation request parameter,
     * which could override a selected country with stale legacy input.
     *
     * @param string $ordering Default federation sort field.
     * @param string $direction Default sort direction.
     * @return void
     */
    protected function populateState($ordering = 'objassoc.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);
    }

    /**
     * Build the filtered and ordered list of federations.
     * Filters federation name and nation through bound SQL parameters
     * and restricts sorting to configured fields. Rejects structured,
     * malformed or control-bearing filter state without PHP 8 string casts
     * or generating SQL conditions from invalid selections.
     * @return mixed Joomla database query for federations.
     */
    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('objassoc') . '.*',
                $db->quoteName('uc.name', 'editor'),
            ])
            ->from($db->quoteName('#__sportsmanagement_federations', 'objassoc'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'uc')
                . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('objassoc.checked_out')
            );

        $rawSearch = $this->getState('filter.search');
        // Reject structured and control-bearing request values before
        // generating the bound SQL search expression.
        $search = is_string($rawSearch) && strlen($rawSearch) <= 255
            && preg_match('//u', $rawSearch) === 1
            && preg_match('/[\\x00-\\x1F\\x7F]/', $rawSearch) !== 1
                ? trim($rawSearch) : '';

        if ($search !== '') {
            $searchToken = '%' . $db->escape($search, true) . '%';
            $query
                ->where('LOWER(' . $db->quoteName('objassoc.name') . ') LIKE LOWER(:searchToken)')
                ->bind(':searchToken', $searchToken, ParameterType::STRING);
        }

        $rawCountry = $this->getState('filter.search_nation');
        $country = is_string($rawCountry) && strlen($rawCountry) === 3
            && preg_match('/^[A-Za-z]{3}$/D', $rawCountry) === 1
                ? strtoupper($rawCountry) : '';

        if ($country !== '') {
            $query
                ->where($db->quoteName('objassoc.country') . ' = :country')
                ->bind(':country', $country, ParameterType::STRING);
        }

        $state = $this->getState('filter.state');

        // Joomla publication filters accept only the real 0/1 values,
        // not arrays, partial numbers or unexpected numeric strings.
        if ($state === 0 || $state === 1 || $state === '0' || $state === '1') {
            $publishedState = (int) $state;
            $query
                ->where($db->quoteName('objassoc.published') . ' = :publishedState')
                ->bind(':publishedState', $publishedState, ParameterType::INTEGER);
        }

        $map = [
            'objassoc.name' => $db->quoteName('objassoc.name'),
            'name' => $db->quoteName('objassoc.name'),
            'objassoc.short_name' => $db->quoteName('objassoc.short_name'),
            'short_name' => $db->quoteName('objassoc.short_name'),
            'objassoc.alias' => $db->quoteName('objassoc.alias'),
            'alias' => $db->quoteName('objassoc.alias'),
            'objassoc.id' => $db->quoteName('objassoc.id'),
            'id' => $db->quoteName('objassoc.id'),
            'objassoc.ordering' => $db->quoteName('objassoc.ordering'),
            'ordering' => $db->quoteName('objassoc.ordering'),
            'objassoc.picture' => $db->quoteName('objassoc.picture'),
            'picture' => $db->quoteName('objassoc.picture'),
            'objassoc.flag_maps' => $db->quoteName('objassoc.flag_maps'),
            'flag_maps' => $db->quoteName('objassoc.flag_maps'),
            'objassoc.assocflag' => $db->quoteName('objassoc.assocflag'),
            'assocflag' => $db->quoteName('objassoc.assocflag'),
            'objassoc.published' => $db->quoteName('objassoc.published'),
            'published' => $db->quoteName('objassoc.published'),
            'state' => $db->quoteName('objassoc.published'),
            'objassoc.modified' => $db->quoteName('objassoc.modified'),
            'modified' => $db->quoteName('objassoc.modified'),
            'objassoc.modified_by' => $db->quoteName('objassoc.modified_by'),
            'modified_by' => $db->quoteName('objassoc.modified_by'),
            'objassoc.checked_out' => $db->quoteName('objassoc.checked_out'),
            'checked_out' => $db->quoteName('objassoc.checked_out'),
            'objassoc.checked_out_time' => $db->quoteName('objassoc.checked_out_time'),
            'checked_out_time' => $db->quoteName('objassoc.checked_out_time'),
            'objassoc.country' => $db->quoteName('objassoc.country'),
            'country' => $db->quoteName('objassoc.country'),
            'search_nation' => $db->quoteName('objassoc.country'),
        ];

        $ordering = (string) $this->getState('list.ordering', 'objassoc.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($map[$ordering] ?? $map['objassoc.name']) . ' ' . $direction);

        return $query;
    }
}
