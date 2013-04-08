<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// Include dependancy of the main model form
jimport('joomla.application.component.modelform');
// import Joomla modelitem library
jimport('joomla.application.component.modelitem');
// Include dependancy of the dispatcher
jimport('joomla.event.dispatcher');

/**
 * UpdHelloWorld Model
 */
class sportsmanagementModelUpdsportsmanagement extends JModelForm
{
	/**
	 * @var object item
	 */
	protected $item;

	/**
	 * Get the data for a new qualification
	 */
	public function getForm($data = array(), $loadData = true)
	{

        $app = JFactory::getApplication('site');

        // Get the form.
		$form = $this->loadForm('com_v.updhelloworld', 'updv', array('control' => 'jform', 'load_data' => true));
		if (empty($form)) {
			return false;
		}
		return $form;

	}

	/**
	 * Get the message
	 * @return object The message to be displayed to the user
	 */
	function &getItem()
	{

		if (!isset($this->_item))
		{
			$cache = JFactory::getCache('com_sportsmanagement', '');
			$id = $this->getState('sportsmanagement.id');
			$this->_item =  $cache->get($id);
			if ($this->_item === false) {

                // Menu parameters
                $menuitemid = JRequest::getInt( 'Itemid' );  // this returns the menu id number so you can reference parameters
                $menu = JSite::getMenu();
                if ($menuitemid) {
                   $menuparams = $menu->getParams( $menuitemid );
                   $headingtxtcolor = $menuparams->get('headingtxtcolor');  // This shows how to get an individual parameter for use
                   $headingbgcolor = $menuparams->get('headingbgcolor');  // This shows how to get an individual parameter for use
                }
                $this->setState('menuparams', $menuparams);  // this sets the parameter values to the state for later use
			}
		}
		return $this->_item;

	}

	public function updItem($data)
	{
        // set the variables from the passed data
        $id = $data['id'];
        $greeting = $data['greeting'];

        // set the data into a query to update the record
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
        $query->clear();
		$query->update(' #__sportsmanagement ');
		$query->set(' greeting = '.$db->Quote($greeting) );
		$query->where(' id = ' . (int) $id );

		$db->setQuery((string)$query);

        if (!$db->query()) {
            JError::raiseError(500, $db->getErrorMsg());
        	return false;
        } else {
        	return true;
		}
	}
}
