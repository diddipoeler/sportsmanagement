<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       installhelper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementModelinstallhelper
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2021
 * @version $Id$
 * @access public
 */
class sportsmanagementModelinstallhelper extends JSMModelAdmin
{

	/**
	 * Override parent constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see   BaseDatabaseModel
	 * @since 3.2
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}
    
    
    /**
     * sportsmanagementModelinstallhelper::savesportstype()
     * 
     * @param mixed $post
     * @return void
     */
    function savesportstype($post = array() )
    {
    
    if ( !$post['filter_sports_type'] )
    {
    JSMModelAdmin::setWarning(Text::_('COM_SPORTSMANAGEMENT_ADMIN_INSTALLHELPER_ERROR_1'));
    //Factory::getApplication()->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' warning <pre>'.print_r(JSMModelAdmin::$_warnings,true).'</pre>' ), '');
    //  $this->setMessage($model->getError(), 'error');
    return JSMModelAdmin::$_warnings;    
    }   
    else
    {
        $this->jsmquery->clear();
        $profile = new stdClass;
		$profile->name = 'COM_SPORTSMANAGEMENT_ST_'.strtoupper($post['filter_sports_type']);
        
        $profile->modified         = $this->jsmdate->toSql();
		$profile->modified_by      = $this->jsmuser->get('id');
		$profile->checked_out      = 0;
		$profile->checked_out_time = $this->jsmdb->getNullDate();
        
		$insertresult = $this->jsmdb->insertObject('#__sportsmanagement_sports_type', $profile);
        
        
    return true;     
    } 
        
       
    }



}
