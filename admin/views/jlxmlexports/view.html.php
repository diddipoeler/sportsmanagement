<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage jlxmlexports
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewJLXMLExports
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementViewJLXMLExports extends sportsmanagementView
{
    
	/**
	 * sportsmanagementViewJLXMLExports::init()
	 * 
	 * @return void
	 */
	function init()
	{


	}

    /**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_EXPORT_TITLE'),'generic.png');
        ToolbarHelper::custom('jlxmlexports.export', 'upload', 'upload', Text::_('JTOOLBAR_EXPORT'), false);
        parent::addToolbar();
        
        }
        
}
?>
