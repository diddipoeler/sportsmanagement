<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       player.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;


/**
 * sportsmanagementControllerplayer
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2019
 * @version   $Id$
 * @access    public
 */
class sportsmanagementControllerplayer extends JSMControllerForm
{

	/**
	 * sportsmanagementControllerplayer::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	function __construct($config = array())
	{
		parent::__construct($config);
	}
    
    
    
    /**
     * sportsmanagementControllerplayer::import()
     * 
     * @return void
     */
    function import()
    {
    //      $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$this->view_list), '');
    $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list.'&layout=players_upload', false));
    
        
        
    }

}
