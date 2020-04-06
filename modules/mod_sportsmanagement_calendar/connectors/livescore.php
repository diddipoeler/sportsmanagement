<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       livescore.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage mod_sportsmanagement_calendar
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

/**
 * LivescoreConnector
 * 
 * @package 
 * @author    Dieter Plöger
 * @copyright 2019
 * @version   $Id$
 * @access    public
 */
class LivescoreConnector extends JSMCalendar
{

    var $xparams;
    var $prefix;
  
    /**
   * LivescoreConnector::getMatches()
   * 
   * @param  mixed $caldates
   * @param  mixed $params
   * @param  mixed $matches
   * @return
   */
    function getMatches( &$caldates, &$params, &$matches ) 
    {
        $this->xparams = $params;
        $this->prefix = $params->prefix;
        $rows = LivescoreConnector::getRows($caldates);
        $output = LivescoreConnector::formatRows($rows, $matches);
        return $output;
    }
  
    /**
   * LivescoreConnector::formatRows()
   * 
   * @param  mixed $rows
   * @param  mixed $matches
   * @return
   */
    function formatRows( $rows, &$matches ) 
    {
        $newrows = array();
    
        foreach ($rows AS $key => $row) {
            $newrows[$key]['type'] = 'ls';
            $newrows[$key]['date'] = $row->mdate;
            $newrows[$key]['result'] = 'LIVE!';
            $newrows[$key]['headingtitle'] = parent::jl_utf8_convert('LiveScore', 'iso-8859-1', 'utf-8');
            $newrows[$key]['homename'] = parent::jl_utf8_convert($row->heim, 'iso-8859-1', 'utf-8');
            $newrows[$key]['homepic'] = '';
            $newrows[$key]['awaypic'] = '';
            $newrows[$key]['awayname'] = parent::jl_utf8_convert($row->gast, 'iso-8859-1', 'utf-8');
            $newrows[$key]['matchcode'] = $row->saison;
            $newrows[$key]['project_id'] = 'LIVE!';
            $matches[] = $newrows[$key];
        }
        return $newrows;
    }
  
    /**
   * LivescoreConnector::getRows()
   * 
   * @param  mixed  $caldates
   * @param  string $ordering
   * @return
   */
    function getRows($caldates, $ordering='ASC')
    {
        $database = Factory::getDbo();
        $query = "SELECT  * FROM #__livescore_games";
        $where = ' WHERE ';
        $where .= " mdate >= '".$caldates['start']."'";
        $where .= " AND mdate <= '".$caldates['end']."'";
        $where .= ' ORDER BY mdate '.$ordering;
        $query = ($this->prefix != '') ? str_replace('#__', $this->prefix, $query) : $query;
        $query .= $where;
        $database->setQuery($query);
        if (!$result = $database->loadObjectList() ) { $result = Array();
        }
      
        return $result;
    }
  
    /**
   * LivescoreConnector::build_url()
   * 
   * @param  mixed $row
   * @return void
   */
    function build_url( &$row ) 
    {
    
    }
  
}  
