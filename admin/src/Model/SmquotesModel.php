<?php
/**
 * Joomla 5/6 administrator quote list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;

/**
 * Native Joomla 5/6 administrator list model for SportsManagement quotes.
 */
final class SmquotesModel extends SportsManagementListModel
{
    /**
     * Register the supported quote-list filters and sort fields.
     * @param array $config Joomla administrator model options.
     * @param MVCFactoryInterface|null $factory Optional MVC factory.
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'obj.quote', 'quote',
            'obj.id', 'id',
            'obj.ordering', 'ordering',
            'obj.author', 'author',
            'obj.catid', 'catid',
            'obj.published', 'published', 'state',
        ];

        parent::__construct($config, $factory);
    }

    /**
     * Load quote text, published-state and category filters.
     * @param string $ordering Default quote ordering field.
     * @param string $direction Default sorting direction.
     * @return void
     */
    protected function populateState($ordering = 'obj.quote', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = $this->administratorApplication();
        $this->setState(
            'filter.search',
            $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string')
        );
        $this->setState(
            'filter.state',
            $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string')
        );
        $this->setState(
            'filter.catid',
            $app->getUserStateFromRequest($this->context . '.filter.catid', 'filter_catid', '', 'string')
        );
    }

    /**
     * Build a filtered and sorted administrator quote query.
     * Uses bound values for search, category and publication filters
     * and a fixed map of permitted ORDER BY columns. Rejects structured
     * filters, partial numeric values and integer overflow before query
     * construction instead of relying on PHP's permissive type casts.
     * @return mixed Joomla database query for quotes.
     */
    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('obj') . '.*',
                $db->quoteName('obj.author', 'name'),
                $db->quoteName('uc.name', 'editor'),
                $db->quoteName('c.title', 'category_title'),
            ])
            ->from($db->quoteName('#__sportsmanagement_rquote', 'obj'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'uc')
                . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('obj.checked_out')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__categories', 'c')
                . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('obj.catid')
            );

        $rawSearch = $this->getState('filter.search');
        $search = is_string($rawSearch) && strlen($rawSearch) <= 256
            && preg_match('/[\\x00-\\x1F\\x7F]/', $rawSearch) !== 1
            ? trim($rawSearch) : '';

        if ($search !== '') {
            $token = '%' . $db->escape($search, true) . '%';
            $query->where('LOWER(' . $db->quoteName('obj.author') . ') LIKE LOWER(:quoteSearch)')
                ->bind(':quoteSearch', $token, ParameterType::STRING);
        }

        $state = self::numericFilter($this->getState('filter.state'), -2);

        if ($state !== null) {
            $query->where($db->quoteName('obj.published') . ' = :quotePublished')
                ->bind(':quotePublished', $state, ParameterType::INTEGER);
        }

        $categoryId = self::numericFilter($this->getState('filter.catid'), 0);

        if ($categoryId !== null) {
            $query->where($db->quoteName('obj.catid') . ' = :quoteCategoryId')
                ->bind(':quoteCategoryId', $categoryId, ParameterType::INTEGER);
        }

        $orderMap = [
            'obj.quote' => $db->quoteName('obj.quote'),
            'quote' => $db->quoteName('obj.quote'),
            'obj.id' => $db->quoteName('obj.id'),
            'id' => $db->quoteName('obj.id'),
            'obj.ordering' => $db->quoteName('obj.ordering'),
            'ordering' => $db->quoteName('obj.ordering'),
            'obj.author' => $db->quoteName('obj.author'),
            'author' => $db->quoteName('obj.author'),
            'obj.catid' => $db->quoteName('obj.catid'),
            'catid' => $db->quoteName('obj.catid'),
            'obj.published' => $db->quoteName('obj.published'),
            'published' => $db->quoteName('obj.published'),
            'state' => $db->quoteName('obj.published'),
        ];
        $rawOrdering = $this->getState('list.ordering', 'obj.quote');
        $ordering = is_string($rawOrdering) ? $rawOrdering : 'obj.quote';
        $rawDirection = $this->getState('list.direction', 'ASC');
        $listDirection = is_string($rawDirection) && strtoupper($rawDirection) === 'DESC'
            ? 'DESC' : 'ASC';
        $query->order(($orderMap[$ordering] ?? $orderMap['obj.quote']) . ' ' . $listDirection);

        return $query;
    }

    /**
     * Validate administrator publication and category filters as integers.
     *
     * Accepts signed decimal strings and zero-padded values, but never
     * arrays, booleans, floats, scientific notation, partial strings or
     * values outside PHP's integer range. Preserves Joomla's negative
     * published-state selection down to -2 and category zero.
     *
     * @param mixed $raw Filter state from Joomla request or user state.
     * @param int $minimum Lowest supported filter selection.
     * @return int|null Validated integer or null when absent or malformed.
     */
    private static function numericFilter(mixed $raw, int $minimum): ?int
    {
        if (is_int($raw)) {
            return $raw >= $minimum ? $raw : null;
        }

        if (!is_string($raw) || strlen($raw) > 64
            || preg_match('/^-?[0-9]+$/D', $raw) !== 1) {
            return null;
        }

        $digits = ltrim(ltrim($raw, '-'), '0');
        $digits = $digits === '' ? '0' : $digits;
        $negative = str_starts_with($raw, '-') && $digits !== '0';
        $maximum = $negative ? substr((string) PHP_INT_MIN, 1) : (string) PHP_INT_MAX;

        if (strlen($digits) > strlen($maximum)
            || (strlen($digits) === strlen($maximum) && strcmp($digits, $maximum) > 0)) {
            return null;
        }

        $value = (int) (($negative ? '-' : '') . $digits);

        return $value >= $minimum ? $value : null;
    }
}
