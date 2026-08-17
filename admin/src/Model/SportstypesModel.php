<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
/** Native Joomla 5/6 sports-types list for standard CRUD. */
final class SportstypesModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? ['s.name','name','s.icon','icon','s.sportsart','sportsart','s.id','id','s.ordering','ordering','s.published','published','state'];
        parent::__construct($config, $factory);
    }
    protected function populateState($ordering = 's.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);
        $app = Factory::getApplication();
        $this->setState('filter.search', $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
        $this->setState('filter.state', $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string'));
    }
    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('s.id'),$db->quoteName('s.name'),$db->quoteName('s.icon'),$db->quoteName('s.sportsart'),$db->quoteName('s.eventtime'),$db->quoteName('s.published'),$db->quoteName('s.ordering'),$db->quoteName('s.checked_out'),$db->quoteName('s.checked_out_time'),$db->quoteName('uc.name','editor')])
            ->from($db->quoteName('#__sportsmanagement_sports_type','s'))
            ->join('LEFT', $db->quoteName('#__users','uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('s.checked_out'));
        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('LOWER(' . $db->quoteName('s.name') . ') LIKE LOWER(' . $token . ')');
        }
        $state = $this->getState('filter.state');
        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('s.published') . ' = ' . (int) $state);
        }
        $map = ['s.name'=>$db->quoteName('s.name'),'name'=>$db->quoteName('s.name'),'s.icon'=>$db->quoteName('s.icon'),'icon'=>$db->quoteName('s.icon'),'s.sportsart'=>$db->quoteName('s.sportsart'),'sportsart'=>$db->quoteName('s.sportsart'),'s.published'=>$db->quoteName('s.published'),'published'=>$db->quoteName('s.published'),'state'=>$db->quoteName('s.published'),'s.ordering'=>$db->quoteName('s.ordering'),'ordering'=>$db->quoteName('s.ordering'),'s.id'=>$db->quoteName('s.id'),'id'=>$db->quoteName('s.id')];
        $ordering = (string) $this->getState('list.ordering', 's.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($map[$ordering] ?? $map['s.name']) . ' ' . $direction);
        return $query;
    }
    public function getSportsTypes(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)->select([$db->quoteName('id'),$db->quoteName('name'),$db->quoteName('name','text'),$db->quoteName('icon')])->from($db->quoteName('#__sportsmanagement_sports_type'))->order($db->quoteName('name') . ' ASC');
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }
}
