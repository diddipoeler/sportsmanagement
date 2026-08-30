<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Native Joomla 5/6 administrator list model for SportsManagement quotes.
 */
final class SmquotesModel extends SportsManagementListModel
{
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

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
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

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('LOWER(' . $db->quoteName('obj.author') . ') LIKE LOWER(' . $token . ')');
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('obj.published') . ' = ' . (int) $state);
        }

        $categoryId = $this->getState('filter.catid');

        if ($categoryId !== '' && is_numeric($categoryId)) {
            $query->where($db->quoteName('obj.catid') . ' = ' . (int) $categoryId);
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
        $ordering = (string) $this->getState('list.ordering', 'obj.quote');
        $listDirection = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($orderMap[$ordering] ?? $orderMap['obj.quote']) . ' ' . $listDirection);

        return $query;
    }
}
