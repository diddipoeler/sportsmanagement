<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.pdf.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage predictionresults
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;
jimport('joomla.application.component.view');
jimport( 'joomla.filesystem.file' );

require_once(JPATH_COMPONENT .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'pagination.php');

/**
 * Joomleague Component prediction View
 *
 * @author	Kurt Norgaz
 * @package	JoomLeague
 * @since	1.5.100627
 */
class JoomleagueViewPredictionResults extends JViewLegacy
{
	function display($tpl=null)
	{
		// Get a refrence of the page instance in joomla
		$document	=& Factory::getDocument();
		$model		=& $this->getModel();
    $option = Factory::getApplication()->input->getCmd('option');
    $optiontext = strtoupper(Factory::getApplication()->input->getCmd('option').'_');
    $this->optiontext = $optiontext;
    
		$app = Factory::getApplication();

		$this->predictionGame = $model->getPredictionGame();

		if (isset($this->predictionGame))
		{
			$config	= $model->getPredictionTemplateConfig($this->getName());
			$configavatar = $model->getPredictionTemplateConfig('predictionusers');
			$configentry = $model->getPredictionTemplateConfig('predictionentry');
			$config = array_merge($configentry,$config);
			$overallConfig = $model->getPredictionOverallConfig();
     
			$this->model = $model;
			$this->roundID = $this->model->roundID;
			$this->config = array_merge($overallConfig,$config);
			$this->configavatar = $configavatar;
			$this->predictionMember = $model->getPredictionMember($configavatar);
			$this->predictionProjectS = $model->getPredictionProjectS();
			$this->actJoomlaUser = Factory::getUser();

      $predictionRounds[] = HTMLHelper::_('select.option','0',Text::_('COM_SPORTSMANAGEMENT_PRED_SELECT_ROUNDS'),'value','text');
      if ( $res = &$model->getRoundNames($this->predictionGame->id) ){$predictionRounds = array_merge($predictionRounds,$res);}
			$lists['predictionRounds']=HTMLHelper::_('select.genericList',$predictionRounds,'r','class="inputbox" onchange="this.form.submit(); "','value','text',$this->model->roundID);
			unset($res);
			unset($predictionRounds);
			
			$this->lists = $lists;
			$this->show_debug_info = ComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info',0);
			// Set page title
			$pageTitle = Text::_('COM_SPORTSMANAGEMENT_PRED_RESULTS_TITLE');

			$document->setTitle($pageTitle);

			parent::display($tpl);
		}
		else
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING'), Log::INFO, 'jsmerror');
		}
	}

}
?>
