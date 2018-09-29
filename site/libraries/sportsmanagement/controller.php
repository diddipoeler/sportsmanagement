<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      controller.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage libraries
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Controller\AdminController;

/**
 * JSMControllerAdmin
 *
 * @package
 * @author Dieter Plöger
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class JSMControllerAdmin extends AdminController
{

    /**
     * Constructor.
     *
     * @param    array An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     * @throws Exception
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->app = JFactory::getApplication();
        $this->jinput = $this->app->input;
        $this->option = $this->jinput->getCmd('option');

    }

     /**
      * JSMControllerAdmin::cancel()
      * 
      * @return void
      */
     function cancel()
	{
	$msg = '';
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);
	}

}


?>