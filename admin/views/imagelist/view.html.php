<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage imagelist
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * https://www.jqueryscript.net/form/Drag-Drop-File-Upload-Dialog-with-jQuery-Bootstrap.html
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\String\StringHelper;
use Joomla\CMS\Application\WebApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementViewimagelist
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewimagelist extends sportsmanagementView
{


	/**
	 * sportsmanagementViewImagehandler::init()
	 *
	 * @return
	 */
	public function init()
	{
	Factory::getLanguage()->load('com_media', JPATH_ADMINISTRATOR);   
    // Include jQuery
//		JHtml::_('jquery.framework');
//		JHtml::_('script', 'media/popup-imagemanager.js', true, true);
//		JHtml::_('stylesheet', 'media/popup-imagemanager.css', array(), true);
       $lang = JFactory::getLanguage();

		//JHtml::_('stylesheet', 'media/popup-imagelist.css', array(), true);

		if ($lang->isRtl())
		{
		//	JHtml::_('stylesheet', 'media/popup-imagelist_rtl.css', array(), true);
		}

//		$document = JFactory::getDocument();
//		$document->addScriptDeclaration("var ImageManager = window.parent.ImageManager;");
       
   $data = Factory::getApplication()->input->getArray();
//      echo '<pre>'.print_r($data,true).'</pre>';
$this->folder = $data['folder'];
$this->images = sportsmanagementModelimagelist::getFiles($data['folder'].$data['pid'],'');    
		
		
		
		
  }
  
/**
	 * Set the active image
	 *
	 * @param   integer  $index  Image position
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setImage($index = 0)
	{
		if (isset($this->images[$index]))
		{
			$this->_tmp_img = &$this->images[$index];
		}
		else
		{
			$this->_tmp_img = new JObject;
		}
	}
	
  }
