<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage imagehandler
 * https://www.jqueryscript.net/form/Drag-Drop-File-Upload-Dialog-with-jQuery-Bootstrap.html
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\String\StringHelper;
use Joomla\CMS\Application\WebApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementViewImagehandler
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewImagehandler extends sportsmanagementView  
{

	
    /**
     * sportsmanagementViewImagehandler::init()
     * 
     * @return
     */
    public function init ()
	{
		$app	= Factory::getApplication();
		$document = Factory::getDocument();
		$jinput = $app->input;
        $tpl = '';

switch ( $this->getLayout() )
{
case 'upload':
case 'upload_3':
case 'upload_4':
$this->_displayupload($tpl);
return;		
break;
case 'uploaddraganddrop':
case 'uploaddraganddrop_3':
case 'uploaddraganddrop_4':	
$this->folder = ImageSelectSM::getfolder($this->jinput->get( 'type' ));
$this->setLayout('uploaddraganddrop');		
return;		
break;		
}


		//get vars
		$type     	= Factory::getApplication()->input->getVar( 'type' );
		$folder 	= ImageSelectSM::getfolder($type);
		$field 		= Factory::getApplication()->input->getVar( 'field' );
		$fieldid 	= Factory::getApplication()->input->getVar( 'fieldid' );
		$search 	= $app->getUserStateFromRequest( 'com_sportsmanagement.imageselect', 'search', '', 'string' );
		$search 	= trim(StringHelper::strtolower( $search ) );
        
		$jinput->set( 'folder', $folder );

		// Do not allow cache
		//WebApplication::allowCache(false);

		//get images
		$images 	= $this->get('Images');
		$pageNav 	= $this->get('Pagination');
        
       // $this->request_url	= $uri->toString();

		if (count($images) > 0 || $search) {
			$this->images	= $images;
			$this->type	= $type;
			$this->folder	= $folder;
			$this->search	= $search;
			$this->state	= $this->get('state');
			$this->pageNav	= $pageNav;
			$this->field	= $field;
			$this->fieldid	= $fieldid;
			//$this->assign('form'      	, $this->get('form'));
			//parent::display($tpl);
		} else {
			//no images in the folder, redirect to uploadscreen and raise notice
$this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_NO_IMAGES'),'error');			
			$this->setLayout('upload');
			$this->form = $this->get('form');
			$this->_displayupload($tpl);
			return;
		}
	}

	/**
	 * sportsmanagementViewImagehandler::setImage()
	 * 
	 * @param integer $index
	 * @return void
	 */
	function setImage($index = 0)
	{
		if (isset($this->images[$index]))
		{
			$this->_tmp_img = &$this->images[$index];
		} else {
			$this->_tmp_img = new JObject;
		}
	}

	/**
	 * Prepares the upload image screen
	 *
	 * @param $tpl
	 *
	 * @since 0.9
	 */
	function _displayupload($tpl = null)
	{
		$app	= Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		//initialise variables
		$document	= Factory::getDocument();
		//$uri 		= Factory::getURI();
		$params 	= ComponentHelper::getParams($option);
		$type     	= $jinput->get( 'type' );
		$folder 	= ImageSelectSM::getfolder($type);
		$field  	= $jinput->get( 'field' );
		$fieldid  	= $jinput->get( 'fieldid' );
		$menu 		= $jinput->set( 'hidemainmenu', 1 );
		//get vars
		$task 		= $jinput->get( 'task' );

		jimport('joomla.client.helper');
		$ftp = JClientHelper::setCredentialsFromRequest('ftp');

		//assign data to template
		$this->params	= $params;
		//$this->request_url	= $uri->toString();
		$this->ftp	= $ftp;
		$this->folder	= $folder;
		$this->field	= $field;
		$this->fieldid	= $fieldid;
		$this->menu	= $menu;
        $this->setLayout('upload');
		//parent::display($tpl);
	}
}
?>
