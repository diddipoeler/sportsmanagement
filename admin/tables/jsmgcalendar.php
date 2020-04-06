<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       jsmgcalendar.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage tables
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Filter\OutputFilter;
use Joomla\Registry\Registry;
use Joomla\CMS\Crypt\Crypt;

JLoader::import('joomla.database.table');


/**
 * sportsmanagementTablejsmGCalendar
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class  sportsmanagementTablejsmGCalendar extends JTable
{
    /**
     * sportsmanagementTablejsmGCalendar::__construct()
     *
     * @param  mixed $db
     * @return
     */
    function __construct(&$db)
    {
        parent::__construct('#__sportsmanagement_gcalendar', 'id', $db);
    }

    /**
     * sportsmanagementTablejsmGCalendar::bind()
     *
     * @param  mixed  $array
     * @param  string $ignore
     * @return
     */
    public function bind($array, $ignore = '')
    {
        if (isset($array['params']) && is_array($array['params'])) {
            // Convert the params field to a string.
            $parameter = new Registry;
            $parameter->loadArray($array['params']);
            $array['params'] = (string)$parameter;
        }

        return parent::bind($array, $ignore);
    }

    /**
     * sportsmanagementTablejsmGCalendar::load()
     *
     * @param  mixed $keys
     * @param  bool  $reset
     * @return
     */
    public function load($keys = null, $reset = true)
    {
        $result = parent::load($keys, $reset);

        if(isset($this->password) && !empty($this->password)) {
            $cryptor = new Crypt();
            $this->password = $cryptor->decrypt($this->password);
        }

        return $result;
    }

    /**
     * sportsmanagementTablejsmGCalendar::store()
     *
     * @param  bool $updateNulls
     * @return
     */
    public function store($updateNulls = false)
    {
        $oldPassword = $this->password;
        if(!empty($oldPassword)) {
            $cryptor = new Crypt();
            $this->password = $cryptor->encrypt($oldPassword);
        }
        $result = parent::store($updateNulls);

        $this->password = $oldPassword;

        return $result;
    }
}
