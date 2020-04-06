<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       clubinfo.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage clubinfo
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelClubInfo
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelClubInfo extends BaseDatabaseModel
{

    static $projectid = 0;
    static $clubid = 0;
    static $club = null;
    static $new_club_id = 0;
    static $first_club_id = 0;
    static $historyhtml = '';
    static $historyobj = array();
    var $catssorted = array();
    static $jgcat_rows = array();
    static $jgcat_rows_sorted = Array();
    static $treedepth = 0;
    static $treedepthold = 0;
    static $cfg_which_database = 0;
    static $tree_fusion = Array();
    static $arrPCat = Array();
    static $historyhtmltree = '';

    /**
     * sportsmanagementModelClubInfo::__construct()
     *
     * @return void
     */
    function __construct()
    {
        // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;

        self::$projectid = $jinput->getInt("p", 0);
        self::$clubid = $jinput->getInt("cid", 0);

        sportsmanagementModelProject::$projectid = self::$projectid;
        self::$cfg_which_database = $jinput->getInt('cfg_which_database', 0);
        sportsmanagementModelProject::$cfg_which_database = self::$cfg_which_database;

        if (empty(self::$projectid) ) {
             Log::add(Text::_('COM_SPORTSMANAGEMENT_NO_RANKING_PROJECTINFO'), Log::ERROR, 'jsmerror');   
        }
        parent::__construct();
    }

    /**
     * sportsmanagementModelClubInfo::getFirstClubId()
     *
     * @param  integer $club_id
     * @param  integer $new_club_id
     * @return
     */
    static function getFirstClubId($club_id=0,$new_club_id=0)
    {
        $app = Factory::getApplication();
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);  
        $query = $db->getQuery(true);
 
        if ($new_club_id > 0 ) {
  
            $query->select('id,new_club_id');
            // From
            $query->from('#__sportsmanagement_club');
            // Where
            $query->where('id = ' . $new_club_id);
            $db->setQuery($query);
            $result_club_id = $db->loadObject();

            self::$first_club_id = $result_club_id->id;  
            self::getFirstClubId($result_club_id->id, $result_club_id->new_club_id);  
        }
        else
        {
            self::$first_club_id = $club_id;
            return $club_id;
        }
  
    }
  
    /**
     * sportsmanagementModelClubInfo::generateTree()
     *
     * @param  mixed $parent
     * @return void
     */
    static function generateTree($parent, $tree = 0)
    {
        $app = Factory::getApplication();

        if (array_key_exists($parent, self::$arrPCat)) {
            self::$historyhtmltree .= '<ul' . ($parent == 0 ? ' class="tree"' : '') . '>';
            foreach (self::$arrPCat[$parent] as $arrC) {

                if (!$tree) {
                    $treespan = '<span><i class="icon-minus-sign"></i>'.HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/arrow_left.png', '').'</span>';
                } else {
                    $treespan = '';
                }
                self::$historyhtmltree .= '<li>' . $treespan . '<span style="background-color:' . $arrC['color'] . '"><a href="' . $arrC['clublink'] . '">' . HTMLHelper::image($arrC['logo_big'], $arrC['name'], 'width="30"') . ' ' . $arrC['name'] . '</a></span>';
                self::generateTree($arrC['id'], $tree);

                self::$historyhtmltree .= '</li>';
            }
            self::$historyhtmltree .= '</ul>';
        }
    }

    /**
     * sportsmanagementModelClubInfo::fbTreeRecurse()
     *
     * @param  mixed   $id
     * @param  mixed   $indent
     * @param  mixed   $list
     * @param  mixed   $children
     * @param  integer $maxlevel
     * @param  integer $level
     * @param  integer $type
     * @return
     */
    static function fbTreeRecurse($id, $indent, $list, &$children, $maxlevel = 9999, $level = 0, $type = 1)
    {
        $app = Factory::getApplication();

        if (isset($children[$id]) && $level <= $maxlevel) {
            foreach ($children[$id] as $v) {
                $id = $v->id;
                if ($type) {
                    $pre = '&nbsp;';
                    $spacer = '...';
                } else {
                    $pre = '- ';
                    $spacer = '&nbsp;&nbsp;';
                }

                if ($v->new_club_id == 0) {
                    $txt = $v->name;
                } else {
                    $txt = $pre . $v->name;
                }
                $pt = $v->new_club_id;
                $list[$id] = $v;
                $list[$id]->treename = $indent . $txt;
                $list[$id]->children = !empty($children[$id]) ? count($children[$id]) : 0;
                $list[$id]->section = ($v->new_club_id == 0);

                $list = self::fbTreeRecurse($id, $indent . $spacer, $list, $children, $maxlevel, $level + 1, $type);

            }
        }
        return $list;
    }

    /**
     * sportsmanagementModelClubInfo::limitText()
     *
     * @param  mixed $text
     * @param  mixed $wordcount
     * @return
     */
    function limitText($text, $wordcount)
    {
        if (!$wordcount) {
            return $text;
        }

        $texts = explode(' ', $text);
        $count = count($texts);

        if ($count > $wordcount) {
            $texts = array_slice($texts, 0, $wordcount);
            $text = implode(' ', $texts);
            $text .= '...';
        }

        return $text;
    }

    /**
     * sportsmanagementModelClubInfo::getRssFeeds()
     *
     * @param  mixed $rssfeedlink
     * @param  mixed $rssitems
     * @return
     */
    public static function getRssFeeds($rssfeedlink, $rssitems)
    {
        $rssIds = array();
        $rssIds = explode(',', $rssfeedlink);
        //  get RSS parsed object
        $options = array();
        $options['cache_time'] = null;

        $lists = array();
        foreach ($rssIds as $rssId) {
            $options['rssUrl'] = $rssId;

            if (version_compare(JSM_JVERSION, '4', 'eq')) {
              
            } elseif (version_compare(JSM_JVERSION, '3', 'eq')) {
                // Joomla! 3.0 code here
                $rssDoc = Factory::getFeedParser($options);
            } elseif (version_compare(JSM_JVERSION, '2', 'eq')) {
                // Joomla! 2.5 code here
                $rssDoc = Factory::getXMLparser('RSS', $options);
            } elseif (version_compare(JVERSION, '1.7.0', 'ge')) {
                // Joomla! 1.7 code here
            } elseif (version_compare(JVERSION, '1.6.0', 'ge')) {
                // Joomla! 1.6 code here
            } else {
                // Joomla! 1.5 code here
            }

            if (version_compare(JSM_JVERSION, '4', 'eq')) {
                try {
                    $feed = new \JFeedFactory;
                    //$feeds = new stdclass();
                    $rssDoc = $feed->getFeed($rssId);
                    return $rssDoc;
                } catch (\InvalidArgumentException $e) {
                    Factory::getApplication()->enqueueMessage(Text::_('COM_NEWSFEEDS_ERRORS_FEED_NOT_RETRIEVED'), 'Notice');
                } catch (\RuntimeException $e) {
                    Factory::getApplication()->enqueueMessage(Text::_('COM_NEWSFEEDS_ERRORS_FEED_NOT_RETRIEVED'), 'Notice');
                }
            } else {
                $feed = new stdclass();
                if ($rssDoc != false) {
                    // channel header and link
                    $feed->title = $rssDoc->get_title();
                    $feed->link = $rssDoc->get_link();
                    $feed->description = $rssDoc->get_description();

                    // channel image if exists
                    $feed->image->url = $rssDoc->get_image_url();
                    $feed->image->title = $rssDoc->get_image_title();

                    // items
                    $items = $rssDoc->get_items();
                    // feed elements
                    $feed->items = array_slice($items, 0, $rssitems);
                    $lists[] = $feed;
                }
                return $lists;
            }
        }
    
    }

    /**
     * sportsmanagementModelClubInfo::getClubAssociation()
     *
     * @param  mixed $associations
     * @return
     */
    public static function getClubAssociation($associations)
    {
        // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
        $query = $db->getQuery(true);
      
        $query->select('asoc.*');
        // From
        $query->from('#__sportsmanagement_associations AS asoc');
        // Where
        $query->where('asoc.id = ' . $db->Quote($associations));

        $db->setQuery($query);
        $result = $db->loadObject();
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        return $result;
    }

    /**
 * sportsmanagementModelClubInfo::getFirstClub()
 *
 * @param  integer $club_id
 * @return
 */
    static function getFirstClub($club_id = 0)
    {
        // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
        $query = $db->getQuery(true);
      
                $query->select('c.*');
         $query->select('CONCAT_WS( \':\', c.id, c.alias ) AS club_slug');
         $query->select('CONCAT_WS(\':\',p.id,p.alias) as pro_slug');
                // From
                $query->from('#__sportsmanagement_club AS c');
         $query->join('INNER', '#__sportsmanagement_team AS t on t.club_id = c.id');
            $query->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
            $query->join('INNER', ' #__sportsmanagement_project_team AS pt ON pt.team_id = st.id ');
            $query->join('INNER', ' #__sportsmanagement_project AS p ON p.id = pt.project_id ');
                // Where
                $query->where('c.id = ' . $db->Quote($club_id));
        $query->group('c.name');
                $db->setQuery($query);
  
         $firstclub = $db->loadObject();
         $firstclub->clublink = sportsmanagementHelperRoute::getClubInfoRoute($firstclub->pro_slug, $firstclub->club_slug, null, self::$cfg_which_database);
         $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
                return $firstclub;
      
      
    }
  
    /**
     * sportsmanagementModelClubInfo::updateHits()
     *
     * @param  integer $clubid
     * @param  integer $inserthits
     * @return void
     */
    public static function updateHits($clubid = 0, $inserthits = 0)
    {
        $option = Factory::getApplication()->input->getCmd('option');
        $app = Factory::getApplication();
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        if ($inserthits) {
            $query->update($db->quoteName('#__sportsmanagement_club'))->set('hits = hits + 1')->where('id = ' . $clubid);

            $db->setQuery($query);

            $result = $db->execute();
            $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        }
   
    }

  
    /**
     * sportsmanagementModelClubInfo::getClub()
     *
     * @param  integer $inserthits
     * @param  integer $club_id
     * @return
     */
    static function getClub($inserthits = 0,$club_id = 0)
    {
        // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
        $query = $db->getQuery(true);

        self::$projectid = $jinput->getInt("p", 0);

        if (empty(self::$projectid) ) {
                 Log::add(Text::_('COM_SPORTSMANAGEMENT_NO_RANKING_PROJECTINFO'), Log::ERROR, 'jsmerror');   
        }
      
        if ($club_id ) {
            self::$clubid = $club_id;
        }
        else
        {
              self::$clubid = $jinput->getInt("cid", 0);
        }
      
        self::updateHits(self::$clubid, $inserthits);

        if (is_null(self::$club)) {
            if (self::$clubid > 0) {
              
                $query->select('c.*');
                // From
                $query->from('#__sportsmanagement_club AS c');
                // Where
                $query->where('c.id = ' . $db->Quote(self::$clubid));

                $db->setQuery($query);
                self::$club = $db->loadObject();
            }
        }
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        return self::$club;
    }

    /**
     * sportsmanagementModelClubInfo::getTeamsByClubId()
     *
     * @return
     */
    public static function getTeamsByClubId()
    {
        // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
        $query = $db->getQuery(true);
        $subquery1 = $db->getQuery(true);
        $subquery2 = $db->getQuery(true);
        $start_time = microtime(true);

        $teams = array(0);
        if (self::$clubid && self::$projectid ) {

            $query->select('t.id,t.name as team_name,t.short_name as team_shortcut,t.info as team_description');
            $query->select('CONCAT_WS( \':\', t.id, t.alias ) AS team_slug');
            $query->select(
                'COALESCE((SELECT MAX(pt.project_id)
				FROM #__sportsmanagement_project_team as pt
				RIGHT JOIN #__sportsmanagement_project as p on pt.project_id = p.id
                RIGHT JOIN #__sportsmanagement_season_team_id AS st on pt.team_id = st.id
				WHERE st.team_id = t.id), 0) as pid'
            );
            $query->from('#__sportsmanagement_team as t ');
            $query->where('t.club_id = ' . (int) self::$clubid);

            try {
                $db->setQuery($query);
                $teams = $db->loadObjectList();

                foreach ($teams AS $team) {
                    $subquery1->clear();
                    $subquery1->select('pt.id as ptid,pt.picture as project_team_picture,pt.trikot_home,pt.trikot_away');
                    $subquery1->from('#__sportsmanagement_project_team AS pt');
                    $subquery1->join('LEFT', '#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id ');
                    $subquery1->where('pt.project_id = ' . $team->pid);
                    $subquery1->where('st.team_id = ' . $team->id);
                    $db->setQuery($subquery1);
                    $result = $db->loadObject();
                    $team->ptid = $result->ptid;
                    $team->project_team_picture = $result->project_team_picture;
                    $team->trikot_home = $result->trikot_home;
                    $team->trikot_away = $result->trikot_away;
                    $subquery1->clear();
                    $subquery1->select('CONCAT_WS( \':\', p.id , p.alias )');
                    $subquery1->from('#__sportsmanagement_project AS p');
                    $subquery1->where('p.id = ' . $team->pid);
                    try {
                                     $db->setQuery($subquery1);
                                     $team->pid = $db->loadResult();
                    } catch (Exception $e) {
                                  $msg = $e->getMessage(); // Returns "Normally you would have other code...
                                  $code = $e->getCode(); // Returns
                                  Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error');
                                  $team->pid = 0;
                    }      
                
          
          
                }
             
            } catch (Exception $e) {
                $msg = $e->getMessage(); // Returns "Normally you would have other code...
                $code = $e->getCode(); // Returns
                Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error');
                return false;
            }
        }
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        $diff = microtime(true) - $start_time;
        $logarray['method'] = __METHOD__;
        $logarray['line'] = __LINE__;
        $logarray['zeit'] = $diff;
        if (ComponentHelper::getParams($option)->get('show_query_debug_info') ) {
                /**
 * Add the message.
 */
                Log::add(json_encode($logarray), Log::INFO, 'dbperformance');
        }
        return $teams;
    }

    /**
     * sportsmanagementModelClubInfo::getStadiums()
     *
     * @return
     */
    public static function getStadiums()
    {
        // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
        $query = $db->getQuery(true);

        $stadiums = array();

        $club = self::getClub();
        if (!isset($club)) {
            return null;
        }
        if ($club->standard_playground > 0) {
            $stadiums[] = $club->standard_playground;
        }
        $teams = self::getTeamsByClubId();

        if (count($teams)>0) {
            foreach ($teams AS $team) {
                $query->clear();
              
                $query->select('distinct(pt.standard_playground)');
                // From
                $query->from('#__sportsmanagement_project_team AS pt');
                $query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id');
                // Where
                $query->where('st.team_id = ' . (int) $team->id);
                $query->where('pt.standard_playground > 0');

                if ($club->standard_playground > 0) {
                    // Where
                    $query->where('pt.standard_playground <> ' . $club->standard_playground);
                }

                $query->group('pt.standard_playground');

                $db->setQuery($query);
                if ($res = $db->loadResult()) {
                    $stadiums[] = $res;
                }
            }
        }
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        return $stadiums;
    }

    /**
     * sportsmanagementModelClubInfo::getPlaygrounds()
     *
     * @return
     */
    public static function getPlaygrounds()
    {
        // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
        $query = $db->getQuery(true);

        $playgrounds = array();

        $stadiums = self::getStadiums();
        if (!isset($stadiums)) {
            return null;
        }

        foreach ($stadiums AS $stadium) {
            $query->clear();
          
            $query->select('id AS value, name AS text, pl.*');
            $query->select('CONCAT_WS( \':\', pl.id, pl.alias ) AS slug');
            // From
            $query->from('#__sportsmanagement_playground AS pl');
            // Where
            $query->where('id = ' . $stadium);

            $db->setQuery($query, 0, 1);
            $result = $db->loadObject();
            if ($result ) {
                      $playgrounds[] = $result;
            }
        }
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        return $playgrounds;
    }

    /**
     * sportsmanagementModelClubInfo::getClubHistory()
     *
     * @param  mixed $clubid
     * @return
     */
    public static function getClubHistory($clubid)
    {
        // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
        $query = $db->getQuery(true);
        $query->select('c.id, c.name, c.new_club_id');
        $query->select('CONCAT_WS( \':\', id, alias ) AS slug');
        $query->from('#__sportsmanagement_club AS c ');
        $query->where('c.new_club_id = ' . $clubid);
        try {
            $db->setQuery($query);
            $result = $db->loadObjectList();
            $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        } catch (Exception $e) {
            $msg = $e->getMessage(); // Returns "Normally you would have other code...
            $code = $e->getCode(); // Returns
            $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
            Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error');
                return false;
        }

        foreach ($result as $row) {
            $temp = new stdClass();
            $temp->id = $row->id;
            $temp->name = $row->name;
            $temp->slug = $row->slug;
            self::$historyobj[] = $temp;

            if ($row->new_club_id) {
                self::getClubHistory($row->id);
            } else {
                return self::$historyobj;
            }
        }
        return self::$historyobj;
    }

    /**
     * sportsmanagementModelClubInfo::getClubHistoryHTML()
     *
     * @param  mixed $clubid
     * @return
     */
    public static function getClubHistoryHTML($clubid)
    {
        // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
        $query = $db->getQuery(true);
        $subquery = $db->getQuery(true);
        $query->select('c.id, c.name, c.new_club_id,c.logo_big,c.founded_year');
        $query->select('CONCAT_WS( \':\', id, alias ) AS slug');
        $query->from('#__sportsmanagement_club AS c');
        $query->where('c.new_club_id = ' . $clubid);
     
        try {
            $db->setQuery($query);
            $result = $db->loadObjectList();
            $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        } catch (Exception $e) {
            $msg = $e->getMessage(); // Returns "Normally you would have other code...
            $code = $e->getCode(); // Returns
            $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
            Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error');
            return false;
        }

        foreach ($result as $row) {

            $subquery->clear();
            $subquery->select('max(p.id) as maxpid');
            $subquery->select('CONCAT_WS( \':\', p.id, p.alias ) AS pid');
            $subquery->from('#__sportsmanagement_project AS p');
            $subquery->join('INNER', '#__sportsmanagement_project_team AS pt on pt.project_id = p.id');
            $subquery->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id');
            $subquery->join('INNER', '#__sportsmanagement_team AS t ON t.id = st.team_id');
            $subquery->where('t.club_id = '. $row->id);
            $subquery->where('p.published = 1');  
            $db->setQuery($subquery);
            $result2 = $db->loadObject();
            $row->pid = $result2->pid;
          
            $pt = $row->new_club_id;
            $list = isset(self::$tree_fusion[$pt]) ? self::$tree_fusion[$pt] : array();
            array_push($list, $row);
            self::$tree_fusion[$pt] = $list;

            if (!$row->pid) {
                $row->pid = 0;
            }
            // store parent and its children into the $arrPCat Array
            if ($row->id == self::$clubid ) {
                        $color = 'lawngreen';
            }
            else
            {
                        $color = '';
            }      
            self::$arrPCat[$pt][] = Array('id' => $row->id,
                'name' => $row->name.' ('.$row->founded_year.')',
                'pid' => $row->pid,
                'slug' => $row->slug,
            'color' => $color,
                'logo_big' => $row->logo_big,
                'clublink' => sportsmanagementHelperRoute::getClubInfoRoute($row->pid, $row->slug)
            );

            if (self::$treedepthold === self::$treedepth) {
                $temp = '<li>';
            } else {
                $temp = '<ul><li>';
            }

            $link = sportsmanagementHelperRoute::getClubInfoRoute($row->pid, $row->slug, null, self::$cfg_which_database);
            $imageTitle = Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_HISTORY_FROM');

            $temp .= HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/club_from.png', $imageTitle, 'title= "' . $imageTitle . '"');
            $temp .= "&nbsp;";
            $temp .= HTMLHelper::link($link, $row->name);
            $temp .= '</li>';
            self::$historyhtml .= $temp;

            if ($row->new_club_id) {
                self::$treedepth++;
                self::getClubHistoryHTML($row->id);
            } else {
                for ($a = 0; $a < self::$treedepth; $a++) {
                    self::$historyhtml .= '</ul>';
                }
                return self::$historyhtml;
            }
            self::$treedepthold = self::$treedepth;
        }
        return self::$historyhtml;
    }

    /**
     * sportsmanagementModelClubInfo::getClubHistoryTree()
     *
     * @param  mixed $clubid
     * @param  mixed $new_club_id
     * @return
     */
    public static function getClubHistoryTree($clubid, $new_club_id)
    {
        // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
        $query = $db->getQuery(true);
        $subquery = $db->getQuery(true);

        if (self::$new_club_id != 0) {
            $icon = 'to_club.png';
            $query->where('c.id = ' . self::$new_club_id);
        } else {
            $icon = 'from_club.png';
            $query->where('c.new_club_id = ' . $clubid);
        }

        $query->select('c.id, c.name, c.new_club_id,c.founded_year');
        $query->select('CONCAT_WS( \':\', id, alias ) AS slug');
        $subquery->select('max(pt.project_id)');
        $subquery->from('#__sportsmanagement_project_team AS pt');
        $subquery->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id');
        $subquery->join('INNER', '#__sportsmanagement_team AS t ON t.id = st.team_id');
        $subquery->join('RIGHT', '#__sportsmanagement_project AS p on pt.project_id = p.id');
        $subquery->where('t.club_id = c.id ');
        $subquery->where('p.published = 1');
        $query->select('(' . $subquery . ') as pid ');
        $query->from('#__sportsmanagement_club AS c');
        try {
            $db->setQuery($query);
            $result = $db->loadObjectList();
            $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        } catch (Exception $e) {
            $msg = $e->getMessage(); // Returns "Normally you would have other code...
            $code = $e->getCode(); // Returns
            $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
            Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error');
            return false;
        }

        foreach ($result as $row) {
            $row->link = sportsmanagementHelperRoute::getClubInfoRoute($row->pid, $row->slug);
            $row->icon = $icon;
        }

        self::$jgcat_rows = array_merge(self::$jgcat_rows, $result);

        foreach ($result as $row) {
            if ($row->new_club_id) {
                self::$treedepth++;
                self::getClubHistoryTree($row->id, $row->new_club_id);
            } else {
                return self::$jgcat_rows;
            }
        }
        return self::$jgcat_rows;
    }

    /**
     * sportsmanagementModelClubInfo::getSortClubHistoryTree()
     *
     * @param  mixed $clubtree
     * @param  mixed $root_catid
     * @param  mixed $cat_name
     * @return
     */
    public static function getSortClubHistoryTree($clubtree, $root_catid, $cat_name)
    {
        // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $script = '';

        $jgcat_rows_sorted = Array();
        $jgcat_rows_sorted = self::sortCategoryList($clubtree, $jgcat_rows_sorted);

        $cat_link = '';

        $script .= "d" . $root_catid . " = new dTree('d" . $root_catid . "','" . Uri::base() . "/components/" . $option . "/assets/img/standard2/');" . "\n";
        $script .= "d" . $root_catid . ".add(" . "0" . ", " . "-1" . ", ";
        $script .= "'" . $cat_name . "', ";
        $script .= "'" . $cat_link . "', ";
        $script .= "'" . 'true' . "');" . "\n";

        foreach ($jgcat_rows_sorted as $key => $value) {
            foreach ($value as $row) {
                if ($root_catid == $row->new_club_id) {
                    $script .= "d" . $root_catid . ".add(" . $row->id . ", " . "0" . ", ";
                    $script .= "'" . $row->name.' ('.$row->founded_year.')' . "', ";
                    $script .= "'" . $row->link . "', ";
                    $script .= "'','" . $row->name.' ('.$row->founded_year.')' . "','','" . Uri::base() . "/components/" . $option . "/assets/img/standard2/" . $row->icon . "');" . "\n";
                } else {
                    $script .= "d" . $root_catid . ".add(" . $row->id . ", " . $row->new_club_id . ", ";
                    $script .= "'" . $row->name.' ('.$row->founded_year.')' . "', ";
                    $script .= "'" . $row->link . "', ";
                    $script .= "'','" . $row->name.' ('.$row->founded_year.')' . "','','" . Uri::base() . "/components/" . $option . "/assets/img/standard2/" . $row->icon . "');" . "\n";
                }
            }
        }
        $script .= "document.write(d" . $root_catid . ");" . "\n";
        return $script;
    }

    /**
     * sportsmanagementModelClubInfo::sortCategoryListRecurse()
     *
     * @param  mixed $catid
     * @param  mixed $children
     * @param  mixed $catssorted
     * @return void
     */
    public static function sortCategoryListRecurse($catid, &$children, &$catssorted)
    {
        if (isset($children[$catid])) {
            foreach ($children[$catid] as $cat) {
                $catssorted[] = $cat;
                $this->sortCategoryListRecurse($cat->cid, $children, $catssorted);
            }
        }
    }

    /**
     * sportsmanagementModelClubInfo::sortCategoryList()
     *
     * @param  mixed $cats
     * @param  mixed $catssorted
     * @return
     */
    public static function sortCategoryList(&$cats, &$catssorted)
    {
        // First create a two dimensional array containing the child category objects
        // for each parent category id
        $children = array();
        foreach ($cats as $cat) {
            $pcid = $cat->new_club_id;
            $list = isset($children[$pcid]) ? $children[$pcid] : array();
            $list[] = $cat;
            $children[$pcid] = $list;
        }
        // Now resort the given $cats array with the help of the $children array
        $sortresult = self::sortCategoryListRecurse(0, $children, $catssorted);
        return $children;
    }

    /**
     * sportsmanagementModelClubInfo::getAddressString()
     *
     * @return
     */
    public static function getAddressString()
    {
        $club = self::getClub();
        if (!isset($club)) {
            return null;
        }
        $address_parts = array();
        if (!empty($club->address)) {
            $address_parts[] = $club->address;
        }
        if (!empty($club->state)) {
            $address_parts[] = $club->state;
        }
        if (!empty($club->location)) {
            if (!empty($club->zipcode)) {
                $address_parts[] = $club->zipcode . ' ' . $club->location;
            } else {
                $address_parts[] = $club->location;
            }
        }
        if (!empty($club->country)) {
            $address_parts[] = JSMCountries::getShortCountryName($club->country);
        }
        $address = implode(', ', $address_parts);
        return $address;
    }

    /**
     * sportsmanagementModelClubInfo::hasEditPermission()
     *
     * @param  mixed $task
     * @return
     */
    function hasEditPermission($task = null)
    {
        //check for ACL permsission and project admin/editor
        $allowed = parent::hasEditPermission($task);
        $user = Factory::getUser();
        if ($user->id > 0 && !$allowed) {
            // Check if user is the club admin
            $club = $this->getClub();
            if ($user->id == $club->admin) {
                $allowed = true;
            }
        }
        return $allowed;
    }
}
?>
