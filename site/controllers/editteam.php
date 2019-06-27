<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      editteam.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage editteam
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementControllereditteam
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2019
 * @version $Id$
 * @access public
 */
class sportsmanagementControllereditteam extends FormController {

    /**
     * Class Constructor
     *
     * @param	array	$config		An optional associative array of configuration settings.
     * @return	void
     * @since	1.5
     */
    function __construct($config = array()) {
        parent::__construct($config);

        /** Map the apply task to the save method. */
        $this->registerTask('apply', 'save');
    }

    
    /**
     * sportsmanagementControllereditteam::getModel()
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
     * sportsmanagementControllereditteam::submit()
     * 
     * @return
     */
    public function submit() {

        return true;
    }

   
    
    /**
     * sportsmanagementControllereditteam::save()
     * 
     * @param mixed $key
     * @param mixed $urlVar
     * @return
     */
    public function save($key = NULL, $urlVar = NULL) {
        /** Initialise variables. */
        $app = Factory::getApplication();
        $model = $this->getModel('editteam');
        $data = Factory::getApplication()->input->post->getArray(array());
        $id = Factory::getApplication()->input->getInt('id');

        /** Now update the loaded data to the database via a function in the model */
        $upditem = $model->updItem($data);

        /** Set the redirect based on the task. */
        switch ($this->getTask()) {
            case 'apply':
                $message = Text::_('COM_SPORTSMANAGEMENT_SAVE_SUCCESS');
                $this->setRedirect('index.php?option=com_sportsmanagement&view=editteam&tmpl=component&id='.$id.'&ptid='.$data['ptid'].'&p='.$data['p'].'&tid='.$data['tid'], $message);
                break;

            case 'save':
            default:
                $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
                break;
        }


        return true;
    }

        
        /**
         * sportsmanagementControllereditteam::cancel()
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
}

