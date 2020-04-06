<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       treetos.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage controllers
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Session\Session; 
use Joomla\Utilities\ArrayHelper; 
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * sportsmanagementControllertreetos
 * 
 * @package 
 * @author    Dieter Plöger
 * @copyright 2016
 * @version   $Id$
 * @access    public
 */
class sportsmanagementControllertreetos extends JSMControllerAdmin
{

    /**
 * sportsmanagementControllertreetos::__construct()
 * 
 * @param  mixed $config
 * @return void
 */
    public function __construct($config = array())
    {
          parent::__construct($config);
        // Reference global application object
        $this->jsmapp = Factory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option');
    }
      
    /**
     * Proxy for getModel.
     *
     * @since 1.6
     */
    public function getModel($name = 'treeto', $prefix = 'sportsmanagementModel', $config = Array() ) 
    {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }
    
    /**
 * sportsmanagementControllertreetos::genNode()
 * 
 * @return void
 */
    public function genNode()
    {
          $id = $this->jsmjinput->get->get('id');
          $this->setRedirect('index.php?option=com_sportsmanagement&view=treeto&layout=gennode&id=' . $id);
    }
        
    /**
 * sportsmanagementControllertreetos::save()
 * 
 * @return void
 */
    public function save()
    {
          // Check for token
          Session::checkToken() or jexit(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN'));
          $cid = $this->jsmjinput->get('cid', array(), 'array');
          ArrayHelper::toInteger($cid);
        
          $post = $this->jsmjinput->post->getArray();
          $data['project_id'] = $post['project_id'];
        
        $model = $this->getModel('treeto');
        $row = $model->getTable();
        
        if($row->save($data)) {
            $msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETO_CTRL_SAVED');
        }
        else
          {
            $msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETO_CTRL_ERROR_SAVED') . $model->getError();
        }
    
          $task = $this->getTask();
        
        if($task == 'save') {
            $link = 'index.php?option=com_sportsmanagement&view=treetos';
        }
        else
          {
            $link = 'index.php?option=com_sportsmanagement&task=treeto.edit&id=' . $post['id'];
        }
          $this->setRedirect($link, $msg);
    }    
    
    
    
    
    
}
