<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       predictionproject.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage controllers
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\FormController;

/**
 * sportsmanagementControllerpredictionproject
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerpredictionproject extends FormController
{

    /**
     * Method to store prediction project
     *
     * @access public
     * @return boolean    True on success
     */
    function store()
    {
        $post = Factory::getApplication()->input->post->getArray(array());
        // Check for request forgeries
        Session::checkToken() or jexit(\Text::_('JINVALID_TOKEN'));

        $model = $this->getModel();
         $msg = $model->save($post['jform']);
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $msg);
    }

}
?>
