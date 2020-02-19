<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      smextxmleditor.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage controllers
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text; 
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\Controller\FormController;
 

/**
 * sportsmanagementControllersmextxmleditor
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllersmextxmleditor extends FormController
{
    
    /**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Apply, Save & New, and Save As copy should be standard on forms.
		$this->registerTask('apply',		'save');
	}
    
    /**
     * sportsmanagementControllersmextxmleditor::cancel()
     * 
     * @return void
     */
    public function cancel()
	{
    // Redirect to the list screen.
				$this->setRedirect(Route::_('index.php?option=com_sportsmanagement&view=smextxmleditors&layout=default', false));
    }
    
    /**
	 * Saves a template source file.
	 */
	public function save()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app		= Factory::getApplication();
		//$data		= Factory::getApplication()->input->getVar('jform', array(), 'post', 'array');
		$data  = Factory::getApplication()->input->post->get('jform', array(), 'array');
		//$context	= 'com_templates.edit.source';
		$task		= $this->getTask();
		$model		= $this->getModel();
        $model->save($data);
        
        switch ($task)
		{
			case 'apply':
				// Reset the record data in the session.
				//$app->setUserState($context.'.data',	null);

				// Redirect back to the edit screen.
				$this->setRedirect(Route::_('index.php?option=com_sportsmanagement&view=smextxmleditor&layout=default&file_name='.$data['filename'], false));
				break;

			default:
				// Clear the record id and data from the session.
				//$app->setUserState($context.'.id', null);
				//$app->setUserState($context.'.data', null);

				// Redirect to the list screen.
				$this->setRedirect(Route::_('index.php?option=com_sportsmanagement&view=smextxmleditors&layout=default', false));
				break;
		}
        
        
        
    } 
    
    /**
	 * Method to get a model object, loading it if required.
	 *
	 * @param	string	The model name. Optional.
	 * @param	string	The class prefix. Optional.
	 * @param	array	Configuration array for model. Optional (note, the empty array is atypical compared to other models).
	 *
	 * @return	object	The model.
	 */
	public function getModel($name = 'smextxmleditor', $prefix = 'sportsmanagementModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}   



}
