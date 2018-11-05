<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      ajax.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementModelAjax
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelAjax extends BaseDatabaseModel
{


/**
 * sportsmanagementModelAjax::getProjectSelect()
 * 
 * @param mixed $league_id
 * @return
 */
public function getProjectSelect($league_id)
	{
$app = Factory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);
        
        $query->select('p.id AS value, p.name AS text');
            $query->from('#__sportsmanagement_project AS p');
            $query->join('INNER','#__sportsmanagement_season AS s on s.id = p.season_id ');
            $query->join('INNER','#__sportsmanagement_league AS l on l.id = p.league_id');
            $query->where('p.published = 1');
            $query->where('p.league_id = '. $league_id);
            $query->order('s.name DESC, p.name ASC');

		$db->setQuery($query);
        
        //$this->getProjectSelect = $query->dump();
       
		$res = $db->loadObjectList();
		
		if ($res) 
		{

$options = array(HTMLHelper::_('select.option', 0, Text::_('-- Projekt selektieren --')));
					$options = array_merge($options, $res);

		}

        return $options;		
	}
    
    
/**
 * sportsmanagementModelAjax::getAssocLeagueSelect()
 * 
 * @param mixed $country_id
 * @param mixed $associd
 * @return
 */
public function getAssocLeagueSelect($country_id,$associd)
	{		
//$app = Factory::getApplication();
        $this->_db = sportsmanagementHelper::getDBConnection(); 
        $this->_query = $this->_db->getQuery(true);
        $this->_query->clear();
        $this->_query->select('l.id AS value, l.name AS text');
            $this->_query->from('#__sportsmanagement_league AS l');
            $this->_query->join('INNER','#__sportsmanagement_project AS p on l.id = p.league_id');
            $this->_query->join('INNER','#__sportsmanagement_season AS s on s.id = p.season_id ');
            if ( $associd )
            {
            $this->_query->where('l.associations = ' . $associd );
            }
            $this->_query->where('l.country = \'' . $country_id. '\'' );
            $this->_query->group('l.name');
            $this->_query->order('l.name');

		$this->_db->setQuery($this->_query);
        
        //$this->getAssocLeagueSelect = $query->dump();
        
		$res = $this->_db->loadObjectList();
//		if ($res) 
//        {
//        $options = array(HTMLHelper::_('select.option', 0, Text::_($this->getParam('leagues_text'))));
//		$options = array_merge($options, $res);
//		}

		return $res;
	}
        
    /**
     * sportsmanagementModelAjax::getCountrySubSubAssocSelect()
     * 
     * @param mixed $subassoc_id
     * @return
     */
    public function getCountrySubSubAssocSelect($subassoc_id)
{
$app = Factory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);

        $query->select('s.id AS value, s.name AS text');
$query->from('#__sportsmanagement_associations AS s');
$query->where('s.parent_id = '.$subassoc_id);
$query->where('s.published = 1');
$query->order('s.name');

                
		$db->setQuery($query);
        
        //$this->getCountrySubSubAssocSelect = $query->dump();
        
		$res = $db->loadObjectList();
//		if ($res) 
//        {
//		$options = array(HTMLHelper::_('select.option', 0, Text::_('-- Kreisverbände -- ')));
//			$options = array_merge($options, $res);
//		}

		return $res;

}

    /**
     * sportsmanagementModelAjax::getCountrySubAssocSelect()
     * 
     * @param mixed $assoc_id
     * @return
     */
    public function getCountrySubAssocSelect($assoc_id)
{
$app = Factory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);
        $options = '';
        
        $query->select('s.id AS value, s.name AS text');
$query->from('#__sportsmanagement_associations AS s');
$query->where('s.parent_id = '.$assoc_id);
$query->order('s.name');

		$db->setQuery($query);
        
        //$this->getCountrySubAssocSelect = $query->dump();
        
		$res = $db->loadObjectList();
        return $res;
        }
        
    /**
     * sportsmanagementModelAjax::getCountryAssocSelect()
     * 
     * @param mixed $country
     * @return
     */
    public function getCountryAssocSelect($country)
	{
$app = Factory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(); 
        $query = $db->getQuery(true);
        $options = '';
        
        $query->select('s.id AS value, s.name AS text');
$query->from('#__sportsmanagement_associations AS s');
$query->where('s.country = \''.$country.'\'');
$query->where('s.parent_id = 0');
$query->order('s.name');

		$db->setQuery($query);
       
		$res = $db->loadObjectList();
//		if ($res) {
//		  $options = array(HTMLHelper::_('select.option', 0, Text::_('-- Regionalverbände -- ')));
//			$options = array_merge($options, $res);
//		}
//        else
//        {
//            $options = array(HTMLHelper::_('select.option', 0, Text::_('-- Regionalverbände -- ')));
//        }

		return $res;
	}
    
    
    
    /**
     * sportsmanagementModelAjax::getProjectsOptions()
     * 
     * @param integer $season_id
     * @param integer $league_id
     * @param integer $ordering
     * @return
     */
    function getProjectsOptions($season_id = 0, $league_id = 0, $ordering = 0)
    {
        $app = Factory::getApplication();
        $db = Factory::getDbo();
        //$option = Factory::getApplication()->input->getCmd('option');
        // Select some fields
        $query = $db->getQuery(true);
        $query->select('p.id AS value, p.name AS text, s.name AS season_name, l.name AS league_name');
        $query->from('#__sportsmanagement_project AS p');
        $query->join('INNER', '#__sportsmanagement_season AS s on s.id = p.season_id');
        $query->join('INNER', '#__sportsmanagement_league AS l on l.id = p.league_id');
        $query->where('p.published = 1');


        if ($season_id) {
            $query->where('p.season_id = ' . $season_id);
        }
        if ($league_id) {
            $query->where('p.league_id = ' . $league_id);
        }

        switch ($ordering) {

            case 1:
                $query->order('p.ordering DESC');
                break;

            case 2:
                $query->order('s.ordering ASC, l.ordering ASC, p.ordering ASC');
                break;

            case 3:
                $query->order('s.ordering DESC, l.ordering DESC, p.ordering DESC');
                break;

            case 4:
                $query->order('p.name ASC');
                break;

            case 5:
                $query->order('p.name DESC');
                break;

            case 0:
            default:
                $query->order('p.ordering ASC');
                break;
        }

        $db->setQuery($query);
        $res = $db->loadObjectList();
        return $res;
    }
}
