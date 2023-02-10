<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage statistic
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewstatistic
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewstatistic extends sportsmanagementView
{

	/**
	 * sportsmanagementViewstatistic::init()
	 *
	 * @return
	 */
	public function init()
	{
		//$app               = Factory::getApplication();
		$this->description = '';

//		// Get the Data
//		$form   = $this->get('Form');
//		$item   = $this->get('Item');
//		$script = $this->get('Script');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			Log::add(implode('<br />', $errors));

			return false;
		}

//		// Assign the Data
//		$this->form   = $form;
//		$this->item   = $item;
//		$this->script = $script;

		$isNew = $this->item->id == 0;

		if ($isNew)
		{
			$this->item->class = 'basic';
            $this->item->calculated = 0;
            
            $this->form->setValue('class', null, 'basic');
            $this->form->setValue('calculated', null, 0);
            
		}

		if ($this->getLayout() == 'edit' || $this->getLayout() == 'edit_3')
		{
			// $this->setLayout('edit');
		}

		$this->formparams = sportsmanagementHelper::getExtendedStatistic($this->item->params, $this->item->class);


//$this->app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' <pre>' . print_r($this->item,true).'</pre>', 'error');

	}


	/**
	 * sportsmanagementViewstatistic::addToolBar()
	 *
	 * @return void
	 */
	protected function addToolBar()
	{

		Factory::getApplication()->input->set('hidemainmenu', true);

		$isNew      = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_STATISTIC_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_STATISTIC_NEW');
		$this->icon = 'statistic';

		parent::addToolbar();
	}

}
