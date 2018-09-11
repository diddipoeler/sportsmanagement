<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      editclub.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage editclubs
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementControllerEditClub
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementControllerEditClub extends FormController {

    /**
     * Class Constructor
     *
     * @param	array	$config		An optional associative array of configuration settings.
     * @return	void
     * @since	1.5
     */
    function __construct($config = array()) {
        parent::__construct($config);

        // Map the apply task to the save method.
        $this->registerTask('apply', 'save');
    }

    /**
     * sportsmanagementControllerEditClub::getModel()
     * 
     * @param string $name
     * @param string $prefix
     * @param mixed $config
     * @return
     */
    public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true)) {
        return parent::getModel($name, $prefix, array('ignore_request' => false));
    }

         /**
          * sportsmanagementControllerEditClub::cancel()
          * 
          * @param mixed $key
          * @return
          */
         public function cancel($key = NULL)
        {
            $msg = 'cancel';
            $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);
 
                return true;
        }
 
    /**
     * sportsmanagementControllerEditClub::load()
     * 
     * @return void
     */
    function load() {
        $cid = Factory::getApplication()->input->getInt('cid', 0);

        $club = & JTable::getInstance('Club', 'sportsmanagementTable');
        $club->load($cid);
        $club->checkout($user->id);

        $this->display();
    }

    
    /**
     * sportsmanagementControllerEditClub::display()
     * 
     * @param bool $cachable
     * @param mixed $urlparams
     * @return void
     */
    function display($cachable = false, $urlparams = Array() ) {
        /*
          switch($this->getTask())
          {
          case 'add'     :
          {
          Factory::getApplication()->input->setVar('hidemainmenu',0);
          Factory::getApplication()->input->setVar('layout','form');
          Factory::getApplication()->input->setVar('view','club');
          Factory::getApplication()->input->setVar('edit',false);

          // Checkout the club
          $model=$this->getModel('club');
          $model->checkout();
          } break;
          case 'edit'    :
          {
          Factory::getApplication()->input->setVar('hidemainmenu',0);
          Factory::getApplication()->input->setVar('layout','form');
          Factory::getApplication()->input->setVar('view','club');
          Factory::getApplication()->input->setVar('edit',true);

          // Checkout the club
          $model=$this->getModel('club');
          $model->checkout();
          } break;
          }
          parent::display();
         */
    }

   
    /**
     * sportsmanagementControllerEditClub::save()
     * 
     * @param mixed $key
     * @param mixed $urlVar
     * @return void
     */
    function save($key = NULL, $urlVar = NULL) {
        $app = Factory::getApplication();
        // Check for request forgeries
        JSession::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        $msg = '';
        $address_parts = array();
        $post = Factory::getApplication()->input->post->getArray(array());

        //$app->enqueueMessage(Text::_('post -> '.'<pre>'.print_r($post,true).'</pre>' ),'');

        $cid = Factory::getApplication()->input->getVar('cid', array(0), 'post', 'array');
        $post['id'] = (int) $cid[0];
        $model = $this->getModel('editclub');

        if (!empty($post['address'])) {
            $address_parts[] = $post['address'];
        }
        if (!empty($post['state'])) {
            $address_parts[] = $post['state'];
        }
        if (!empty($post['location'])) {
            if (!empty($post['zipcode'])) {
                $address_parts[] = $post['zipcode'] . ' ' . $post['location'];
            } else {
                $address_parts[] = $post['location'];
            }
        }
        if (!empty($post['country'])) {
            $address_parts[] = JSMCountries::getShortCountryName($post['country']);
        }
        $address = implode(', ', $address_parts);
        //$coords = $model->resolveLocation($address);
        $coords = sportsmanagementHelper::resolveLocation($address);

        //$app->enqueueMessage(Text::_('coords -> '.'<pre>'.print_r($coords,true).'</pre>' ),'');

        foreach ($coords as $key => $value) {
            $post['extended'][$key] = $value;
        }

        $post['latitude'] = $coords['latitude'];
        $post['longitude'] = $coords['longitude'];

        if (isset($post['merge_teams'])) {
            if (count($post['merge_teams']) > 0) {
                $temp = implode(",", $post['merge_teams']);
            } else {
                $temp = '';
            }
            $post['merge_teams'] = $temp;
        } else {
            $post['merge_teams'] = '';
        }

        //$app->enqueueMessage(Text::_('post -> '.'<pre>'.print_r($post,true).'</pre>' ),'');

        if ($model->updItem($post)) {
            $msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_CTRL_SAVED');
            $createTeam = Factory::getApplication()->input->getVar('createTeam');
            if ($createTeam) {
                $team_name = Factory::getApplication()->input->getVar('name');
                $team_short_name = strtoupper(substr(preg_replace('/[^a-zA-Z]/','', $team_name), 0, 3));
                $teammodel = $this->getModel('team');
                $tpost['id'] = "0";
                $tpost['name'] = $team_name;
                $tpost['short_name'] = $team_short_name;
                $tpost['club_id'] = $teammodel->getDbo()->insertid();
                $teammodel->save($tpost);
            }
            $type = 'message';
        } else {
            $msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_CTRL_ERROR_SAVE') . $model->getError();
            $type = 'error';
        }

        // Check the table in so it can be edited.... we are done with it anyway
        $model->checkin();


        if ($this->getTask() == 'save') {
            $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
        } else {
            $this->setRedirect('index.php?option=com_sportsmanagement&close='.Factory::getApplication()->input->getString('close', 0).'&tmpl=component&view=editclub&cid='.$post['id'].'&id='.$post['id'].'&p='.$post['p'], $msg, $type);
        }
    }

}

?>
