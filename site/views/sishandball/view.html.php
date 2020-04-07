<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage sishandball
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

jimport('joomla.application.component.view');

/**
 * sportsmanagementViewsishandball
 *
 * @package
 * @author    diddi
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewsishandball extends JViewLegacy
{



	/**
	 * sportsmanagementViewsishandball::display()
	 *
	 * @param   mixed $tpl
	 * @return void
	 */
	function display($tpl = null)
	{
		// Get a refrence of the page instance in joomla
		$document    = Factory::getDocument();

			  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$params = $app->getParams();
		$paramscomponent = ComponentHelper::getParams($option);
		$model = $this->getModel();

		// $model = &$this->getModelitem();

			  /*
        $vereinsnummer = $params->get( 'sis_meinevereinsnummer');
        $vereinspasswort = $params->get( 'sis_meinvereinspasswort');
        $sis_xmllink = $params->get( 'sis_xmllink');
        */

			  $sis_xmllink    = $paramscomponent->get('sis_xmllink');
		$vereinsnummer    = $paramscomponent->get('sis_meinevereinsnummer');
		$vereinspasswort    = $paramscomponent->get('sis_meinvereinspasswort');

			  $liganummer = $params->get('sis_liganummer');
		$sis_art = $params->get('sis_art');

		if ($sis_art == "x" || $sis_art == "1a")
		{
			$sis_art = 1;
		}

				  $sis_getxmldatei = $params->get('sis_getxmldatei');

				// $sis_getschiedsrichter = $params->get( 'sis_getschiedsrichter');
				// $sis_getspielort = $params->get( 'sis_getspielort');

				$linkresults = $model->getLink($vereinsnummer, $vereinspasswort, $liganummer, $sis_art, $sis_xmllink);

				$this->sis_getxmldatei = $sis_getxmldatei;
				$this->sis_getschiedsrichter = $sis_getschiedsrichter;
				$this->sis_getspielort = $sis_getspielort;
				$this->linkresults = $linkresults;
				$this->params = $params;
				$this->paramscomponent = $paramscomponent;
				$this->sis_art = $sis_art;
				$this->vereinsnummer = $vereinsnummer;
				$this->liganummer = $liganummer;

			  // Tabellenanzeige ist gewünscht
		if ($sis_art == 4 || $sis_art == 6 || $sis_art == 7)
		{
			$tabelle = $model->getTabelle($linkresults, $liganummer, $sis_art);
			$this->tabelle = $tabelle;
		}

				// Spielplan ist gewünscht
		if ($sis_art == 1 || $sis_art == "1a" || $sis_art == 2 || $sis_art == 3 || $sis_art == 10 || $sis_art == 11 || $sis_art == "x")
		{
			$spielplan = $model->getSpielplan($linkresults, $liganummer, $sis_art);
			$this->spielplan = $spielplan;
		}

				// Statistikanzeige ist gewünscht
		if ($sis_art == 12 || $sis_art == "12a")
		{
			$statistik = $model->getStatistik($linkresults, $liganummer);
			$this->statistik = $statistik;
		}

				parent::display($tpl);
	}
}
