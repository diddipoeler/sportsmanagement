<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       league.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelleague
 * 
 * @package 
 * @author    Dieter Plöger
 * @copyright 2016
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelleague extends JSMModelAdmin
{
    
    /**
     * Override parent constructor.
     *
     * @param array $config An optional associative array of configuration settings.
     *
     * @see   BaseDatabaseModel
     * @since 3.2
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }       
    
    /**
     * Method to update checked leagues
     *
     * @access public
     * @return boolean    True on success
     */
    function saveshort()
    {
        // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Get the input
        $pks = $jinput->getVar('cid', null, 'post', 'array');
        $post = Factory::getApplication()->input->post->getArray(array());
        $result = true;
        for ($x=0; $x < count($pks); $x++)
        {
            $tblLeague = & $this->getTable();
            $tblLeague->id    = $pks[$x];
            $tblLeague->associations = $post['association' . $pks[$x]];
            $tblLeague->country = $post['country' . $pks[$x]];
            $tblLeague->agegroup_id = $post['agegroup'.$pks[$x]];
            $tblLeague->published_act_season = $post['published_act_season'.$pks[$x]];
            if(!$tblLeague->store()) {
                $result = false;
            }
        }
        return $result;
    }






        
}
