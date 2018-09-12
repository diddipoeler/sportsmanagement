<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      editprojectteam.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage editprojectteam
 */

// No direct access.
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Language\Text;


class sportsmanagementControllereditprojectteam extends FormController {

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

    
    public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true)) {
        return parent::getModel($name, $prefix, array('ignore_request' => false));
    }

    
    public function submit() {


        return true;
    }

   
    
    public function save($key = NULL, $urlVar = NULL) {
        // Initialise variables.
        $app = Factory::getApplication();
        $model = $this->getModel('editprojectteam');

        //$data	= Factory::getApplication()->input->getVar('jform', array(), 'post', 'array');
        $data = Factory::getApplication()->input->post->getArray(array());
        $id = Factory::getApplication()->input->getInt('id');

        // Now update the loaded data to the database via a function in the model
        $upditem = $model->updItem($data);

        // Set the redirect based on the task.
        switch ($this->getTask()) {
            case 'apply':
                $message = Text::_('COM_SPORTSMANAGEMENT_SAVE_SUCCESS');
                $this->setRedirect('index.php?option=com_sportsmanagement&view=editprojectteam&tmpl=component&id='.$id.'&ptid='.$data['ptid'].'&p='.$data['p'].'&tid='.$data['tid'], $message);
                break;

            case 'save':
            default:
                $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
                break;
        }


        return true;
    }

        
        public function cancel($key = NULL)
        {
            $msg = 'cancel';
            $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);

 
                return true;
        }
}

