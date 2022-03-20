<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       ajax.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * sportsmanagementControllerAjax
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerAjax extends BaseController
{

	/**
	 * sportsmanagementControllerAjax::__construct()
	 *
	 * @return void
	 */
	public function __construct()
	{
		$document = Factory::getDocument();
		$document->setMimeEncoding('application/json');
		parent::__construct();
	}


	/**
	 * sportsmanagementControllerAjax::predictionid()
	 *
	 * @return void
	 */
	public function predictionid()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getpredictionid($jinput->getVar('cfg_which_database', '0'), $jinput->getVar('required', 'false')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::predictiongroup()
	 *
	 * @return void
	 */
	public function predictiongroups()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getpredictiongroups($jinput->getVar('prediction_id', '0'), $jinput->getVar('required', 'false')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::predictionpj()
	 *
	 * @return void
	 */
	public function predictionpj()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getpredictionpj($jinput->getVar('prediction_id', '0'), $jinput->getVar('required', 'false')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::locationzipcodeoptions()
	 *
	 * @return void
	 */
	public function locationzipcodeoptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getlocationzipcodeoptions(Factory::getApplication()->input->getVar('zipcode'), $jinput->getVar('required', 'false'), Factory::getApplication()->input->getInt('slug'), Factory::getApplication()->input->getInt('dbase'), Factory::getApplication()->input->getVar('country')));
		Factory::getApplication()->close();
	}


	/**
	 * sportsmanagementControllerAjax::countryleagueoptions()
	 * 
	 * @return void
	 */
	public function countryleagueoptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
        echo json_encode((array) $model->getcountryleagueoptions(Factory::getApplication()->input->getVar('search_nation'), $jinput->getVar('required', 'false'), Factory::getApplication()->input->getInt('slug'), Factory::getApplication()->input->getInt('dbase')));
		Factory::getApplication()->close();
        }
        
	/**
	 * sportsmanagementControllerAjax::countryzipcodeoptions()
	 *
	 * @return void
	 */
	public function countryzipcodeoptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getcountryzipcodeoptions(Factory::getApplication()->input->getVar('country'), $jinput->getVar('required', 'false'), Factory::getApplication()->input->getInt('slug'), Factory::getApplication()->input->getInt('dbase')));
		Factory::getApplication()->close();
	}


	/**
	 * sportsmanagementControllerAjax::personcontactid()
	 *
	 * @return void
	 */
	public function personcontactid()
	{
		$app    = Factory::getApplication();
		$model  = $this->getModel('ajax');
		$result = $model->getpersoncontactid($this->input->get->get('show_user_profile'));
		echo json_encode($result);

		// Echo new JResponseJson($result);
		// $this->input->get->get('menutype')
		// echo json_encode((array) $model->getProjects( $jinput->get->get('s'), $jinput->get->get('required'),$jinput->get->get('slug'),$jinput->get->get( 'dbase' ) ));

		$app->close();

	}

	/**
	 * sportsmanagementControllerAjax::projects()
	 *
	 * @return void
	 */
	public function projects()
	{
		$app = Factory::getApplication();

		// JInput object
		// $jinput = $app->input;
		// $menutype = $this->input->get->get('menutype');
		$model  = $this->getModel('ajax');
		$result = $model->getProjects($this->input->get->get('s'), $this->input->get->get('required'), $this->input->get->get('slug'), $this->input->get->get('dbase'));

		// Echo $result;
		echo json_encode($result);

		// Echo new JResponseJson($result);
		// $this->input->get->get('menutype')
		// echo json_encode((array) $model->getProjects( $jinput->get->get('s'), $jinput->get->get('required'),$jinput->get->get('slug'),$jinput->get->get( 'dbase' ) ));

		$app->close();
	}


	/**
	 * sportsmanagementControllerAjax::seasons()
	 *
	 * @return void
	 */
	public function seasons()
	{
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getseasons($jinput->getVar('cfg_which_database', '0'), $jinput->getVar('required', 'false'), $jinput->getVar('slug', 'false')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::personlistoptions()
	 *
	 * @return void
	 */
	public function personlistoptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getpersonlistoptions(Factory::getApplication()->input->getInt('person_art'), $jinput->getVar('required', 'false'), Factory::getApplication()->input->getInt('slug'), Factory::getApplication()->input->getInt('dbase')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::personpositionoptions()
	 *
	 * @return void
	 */
	public function personpositionoptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getpersonpositionoptions(Factory::getApplication()->input->getInt('sports_type_id'), $jinput->getVar('required', 'false'), Factory::getApplication()->input->getInt('slug'), Factory::getApplication()->input->getInt('dbase')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::personagegroupoptions()
	 *
	 * @return void
	 */
	public function personagegroupoptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getpersonagegroupoptions(Factory::getApplication()->input->getInt('sports_type_id'), $jinput->getVar('required', 'false'), Factory::getApplication()->input->getInt('slug'), Factory::getApplication()->input->getInt('dbase'), Factory::getApplication()->input->getInt('project')));
		Factory::getApplication()->close();
	}


	/**
	 * sportsmanagementControllerAjax::predictionmembersoptions()
	 *
	 * @return void
	 */
	public function predictionmembersoptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getpredictionmembersoptions(Factory::getApplication()->input->getInt('prediction_id'), $jinput->getVar('required', 'false'), $jinput->getVar('slug', 'false'), Factory::getApplication()->input->getInt('dbase')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::projectdivisionsoptions()
	 *
	 * @return
	 */
	public function projectdivisionsoptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getProjectDivisionsOptions($jinput->getVar('p', '0'), $jinput->getVar('required', 'false'), $jinput->getVar('slug', 'false'), Factory::getApplication()->input->getInt('dbase')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::projecteventsoptions()
	 *
	 * @return
	 */
	public function projecteventsoptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getProjectEventsOptions($jinput->getVar('p', '0'), $jinput->getVar('required', 'false'), $jinput->getVar('slug', 'false'), Factory::getApplication()->input->getInt('dbase')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::projectteamsbydivisionoptions()
	 *
	 * @return
	 */
	public function projectteamsbydivisionoptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getProjectTeamsByDivisionOptions($jinput->getVar('p', '0'), Factory::getApplication()->input->getInt('division'), $jinput->getVar('required', 'false'), $jinput->getVar('slug', 'false'), Factory::getApplication()->input->getInt('dbase')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::projectsbysportstypesoptions()
	 *
	 * @return
	 */
	public function projectsbysportstypesoptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getProjectsBySportsTypesOptions(Factory::getApplication()->input->getInt('sportstype'), $jinput->getVar('required', 'false'), $jinput->getVar('slug', 'false'), Factory::getApplication()->input->getInt('dbase')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::agegroupsbysportstypesoptions()
	 *
	 * @return
	 */
	public function agegroupsbysportstypesoptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getAgeGroupsBySportsTypesOptions(Factory::getApplication()->input->getInt('sportstype'), $jinput->getVar('required', 'false'), $jinput->getVar('slug', 'false'), Factory::getApplication()->input->getInt('dbase')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::projectsbycluboptions()
	 *
	 * @return
	 */
	public function projectsbycluboptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getProjectsByClubOptions(Factory::getApplication()->input->getInt('cid'), $jinput->getVar('required', 'false'), $jinput->getVar('slug', 'false'), Factory::getApplication()->input->getInt('dbase')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::projectteamsoptions()
	 *
	 * @return
	 */
	public function projectteamoptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getProjectTeamOptions($jinput->getVar('p', '0'), $jinput->getVar('required', 'false'), $jinput->getVar('slug', 'false'), Factory::getApplication()->input->getInt('dbase'),$jinput->getVar('club_id', '0') ));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::projectteamsptidoptions()
	 *
	 * @return
	 */
	public function projectteamsptidoptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getProjectTeamPtidOptions($jinput->getVar('p', '0'), $jinput->getVar('required', 'false'), $jinput->getVar('slug', 'false'), Factory::getApplication()->input->getInt('dbase')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::projectplayeroptions()
	 *
	 * @return
	 */
	public function projectplayeroptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getProjectPlayerOptions($jinput->getVar('p', '0'), $jinput->getVar('required', 'false'), $jinput->getVar('slug', 'false'), Factory::getApplication()->input->getInt('dbase')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::projectstaffoptions()
	 *
	 * @return
	 */
	public function projectstaffoptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getProjectStaffOptions($jinput->getVar('p', '0'), $jinput->getVar('required', 'false'), $jinput->getVar('slug', 'false'), Factory::getApplication()->input->getInt('dbase')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::projectclubsoptions()
	 *
	 * @return
	 */
	public function projectcluboptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getProjectClubOptions($jinput->getVar('p', '0'), $jinput->getVar('required', 'false'), $jinput->getVar('slug', 'false'), Factory::getApplication()->input->getInt('dbase')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::projectstatsoptions()
	 *
	 * @return
	 */
	public function projectstatsoptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getProjectStatOptions($jinput->getVar('p', '0'), $jinput->getVar('required', 'false'), $jinput->getVar('slug', 'false'), Factory::getApplication()->input->getInt('dbase')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::matchesoptions()
	 *
	 * @return
	 */
	public function matchesoptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getMatchesOptions($jinput->getVar('p', '0'), $jinput->getVar('required', 'false'), $jinput->getVar('slug', 'false'), Factory::getApplication()->input->getInt('dbase')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::refereesoptions()
	 *
	 * @return
	 */
	public function refereesoptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getRefereesOptions($jinput->getVar('p', '0'), $jinput->getVar('required', 'false'), $jinput->getVar('slug', 'false'), Factory::getApplication()->input->getInt('dbase')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::roundsoptions()
	 *
	 * @return
	 */
	public function projectroundoptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getProjectRoundOptions($jinput->getVar('p', '0'), $jinput->getVar('required', 'false'), $jinput->getVar('slug', 'false'), 'ASC', null, Factory::getApplication()->input->getInt('dbase')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::projecttreenodeoptions()
	 *
	 * @return
	 */
	public function projecttreenodeoptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getProjectTreenodeOptions($jinput->getVar('p', '0'), $jinput->getVar('required', 'false'), $jinput->getVar('slug', 'false'), Factory::getApplication()->input->getInt('dbase')));
		Factory::getApplication()->close();
	}

	/**
	 * sportsmanagementControllerAjax::sportstypesoptions()
	 *
	 * @return
	 */
	public function sportstypesoptions()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$model  = $this->getModel('ajax');
		echo json_encode((array) $model->getsportstypes($jinput->getVar('cfg_which_database', '0'), $jinput->getVar('required', 'false'), $jinput->getVar('slug', 'false')));
		Factory::getApplication()->close();
	}

}

