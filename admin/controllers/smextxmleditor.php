<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 

class sportsmanagementControllersmextxmleditor extends JControllerForm
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
    
    public function cancel()
	{
    // Redirect to the list screen.
				$this->setRedirect(JRoute::_('index.php?option=com_sportsmanagement&view=smextxmleditors&layout=default', false));
    }
    
    /**
	 * Saves a template source file.
	 */
	public function save()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app		= JFactory::getApplication();
		$data		= JRequest::getVar('jform', array(), 'post', 'array');
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
				$this->setRedirect(JRoute::_('index.php?option=com_sportsmanagement&view=smextxmleditor&layout=default&file_name='.$data['filename'], false));
				break;

			default:
				// Clear the record id and data from the session.
				//$app->setUserState($context.'.id', null);
				//$app->setUserState($context.'.data', null);

				// Redirect to the list screen.
				$this->setRedirect(JRoute::_('index.php?option=com_sportsmanagement&view=smextxmleditors&layout=default', false));
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
