<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/** Native Joomla 5/6 administrator list model for countries. */
final class JlextcountriesModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'objcountry.name', 'name',
            'objcountry.picture', 'picture',
            'objcountry.flag_maps', 'flag_maps',
            'objcountry.id', 'id',
            'objcountry.alpha2', 'alpha2',
            'objcountry.alpha3', 'alpha3',
            'objcountry.itu', 'itu',
            'objcountry.fips', 'fips',
            'objcountry.ioc', 'ioc',
            'objcountry.fifa', 'fifa',
            'objcountry.ds', 'ds',
            'objcountry.wmo', 'wmo',
            'objcountry.ordering', 'ordering',
            'objcountry.checked_out', 'checked_out',
            'objcountry.published', 'published', 'state',
            'objcountry.checked_out_time', 'checked_out_time',
            'objcountry.federation', 'federation',
        ];

        parent::__construct($config, $factory);
    }

    public function getFederation(): array
    {
        $app = $this->administratorApplication();
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
                $db->quoteName('picture', 'listpicture'),
            ])
            ->from($db->quoteName('#__sportsmanagement_federations'))
            ->order($db->quoteName('name') . ' ASC');

        try {
            $db->setQuery($query);
            $results = $db->loadObjectList() ?: [];

            if (!$results) {
                $app->enqueueMessage(
                    Text::_('COM_SPORTSMANAGEMENT_ADMIN_FEDERATIONS_NULL'),
                    'notice'
                );
            }

            return $results;
        } catch (\Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'error');

            return [];
        }
    }

    protected function populateState($ordering = 'objcountry.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $input = $this->administratorApplication()->getInput();
        $federation = $input->getInt('filter_federation');
        $countryMap = strtoupper(trim($input->getString('filter_search_countrymap')));

        if ($federation > 0) {
            $this->setState('filter.federation', $federation);
        }

        if (in_array($countryMap, ['IS NULL', 'IS NOT NULL'], true)) {
            $this->setState('filter.search_countrymap', $countryMap);
        } elseif ($countryMap === '') {
            $this->setState('filter.search_countrymap', '');
        }
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('objcountry') . '.*',
                $db->quoteName('f.name', 'federation_name'),
                $db->quoteName('uc.name', 'editor'),
            ])
            ->from($db->quoteName('#__sportsmanagement_countries', 'objcountry'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_federations', 'f')
                . ' ON ' . $db->quoteName('f.id') . ' = ' . $db->quoteName('objcountry.federation')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'uc')
                . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('objcountry.checked_out')
            );

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('LOWER(' . $db->quoteName('objcountry.name') . ') LIKE LOWER(' . $token . ')');
        }

        $federation = (int) $this->getState('filter.federation', 0);

        if ($federation > 0) {
            $query->where($db->quoteName('objcountry.federation') . ' = ' . $federation);
        }

        $countryMap = strtoupper(trim((string) $this->getState('filter.search_countrymap')));

        if (in_array($countryMap, ['IS NULL', 'IS NOT NULL'], true)) {
            $query->where($db->quoteName('objcountry.countrymap_mapdata') . ' ' . $countryMap);
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('objcountry.published') . ' = ' . (int) $state);
        }

        $map = [
            'objcountry.name' => $db->quoteName('objcountry.name'),
            'name' => $db->quoteName('objcountry.name'),
            'objcountry.picture' => $db->quoteName('objcountry.picture'),
            'picture' => $db->quoteName('objcountry.picture'),
            'objcountry.flag_maps' => $db->quoteName('objcountry.flag_maps'),
            'flag_maps' => $db->quoteName('objcountry.flag_maps'),
            'objcountry.id' => $db->quoteName('objcountry.id'),
            'id' => $db->quoteName('objcountry.id'),
            'objcountry.alpha2' => $db->quoteName('objcountry.alpha2'),
            'alpha2' => $db->quoteName('objcountry.alpha2'),
            'objcountry.alpha3' => $db->quoteName('objcountry.alpha3'),
            'alpha3' => $db->quoteName('objcountry.alpha3'),
            'objcountry.itu' => $db->quoteName('objcountry.itu'),
            'itu' => $db->quoteName('objcountry.itu'),
            'objcountry.fips' => $db->quoteName('objcountry.fips'),
            'fips' => $db->quoteName('objcountry.fips'),
            'objcountry.ioc' => $db->quoteName('objcountry.ioc'),
            'ioc' => $db->quoteName('objcountry.ioc'),
            'objcountry.fifa' => $db->quoteName('objcountry.fifa'),
            'fifa' => $db->quoteName('objcountry.fifa'),
            'objcountry.ds' => $db->quoteName('objcountry.ds'),
            'ds' => $db->quoteName('objcountry.ds'),
            'objcountry.wmo' => $db->quoteName('objcountry.wmo'),
            'wmo' => $db->quoteName('objcountry.wmo'),
            'objcountry.ordering' => $db->quoteName('objcountry.ordering'),
            'ordering' => $db->quoteName('objcountry.ordering'),
            'objcountry.checked_out' => $db->quoteName('objcountry.checked_out'),
            'checked_out' => $db->quoteName('objcountry.checked_out'),
            'objcountry.published' => $db->quoteName('objcountry.published'),
            'published' => $db->quoteName('objcountry.published'),
            'state' => $db->quoteName('objcountry.published'),
            'objcountry.checked_out_time' => $db->quoteName('objcountry.checked_out_time'),
            'checked_out_time' => $db->quoteName('objcountry.checked_out_time'),
            'objcountry.federation' => $db->quoteName('objcountry.federation'),
            'federation' => $db->quoteName('objcountry.federation'),
        ];

        $ordering = (string) $this->getState('list.ordering', 'objcountry.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($map[$ordering] ?? $map['objcountry.name']) . ' ' . $direction);

        return $query;
    }
}
