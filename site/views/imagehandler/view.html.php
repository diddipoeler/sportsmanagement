<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage imagehandler
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

class sportsmanagementViewImagehandler extends JViewLegacy {

    /**
     * Image selection List
     *
     * @since 0.9
     */
    function display($tpl = null) {
        $app = JFactory::getApplication();
        $document = JFactory::getDocument();
        if (version_compare(JSM_JVERSION, '4', 'eq')) {
            $uri = JUri::getInstance();
        } else {
            $uri = JFactory::getURI();
        }


        if ($this->getLayout() == 'upload') {
            $this->_displayupload($tpl);
            return;
        }

        //get vars
        $type = JFactory::getApplication()->input->getVar('type');
        $folder = ImageSelectSM::getfolder($type);
        $field = JFactory::getApplication()->input->getVar('field');
        $fieldid = JFactory::getApplication()->input->getVar('fieldid');
        $search = $app->getUserStateFromRequest('com_sportsmanagement.imageselect', 'search', '', 'string');
        $search = trim(JString::strtolower($search));

        //add css
        //$version = urlencode(sportsmanagementHelper::getVersion());
        //$document->addStyleSheet('components/com_sportsmanagement/assets/css/imageselect.css?v='.$version);

        JFactory::getApplication()->input->setVar('folder', $folder);

        // Do not allow cache
        JResponse::allowCache(false);

        //get images
        $images = $this->get('Images');
        $pageNav = $this->get('Pagination');

        $this->request_url = $uri->toString();

        if (count($images) > 0 || $search) {
            $this->images = $images;
            $this->type = $type;
            $this->folder = $folder;
            $this->search = $search;
            $this->state = $this->get('state');
            $this->pageNav = $pageNav;
            $this->field = $field;
            $this->fieldid = $fieldid;
            parent::display($tpl);
        } else {
            //no images in the folder, redirect to uploadscreen and raise notice
            JError::raiseNotice('SOME_ERROR_CODE', JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_NO_IMAGES'));
            $this->setLayout('upload');
            $this->form = $this->get('form');
            $this->_displayupload($tpl);
            return;
        }
    }

    function setImage($index = 0) {
        if (isset($this->images[$index])) {
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
    function _displayupload($tpl = null) {
        $option = JFactory::getApplication()->input->getCmd('option');
        $app = JFactory::getApplication();

        //initialise variables
        $document = JFactory::getDocument();
        $uri = JFactory::getURI();
        $params = JComponentHelper::getParams($option);
        $type = JFactory::getApplication()->input->getVar('type');
        $folder = ImageSelectSM::getfolder($type);
        $field = JFactory::getApplication()->input->getVar('field');
        $fieldid = JFactory::getApplication()->input->getVar('fieldid');
        $menu = JFactory::getApplication()->input->setVar('hidemainmenu', 1);
        //get vars
        $task = JFactory::getApplication()->input->getVar('task');

        jimport('joomla.client.helper');
        $ftp = JClientHelper::setCredentialsFromRequest('ftp');

        //assign data to template
        $this->params = $params;
        $this->request_url = $uri->toString();
        $this->ftp = $ftp;
        $this->folder = $folder;
        $this->field = $field;
        $this->fieldid = $fieldid;
        $this->menu = $menu;
        parent::display($tpl);
    }

}

?>