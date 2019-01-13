<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage updates
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

jimport('joomla.html.html.bootstrap');

/**
 * sportsmanagementViewUpdates
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewUpdates extends sportsmanagementView {

    /**
     * sportsmanagementViewUpdates::init()
     * 
     * @return void
     */
    public function init() {
        $this->app->setUserState($this->option . 'update_part', 0); // 0
        $filter_order = $this->app->getUserStateFromRequest($this->option . 'updates_filter_order', 'filter_order', 'dates', 'cmd');
        $filter_order_Dir = $this->app->getUserStateFromRequest($this->option . 'updates_filter_order_Dir', 'filter_order_Dir', '', 'word');



        $db = sportsmanagementHelper::getDBConnection();
        if (version_compare(JSM_JVERSION, '4', 'eq')) {
            $uri = JUri::getInstance();
        } else {
            $uri = Factory::getURI();
        }
        $model = $this->getModel();
        $versions = $model->getVersions();
        //$versionhistory=$model->getVersionHistory();
        $updateFiles = array();
        $lists = array();
        $updateFiles = $model->loadUpdateFiles();
        /*
          if($updateFiles=$model->loadUpdateFiles()) {
          for ($i=0, $n=count($updateFiles); $i < $n; $i++)
          {
          foreach ($versions as $version)
          {
          if (strpos($version->version,$updateFiles[$i]['file_name']))
          {
          $updateFiles[$i]['updateTime']=$version->date;
          break;
          }
          else
          {
          $updateFiles[$i]['updateTime']="-";
          }
          }
          }
          }
         */
        // table ordering
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order'] = $filter_order;
        //$this->assignRef('versionhistory',$versionhistory);
        $this->updateFiles = $updateFiles;
        $this->request_url = $uri->toString();
        $this->lists = $lists;




//        $this->addToolbar();
//		parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since	1.7
     */
    protected function addToolbar() {
        //// Get a refrence of the page instance in joomla
//        $document = Factory::getDocument();
//        // Set toolbar items for the page
//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//        $document->addCustomTag($stylelink);
        // Set toolbar items for the page
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_TITLE');
        $this->icon = 'updates';
//		sportsmanagementHelper::ToolbarButtonOnlineHelp();

        parent::addToolbar();
    }

}

?>
