<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage imagehandler
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');


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
		$app	= JFactory::getApplication();
		$document = JFactory::getDocument();
		$jinput = $app->input;
        $uri = JFactory::getURI();
        $tpl = '';


		if( $this->getLayout() == 'upload' || $this->getLayout() == 'upload_3' ) 
        {
			$this->_displayupload($tpl);
			return;
		}

		//get vars
		$type     	= JFactory::getApplication()->input->getVar( 'type' );
		$folder 	= ImageSelectSM::getfolder($type);
		$field 		= JFactory::getApplication()->input->getVar( 'field' );
		$fieldid 	= JFactory::getApplication()->input->getVar( 'fieldid' );
		$search 	= $app->getUserStateFromRequest( 'com_sportsmanagement.imageselect', 'search', '', 'string' );
		$search 	= trim(JString::strtolower( $search ) );
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' type -> '.$type.''),'Notice');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' folder -> '.$folder.''),'Notice');

		//add css
		//$version = urlencode(sportsmanagementHelper::getVersion());
		//$document->addStyleSheet('components/com_sportsmanagement/assets/css/imageselect.css?v='.$version);

		$jinput->set( 'folder', $folder );

		// Do not allow cache
		JResponse::allowCache(false);

		//get images
		$images 	= $this->get('Images');
		$pageNav 	= $this->get('Pagination');
        
        $this->request_url	= $uri->toString();

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
			JError::raiseNotice('SOME_ERROR_CODE', JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_NO_IMAGES'));
			$this->setLayout('upload');
			$this->form	= $this->get('form');
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
		$app	= JFactory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		//initialise variables
		$document	= JFactory::getDocument();
		$uri 		= JFactory::getURI();
		$params 	= JComponentHelper::getParams($option);
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
		$this->request_url	= $uri->toString();
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