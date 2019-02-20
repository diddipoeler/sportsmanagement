<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictiontemplate
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text; 
use Joomla\CMS\Factory; 
use Joomla\CMS\Form\Form;

/**
 * sportsmanagementViewPredictionTemplate
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementViewPredictionTemplate extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewPredictionTemplate::init()
	 * 
	 * @return
	 */
	public function init ()
	{

        $item = $this->get('Item');
		$this->item = $item;
        
		$templatepath = JPATH_COMPONENT_SITE.DS.'settings';
		$xmlfile = $templatepath.DS.'default'.DS.$item->template.'.xml';
       
		$form = Form::getInstance($item->template, $xmlfile,array('control'=> 'params'));
		$form->bind($item->params);
        // Assign the Data
		$this->form = $form;
        
		$script = $this->get('Script');
		$this->script = $script;
        
		$this->prediction_id = $this->app->getUserState( "$this->option.prediction_id", '0' );
		$this->predictionGame = $this->model->getPredictionGame( $this->prediction_id );

	}

	

  /**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
		
        $jinput = Factory::getApplication()->input;
        $jinput->set('hidemainmenu', true);
        $isNew = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_PREDICTIONTEMPLATE_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_PREDICTIONTEMPLATE_NEW');
        $this->icon = 'predtemplate';
        
        $this->item->name = $this->item->template;

        parent::addToolbar();

	}		
	
}
?>