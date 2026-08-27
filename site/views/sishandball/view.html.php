<?php
/**
 * SportsManagement SIS handball legacy view for Joomla 5/6.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;

class sportsmanagementViewsishandball extends HtmlView
{
    public $sis_getxmldatei = 0;
    public $sis_getschiedsrichter = 1;
    public $sis_getspielort = 1;
    public $linkresults = '';
    public $params;
    public $paramscomponent;
    public $sis_art = '';
    public $vereinsnummer = '';
    public $liganummer = '';
    public $tabelle = null;
    public $spielplan = null;
    public $statistik = null;
    public $article;

    public function display($tpl = null): void
    {
        $app = Factory::getApplication();
        $params = $app->getParams();
        $paramsComponent = ComponentHelper::getParams('com_sportsmanagement');
        $model = $this->getModel();

        $xmlBaseUrl = (string) $paramsComponent->get('sis_xmllink', '');
        $clubNumber = (string) $paramsComponent->get('sis_meinevereinsnummer', '');
        $clubPassword = (string) $paramsComponent->get('sis_meinvereinspasswort', '');
        $leagueNumber = (string) $params->get('sis_liganummer', '');
        $requestedSisType = (string) $params->get('sis_art', '4');
        $sisType = in_array($requestedSisType, ['x', '1a'], true) ? '1' : $requestedSisType;

        $linkResults = $model->getLink(
            $clubNumber,
            $clubPassword,
            $leagueNumber,
            $sisType,
            $xmlBaseUrl
        );

        $this->sis_getxmldatei = (int) $params->get('sis_getxmldatei', 0);
        $this->sis_getschiedsrichter = (int) $params->get('sis_getschiedsrichter', 1);
        $this->sis_getspielort = (int) $params->get('sis_getspielort', 1);
        $this->linkresults = $linkResults;
        $this->params = $params;
        $this->paramscomponent = $paramsComponent;
        $this->sis_art = $sisType;
        $this->vereinsnummer = $clubNumber;
        $this->liganummer = $leagueNumber;
        $this->article = (object) ['title' => ''];

        if (in_array($sisType, ['4', '6', '7'], true)) {
            $this->tabelle = $model->getTabelle($linkResults, $leagueNumber, $sisType);
        }

        if (in_array($sisType, ['1', '2', '3', '10', '11'], true)) {
            $this->spielplan = $model->getSpielplan($linkResults, $leagueNumber, $sisType);
        }

        if (in_array($sisType, ['12', '12a'], true)) {
            $this->statistik = $model->getStatistik($linkResults, $leagueNumber);
        }

        parent::display($tpl);
    }
}
