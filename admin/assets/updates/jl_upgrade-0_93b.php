<?php
// ToDo:
// - Remove old checks for already existing records in different functions as it was done with matches table
// - check ranking class changes in tables or templates etc...
// no direct access
defined('_JEXEC') or die('Restricted access');

$version			= '2.0.43.b3fd04d-a';
$updateFileDate		= '2012-09-13';
$updateFileTime		= '00:05';
$updatefilename		= 'jl_upgrade-0_93b';
$lastVersion		= '0.93b';
$JLtablePrefix		= 'joomleague';
$updateDescription	= '<span style="color:orange">Perform an update of existing old JL 0.93b tables inside the database to work with latest JoomLeague</span>';
$excludeFile		= 'false';

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
$maxImportTime=JComponentHelper::getParams('com_joomleague')->get('max_import_time',0);
if (empty($maxImportTime))
{
	$maxImportTime=480;
}
if ((int)ini_get('max_execution_time') < $maxImportTime){@set_time_limit($maxImportTime);}

$maxImportMemory=JComponentHelper::getParams('com_joomleague')->get('max_import_memory',0);
if (empty($maxImportMemory))
{
	$maxImportMemory='150M';
}
if ((int)ini_get('memory_limit') < (int)$maxImportMemory){ini_set('memory_limit',$maxImportMemory);}

$updates=array();
$updates2=array();
$updates3=array();

if (0 ==0)
{
	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_project'][0]['tablename']='#__joomleague_project';

		$updates['joomleague_project'][1]['action']="ALTER TABLE";
		$updates['joomleague_project'][1]['job']="ADD";
		$updates['joomleague_project'][1]['field']="tmp_old_pid";
		$updates['joomleague_project'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_project'][1]['after']="checked_out_time";
	}

	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_club'][0]['tablename']='#__joomleague_club';

		$updates['joomleague_club'][1]['action']="ALTER TABLE";
		$updates['joomleague_club'][1]['job']="ADD";
		$updates['joomleague_club'][1]['field']="tmp_old_id";
		$updates['joomleague_club'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_club'][1]['after']="checked_out_time";
	}

	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_playground'][0]['tablename']='#__joomleague_playground';

		$updates['joomleague_playground'][1]['action']="ALTER TABLE";
		$updates['joomleague_playground'][1]['job']="ADD";
		$updates['joomleague_playground'][1]['field']="tmp_old_id";
		$updates['joomleague_playground'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_playground'][1]['after']="checked_out_time";
	}

	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_season'][0]['tablename']='#__joomleague_season';

		$updates['joomleague_season'][1]['action']="ALTER TABLE";
		$updates['joomleague_season'][1]['job']="ADD";
		$updates['joomleague_season'][1]['field']="tmp_old_id";
		$updates['joomleague_season'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_season'][1]['after']="checked_out_time";
	}

	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_league'][0]['tablename']='#__joomleague_league';

		$updates['joomleague_league'][1]['action']="ALTER TABLE";
		$updates['joomleague_league'][1]['job']="ADD";
		$updates['joomleague_league'][1]['field']="tmp_old_id";
		$updates['joomleague_league'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_league'][1]['after']="checked_out_time";
	}

	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_person'][0]['tablename']='#__joomleague_person';

		$updates['joomleague_person'][1]['action']="ALTER TABLE";
		$updates['joomleague_person'][1]['job']="ADD";
		$updates['joomleague_person'][1]['field']="tmp_old_player_id";
		$updates['joomleague_person'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_person'][1]['after']="checked_out_time";

		$updates['joomleague_person'][2]['action']="ALTER TABLE";
		$updates['joomleague_person'][2]['job']="ADD";
		$updates['joomleague_person'][2]['field']="tmp_old_referee_id";
		$updates['joomleague_person'][2]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_person'][2]['after']="tmp_old_player_id";
	}

	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_project_team'][0]['tablename']='#__joomleague_project_team';

		$updates['joomleague_project_team'][1]['action']="ALTER TABLE";
		$updates['joomleague_project_team'][1]['job']="ADD";
		$updates['joomleague_project_team'][1]['field']="tmp_old_teamtool_id";
		$updates['joomleague_project_team'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_project_team'][1]['after']="checked_out_time";

		$updates['joomleague_project_team'][2]['action']="ALTER TABLE";
		$updates['joomleague_project_team'][2]['job']="ADD";
		$updates['joomleague_project_team'][2]['field']="reason";
		$updates['joomleague_project_team'][2]['values']="VARCHAR(150) NOT NULL";
		$updates['joomleague_project_team'][2]['after']="standard_playground";

	}

	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_team'][0]['tablename']='#__joomleague_team';

		$updates['joomleague_team'][1]['action']="ALTER TABLE";
		$updates['joomleague_team'][1]['job']="ADD";
		$updates['joomleague_team'][1]['field']="tmp_old_team_id";
		$updates['joomleague_team'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_team'][1]['after']="checked_out_time";
	}

	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_division'][0]['tablename']='#__joomleague_division';

		$updates['joomleague_division'][1]['action']="ALTER TABLE";
		$updates['joomleague_division'][1]['job']="ADD";
		$updates['joomleague_division'][1]['field']="tmp_old_division_id";
		$updates['joomleague_division'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_division'][1]['after']="checked_out_time";
	}

	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_eventtype'][0]['tablename']='#__joomleague_eventtype';

		$updates['joomleague_eventtype'][1]['action']="ALTER TABLE";
		$updates['joomleague_eventtype'][1]['job']="ADD";
		$updates['joomleague_eventtype'][1]['field']="tmp_old_id";
		$updates['joomleague_eventtype'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_eventtype'][1]['after']="checked_out_time";

	}

	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_position'][0]['tablename']='#__joomleague_position';

		$updates['joomleague_position'][1]['action']="ALTER TABLE";
		$updates['joomleague_position'][1]['job']="ADD";
		$updates['joomleague_position'][1]['field']="tmp_old_pos_id";
		$updates['joomleague_position'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_position'][1]['after']="checked_out_time";
	}

	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_round'][0]['tablename']='#__joomleague_round';

		$updates['joomleague_round'][1]['action']="ALTER TABLE";
		$updates['joomleague_round'][1]['job']="ADD";
		$updates['joomleague_round'][1]['field']="tmp_old_round_id";
		$updates['joomleague_round'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_round'][1]['after']="checked_out_time";
	}

	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_position_eventtype'][0]['tablename']='#__joomleague_position_eventtype';

		$updates['joomleague_position_eventtype'][1]['action']="ALTER TABLE";
		$updates['joomleague_position_eventtype'][1]['job']="ADD";
		$updates['joomleague_position_eventtype'][1]['field']="tmp_old_pet_id";
		$updates['joomleague_position_eventtype'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_position_eventtype'][1]['after']="checked_out_time";
	}

	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_team_player'][0]['tablename']='#__joomleague_team_player';

		$updates['joomleague_team_player'][1]['action']="ALTER TABLE";
		$updates['joomleague_team_player'][1]['job']="ADD";
		$updates['joomleague_team_player'][1]['field']="tmp_old_player_id";
		$updates['joomleague_team_player'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_team_player'][1]['after']="checked_out_time";

		$updates['joomleague_team_player'][2]['action']="ALTER TABLE";
		$updates['joomleague_team_player'][2]['job']="ADD";
		$updates['joomleague_team_player'][2]['field']="tmp_old_project_id";
		$updates['joomleague_team_player'][2]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_team_player'][2]['after']="tmp_old_player_id";
	}

	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_team_staff'][0]['tablename']='#__joomleague_team_staff';

		$updates['joomleague_team_staff'][1]['action']="ALTER TABLE";
		$updates['joomleague_team_staff'][1]['job']="ADD";
		$updates['joomleague_team_staff'][1]['field']="tmp_old_player_id";
		$updates['joomleague_team_staff'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_team_staff'][1]['after']="checked_out_time";

		$updates['joomleague_team_staff'][2]['action']="ALTER TABLE";
		$updates['joomleague_team_staff'][2]['job']="ADD";
		$updates['joomleague_team_staff'][2]['field']="tmp_old_project_id";
		$updates['joomleague_team_staff'][2]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_team_staff'][2]['after']="tmp_old_player_id";
	}

	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_match'][0]['tablename']='#__joomleague_match';

		$updates['joomleague_match'][1]['action']="ALTER TABLE";
		$updates['joomleague_match'][1]['job']="ADD";
		$updates['joomleague_match'][1]['field']="tmp_old_match_id";
		$updates['joomleague_match'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_match'][1]['after']="checked_out_time";

		$updates['joomleague_match'][2]['action']="ALTER TABLE";
		$updates['joomleague_match'][2]['job']="ADD";
		$updates['joomleague_match'][2]['field']="tmp_old_project_id";
		$updates['joomleague_match'][2]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_match'][2]['after']="tmp_old_match_id";

		$updates['joomleague_match'][3]['action']="ALTER TABLE";
		$updates['joomleague_match'][3]['job']="ADD";
		$updates['joomleague_match'][3]['field']="new_match_id";
		$updates['joomleague_match'][3]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_match'][3]['after']="match_result_detail";

		$updates['joomleague_match'][4]['action']="ALTER TABLE";
		$updates['joomleague_match'][4]['job']="ADD";
		$updates['joomleague_match'][4]['field']="old_match_id";
		$updates['joomleague_match'][4]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_match'][4]['after']="new_match_id";
	}

	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_prediction_game'][0]['tablename']='#__joomleague_prediction_game';

		$updates['joomleague_prediction_game'][1]['action']="ALTER TABLE";
		$updates['joomleague_prediction_game'][1]['job']="ADD";
		$updates['joomleague_prediction_game'][1]['field']="tmp_old_id";
		$updates['joomleague_prediction_game'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_prediction_game'][1]['after']="checked_out_time";
	}

	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_prediction_admin'][0]['tablename']='#__joomleague_prediction_admin';

		$updates['joomleague_prediction_admin'][1]['action']="ALTER TABLE";
		$updates['joomleague_prediction_admin'][1]['job']="ADD";
		$updates['joomleague_prediction_admin'][1]['field']="tmp_old_id";
		$updates['joomleague_prediction_admin'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_prediction_admin'][1]['after']="checked_out_time";

		$updates['joomleague_prediction_admin'][2]['action']="ALTER TABLE";
		$updates['joomleague_prediction_admin'][2]['job']="ADD";
		$updates['joomleague_prediction_admin'][2]['field']="tmp_old_pid";
		$updates['joomleague_prediction_admin'][2]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_prediction_admin'][2]['after']="tmp_old_id";

	}

	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_prediction_project'][0]['tablename']='#__joomleague_prediction_project';

		$updates['joomleague_prediction_project'][1]['action']="ALTER TABLE";
		$updates['joomleague_prediction_project'][1]['job']="ADD";
		$updates['joomleague_prediction_project'][1]['field']="tmp_old_id";
		$updates['joomleague_prediction_project'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_prediction_project'][1]['after']="checked_out_time";

		$updates['joomleague_prediction_project'][2]['action']="ALTER TABLE";
		$updates['joomleague_prediction_project'][2]['job']="ADD";
		$updates['joomleague_prediction_project'][2]['field']="tmp_old_pid";
		$updates['joomleague_prediction_project'][2]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_prediction_project'][2]['after']="tmp_old_id";

	}

	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_prediction_member'][0]['tablename']='#__joomleague_prediction_member';

		$updates['joomleague_prediction_member'][1]['action']="ALTER TABLE";
		$updates['joomleague_prediction_member'][1]['job']="ADD";
		$updates['joomleague_prediction_member'][1]['field']="tmp_old_id";
		$updates['joomleague_prediction_member'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_prediction_member'][1]['after']="checked_out_time";

		$updates['joomleague_prediction_member'][2]['action']="ALTER TABLE";
		$updates['joomleague_prediction_member'][2]['job']="ADD";
		$updates['joomleague_prediction_member'][2]['field']="tmp_old_pid";
		$updates['joomleague_prediction_member'][2]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_prediction_member'][2]['after']="tmp_old_id";

	}

	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_prediction_result'][0]['tablename']='#__joomleague_prediction_result';

		$updates['joomleague_prediction_result'][1]['action']="ALTER TABLE";
		$updates['joomleague_prediction_result'][1]['job']="ADD";
		$updates['joomleague_prediction_result'][1]['field']="tmp_old_id";
		$updates['joomleague_prediction_result'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_prediction_result'][1]['after']="checked_out_time";
	}

	// -------------------------------------------------------------------------------

	if (1 ==1)
	{
		$updates['joomleague_template_config'][0]['tablename']='#__joomleague_template_config';

		$updates['joomleague_template_config'][1]['action']="ALTER TABLE";
		$updates['joomleague_template_config'][1]['job']="ADD";
		$updates['joomleague_template_config'][1]['field']="tmp_old_id";
		$updates['joomleague_template_config'][1]['values']="int(11) NOT NULL default '0'";
		$updates['joomleague_template_config'][1]['after']="checked_out_time";
	}

	// -------------------------------------------------------------------------------

	if (3 ==3)
	{
		$updates3['joomleague_prediction_admin'][0]['tablename']='#__joomleague_prediction_admin';

		$updates3['joomleague_prediction_admin'][1]['action']="ALTER TABLE";
		$updates3['joomleague_prediction_admin'][1]['job']="DROP";
		$updates3['joomleague_prediction_admin'][1]['field']="tmp_old_id";

		$updates3['joomleague_prediction_admin'][2]['action']="ALTER TABLE";
		$updates3['joomleague_prediction_admin'][2]['job']="DROP";
		$updates3['joomleague_prediction_admin'][2]['field']="tmp_old_pid";

	}

	// -------------------------------------------------------------------------------

	if (3 ==3)
	{
		$updates3['joomleague_prediction_result'][0]['tablename']='#__joomleague_prediction_result';

		$updates3['joomleague_prediction_result'][1]['action']="ALTER TABLE";
		$updates3['joomleague_prediction_result'][1]['job']="DROP";
		$updates3['joomleague_prediction_result'][1]['field']="tmp_old_id";
	}

	if (3 ==3)
	{
		$updates3['joomleague_template_config'][0]['tablename']='#__joomleague_template_config';

		$updates3['joomleague_template_config'][1]['action']="ALTER TABLE";
		$updates3['joomleague_template_config'][1]['job']="DROP";
		$updates3['joomleague_template_config'][1]['field']="tmp_old_id";
	}

	// -------------------------------------------------------------------------------

	if (3 ==3)
	{
		$updates3['joomleague_prediction_member'][0]['tablename']='#__joomleague_prediction_member';

		$updates3['joomleague_prediction_member'][1]['action']="ALTER TABLE";
		$updates3['joomleague_prediction_member'][1]['job']="DROP";
		$updates3['joomleague_prediction_member'][1]['field']="tmp_old_id";

		$updates3['joomleague_prediction_member'][2]['action']="ALTER TABLE";
		$updates3['joomleague_prediction_member'][2]['job']="DROP";
		$updates3['joomleague_prediction_member'][2]['field']="tmp_old_pid";

	}

	// -------------------------------------------------------------------------------

	if (3 ==3)
	{
		$updates3['joomleague_prediction_project'][0]['tablename']='#__joomleague_prediction_project';

		$updates3['joomleague_prediction_project'][1]['action']="ALTER TABLE";
		$updates3['joomleague_prediction_project'][1]['job']="DROP";
		$updates3['joomleague_prediction_project'][1]['field']="tmp_old_id";

		$updates3['joomleague_prediction_project'][2]['action']="ALTER TABLE";
		$updates3['joomleague_prediction_project'][2]['job']="DROP";
		$updates3['joomleague_prediction_project'][2]['field']="tmp_old_pid";

	}

	// -------------------------------------------------------------------------------

	if (3 ==3)
	{
		$updates3['joomleague_prediction_game'][0]['tablename']='#__joomleague_prediction_game';

		$updates3['joomleague_prediction_game'][1]['action']="ALTER TABLE";
		$updates3['joomleague_prediction_game'][1]['job']="DROP";
		$updates3['joomleague_prediction_game'][1]['field']="tmp_old_id";
	}

	// -------------------------------------------------------------------------------

	if (3 ==3)
	{
		$updates3['joomleague_project'][0]['tablename']='#__joomleague_project';

		$updates3['joomleague_project'][1]['action']="ALTER TABLE";
		$updates3['joomleague_project'][1]['job']="DROP";
		$updates3['joomleague_project'][1]['field']="tmp_old_pid";
	}

	// -------------------------------------------------------------------------------

	if (3 ==3)
	{
		$updates3['joomleague_club'][0]['tablename']='#__joomleague_club';

		$updates3['joomleague_club'][1]['action']="ALTER TABLE";
		$updates3['joomleague_club'][1]['job']="DROP";
		$updates3['joomleague_club'][1]['field']="tmp_old_id";
	}

	// -------------------------------------------------------------------------------

	if (3 ==3)
	{
		$updates3['joomleague_playground'][0]['tablename']='#__joomleague_playground';

		$updates3['joomleague_playground'][1]['action']="ALTER TABLE";
		$updates3['joomleague_playground'][1]['job']="DROP";
		$updates3['joomleague_playground'][1]['field']="tmp_old_id";
	}

	// -------------------------------------------------------------------------------

	if (3 ==3)
	{
		$updates3['joomleague_season'][0]['tablename']='#__joomleague_season';

		$updates3['joomleague_season'][1]['action']="ALTER TABLE";
		$updates3['joomleague_season'][1]['job']="DROP";
		$updates3['joomleague_season'][1]['field']="tmp_old_id";
	}

	// -------------------------------------------------------------------------------

	if (3 ==3)
	{
		$updates3['joomleague_league'][0]['tablename']='#__joomleague_league';

		$updates3['joomleague_league'][1]['action']="ALTER TABLE";
		$updates3['joomleague_league'][1]['job']="DROP";
		$updates3['joomleague_league'][1]['field']="tmp_old_id";
	}

	// -------------------------------------------------------------------------------

	if (3 ==3)
	{
		$updates3['joomleague_position'][0]['tablename']='#__joomleague_position';

		$updates3['joomleague_position'][1]['action']="ALTER TABLE";
		$updates3['joomleague_position'][1]['job']="DROP";
		$updates3['joomleague_position'][1]['field']="tmp_old_pos_id";
	}

	// -------------------------------------------------------------------------------

	if (3 ==3)
	{
		$updates3['joomleague_eventtype'][0]['tablename']='#__joomleague_eventtype';

		$updates3['joomleague_eventtype'][1]['action']="ALTER TABLE";
		$updates3['joomleague_eventtype'][1]['job']="DROP";
		$updates3['joomleague_eventtype'][1]['field']="tmp_old_id";
	}

	// -------------------------------------------------------------------------------

	if (3 ==3)
	{
		$updates3['joomleague_position_eventtype'][0]['tablename']='#__joomleague_position_eventtype';

		$updates3['joomleague_position_eventtype'][1]['action']="ALTER TABLE";
		$updates3['joomleague_position_eventtype'][1]['job']="DROP";
		$updates3['joomleague_position_eventtype'][1]['field']="tmp_old_pet_id";
	}

	// -------------------------------------------------------------------------------

	if (3 ==3)
	{
		$updates3['joomleague_round'][0]['tablename']='#__joomleague_round';

		$updates3['joomleague_round'][1]['action']="ALTER TABLE";
		$updates3['joomleague_round'][1]['job']="DROP";
		$updates3['joomleague_round'][1]['field']="tmp_old_round_id";
	}

	// -------------------------------------------------------------------------------

	if (3 ==3)
	{
		$updates3['joomleague_team_staff'][0]['tablename']='#__joomleague_team_staff';

		$updates3['joomleague_team_staff'][1]['action']="ALTER TABLE";
		$updates3['joomleague_team_staff'][1]['job']="DROP";
		$updates3['joomleague_team_staff'][1]['field']="tmp_old_player_id";

		$updates3['joomleague_team_staff'][2]['action']="ALTER TABLE";
		$updates3['joomleague_team_staff'][2]['job']="DROP";
		$updates3['joomleague_team_staff'][2]['field']="tmp_old_project_id";
	}

	// -------------------------------------------------------------------------------

	if (3 ==3)
	{
		$updates3['joomleague_match'][0]['tablename']='#__joomleague_match';

		$updates3['joomleague_match'][1]['action']="ALTER TABLE";
		$updates3['joomleague_match'][1]['job']="DROP";
		$updates3['joomleague_match'][1]['field']="tmp_old_project_id";

		$updates3['joomleague_match'][2]['action']="ALTER TABLE";
		$updates3['joomleague_match'][2]['job']="DROP";
		$updates3['joomleague_match'][2]['field']="tmp_old_match_id";
	}

	// -------------------------------------------------------------------------------

	if (3 ==3)
	{
		$updates3['joomleague_person'][0]['tablename']='#__joomleague_person';

		$updates3['joomleague_person'][1]['action']="ALTER TABLE";
		$updates3['joomleague_person'][1]['job']="DROP";
		$updates3['joomleague_person'][1]['field']="tmp_old_player_id";

		$updates3['joomleague_person'][2]['action']="ALTER TABLE";
		$updates3['joomleague_person'][2]['job']="DROP";
		$updates3['joomleague_person'][2]['field']="tmp_old_referee_id";
	}

	// -------------------------------------------------------------------------------

	if (3 ==3)
	{
		$updates3['joomleague_project_team'][0]['tablename']='#__joomleague_project_team';

		$updates3['joomleague_project_team'][1]['action']="ALTER TABLE";
		$updates3['joomleague_project_team'][1]['job']="DROP";
		$updates3['joomleague_project_team'][1]['field']="tmp_old_teamtool_id";
	}

	// -------------------------------------------------------------------------------

	if (3 ==3)
	{
		$updates3['joomleague_team'][0]['tablename']='#__joomleague_team';

		$updates3['joomleague_team'][1]['action']="ALTER TABLE";
		$updates3['joomleague_team'][1]['job']="DROP";
		$updates3['joomleague_team'][1]['field']="tmp_old_team_id";
	}

	// -------------------------------------------------------------------------------

	if (3 ==3)
	{
		$updates3['joomleague_division'][0]['tablename']='#__joomleague_division';

		$updates3['joomleague_division'][1]['action']="ALTER TABLE";
		$updates3['joomleague_division'][1]['job']="DROP";
		$updates3['joomleague_division'][1]['field']="tmp_old_division_id";
	}

	// -------------------------------------------------------------------------------

	if (3 ==3)
	{
		$updates3['joomleague_team_player'][0]['tablename']='#__joomleague_team_player';

		$updates3['joomleague_team_player'][1]['action']="ALTER TABLE";
		$updates3['joomleague_team_player'][1]['job']="DROP";
		$updates3['joomleague_team_player'][1]['field']="tmp_old_player_id";

		$updates3['joomleague_team_player'][2]['action']="ALTER TABLE";
		$updates3['joomleague_team_player'][2]['job']="DROP";
		$updates3['joomleague_team_player'][2]['field']="tmp_old_project_id";
	}

	// -------------------------------------------------------------------------------

}

/**
* obj2Array#
* converts simpleXml object to array
*
* Variables: $o['obj']: simplexml object
*
* @return
*
*/
function obj2Array($obj)
{
	$arr=(array)$obj;
	if (empty($arr))
	{
		$arr='';
	}
	else
	{
		foreach ($arr as $key=>$value)
		{
			if (!is_scalar($value))
			{
				$arr[$key]=obj2Array($value);
			}
		}
	}
	return $arr;
}

function PrintStepResult($result)
{
	if ($result)
	{
		$output=' - <span style="color:green">'.JText::_('SUCCESS').'</span>';
	}
	else
	{
		$output=' - <span style="color:red">'.JText::_('FAILED').'</span>';
	}

	return $output;
}

function doQueries($queries)
{
	$db = JFactory::getDBO();

	/* execute modifications */
	if (count($queries))
	{
		foreach ($queries as $query)
		{
			$db->setQuery($query[0]);
			$db->query($query[0]);
			$bla=$db->getErrorNum();

			echo '<br />';
			if ($bla ==0)
			{
				echo '<img	align="top" src="components/com_joomleague/assets/images/ok.png"
							alt="'.JText::_('SUCCESS').'" title"'.JText::_('SUCCESS').'">';
				echo '&nbsp;';
				echo $query[1];
			}
			else
			{
				echo '<img	align="top" src="components/com_joomleague/assets/images/error.png"
							alt="'.JText::_('Error').'" title"'.JText::_('Error').'">';
				echo '&nbsp;';
				echo '<pre>'.$db->getErrorMsg()."</pre>$query<br/>$query[0]";
			}
		}
		echo '<br />';
	}
	else
	{
		echo ' - <span style="color:green">'.JText::_('No update was needed').'</span>';
	}
	return '';
}

function Update_Tables($updates,$tablename)
{
	$db = JFactory::getDBO();
	$queries=array();
	$tables[0]=$updates[$tablename][0]['tablename'];

	echo JText::sprintf('Updating table [%s]','<b>'.$tables[0].'</b>');

	$fields=$db->getTableFields($tables);
	$fieldlist=implode(",",array_keys($fields[ $tables[0] ]));

	foreach ($updates[$tablename] as $update)
	{
		if ((isset ($update['field'])) && (!is_null($update['field'])))
		{
			$after=$update['after'];

			if (!in_array($update['field'],array_keys($fields[ $tables[0] ])))
			{
				if ($after!="")
				{
					if (in_array($after,array_keys($fields[ $tables[0] ])))
					{
						$after="AFTER `".$after."`";
					}
					else
					{
						$after="";
					}
				}
				$queries[]=array(	$update['action']." `".$tables[0]."` ".$update['job']." `".$update['field']."` " .
									$update['values']." ".$after,
									JText::sprintf('JL_DB_UPDATE_ADDING_FIELD_TO_TABLE','<b>'.$update['field'].'</b>'));
				$fields[ $tables[0] ][$update['field']]="#";
			}
		}
	}

	echo doQueries($queries).'<br />';
	return '';
}

//echo Change_Table_Columns($updates2,'joomleague');
function Change_Table_Columns($updates,$tablename)
{
	$db = JFactory::getDBO();
	$queries=array();
	$tables[0]=$updates[$tablename][0]['tablename'];

	echo JText::sprintf('Updating table [%s]','<b>'.$tables[0].'</b>');

	$fields=$db->getTableFields($tables);
	$fieldlist=implode(",",array_keys($fields[ $tables[0] ]));

	foreach ($updates[$tablename] as $update)
	{
		if ((isset ($update['field'])) && (!is_null($update['field'])))
		{
			$queries[]=array(	$update['action']." `".$tables[0]."` ".$update['job']." `" .
								$update['field']."` `".$update['field']."` ".$update['values'],
								JText::sprintf('- Changing column [%s] in table',$update['field']));
			$fields[ $tables[0] ][$update['field']]="#";
		}
	}

	echo doQueries($queries).'<br />';
	return '';
}

function Delete_Table_Columns($dUpdates,$tablename)
{
	$db = JFactory::getDBO();
	$queries=array();
	$tables[0]=$dUpdates[$tablename][0]['tablename'];
	//echo '<br /><pre>~'.print_r($dUpdates,true).'~</pre><br />';

	echo JText::sprintf('Updating table [%s]','<b>'.$tables[0].'</b>');

	$fields=$db->getTableFields($tables);
	$fieldlist=implode(",",array_keys($fields[$tables[0]]));
	foreach ($dUpdates[$tablename] as $update)
	{
		if ((isset($update['field'])) && (!is_null($update['field'])))
		{
			$queries[]=array(	$update['action']." `".$tables[0]."` ".$update['job']." `".$update['field']."`",
								JText::sprintf('- Deleting column [%s] in table',$update['field']));
			$fields[ $tables[0] ][$update['field']]="#";
		}
	}
	echo doQueries($queries).'<br />';
	return '';
}

function UpdateTemplateProject_ids()
{
	echo JText::sprintf(	'Updating project id\'s for existing frontend settings (template-files) and correcting file name inside of [%1$s]',
							'<b>'.'#__joomleague_template_config'.'</b>').'<br /><br />';

	$result=false;
	$db = JFactory::getDBO();
	$projectID='';

	$query='SELECT * FROM #__joomleague_template_configs ORDER by project_id,template';
	$db->setQuery($query);

	if ($templates=$db->loadObjectList()) // check that there are existing old template records...
	{

		$dtemplates[0]['oldname']="show_club.tpl";			$dtemplates[0]['newname']="clubinfo";
		$dtemplates[1]['oldname']="show_events_stats.tpl";	$dtemplates[1]['newname']="eventsranking";
		$dtemplates[2]['oldname']="map.tpl";				$dtemplates[2]['newname']="map";
		$dtemplates[3]['oldname']="show_report.tpl";		$dtemplates[3]['newname']="matchreport";
		$dtemplates[4]['oldname']="show_matrix.tpl";		$dtemplates[4]['newname']="matrix";
		$dtemplates[5]['oldname']="show_next_match.tpl";	$dtemplates[5]['newname']="nextmatch";
		$dtemplates[6]['oldname']="overall.tpl";			$dtemplates[6]['newname']="overall";
		$dtemplates[7]['oldname']="show_player.tpl";		$dtemplates[7]['newname']="player";
		$dtemplates[8]['oldname']="show_playground.tpl";	$dtemplates[8]['newname']="playground";
		$dtemplates[9]['oldname']="show_table.tpl";			$dtemplates[9]['newname']="ranking";
		$dtemplates[10]['oldname']="referee.tpl";			$dtemplates[10]['newname']="referee";
		$dtemplates[11]['oldname']="show_results.tpl";		$dtemplates[11]['newname']="results";
		$dtemplates[12]['oldname']="show_players.tpl";		$dtemplates[12]['newname']="roster";
		$dtemplates[13]['oldname']="show_stats.tpl";			$dtemplates[13]['newname']="stats";
		$dtemplates[14]['oldname']="show_table.tpl";			$dtemplates[14]['newname']="ranking";
		$dtemplates[15]['oldname']="show_team_info.tpl";		$dtemplates[15]['newname']="teaminfo";
		$dtemplates[16]['oldname']="show_plan.tpl";			$dtemplates[16]['newname']="teamplan";
		$dtemplates[17]['oldname']="table";					$dtemplates[17]['newname']="ranking";

		foreach ($templates as $template)
		{
			if ($template->project_id!=$projectID)
			{
				$projectID=$template->project_id;
				$query='SELECT * FROM #__joomleague_project WHERE tmp_old_pid='.$projectID;
				$db->setQuery($query);
				if ($project=$db->loadObject()) // get new project_id...
				{
					$NewProjectID=$project->id;
				}
				else
				{
					$NewProjectID=0;
					continue; // skip as project was not found
				}
			}

			foreach ($dtemplates as $dtemplate)
			{
				if (trim($template->template)==$dtemplate['oldname'])
				{
					$template->template=$dtemplate['newname'];
					break;
				}
			}

			if (($NewProjectID > 0) &&
				($template->template!='do_tips.tpl') &&
				($template->template!='show_tip_ranking.tpl') &&
				($template->template!='show_tip_results.tpl') &&
				($template->template!='show_user.tpl') &&
				($template->template!='show_frontpage.tpl')
				)
			{
				$query="	INSERT INTO	#__joomleague_template_config
											(	`project_id`,
												`template`,
												`title`,
												`params`,
												`tmp_old_id`
											)
										VALUES
											(	'$NewProjectID',
												'$template->template',
												'$template->title',
												'$template->params',
												'$template->id'
											)";

				$db->setQuery($query);
				if (!$db->query())
				{
					$result=false;
					echo '<span style="color:red">';
						echo JText::sprintf('Problems while updating template settings for template-id [%1$s] with project-ID [%2$s]!',$template->id,$template->project_id);
					echo '</span>'.'<br />';
					echo $db->getErrorMsg().' <br />';
					break;
				}
				else
				{
					$result=true;
					echo JText::sprintf(	'Updated template id [%1$s] - Old project id [%2$s] - New project-id [%3$s]',
											'<b>'.$template->id.'</b>',
											'<b>'.$template->project_id.'</b>',
											'<b>'.$NewProjectID.'</b>').PrintStepResult(true).'<br />';
				}
			}
			else
			{
				//echo 'Skipped : '.$template->template.'<br />';
			}
		}
	}
	if (!$result){echo '<span style="color:orange">'.JText::_ ('No update of project ids were made!!! DO NOT WORRY... Surely the correction was done before or you have a clean install of JoomLeague!!!').'</span><br />';}
	return '';
}

function UpdateMasterTemplateProject_ids()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;

	echo JText::sprintf('Correcting Master Template Project Ids inside table %1$s','<b>joomleague_project</b>').'<br /><br />';

	$query='SELECT * FROM #__joomleague_project';
	$db->setQuery($query);
	if ($projects=$db->loadObjectList()) // get new projects...
	{
		foreach ($projects as $project)
		{
			$query='SELECT id FROM #__joomleague_project WHERE tmp_old_pid='.$project->master_template;
			$db->setQuery($query);
			if ($dbresult=$db->loadResult()) // get new project_id...
			{
				$project_id=$dbresult;
			}
			else
			{
				$project_id=0;
				continue; // skip as project was not found
			}

			echo JText::sprintf('Updating Master-Template-ID of project %1$s','<b>'.$project->name.'</b>');
			//.'<br />';
			$query="UPDATE #__joomleague_project SET master_template='$project_id' WHERE id='$project->id'";
			$db->setQuery($query);
			if (!$db->query())
			{
				$result=false;
				echo PrintStepResult($result).'<br />';
				echo '<br />'.$db->getErrorMsg().' <br />';
			}
			else
			{
				echo PrintStepResult(true).'<br />';
				$i++;
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}
	return '';
}

function UpdateTemplateMasters()
{
	/**********************************
	******** Update Script for xml template to store the non existing Variable
	***********************************/

/*
* developer: diddipoeler 
* date: 13.01.2011
* Bugtracker Backend Bug #579
* change old textname in newtextname					
*/
$convert = array (
'AGAINST' => 'JL_AGAINST',
'AVG' => 'JL_AVG',
'BONUS' => 'JL_BONUS',
'DIFF' => 'JL_DIFF',
'FOR' => 'JL_FOR',
'GB' => 'JL_GB',
'H2H' => 'JL_H2H',
'H2H_AWAY' => 'JL_H2H_AWAY',
'H2H_DIFF' => 'JL_H2H_DIFF',
'H2H_FOR' => 'JL_H2H_FOR',
'LEGS' => 'JL_LEGS',
'LEGS_DIFF' => 'JL_LEGS_DIFF',
'LEGS_RATIO' => 'JL_LEGS_RATIO',
'LEGS_WIN' => 'JL_LEGS_WIN',
'LOSSES' => 'JL_LOSSES',
'NEGPOINTS' => 'JL_NEGPOINTS',
'OLDNEGPOINTS' => 'JL_OLDNEGPOINTS',
'PLAYED' => 'JL_PLAYED',
'POINTS' => 'JL_POINTS',
'POINTS_RATIO' => 'JL_POINTS_RATIO',
'QUOT' => 'JL_QUOT',
'RESULTS' => 'JL_RESULTS',
'SCOREAGAINST' => 'JL_SCOREAGAINST',
'SCOREFOR' => 'JL_SCOREFOR',
'SCOREPCT' => 'JL_SCOREPCT',
'START' => 'JL_START',
'TIES' => 'JL_TIES',
'WINPCT' => 'JL_WINPCT',
'WINS' => 'JL_WINS'

  );
  
  	
	echo '<b>'.JText::_('Updating new variables and templates for usage in the Master-Templates').'</b><br />';
	$db = JFactory::getDBO();

	$query='SELECT id,name,master_template FROM #__joomleague_project';
	$db->setQuery($query);
	if ($projects=$db->loadObjectList()) // check that there are projects...
	{
		//echo '<br />';
		$xmldir=JPATH_SITE.DS.'components'.DS.'com_joomleague'.DS.'settings'.DS.'default';

		if ($handle=JFolder::files($xmldir))
		{
			# check that each xml template has a corresponding record in the
			# database for this project (except for projects using master templates).
			# If not,create the rows with default values
			# from the xml file

			foreach ($handle as $file)
			{
				if ((strtolower(substr($file,-3))=='xml') &&
					(substr($file,0,(strlen($file) - 4))!='table') &&
					(substr($file,0,10)!='prediction')
					)
				{
					$defaultconfig=array ();
					$template=substr($file,0,(strlen($file) - 4));
					$out=simplexml_load_file($xmldir.DS.$file,'SimpleXMLElement',LIBXML_NOCDATA);
					$temp='';
					$arr=obj2Array($out);
					$outName=JText::_($out->name[0]);
					echo '<br />'.JText::sprintf('Template: [%1$s] - Name: [%2$s]',"<b>$template</b>","<b>$outName</b>").'<br />';
					if (isset($arr["params"]["param"]))
					{
						foreach ($arr["params"]["param"] as $param)
						{
							$temp .= $param["@attributes"]["name"]."=".$param["@attributes"]["default"]."\n";
							$defaultconfig[$param["@attributes"]["name"]]=$param["@attributes"]["default"];
						}
					}
					else
					{
						foreach ($arr["params"] as $paramsgroup)
						{
							foreach ($paramsgroup["param"] as $param)
							{
							
								if (!isset($param["@attributes"]))
								{
									if (isset($param["name"]))
									{
										$temp .= $param["name"]."=".$param["default"]."\n";
										$defaultconfig[$param["name"]]=$param["default"];
									}
								}
								else if (isset($param["name"]))
								{
/*
* developer: diddipoeler 
* date: change on 13.01.2011
* Bugtracker Backend Bug #579
* error message string to object example template teamstats					
*/
										//$temp .= $param["@attributes"]["name"]."=".$param["@attributes"]["default"]."\n";
										$temp .= $param["name"]."=".$param["default"]."\n";
                    //$defaultconfig[$param["@attributes"]["name"]]=$param["@attributes"]["default"];										
										$defaultconfig[$param["name"]]=$param["default"];
								}
							}
						}
					}
					$changeNeeded=false;
					foreach ($projects as $proj)
					{
						$count_diff=0;
						$configvalues=array();
						$query="SELECT params FROM #__joomleague_template_config WHERE project_id=$proj->id AND template='".$template."'";
						$db->setQuery($query);
						if ($id=$db->loadResult())
						{
			 				//template present in db for this project
							$string='';
							$templateTitle='';
							$params=explode("\n",trim($id));
							foreach($params AS $param)
							{
								list ($name,$value)=explode("=",$param);
								$configvalues[$name]=$value;
							}

						
              foreach($defaultconfig AS $key => $value)
							{
								if (!array_key_exists($key,$configvalues))
								{
									$count_diff++;
									$configvalues[$key]=$value;
								}
							}
							
							if ($count_diff)
							{
								foreach ($configvalues AS $key=>$value)
								{

/*
* developer: diddipoeler 
* date: change on 13.01.2011
* Bugtracker Backend Bug #579
* change old textname in newtextname					
*/								
								if (preg_match("/JL_/i", $value)) 
                {
                // text ok
                }
                else 
                {
                // change text
                $value = str_replace(array_keys($convert), array_values($convert), $value  );
                }
								
									$value=trim($value);
									$string .= "$key=$value\n";
								}
								echo JText::sprintf('Difference found for project [%1$s]','<b>'.$proj->name.'</b>').' - ';
								$changeNeeded=true;
								$query =" UPDATE #__joomleague_template_config ";
								$query .= " SET ";
								$query .= " title='".$out->name[0]."',";
								$query .= " params='$string' ";
								$query .= " WHERE template='$template' AND project_id=".$proj->id;

								$db->setQuery($query);
								if (!$db->query())
								{
									echo '<span style="color:red">';
									echo JText::sprintf(	'Problems while saving config for [%1$s] with project-ID [%2$s]!',
															'<b>'.$template.'</b>',
															'<b>'.$proj->id.'</b>');
									echo '</span>'.'<br />';
									echo $db->getErrorMsg().'<br />';
								}
								else
								{
									echo JText::sprintf(	'Updated template [%1$s] with project-ID [%2$s]',
															'<span style="color:green;"><b>'.$template.'</b></span>',
															'<span style="color:green"><b>'.$proj->id.'</b></span>').PrintStepResult(true).'<br />';
								}
							}
						}
						elseif ($proj->master_template ==0)
							{	//check that project has own templates
							 	//or if template not present,create a row with default values
								echo '<br /><span style="color:orange;">'.JText::sprintf(	'Need to insert into project with project-ID [%1$s] - Project name is [%2$s]',
																	'<b>'.$proj->id.'</b>','<b>'.$proj->name.'</b>').'</span><br />';
								$changeNeeded=true;
								$temp=trim($temp);
								$query="	INSERT
											INTO #__joomleague_template_config
												(template,title,params,project_id)
											VALUES
												('$template','".$out->name[0]."','$temp','$proj->id')";
								$db->setQuery($query);
								//echo error,allows to check if there is a mistake in the template file

								if (!$db->query())
								{
									echo '<span style="color:red; font-weight:bold; ">'.JText::sprintf('Error with [%s]:',$template).'</span><br />';
									echo $db->getErrorMsg().'<br/>';
								}
								else
								{
									echo JText::sprintf(	'Inserted %1$s into project with ID %2$s',
															'<b>'.$template.'</b>','<b>'.$proj->id.'</b>').PrintStepResult(true).'<br />';
								}
		 					}
		 			}
		 			if (!$changeNeeded)
		 			{
						echo '<span style="color:green">'.JText::_('No changes needed for this template').'</span>'.'<br />';
		 			}
				}
			}
		}
		else
		{
			echo ' - <span style="color:red">'.JText::_('No templates found').'</span>';
		}
	}
	else
	{
		echo ' - <span style="color:green">'.JText::_('No update was needed').'</span>';
	}

	return '';
}

function updateVersion($version,$updatefilename)
{
	$db = JFactory::getDBO();

	$updateVersionFile=JPATH_COMPONENT_ADMINISTRATOR.DS.'assets'.DS.'updates'.DS.'update_version.sql';

	if (JFile::exists($updateVersionFile))
	{
		$fileContent=JFile::read($updateVersionFile);
	}
	else
	{
		$fileContent="update #__joomleague_version set major='1',minor='5',build='0',revision='a'";
	}

	$dummy=explode("'",$fileContent);

	$versionData=new stdClass();
	$versionData->major=$dummy[1];
	$versionData->minor=$dummy[3];
	$versionData->build=$dummy[5];
	$versionData->revision=$dummy[7];
	$versionData->file='joomleague';
	$versionData->version=$version;
	$versionData->date='0000-00-00 00:00:00';
	$version=sprintf('v%1$s.%2$s.%3$s.%4$s',$versionData->major,$versionData->minor,$versionData->build,$versionData->revision);

	echo JText::sprintf('Updating version number to %1$s',$version);
	$query="INSERT INTO #__joomleague_version (major,minor,build,revision,file)
				VALUES ('$versionData->major','$versionData->minor','$versionData->build','$versionData->revision','$versionData->file')";
	$db->setQuery($query);
	$db->query();

	return '';
}

function addGhostPlayer()
{
	$result=false;
	$db = JFactory::getDBO();

	echo JText::sprintf('JL_DB_UPDATE_ADDING_GHOSTPERSON','<b>'.'#__joomleague_person'.'</b>');

	// Add new Parent position to PlayersPositions
	$queryAdd="INSERT INTO #__joomleague_person
						(`firstname`,`lastname`,`nickname`,`birthday`,`show_pic`,`published`,`ordering`)
				VALUES	('!Unknown','!Player','!Ghost','1970-01-01','0','1','0')";

	$query="SELECT * FROM #__joomleague_person WHERE firstname='!Unknown' AND nickname='!Ghost' AND lastname='!Player' ";
	$db->setQuery($query);
	if (!$dbresult=$db->loadObject())
	{
		$db->setQuery($queryAdd);
		$result=$db->query();
	}

	echo PrintStepResult($result).'<br />';
	if (!$result){echo JText::_ ('JL_DB_UPDATE_DO_NOT_WORRY_GP').'<br />';}

	return '';
}

function build_SelectQuery($tablename,$param1)
{
	$query="SELECT * FROM #__joomleague_".$tablename." WHERE name='".$param1."'";
	return $query;
}

function build_InsertQuery_Position($tablename,$param1,$param2,$param3,$param4,$sports_type_id,$order_count)
{
	$alias=JFilterOutput::stringURLSafe($param1);
	$query="INSERT INTO #__joomleague_".$tablename." (`name`,`alias`,`".$param2."`,`parent_id`,`sports_type_id`,`published`,`ordering`) VALUES ('".$param1."','".$alias."','".$param4."','".$param3."','".$sports_type_id."','1','".$order_count."')";
	return $query;
}

function build_InsertQuery_Event($tablename,$param1,$param2,$sports_type_id,$order_count)
{
	$alias=JFilterOutput::stringURLSafe($param1);
	$query="INSERT INTO #__joomleague_".$tablename." (`name`,`alias`,`icon`,`sports_type_id`,`published`,`ordering`) VALUES ('".$param1."','".$alias."','".$param2."','".$sports_type_id."','1','".$order_count."')";
	return $query;
}

function build_InsertQuery_PositionEventType($param1,$param2)
{
	$query="	INSERT INTO	#__joomleague_position_eventtype
				(`position_id`,`eventtype_id`)
				VALUES
				('".$param1."','".$param2."')";
	return $query;
}

/*
function addStandardsForSoccer()
{
	$events_player=array();
	$events_staff=array();
	$events_referees=array();
	$events_clubstaff=array();
	$PlayersPositions=array();
	$StaffPositions=array();
	$RefereePositions=array();
	$ClubStaffPositions=array();

	//$EventCounter=1;
	$ParentPosCounter=1;
	$PositionCounter=1;

	$result=false;
	$db = JFactory::getDBO();

	{
		echo JText::sprintf('Adding standard soccer events to table table [%s]','<b>'.'#__joomleague_eventtype'.'</b>');

		$squery='SELECT * FROM #__joomleague_eventtype WHERE name=`%s`';
		$isquery='INSERT INTO #__joomleague_eventtype (`name`,`icon`) VALUES (`%1$s`,`%2$s`)';
		$query="SELECT * FROM #__joomleague_sports_type WHERE name='Soccer'"; $db->setQuery($query); $sports_type=$db->loadObject();

		$newEventName='JL_E_GOAL';
		$newEventIcon='media/com_joomleague/events/goal.png';
		$query=build_SelectQuery('eventtypes',$newEventName); $db->setQuery($query);
		if (!$object=$db->loadObject())
		{
			$query=build_InsertQuery_Event('eventtype',$newEventName,$newEventIcon,$sports_type->id,1); $db->setQuery($query);
			$result=$db->query();
			$events_player['0']=$db->insertid();
		}
		else
		{
			$events_player['0']=$object->id;
		}

		$newEventName='JL_E_ASSISTS';
		$newEventIcon='media/com_joomleague/events/assists.gif';
		$query=build_SelectQuery('eventtypes',$newEventName); $db->setQuery($query);
		if (!$object=$db->loadObject())
		{
			$query=build_InsertQuery_Event('eventtype',$newEventName,$newEventIcon,$sports_type->id,2); $db->setQuery($query);
			$result=$db->query();
			$events_player['1']=$db->insertid();
		}
		else
		{
			$events_player['1']=$object->id;
		}

		$newEventName='JL_E_YELLOW_CARD';
		$newEventIcon='media/com_joomleague/events/yellow_card.gif';
		$query=build_SelectQuery('eventtypes',$newEventName); $db->setQuery($query);
		if (!$object=$db->loadObject())
		{
			$query=build_InsertQuery_Event('eventtype',$newEventName,$newEventIcon,$sports_type->id,3); $db->setQuery($query);
			$result=$db->query();
			$events_player['2']=$db->insertid();
			$events_staff['2']=$db->insertid();
			$events_clubstaff['2']=$db->insertid();
			$events_referees['2']=$db->insertid();
		}
		else
		{
			$events_player['2']=$object->id;
			$events_staff['2']=$object->id;
			$events_clubstaff['2']=$object->id;
			$events_referees['2']=$object->id;
		}

		$newEventName='JL_E_YELLOW-RED_CARD';
		$newEventIcon='media/com_joomleague/events/yellow_red_card.gif';
		$query=build_SelectQuery('eventtypes',$newEventName); $db->setQuery($query);
		if (!$object=$db->loadObject())
		{
			$query=build_InsertQuery_Event('eventtype',$newEventName,$newEventIcon,$sports_type->id,4); $db->setQuery($query);
			$result=$db->query();
			$events_player['3']=$db->insertid();
			$events_staff['3']=$db->insertid();
			$events_clubstaff['3']=$db->insertid();
			$events_referees['3']=$db->insertid();
		}
		else
		{
			$events_player['3']=$object->id;
			$events_staff['3']=$object->id;
			$events_clubstaff['3']=$object->id;
			$events_referees['3']=$object->id;
		}

		$newEventName='JL_E_RED_CARD';
		$newEventIcon='media/com_joomleague/events/red_card.gif';
		$query=build_SelectQuery('eventtypes',$newEventName); $db->setQuery($query);
		if (!$object=$db->loadObject())
		{
			$query=build_InsertQuery_Event('eventtype',$newEventName,$newEventIcon,$sports_type->id,5); $db->setQuery($query);
			$result=$db->query();
			$events_player['4']=$db->insertid();
			$events_staff['4']=$db->insertid();
			$events_clubstaff['4']=$db->insertid();
			$events_referees['4']=$db->insertid();
		}
		else
		{
			$events_player['4']=$object->id;
			$events_staff['4']=$object->id;
			$events_clubstaff['4']=$object->id;
			$events_referees['4']=$object->id;
		}

		$newEventName='JL_E_FOUL';
		$newEventIcon='media/com_joomleague/events/foul.png';
		$query=build_SelectQuery('eventtypes',$newEventName); $db->setQuery($query);
		if (!$object=$db->loadObject())
		{
			$query=build_InsertQuery_Event('eventtype',$newEventName,$newEventIcon,$sports_type->id,6); $db->setQuery($query);
			$result=$db->query();
			$events_player['5']=$db->insertid();
			$events_referees['5']=$db->insertid();
		}
		else
		{
			$events_player['5']=$object->id;
			$events_referees['5']=$object->id;
		}

		$newEventName='JL_E_FOUL_TIME';
		$newEventIcon='media/com_joomleague/events/foul_time.png';
		$query=build_SelectQuery('eventtypes',$newEventName); $db->setQuery($query);
		if (!$object=$db->loadObject())
		{
			$query=build_InsertQuery_Event('eventtype',$newEventName,$newEventIcon,$sports_type->id,7); $db->setQuery($query);
			$result=$db->query();
			$events_player['6']=$db->insertid();
			$events_referees['6']=$db->insertid();
		}
		else
		{
			$events_player['6']=$object->id;
			$events_referees['6']=$object->id;
		}

		$newEventName='JL_E_OWN_GOAL';
		$newEventIcon='media/com_joomleague/events/own_goal.png';
		$query=build_SelectQuery('eventtypes',$newEventName); $db->setQuery($query);
		if (!$object=$db->loadObject())
		{
			$query=build_InsertQuery_Event('eventtype',$newEventName,$newEventIcon,$sports_type->id,8); $db->setQuery($query);
			$result=$db->query();
			$events_player['7']=$db->insertid();
		}
		else
		{
			$events_player['7']=$object->id;
		}

		$newEventName='JL_E_PENALTY_GOAL';
		$newEventIcon='media/com_joomleague/events/penalty_goal.png';
		$query=build_SelectQuery('eventtypes',$newEventName); $db->setQuery($query);
		if (!$object=$db->loadObject())
		{
			$query=build_InsertQuery_Event('eventtype',$newEventName,$newEventIcon,$sports_type->id,9); $db->setQuery($query);
			$result=$db->query();
			$events_player['8']=$db->insertid();
			$events_referees['8']=$db->insertid();
		}
		else
		{
			$events_player['8']=$object->id;
			$events_referees['8']=$object->id;
		}

		$newEventName='JL_E_INJURY';
		$newEventIcon='media/com_joomleague/events/injured.gif';
		$query=build_SelectQuery('eventtypes',$newEventName); $db->setQuery($query);
		if (!$object=$db->loadObject())
		{
			$query=build_InsertQuery_Event('eventtype',$newEventName,$newEventIcon,$sports_type->id,10); $db->setQuery($query);
			$result=$db->query();
			$events_player['9']=$db->insertid();
			$events_staff['9']=$db->insertid();
			$events_clubstaff['9']=$db->insertid();
			$events_referees['9']=$db->insertid();
		}
		else
		{
			$events_player['9']=$object->id;
			$events_staff['9']=$object->id;
			$events_clubstaff['9']=$object->id;
			$events_referees['9']=$object->id;
		}

		echo PrintStepResult($result).'<br />';
		if (!$result){echo JText::_ ('DO NOT WORRY... Surely at least one of the events was already existing in your database!!!').'<br />';}
		echo '<br />';
	}

	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

	$result=false;

	{
		echo JText::sprintf('Adding standard soccer positions to table table [%s]','<b>'.'#__joomleague_position'.'</b>');

		if ('AddGeneralPlayersPositions' =='AddGeneralPlayersPositions')
		{
			// Add new Parent position to PlayersPositions
			$newPosName='JL_F_PLAYERS'; $newPosSwitch='persontype'; $newPosParent='0'; $newPosContent='1';
			$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
			if (!$dbresult=$db->loadObject())
			{
				$query=build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,1); $db->setQuery($query);
				$result=$db->query();
				$ParentID=$db->insertid();
				$PlayersPositions['0']=$db->insertid();
			}
			else
			{
				$ParentID=$dbresult->id;
				$PlayersPositions['0']=$dbresult->id;
			}

			if ('AddGeneralPlayersChildPositions' =='AddGeneralPlayersChildPositions')
			{
				// Add new Child positions to PlayersPositions

				// New Child position for PlayersPositions
				$newPosName='JL_P_GOALKEEPER'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='1';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
				if (!$object=$db->loadObject())
				{
					$query=build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,2); $db->setQuery($query);
					$result=$db->query();
					$PlayersPositions['1']=$db->insertid();
				}
				else
				{
					$PlayersPositions['1']=$object->id;
				}

				// New Child position for PlayersPositions
				$newPosName='JL_P_DEFENDER'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='1';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
				if (!$object=$db->loadObject())
				{
					$query=build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,3); $db->setQuery($query);
					$result=$db->query();
					$PlayersPositions['2']=$db->insertid();
				}
				else
				{
					$PlayersPositions['2']=$object->id;
				}

				// New Child position for PlayersPositions
				$newPosName='JL_P_MIDFIELDER'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='1';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
				if (!$object=$db->loadObject())
				{
					$query=build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,4); $db->setQuery($query);
					$result=$db->query();
					$PlayersPositions['3']=$db->insertid();
				}
				else
				{
					$PlayersPositions['3']=$object->id;
				}

				// New Child position for PlayersPositions
				$newPosName='JL_P_FORWARD'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='1';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
				if (!$object=$db->loadObject())
				{
					$query=build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,5); $db->setQuery($query);
					$result=$db->query();
					$PlayersPositions['4']=$db->insertid();
				}
				else
				{
					$PlayersPositions['4']=$object->id;
				}
			}
		}

		//----------------------------------------------------------------------

		if ('AddGeneralStaffPositions' =='AddGeneralStaffPositions')
		{
			if ('AddGeneralStaffTeamStaffPositions' =='AddGeneralStaffTeamStaffPositions')
			{
				// Add new Parent position to StaffPositions
				$newPosName='JL_F_TEAM_STAFF'; $newPosSwitch='persontype'; $newPosParent='0'; $newPosContent='2';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
				if (!$dbresult=$db->loadObject())
				{
					$query=build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,6); $db->setQuery($query);
					$result=$db->query();
					$ParentID=$db->insertid();
					$StaffPositions['0']=$db->insertid();
				}
				else
				{
					$ParentID=$dbresult->id;
					$StaffPositions['0']=$dbresult->id;
				}
			}

			//----------------------------------------------------------------------

			if ('AddGeneralStaffCoachesPositions' =='AddGeneralStaffCoachesPositions')
			{
				// Add new Parent position to StaffPositions
				$newPosName='JL_F_COACHES'; $newPosSwitch='persontype'; $newPosParent='0'; $newPosContent='2';
				$query=build_SelectQuery('position',$newPosName,3); $db->setQuery($query);
				if (!$dbresult=$db->loadObject())
				{
					$query=build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,7); $db->setQuery($query);
					$result=$db->query();
					$ParentID=$db->insertid();
					$StaffPositions['1']=$db->insertid();
				}
				else
				{
					$ParentID=$dbresult->id;
					$StaffPositions['1']=$dbresult->id;
				}

				if ('AddGeneralStaffCoachesChildPositions' =='AddGeneralStaffCoachesChildPositions')
				{
					// New Child position for StaffPositions
					$newPosName='JL_F_COACH'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='2';
					$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
					if (!$object=$db->loadObject())
					{
						$query=build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,8); $db->setQuery($query);
						$result=$db->query();
						$StaffPositions['2']=$db->insertid();
					}
					else
					{
						$StaffPositions['2']=$object->id;
					}

					// New Child position for StaffPositions
					$newPosName='JL_F_HEAD_COACH'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='2';
					$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
					if (!$object=$db->loadObject())
					{
						$query=build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,9); $db->setQuery($query);
						$result=$db->query();
						$StaffPositions['3']=$db->insertid();
					}
					else
					{
						$StaffPositions['3']=$object->id;
					}
				}
			}

			//----------------------------------------------------------------------

			if ('AddGeneralStaffMaintainerteamPositions' =='AddGeneralStaffMaintainerteamPositions')
			{
				// Add new Parent position to StaffPositions
				$newPosName='JL_F_MAINTAINER_TEAM'; $newPosSwitch='persontype'; $newPosParent='0'; $newPosContent='2';
				$query=build_SelectQuery('position',$newPosName,4); $db->setQuery($query);
				if (!$dbresult=$db->loadObject())
				{
					$query=build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,10); $db->setQuery($query);
					$result=$db->query();
					$ParentID=$db->insertid();
					$StaffPositions['4']=$db->insertid();
				}
				else
				{
					$ParentID=$dbresult->id;
					$StaffPositions['4']=$dbresult->id;
				}

				if ('AddGeneralStaffMaintainerChildPositions' =='AddGeneralStaffMaintainerChildPositions')
				{
					// New Child position for StaffPositions
					$newPosName='JL_F_MAINTAINER'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='2';
					$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
					if (!$object=$db->loadObject())
					{
						$query=build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,11); $db->setQuery($query);
						$result=$db->query();
						$StaffPositions['5']=$db->insertid();
					}
					else
					{
						$StaffPositions['5']=$object->id;
					}
				}
			}

			//----------------------------------------------------------------------

			if ('AddGeneralStaffMedicalStaffPositions' =='AddGeneralStaffMedicalStaffPositions')
			{
				// Add new Parent position to StaffPositions
				$newPosName='JL_F_MEDICAL_STAFF'; $newPosSwitch='persontype'; $newPosParent='0'; $newPosContent='2';
				$query=build_SelectQuery('position',$newPosName,5); $db->setQuery($query);
				if (!$dbresult=$db->loadObject())
				{
					$query=build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,12); $db->setQuery($query);
					$result=$db->query();
					$ParentID=$db->insertid();
					$StaffPositions['6']=$db->insertid();
				}
				else
				{
					$ParentID=$dbresult->id;
					$StaffPositions['6']=$dbresult->id;
				}
			}
		}

		//----------------------------------------------------------------------

		if ('AddGeneralRefereesPositions' =='AddGeneralRefereesPositions')
		{
			// Add new Parent position to RefereesPositions
			$newPosName='JL_F_REFEREES'; $newPosSwitch='persontype'; $newPosParent='0'; $newPosContent='3';
			$query=build_SelectQuery('position',$newPosName,6); $db->setQuery($query);
			if (!$dbresult=$db->loadObject())
			{
				$query=build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,13); $db->setQuery($query);
				$result=$db->query();
				$ParentID=$db->insertid();
				$RefereePositions['0']=$db->insertid();
			}
			else
			{
				$ParentID=$dbresult->id;
				$RefereePositions['0']=$dbresult->id;
			}

			if ('AddGeneralRefereesChildPositions' =='AddGeneralRefereesChildPositions')
			{
				// New Child position for RefereePositions
				$newPosName='JL_F_CENTER_REFEREE'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='3';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
				if (!$object=$db->loadObject())
				{
					$query=build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,14); $db->setQuery($query);
					$result=$db->query();
					$RefereePositions['1']=$db->insertid();
				}
				else
				{
					$RefereePositions['1']=$object->id;
				}

				// New Child position for RefereePositions
				$newPosName='JL_F_LINESMAN'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='3';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
				if (!$object=$db->loadObject())
				{
					$query=build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,15); $db->setQuery($query);
					$result=$db->query();
					$RefereePositions['2']=$db->insertid();
				}
				else
				{
					$RefereePositions['2']=$object->id;
				}

				// New Child position for RefereePositions
				$newPosName='JL_F_FOURTH_OFFICIAL'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='3';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
				if (!$object=$db->loadObject())
				{
					$query=build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,16); $db->setQuery($query);
					$result=$db->query();
					$RefereePositions['3']=$db->insertid();
				}
				else
				{
					$RefereePositions['3']=$object->id;
				}

				// New Child position for RefereePositions
				$newPosName='JL_F_FIFTH_OFFICIAL'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='3';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
				if (!$object=$db->loadObject())
				{
					$query=build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,17); $db->setQuery($query);
					$result=$db->query();
					$RefereePositions['4']=$db->insertid();
				}
				else
				{
					$RefereePositions['4']=$object->id;
				}
			}
		}

		//----------------------------------------------------------------------

		if ('AddGeneralClubstaffPositions' =='AddGeneralClubstaffPositions')
		{
			// Add new Parent position to ClubStaffPositions
			$newPosName='JL_F_CLUB_STAFF'; $newPosSwitch='persontype'; $newPosParent='0'; $newPosContent='4';
			$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
			if (!$dbresult=$db->loadObject())
			{
				$query=build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,18); $db->setQuery($query);
				$result=$db->query();
				$ParentID=$db->insertid();
				$ClubStaffPositions['0']=$db->insertid();
			}
			else
			{
				$ParentID=$dbresult->id;
				$ClubStaffPositions['0']=$dbresult->id;
			}


			if ('AddGeneralClubstaffChildPositions' =='AddGeneralClubstaffChildPositions')
			{
				// New Child position for ClubStaffPositions
				$newPosName='JL_F_CLUB_MANAGER'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='4';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query); // hier die 4 als newposcontent einf?gen
				if (!$object=$db->loadObject())
				{
					$query=build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,19); $db->setQuery($query);
					$result=$db->query();
					$ClubStaffPositions['1']=$db->insertid();
				}
				else
				{
					$ClubStaffPositions['1']=$object->id;
				}

				$newPosName='JL_F_CLUB_YOUTH_MANAGER'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='4';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query); // hier die 4 als newposcontent einf?gen
				if (!$object=$db->loadObject())
				{
					$query=build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,20); $db->setQuery($query);
					$result=$db->query();
					$ClubStaffPositions['2']=$db->insertid();
				}
				else
				{
					$ClubStaffPositions['2']=$object->id;
				}
			}
		}

		echo PrintStepResult($result).'<br />';
		if (!$result){echo JText::_ ('DO NOT WORRY... Surely at least one of the positions was already existing in your database!!!').'<br />';}
		echo '<br />';
	}

	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

	$result=false;

	echo JText::sprintf('Adding standard position-related-events for soccer to table table [%s]','<b>'.'#__joomleague_position_eventtype'.'</b>');

	foreach ($PlayersPositions AS $ppkey => $ppid)
	{
		foreach ($events_player AS $epkey => $epid)
		{
			$query=build_InsertQuery_PositionEventType($ppid,$epid); $db->setQuery($query); $result=$db->query();
		}
	}

	foreach ($StaffPositions AS $spkey => $spid)
	{
		foreach ($events_staff AS $eskey => $esid)
		{
			$query=build_InsertQuery_PositionEventType($spid,$esid); $db->setQuery($query); $result=$db->query();
		}
	}

	foreach ($RefereePositions AS $rkey => $rid)
	{
		foreach ($events_referees AS $erkey => $erid)
		{
			$query=build_InsertQuery_PositionEventType($rid,$erid); $db->setQuery($query); $result=$db->query();
		}
	}

	echo PrintStepResult($result).'<br />';
	if (!$result){echo JText::_ ('DO NOT WORRY... Surely at least one of the position related events was already existing in your database!!!').'<br />';}

	return '';
}
*/

function tableExists($tableName)
{
	$db = JFactory::getDBO();
	$query='SELECT * FROM #__'.$tableName;
	$db->setQuery($query);
	$result=$db->query();
	if ((!$result) || ($db->getNumRows() ==0)) // check that table exists...
	{
		echo '<span style="color:red">'.JText::sprintf('Failed checking existance of table [#__%s]',$tableName).'</span><br />';
		echo JText::_ ('DO NOT WORRY... Surely you make a clean install of JoomLeague 1.5 or the table in your DB was empty!!!');
		echo '<br /><br />';
		return false;
	}
	return true;
}

function getVersion()
{
	$db = JFactory::getDBO();
	$query='SELECT * FROM #__joomleague_version ORDER BY date DESC ';
	$db->setQuery($query);
	$result=$db->loadObject();
	if (!$result){return '';}
	return $result->version;
}

function getUpdatePart()
{
	$option = JRequest::getCmd('option');;

	$mainframe = JFactory::getApplication();
	$update_part=$mainframe->getUserState($option.'update_part');
	if ($update_part =='')
	{
		$update_part=1;
	}
	#return 1;
	return $update_part;
}

function setUpdatePart($val=1)
{
	$option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
	$update_part=$mainframe->getUserState($option.'update_part');
	if ($val!=0)
	{
		if ($update_part=='')
		{
			$update_part=1; //1;
		}
		else
		{
			$update_part++;
		}
	}
	else
	{
		$update_part=0;
	}
	$mainframe->setUserState($option.'update_part',$update_part);
}

function TruncateTablesForDevelopment()
{
	$db = JFactory::getDBO();

	echo '<b>'.JText::_('Truncating some tables for a clean update').'</b><br /><br />';

	$query='TRUNCATE TABLE `#__joomleague_club`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_division`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_eventtype`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_league`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_match`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_match_event`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_match_player`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_match_referee`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_match_staff`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_person`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_playground`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_position`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_position_eventtype`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_project`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_project_position`'; $db->setQuery($query); $db->query();

	$query='ALTER TABLE `#__joomleague_project_position`  AUTO_INCREMENT =1000'; $db->setQuery($query); $db->query();

	$query='TRUNCATE TABLE `#__joomleague_project_referee`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_project_team`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_round`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_season`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_team`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_team_player`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_team_staff`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_template_config`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_team_trainingdata`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_prediction_game`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_prediction_admin`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_prediction_member`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_prediction_project`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_prediction_result`'; $db->setQuery($query); $db->query();
	$query='TRUNCATE TABLE `#__joomleague_prediction_template`'; $db->setQuery($query); $db->query();

	echo addGhostPlayer();
	return '';
}

function HandleVersion()
{
	$db = JFactory::getDBO();
	$query='SHOW TABLES LIKE "#__joomleague_versions"';
	$db->setQuery($query);
	if (!$db->query())
	{
		echo JText::_('Renaming and creating new table #__joomleague_version');

		$query='RENAME TABLE `#__joomleague_version` TO `#__joomleague_versions`';
		$db->setQuery($query);
		$db->query();
	}

	$query="
CREATE  TABLE IF NOT EXISTS `#__joomleague_version` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `major` INT NOT NULL ,
  `minor` INT NOT NULL ,
  `build` INT NOT NULL ,
  `count` INT NOT NULL ,
  `revision` VARCHAR(128) NOT NULL ,
  `file` VARCHAR(255) NOT NULL DEFAULT '' ,
  `date` TIMESTAMP NOT NULL ,
  `version` VARCHAR(255) NOT NULL DEFAULT '' ,
  PRIMARY KEY (`id`)
  )
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8;
	";
	$db->setQuery($query);
	$result=$db->query();

	echo PrintStepResult($result).'<br />';
	if (!$result){echo JText::_ ('JL_DB_UPDATE_DO_NOT_WORRY_GP').'<br />';}

	return '';
}

function HandlePositionEventtype()
{
	$db = JFactory::getDBO();

	$query='select count(*) from `#__joomleague_position_eventtypes` WHERE 1';
	$db->setQuery($query);
	if ($db->query()){return '';}

	echo JText::_('Renaming and creating new table #__joomleague_position_eventtype');

	$query='RENAME TABLE `#__joomleague_position_eventtype` TO `#__joomleague_position_eventtypes`';
	$db->setQuery($query);
	$db->query();

	$query="
CREATE  TABLE IF NOT EXISTS `#__joomleague_position_eventtype` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `position_id` INT(11) NOT NULL DEFAULT '0' ,
  `eventtype_id` INT(11) NOT NULL DEFAULT '0' ,
  `ordering` INT(11) NOT NULL DEFAULT '0' ,
  `checked_out` INT(11) NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  PRIMARY KEY (`id`) ,
  KEY `position_id` (`position_id`),
  KEY `eventtype_id` (`eventtype_id`),
  UNIQUE INDEX `pos_et` (`position_id` ASC, `eventtype_id` ASC)
  )
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8;
	";
	$db->setQuery($query);
	$result=$db->query();

	echo PrintStepResult($result).'<br />';
	if (!$result){echo JText::_ ('JL_DB_UPDATE_DO_NOT_WORRY_GP').'<br />';}
	return '';
}

function HandleTemplateConfig()
{
	$db = JFactory::getDBO();

	$query='select count(*) from `#__joomleague_template_configs` WHERE 1';
	$db->setQuery($query);
	if ($db->query()){return '';}

	echo JText::_('Renaming and creating new table #__joomleague_template_config');

	$query='RENAME TABLE `#__joomleague_template_config` TO `#__joomleague_template_configs`';
	$db->setQuery($query);
	$db->query();

	$query="
CREATE  TABLE IF NOT EXISTS `#__joomleague_template_config` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `project_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' ,
  `template` VARCHAR(64) NOT NULL DEFAULT '' ,
  `func` VARCHAR(64) NOT NULL DEFAULT '' ,
  `title` VARCHAR(255) NOT NULL DEFAULT '' ,
  `params` TEXT NOT NULL ,
  `published` INT(1) UNSIGNED NOT NULL DEFAULT '1' ,
  `checked_out` INT(11) UNSIGNED NOT NULL DEFAULT '0' ,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
  )
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8;
	";
	$db->setQuery($query);
	$result=$db->query();

	echo PrintStepResult($result).'<br />';
	if (!$result){echo JText::_ ('JL_DB_UPDATE_DO_NOT_WORRY_GP').'<br />';}

	return '';
}

function addSportsType()
{
	$result=false;
	$db = JFactory::getDBO();

	echo JText::sprintf	(	'Adding the sports-type [%1$s] to table [%2$s] if it does not exist yet!',
							'<strong>'.'Soccer'.'</strong>',
							'<strong>'.'#__joomleague_sports_type'.'</strong>'
						);

	$query="SELECT id FROM #__joomleague_sports_type WHERE name='Soccer'";
	$db->setQuery($query);
	if (!$dbresult=$db->loadObject())
	{
		// Add new sportstype Soccer to #__joomleague_sports_type
		$queryAdd="INSERT INTO #__joomleague_sports_type (`name`) VALUES ('Soccer')";
		$db->setQuery($queryAdd);
		$result=$db->query();
	}

	echo PrintStepResult($result).'<br />';
	if (!$result){echo Jtext::_ ('DO NOT WORRY... Surely the sports-type soccer was already existing in your database!!!').'<br />';}

	return '';
}

function LeaguesToLeague()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;

	echo JText::sprintf(	'Copying --> Table content of [%1$s] to [%2$s]',
							 '<b>joomleague_leagues</b>','<b>joomleague_league</b>');

	$query='SELECT * FROM #__joomleague_leagues ORDER by ordering,name'; $db->setQuery($query);

	if ($leagues=$db->loadObjectList()) // get old leagues...
	{
		foreach ($leagues as $league)
		{
			$query='SELECT id FROM #__joomleague_league WHERE tmp_old_id='.$league->id; $db->setQuery($query);
			if (! $object=$db->loadObject()) // check if league already exists in joomleague_league...
			{
				$query='SELECT * FROM #__joomleague_countries WHERE countries_id="'.$league->country.'"'; $db->setQuery($query);
				if ($country=$db->loadObject()) // get new Country-code...
				{
					$countries_iso_code_3=$country->countries_iso_code_3;
				}
				else
				{
					$countries_iso_code_3='';
				}

				if (!$copied){echo '<br />';}
				$copied=true;
				$result=true;

				$query="	INSERT INTO	#__joomleague_league
											(	`name`,
												`country`,
												`ordering`,
												`tmp_old_id`
											)
										VALUES
											(	'$league->name',
												'$countries_iso_code_3',
												'$league->ordering',
												'$league->id'
											)";
				$db->setQuery($query);

				if (!$db->query())
				{
					$result=false;
					echo PrintStepResult($result).'<br />';
					echo '<br />'.$db->getErrorMsg().' <br />';
				}
				else
				{
					$i++;
				}
			}
			else // league already exists
			{
				$result=false;
				echo JText::sprintf('Notice: League %1$s already exists','<b>'.$league->name.'</b>').'<br />';
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}

	if ($i==count($leagues)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><b style="color:'.$color.';" >';
	echo JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($leagues));
	echo '</b><br /><br />';

	return '';
}

function SeasonsToSeason()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;

	echo JText::sprintf(	'Copying --> Table content of [%1$s] to [%2$s]',
							 '<b>joomleague_seasons</b>','<b>joomleague_season</b>');

	$query='SELECT * FROM #__joomleague_seasons ORDER by ordering,name'; $db->setQuery($query);

	if ($seasons=$db->loadObjectList()) // get old seasons...
	{
		foreach ($seasons as $season)
		{
			$query='SELECT id FROM #__joomleague_season WHERE tmp_old_id='.$season->id; $db->setQuery($query);
			if (!$object=$db->loadObject()) // check if season already exists in joomleague_season...
			{
				if (!$copied){echo '<br />';}
				$copied=true;
				$result=true;
				$alias=JFilterOutput::stringURLSafe($season->name);

				$query="	INSERT INTO	#__joomleague_season
											(	`name`,
												`alias`,
												`ordering`,
												`tmp_old_id`
											)
										VALUES
											(	'$season->name',
												'$alias',
												'$season->ordering',
												'$season->id'
											)";
				$db->setQuery($query);

				if (!$db->query())
				{
					$result=false;
					echo PrintStepResult($result).'<br />';
					echo '<br />'.$db->getErrorMsg().' <br />';
				}
				else
				{
					$i++;
				}
			}
			else // season already exists
			{
				$result=false;
				echo JText::sprintf('Notice: Season %1$s already exists','<b>'.$season->name.'</b>').'<br />';
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}

	if ($i==count($seasons)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><b style="color:'.$color.';" >';
	echo JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($seasons));
	echo '</b><br /><br />';

	return '';
}

function EventtypesToEventtype()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;

	echo JText::sprintf(	'Copying --> Table content of [%1$s] to [%2$s]',
							 '<b>joomleague_eventtypes</b>','<b>joomleague_eventtype</b>');

	$query="SELECT id FROM #__joomleague_sports_type WHERE name='Soccer' "; $db->setQuery($query);	$SportsTypeID=$db->loadResult();
	$query="SELECT id FROM #__joomleague_eventtype WHERE sports_type_id=".$SportsTypeID; $db->setQuery($query);	$order=count($db->loadObjectList());

	$query='SELECT * FROM #__joomleague_eventtypes ORDER by name';
	$db->setQuery($query);

	if ($eventtypes=$db->loadObjectList()) // get old eventtypes...
	{
		foreach ($eventtypes as $eventtype)
		{
			$order++;
			
/*
* diddipoeler: diddipoeler 
* date: change on 13.01.2011
* Bugtracker Backend Bug #579
* old_position to new_position					
*/      
      //$eventtype->name='!u!-'.$eventtype->name;
      $eventtype->name = $eventtype->name;

			if (!$copied){echo '<br />';}
			$copied=true;
			$result=true;

			$pos=strpos($eventtype->icon,'/');
			if ($pos ===false)
			{
				$eventtype->icon='media/com_joomleague/events/'.$eventtype->icon;
			}

			$alias=JFilterOutput::stringURLSafe($eventtype->name);

			$query="	INSERT INTO	#__joomleague_eventtype
										(
											`name`,
											`alias`,
											`icon`,
											`splitt`,
											`direction`,
											`double`,
											`sports_type_id`,
											`ordering`,
											`tmp_old_id`
										)
									VALUES
										(
											'$eventtype->name',
											'$alias',
											'$eventtype->icon',
											'$eventtype->splitt',
											'$eventtype->direction',
											'$eventtype->double',
											'$SportsTypeID',
											'$order',
											'$eventtype->id'
										)";
			$db->setQuery($query);

			if (!$db->query())
			{
				$result=false;
				echo PrintStepResult($result).'<br />';
				echo '<br />'.$db->getErrorMsg().' <br />';
			}
			else
			{
				$i++;
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}

	if ($i==count($eventtypes)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><b style="color:'.$color.';" >';
	echo JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($eventtypes));
	echo '</b><br /><br />';

	return '';
}

function PositionsToPosition()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;

	echo JText::sprintf(	'Copying --> Table content of [%1$s] to [%2$s]',
							 '<b>joomleague_positions</b>','<b>joomleague_position</b>');

	$query="SELECT id FROM #__joomleague_sports_type WHERE name='SOCCER' "; $db->setQuery($query); $SportsTypeID=$db->loadResult();
	$query="SELECT id FROM #__joomleague_position WHERE sports_type_id=".$SportsTypeID; $db->setQuery($query); $order=count($db->loadObjectList());

	// Get new id of parent position JL_F_PLAYERS
	$query="	SELECT id
				FROM #__joomleague_position
				WHERE parent_id=0 AND name='JL_F_PLAYERS' AND sports_type_id=".$SportsTypeID." AND persontype=1";

	$db->setQuery($query);
	$ParentPosPlayersID=$db->loadResult();

	// Get new id of parent position JL_F_TEAM_STAFF
	$query="	SELECT id
				FROM #__joomleague_position
				WHERE parent_id=0 AND name='JL_F_TEAM_STAFF' AND sports_type_id=".$SportsTypeID." AND persontype=2";

	$db->setQuery($query);
	$ParentPosTeamStaffID=$db->loadResult();

	$query='SELECT * FROM #__joomleague_positions ORDER by ordering,isStaff,name'; $db->setQuery($query);

	if ($positions=$db->loadObjectList()) // get old positions...
	{
		foreach ($positions as $position)
		{
			$order++;
			
/*
* developer: diddipoeler 
* date: change on 13.01.2011
* Bugtracker Backend Bug #579
* old_position to new_position					
*/ 
			//$position->name='!u!-'.$position->name;
			$position->name=$position->name;

			if (!$copied){echo '<br />';}
			$copied=true;
			$result=true;
			if ($position->isStaff ==1)
			{
				$persontype=2;
				$parentID=$ParentPosTeamStaffID;
			}
			else
			{
				$persontype=1;
				$parentID=$ParentPosPlayersID;
			}

			$alias=JFilterOutput::stringURLSafe($position->name);

			$query="	INSERT INTO	#__joomleague_position
										(
											`name`,
											`alias`,
											`parent_id`,
											`persontype`,
											`sports_type_id`,
											`published`,
											`ordering`,
											`tmp_old_pos_id`
										)
									VALUES
										(
											'$position->name',
											'$alias',
											'$parentID',
											'$persontype',
											'$SportsTypeID',
											'1',
											'$order',
											'$position->id'
										)";
			$db->setQuery($query);

			if (!$db->query())
			{
				$result=false;
				echo PrintStepResult($result).'<br />';
				echo '<br />'.$db->getErrorMsg().' <br />';
			}
			else
			{
				$i++;
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}

	if ($i==count($positions)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><b style="color:'.$color.';" >';
	echo JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($positions));
	echo '</b><br /><br />';

	return '';
}

function PositionEventtypesToPositionEventtype()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;

	echo JText::sprintf('Copying --> Table content of [%1$s] to [%2$s]','<b>joomleague_position_eventtypes</b>','<b>joomleague_position_eventtype</b>');

	$query='	SELECT	p.id AS newPosID,
						p.name AS posName,
						e.id AS newEventID,
						e.name AS eventName,
						pe.*
				FROM #__joomleague_position_eventtypes AS pe
				LEFT JOIN #__joomleague_position AS p ON pe.position_id=p.tmp_old_pos_id
				LEFT JOIN #__joomleague_eventtype AS e ON pe.eventtype_id=e.tmp_old_id
				ORDER by pe.ordering';
	$db->setQuery($query);
	
/*
* developer: diddipoeler 
* date: change on 13.01.2011
* Bugtracker Backend Bug #579
* old_position to new_position					
*/
  $posevents=$db->loadObjectList();
	
	if ($posevents) // get old positions...
	{
		if(count($posevents) > 0)
		{
			foreach ($posevents as $posevent)
			{
				$query='	SELECT name
							FROM #__joomleague_position
							WHERE tmp_old_pet_id='.$posevent->position_eventtype_id;
				$db->setQuery($query);
				if (! $object=$db->loadObject()) // check if position_eventtype already exists in joomleague_position_eventtype...
				{
					if (!$copied){echo '<br />';}
					$copied=true;
					$result=true;
					$query="	INSERT INTO	#__joomleague_position_eventtype
												(
													`position_id`,
													`eventtype_id`,
													`ordering`,
													`tmp_old_pet_id`
												)
											VALUES
												(
													'$posevent->newPosID',
													'$posevent->newEventID',
													'$posevent->ordering',
													'$posevent->position_eventtype_id'
												)";
					$db->setQuery($query);
					if (!$db->query())
					{
						$result=false;
						echo PrintStepResult($result).'<br />';
						echo '<br />'.$db->getErrorMsg().' <br />';
					}
					else
					{
						$i++;
					}
				}
				else // position_eventtype already exists
				{
					$result=false;
					echo JText::sprintf(	'Notice: position_eventtype %1$s - %2$s already exists',
											'<b>'.$posevent->posName.'</b>',
											'<b>'.$posevent->eventName.'</b>').'<br />';
				}
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}
	if ($i==count($posevents)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><b style="color:'.$color.';" >';
	echo JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($posevents));
	echo '</b><br /><br />';
	return '';
}

function PlaygroundsToPlayground()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;

	echo JText::sprintf('Copying --> Table content of [%1$s] to [%2$s]','<b>joomleague_playgrounds</b>','<b>joomleague_playground</b>');

	$query='SELECT * FROM #__joomleague_playgrounds ORDER by country,name';
	$db->setQuery($query);
	if ($playgrounds=$db->loadObjectList()) // get old playgrounds...
	{
		foreach ($playgrounds as $playground)
		{
			$query='SELECT id FROM #__joomleague_playground WHERE tmp_old_id='.$playground->id;
			$db->setQuery($query);
			if (!$dbresult=$db->loadResult()) // check if playground already exists in joomleague_playground...
			{
				$query='SELECT * FROM #__joomleague_countries WHERE countries_id="'.$playground->country.'"';
				$db->setQuery($query);
				if ($country=$db->loadObject()) // get new Country-code...
				{
					$countries_iso_code_3=$country->countries_iso_code_3;
				}
				else
				{
					$countries_iso_code_3='';
				}
				$query='SELECT id AS clubID FROM #__joomleague_club WHERE tmp_old_id='.$playground->club_id;
				$db->setQuery($query);
				if ($club=$db->loadObject()) // get new club_id...
				{
					$club_id=$club->clubID;
				}
				else
				{
					$club_id=0;
				}

				if (!$copied){echo '<br />';}
				$copied=true;
				$result=true;
				$alias=JFilterOutput::stringURLSafe($playground->name);

				$query="	INSERT INTO	#__joomleague_playground
											(
												`name`,
												`short_name`,
												`alias`,
												`address`,
												`zipcode`,
												`city`,
												`country`,
												`max_visitors`,
												`website`,
												`picture`,
												`notes`,
												`club_id`,
												`tmp_old_id`
											)
										VALUES
											(
												'".addslashes(stripslashes($playground->name))."',
												'".addslashes(stripslashes($playground->short_name))."',
												'$alias',
												'".addslashes(stripslashes($playground->address))."',
												'$playground->zip',
												'".addslashes(stripslashes($playground->city))."',
												'$countries_iso_code_3',
												'$playground->max_visitors',
												'$playground->link',
												'$playground->picture',
												'".addslashes(stripslashes($playground->description))."',
												'$playground->club_id',
												'$playground->id'
											)";
				$db->setQuery($query);
				if (!$db->query())
				{
					$result=false;
					echo PrintStepResult($result).'<br />';
					echo '<br />'.$db->getErrorMsg().' <br />';
				}
				else
				{
					$i++;
				}
			}
			else // playground already exists
			{
				$result=false;
				echo JText::sprintf('Notice: Playground %1$s already exists','<b>'.$playground->name.'</b>').'<br />';
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}

	if ($i==count($playgrounds)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><b style="color:'.$color.';" >';
	echo JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($playgrounds));
	echo '</b><br /><br />';

	return '';
}

function ConvertPlaygroundClubID()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;

	echo JText::sprintf(	'Updating ClubIDs inside table [%1$s]',
							'<b>joomleague_playground</b>').'<br />';

	$query='SELECT * FROM #__joomleague_playground';
	$db->setQuery($query);
	if ($playgrounds=$db->loadObjectList()) // get all playground data...
	{
		foreach ($playgrounds as $playground)
		{
			$query='SELECT id FROM #__joomleague_club WHERE tmp_old_id='.$playground->club_id;

			$db->setQuery($query);
			if ($object=$db->loadObject()) // check if club data exists in joomleague_club...
			{
				$query="UPDATE #__joomleague_playground SET club_id='".$object->id."' WHERE id='".$playground->id."'";
				$db->setQuery($query);

				if (!$db->query())
				{
					$result=false;
					echo PrintStepResult($result).'<br />';
					echo '<br />'.$db->getErrorMsg().' <br />';
				}
				else
				{
					$i++;
				}
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}

	if ($i==count($playgrounds)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><b style="color:'.$color.';" >';
	echo JText::sprintf('Updated %1$s of %2$s old records',$i,count($playgrounds));
	echo '</b><br /><br />';

	return '';
}

function ClubsToClub()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;

	echo JText::sprintf('Copying --> Table content of [%1$s] to [%2$s]','<b>joomleague_clubs</b>','<b>joomleague_club</b>');

	$query='	SELECT	pg.id AS newpgID,
						c.*
				FROM #__joomleague_clubs AS c
				LEFT JOIN #__joomleague_playground AS pg ON pg.tmp_old_id=c.standard_playground
				ORDER by c.name,c.country';
	$db->setQuery($query);

	if ($clubs=$db->loadObjectList()) // get old clubs...
	{
		foreach ($clubs as $club)
		{
			$query='SELECT id FROM #__joomleague_club WHERE tmp_old_id='.$club->id;
			$db->setQuery($query);
			if (!$object=$db->loadResult()) // check if club already exists in joomleague_club...
			{
				$query="SELECT * FROM #__joomleague_countries WHERE countries_id='$club->country'";
				$db->setQuery($query);
				if ($country=$db->loadObject()) // get new Country-code...
				{
					$countries_iso_code_3="'".$country->countries_iso_code_3."'";
				}
				else
				{
					$countries_iso_code_3='NULL';
				}
				if (!$copied){echo '<br />';}
				$copied=true;
				$result=true;
				$alias=JFilterOutput::stringURLSafe($club->name);
				$dFounded=(!empty($club->founded)) ? "'".$club->founded."'" : 'NULL';
				$dPlaygroundID=(!empty($club->newpgID)) ? "'".$club->newpgID."'" : 'NULL';
				$query="	INSERT INTO	#__joomleague_club
											(
												`name`,
												`alias`,
												`admin`,
												`address`,
												`zipcode`,
												`location`,
												`state`,
												`country`,
												`founded`,
												`phone`,
												`fax`,
												`email`,
												`website`,
												`president`,
												`manager`,
												`logo_big`,
												`logo_middle`,
												`logo_small`,
												`standard_playground`,
												`tmp_old_id`
											)
										VALUES
											(
												'".addslashes(stripslashes($club->name))."',
												'$alias',
												'62',
												'".addslashes(stripslashes($club->address))."',
												'$club->zipcode',
												'".addslashes(stripslashes($club->location))."',
												'".addslashes(stripslashes($club->state))."',
												$countries_iso_code_3,
												$dFounded,
												'$club->phone',
												'$club->fax',
												'$club->email',
												'$club->website',
												'".addslashes(stripslashes($club->president))."',
												'".addslashes(stripslashes($club->manager))."',
												'$club->logo_big',
												'$club->logo_middle',
												'$club->logo_small',
												$dPlaygroundID,
												'$club->id'
											)";
				$db->setQuery($query);
				if (!$db->query())
				{
					$result=false;
					echo PrintStepResult($result).'<br />';
					echo '<br />'.$db->getErrorMsg().' <br />';
				}
				else
				{
					$i++;
				}
			}
			else // club already exists
			{
				$result=false;
				echo JText::sprintf('Notice: Club %1$s already exists','<b>'.$team->name.'</b>');
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}
	if ($i==count($clubs)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><b style="color:'.$color.';" >';
	echo JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($clubs));
	echo '</b><br /><br />';
	return '';
}

function TeamsToTeam()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;

	echo JText::sprintf('Copying --> Table content of [%1$s] to [%2$s]','<b>joomleague_teams</b>','<b>joomleague_team</b>');

	$query='SELECT * FROM #__joomleague_teams ORDER by club_id,name';
	$db->setQuery($query);
	if ($teams=$db->loadObjectList()) // get old teams...
	{
		foreach ($teams as $team)
		{
			$query='SELECT id FROM #__joomleague_team WHERE tmp_old_team_id='.$team->id;
			$db->setQuery($query);
//echo '<pre>'.print_r($query,true).'</pre>';
			if (!$dbresult=$db->loadResult()) // check if team already exists in joomleague_team...
			{
				$query='SELECT id AS clubID FROM #__joomleague_club WHERE tmp_old_id='.$team->club_id;
				$db->setQuery($query);

				if ($club=$db->loadObject()) // get new club_id...
				{
					$club_id=$club->clubID;
				}
				else
				{
					$club_id=0;
				}
				if (!$copied){echo '<br />';}
				$copied=true;
				$result=true;
				$alias=JFilterOutput::stringURLSafe($team->name);

				$query="	INSERT INTO	#__joomleague_team
											(
												`club_id`,
												`name`,
												`short_name`,
												`middle_name`,
												`alias`,
												`info`,
												`tmp_old_team_id`
											)
										VALUES
											(
												'$club_id',
												'".addslashes(stripslashes($team->name))."',
												'".addslashes(stripslashes($team->short_name))."',
												'".addslashes(stripslashes($team->middle_name))."',
												'$alias',
												'".addslashes(stripslashes($team->description))."',
												'$team->id'
											)";
				$db->setQuery($query);

				if (!$db->query())
				{
					$result=false;
					echo PrintStepResult($result).'<br />';
					echo '<br />'.$db->getErrorMsg().' <br />';
				}
				else
				{
					$i++;
				}
			}
			else // team already exists
			{
				if (!$copied){echo '<br />';}
				$copied=true;
				$result=false;
				echo JText::sprintf('Notice: Team %1$s already exists','<b>'.$team->name.'</b>').'<br />';
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}

	if ($i==count($teams)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><b style="color:'.$color.';" >';
	echo JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($teams));
	echo '</b><br /><br />';

	return '';
}

function PlayersToPerson()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;

	echo JText::sprintf('Copying --> Table content of [%1$s] to [%2$s]','<b>joomleague_players</b>','<b>joomleague_person</b>');

	$query='SELECT * FROM #__joomleague_players ORDER by lastname,firstname,birthday,nation';
	$db->setQuery($query);
	
	if ($players=$db->loadObjectList()) // get old players...
	{
	
		foreach ($players as $person)
		{
			$query='SELECT id FROM #__joomleague_person WHERE tmp_old_player_id='.$person->id;
			$db->setQuery($query);
			
//echo '<pre>'.print_r($query,true).'</pre>';
			if (!$dbresult=$db->loadResult()) // check if person already exists in joomleague_peson...
			{
				$query="SELECT * FROM #__joomleague_countries WHERE countries_id='$person->nation'";
				$db->setQuery($query);
				if ($country=$db->loadObject()) // get new Country-code...
				{
					$countries_iso_code_3="'".$country->countries_iso_code_3."'";
				}
				else
				{
					$countries_iso_code_3='NULL';
				}

				$query="SELECT id FROM #__joomleague_position WHERE tmp_old_pos_id='$person->default_position_id'";
				$db->setQuery($query);
				
/*
* developer: diddipoeler 
* date: change on 13.01.2011
* Bugtracker Backend Bug #579
* old_position to new_position					
*/        
        $dResult=$db->loadResult();
				
//				if ( !$dResult=$db->loadResult() ) // get new Position id...
				if ( $dResult ) // get new Position id...
				{
                  
					if (!empty($person->default_position_id) && !empty($dResult))
					{
						$positionID="'$dResult'";
              
					}
					else
					{
						$positionID='NULL';
							
					}
				}
				else
				{
					$positionID='NULL';
						
				}

        $dHeight=(!empty($person->default_height)) ? "'".$person->default_height."'" : 'NULL';
				$dWeight=(!empty($person->default_weight)) ? "'".$person->default_weight."'" : 'NULL';

				if (!$copied){echo '<br />';}
				$copied=true;
				$result=true;
				$alias=JFilterOutput::stringURLSafe($person->firstname.' '.$person->lastname);

				$query="	INSERT INTO	#__joomleague_person
											(
												`user_id`,
												`firstname`,
												`lastname`,
												`alias`,
												`picture`,
												`height`,
												`weight`,
												`birthday`,
												`country`,
												`info`,
												`notes`,
												`position_id`,
												`published`,
												`tmp_old_player_id`
											)
										VALUES
											(
												'$person->jl_user_id',
												'".addslashes(stripslashes($person->firstname))."',
												'".addslashes(stripslashes($person->lastname))."',
												'$alias',
												'$person->default_picture',
												$dHeight,
												$dWeight,
												'$person->birthday',
												$countries_iso_code_3,
												'".addslashes(stripslashes($person->info))."',
												'".addslashes(stripslashes($person->description))."',
												$positionID,
												'1',
												'$person->id'
											)";
				$db->setQuery($query);

				if (!$db->query())
				{
					$result=false;
					echo PrintStepResult($result).'<br />';
					echo '<br />'.$db->getErrorMsg().' <br />';
				}
				else
				{
					$i++;
				}
			}
			else // person already exists
			{
				if (!$copied){echo '<br />';}
				$copied=true;
				$result=false;
				echo JText::sprintf(	'Notice: Person %1$s %2$s already exists',
										'<b>'.$person->firstname.'</b>',
										'<b>'.$person->lastname.'</b>').'<br />';
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}

	if ($i==count($players)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><b style="color:'.$color.';" >';
	echo JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($players));
	echo '</b><br /><br />';

	return '';
}

function RefereesToPerson()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;

	echo JText::sprintf(	'Copying --> Table content of [%1$s] to [%2$s]',
							 '<b>joomleague_referees</b>','<b>joomleague_person</b>');

	$query='SELECT * FROM #__joomleague_referees ORDER by lastname,firstname,birthday,nation';
	$db->setQuery($query);
	if ($referees=$db->loadObjectList()) // get old players...
	{
		foreach ($referees as $person)
		{
			$query='	SELECT	firstname,
								lastname,
								birthday
						FROM #__joomleague_person
						WHERE	firstname="'.$person->firstname.'" AND
								lastname="'.$person->lastname.'" AND
								birthday="'.$person->birthday.'" ';

			$db->setQuery($query);
			if (!$object=$db->loadObject()) // check if person already exists in joomleague_peson...
			{
				$query='SELECT * FROM #__joomleague_countries WHERE countries_id="'.$person->nation.'"';
				$db->setQuery($query);

				if ($country=$db->loadObject()) // get new Country-code...
				{
					$countries_iso_code_3=$country->countries_iso_code_3;
				}
				else
				{
					$countries_iso_code_3='';
				}

				if (!$copied){echo '<br />';}
				$copied=true;
				$result=true;
				$alias=JFilterOutput::stringURLSafe($person->firstname.' '.$person->lastname);

				$query="	INSERT INTO	#__joomleague_person
											(
												`firstname`,
												`lastname`,
												`alias`,
												`picture`,
												`birthday`,
												`country`,
												`notes`,
												`tmp_old_referee_id`
											)
										VALUES
											(
												'".addslashes(stripslashes($person->firstname))."',
												'".addslashes(stripslashes($person->lastname))."',
												'$alias',
												'$person->picture',
												'$person->birthday',
												'$countries_iso_code_3',
												'".addslashes(stripslashes($person->description))."',
												'$person->id'
											)";
				$db->setQuery($query);

				if (!$db->query())
				{
					$result=false;
					echo PrintStepResult($result).'<br />';
					echo '<br />'.$db->getErrorMsg().' <br />';
				}
				else
				{
					$i++;
				}
			}
			else // person already exists
			{
				$result=false;
				echo JText::sprintf(	'Notice: Referee %1$s %2$s already exists',
										'<b>'.$person->firstname.'</b>',
										'<b>'.$person->lastname.'</b>').'<br />';
			}
		}

	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}

	if ($i==count($referees)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><b style="color:'.$color.';" >';
	echo JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($referees));
	echo '</b><br /><br />';

	return '';
}

function JoomleagueToProject()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;

	echo JText::sprintf('Copying --> Table content of [%1$s] to [%2$s]','<b>joomleague</b>','<b>joomleague_project</b>');
	$query='SELECT * FROM #__joomleague ORDER by season_id,league_id,published,name';
	$db->setQuery($query);
	if ($projects=$db->loadObjectList()) // get old projects...
	{
		foreach ($projects as $project)
		{
			$query='SELECT id FROM #__joomleague_project WHERE tmp_old_pid='.$project->id;

			$db->setQuery($query);
			if (!$dbresult=$db->loadResult()) // check if project already exists in joomleague_project...
			{
				if (!$copied){echo '<br />';}
				$query='SELECT id AS leagueID FROM #__joomleague_league WHERE tmp_old_id='.$project->league_id;
				$db->setQuery($query);
				if ($league=$db->loadObject()) // get new league_id...
				{
					$league_id=$league->leagueID;
				}
				else
				{
					$league_id=null;
				}

				$query='SELECT id AS seasonID FROM #__joomleague_season WHERE tmp_old_id='.$project->season_id;
				$db->setQuery($query);
				if ($season=$db->loadObject()) // get new season_id...
				{
					$season_id=$season->seasonID;
				}
				else
				{
					$season_id=null;
				}

				// Convert fav team ids here
				// -------------------------------
					$newFavTeams='';
					$favteams=explode(",",$project->fav_team);
					for ($x=0; $x < (count($favteams)); $x++)
					{
						if (isset($favteams[$x]) && !empty($favteams[$x]))
						{
							$value=$favteams[$x];

							$query='SELECT id FROM #__joomleague_team WHERE tmp_old_team_id='.$favteams[$x];
							$db->setQuery($query);
							if ($dproject=$db->loadResult()) // get new project_id...
							{
								if ($newFavTeams!='') {$newFavTeams=$newFavTeams.',';}
								$newFavTeams .= $dproject;
							}
						}
					}
				// -------------------------------

				$copied=true;
				$result=true;
				$alias=JFilterOutput::stringURLSafe($project->name);

				$query="	INSERT INTO	#__joomleague_project
											(	`name`,
												`alias`,
												`league_id`,
												`season_id`,
												`admin`,
												`editor`,
												`master_template`,

												`serveroffset`,
												`project_type`,
												`teams_as_referees`,

												`start_date`,

												`current_round_auto`,
												`current_round`,
												`auto_time`,
												`game_regular_time`,
												`game_parts`,
												`halftime`,
												`points_after_regular_time`,
												`use_legs`,
												`allow_add_time`,
												`add_time`,
												`points_after_add_time`,
												`points_after_penalty`,
												`fav_team`,
												`fav_team_color`,

												`template`,
												`enable_sb`,
												`sb_catid`,
												`published`,
												`tmp_old_pid`
											)
										VALUES
											(	'".addslashes(stripslashes($project->name))."',
												'$alias',
												'$league_id',
												'$season_id',
												'$project->joomleague_admin',
												'$project->joomleague_editor',
												'$project->master_template',

												'$project->serveroffset',
												'$project->project_type',
												'$project->teams_as_referees',

												'$project->start_date',

												'$project->current_round_auto',
												'$project->current_round',
												'$project->auto_time',
												'$project->game_regular_time',
												'$project->game_parts',
												'$project->halftime',
												'$project->points_after_regular_time',
												'$project->use_legs',
												'$project->allow_add_time',
												'$project->add_time',
												'$project->points_after_add_time',
												'$project->points_after_penalty',
												'$newFavTeams',
												'$project->fav_team_color',

												'$project->template',
												'$project->enable_sb',
												'$project->sb_catid',
												'$project->published',
												'$project->id'
											)";
				$db->setQuery($query);
				if (!$db->query())
				{
					$result=false;
					echo PrintStepResult($result).'<br />';
					echo '<br />'.$db->getErrorMsg().' <br />';
				}
				else
				{
					$i++;
					$new_project_id=$db->insertid();
					$query="UPDATE #__joomleague_project SET ordering='".$new_project_id."' WHERE id='".$new_project_id."'";
					$db->setQuery($query);
					$db->query();
				}
			}
			else // project already exists
			{
				$result=false;
				echo JText::sprintf('Notice: Project %1$s already exists','<b>'.$project->name.'</b>').'<br />';
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}

	if ($i==count($projects)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><b style="color:'.$color.';" >';
	echo JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($projects));
	echo '</b><br /><br />';

	return '';
}

function DivisionsToDivision()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;

	echo JText::sprintf('Copying --> Table content of [%1$s] to [%2$s]','<b>joomleague_divisions</b>','<b>joomleague_division</b>');

	$query='SELECT * FROM #__joomleague_divisions ORDER by ordering,project_id,name';
	$db->setQuery($query);

	if ($divisions=$db->loadObjectList()) // get old divisions...
	{
		foreach ($divisions as $division)
		{
			$query='SELECT id AS projectID FROM #__joomleague_project WHERE tmp_old_pid='.$division->project_id;
			$db->setQuery($query);
			if ($project=$db->loadObject()) // get new project_id...
			{
				$project_id=$project->projectID;
			}
			else
			{
				$project_id=0;
			}

			if (!empty($project_id))
			{
				$query='SELECT id FROM #__joomleague_division WHERE tmp_old_division_id='.$division->id;
				$db->setQuery($query);
				if (!$dbresult=$db->loadResult()) // check if division already exists in joomleague_division...
				{
					$parent_id=0;
					if ($division->parent_id > 0)
					{
						$query='SELECT id FROM #__joomleague_division WHERE tmp_old_division_id='.$division->parent_id;
						$db->setQuery($query);
						if ($parent_division=$db->loadObject()) // get new parent_division_id...
						{
							$parent_id=$parent_division->id;
						}
					}

					$dParent=(!empty($parent_id)) ? "'".$parent_id."'" : 'NULL';
					if (!$copied){echo '<br />';}
					$copied=true;
					$result=true;
					$alias=JFilterOutput::stringURLSafe($division->name);

					$query="	INSERT INTO	#__joomleague_division
												(
													`project_id`,
													`name`,
													`alias`,
													`shortname`,
													`notes`,
													`parent_id`,
													`ordering`,
													`tmp_old_division_id`
												)
											VALUES
												(
													'$project_id',
													'".addslashes(stripslashes($division->name))."',
													'$alias',
													'".addslashes(stripslashes($division->shortname))."',
													'".addslashes(stripslashes($division->description))."',
													$dParent,
													'$division->ordering',
													'$division->id'
												)";
					$db->setQuery($query);

					if (!$db->query())
					{
						$result=false;
						echo PrintStepResult($result).'<br />';
						echo '<br />'.$db->getErrorMsg().' <br />';
					}
					else
					{
						$i++;
					}
				}
				else // division already exists
				{
					$result=false;
					echo JText::sprintf('Notice: Division %1$s already exists','<b>'.$division->name.'</b>').'<br />';
				}
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}

	if ($i==count($divisions)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><b style="color:'.$color.';" >';
	echo JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($divisions));
	echo '</b><br /><br />';

	return '';
}

/*
* step 5
*/
function PosjoomleagueToProjectposition()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;

	echo JText::sprintf(	'Copying --> Table content of [%1$s] to [%2$s]',
							'<b>joomleague_pos_joomleague</b>','<b>joomleague_project_position</b>');

	$query='	SELECT	pos.*,
						pos.project_id AS oldProjectID,
						p.name AS projectName,
						po.id AS newposID,
						po.name AS posName

				FROM #__joomleague_pos_joomleague AS pos
				LEFT JOIN #__joomleague AS p ON pos.project_id=p.id
				LEFT JOIN #__joomleague_position AS po ON po.tmp_old_pos_id=pos.position_id
				ORDER by pos.project_id,pos.position_id';

	$db->setQuery($query);

	if ($projectpositions=$db->loadObjectList()) // get old project positions...
	{
		foreach ($projectpositions as $projectposition)
		{
			
      $query='SELECT id FROM #__joomleague_project WHERE tmp_old_pid='.$projectposition->oldProjectID;
			$db->setQuery($query);
			
      if ($dbresult=$db->loadResult()) // get new project_id...
			{
				$project_id=$dbresult;
			}
			else
			{
				echo '<span style="color:red;">'.JText::sprintf('Skipping projectposition %1$s as it doesn\'n exist',"<b>$projectposition->id</b>").'</span><br />';
				continue; // skip as project was not found
			}
			
			$query='	SELECT position_id, project_id
						FROM #__joomleague_project_position
						WHERE position_id='.$projectposition->newposID.' AND project_id='.$project_id;
			$db->setQuery($query);
			if (!$object=$db->loadObject()) // check if project position already exists in joomleague_project_position...
			{
				if (!$copied){echo '<br />';}
				$copied=true;
				$result=true;
				$unknowndata=false;
				if ($projectposition->projectName==''){$projectposition->projectName='Unknown Project';$unknowndata=true;}
				$copied=true;
				$result=true;
				if ($unknowndata)
				{
					echo JText::sprintf(	'Copying projectpositions - OldProjectID %1$s - %2$s - PosName: %3$s',
											'<b>'.$projectposition->oldProjectID.'</b>',
											'<span style="color:red; ">'.'<b>'.$projectposition->projectName.'</b>'.'</span>',
											'<i>'.$projectposition->posName.'</i>'
											).'<br />';
				}
				else
				{
					$query="	INSERT INTO	#__joomleague_project_position
												(
													`project_id`,
													`position_id`
												)
											VALUES
												(
													'$project_id',
													'$projectposition->newposID'
												)";
					$db->setQuery($query);
					if (!$db->query())
					{
						$result=false;
						echo PrintStepResult($result).'<br />';
						echo '<br />'.$db->getErrorMsg().' <br />';
					}
					else
					{
						$i++;
					}
				}
			}
			else // project_position already exists
			{
				$result=false;
				echo JText::sprintf(	'Notice: Project_position %1$s already exists in project %2$s',
										'<b>'.$projectposition->projectName.'</b>',
										'<b>'.$projectposition->posName.'</b>').'<br />';
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}
	if ($i==count($projectpositions)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><b style="color:'.$color.';" >';
	echo JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($projectpositions));
	echo '</b><br /><br />';
	return '';
}

/*
* step 5
*/
function RoundsToRound()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;

	echo JText::sprintf(	'Copying --> Table content of [%1$s] to [%2$s]',
							 '<b>joomleague_rounds</b>','<b>joomleague_round</b>');

	$query='	SELECT	r.*,
						p.name AS projectName,
						r.project_id AS oldProjectID

				FROM #__joomleague_rounds AS r
				LEFT JOIN #__joomleague AS p ON r.project_id=p.id
				ORDER by project_id,matchcode';

	$db->setQuery($query);

	if ($rounds=$db->loadObjectList()) // get old rounds...
	{
		foreach ($rounds as $round)
		{
			$query='SELECT id AS projectID FROM #__joomleague_project WHERE tmp_old_pid='.$round->project_id;
			$db->setQuery($query);

			if ($project=$db->loadObject()) // get new project_id...
			{
				$project_id=$project->projectID;
			}
			else
			{
				$project_id=0;
			}

			$query='	SELECT *
						FROM #__joomleague_round
						WHERE roundcode='.$round->matchcode.' AND project_id='.$project_id.' AND tmp_old_round_id='.$round->id;

			$db->setQuery($query);
			if (! $object=$db->loadObject()) // check if round already exists in joomleague_round...
			{
				if (!$copied){echo '<br />';}
				$copied=true;
				$result=true;
				$unknowndata=false;

				$alias=JFilterOutput::stringURLSafe($round->name);
				if ($round->projectName==''){$round->projectName='Unknown Project';$unknowndata=true;}
				if ($unknowndata)
				{
					echo JText::sprintf	(	'Copying rounds - OldProjectID %1$s / %2$s / Roundname: %3$s',
											'<b>'.$round->project_id.'</b>',
											'<span style="color:red; ">'.'<b>'.$round->projectName.'</b>'.'</span>',
											'<i>'.$round->name.'</i>'
										).'<br />';
				}
				else
				{
					if ($round->round_date_last =="0000-00-00"){$round->round_date_last=$round->round_date_first;}
					$query="	INSERT INTO	#__joomleague_round
												(
													`project_id`,
													`roundcode`,
													`name`,
													`alias`,
													`round_date_first`,
													`round_date_last`,
													`tmp_old_round_id`
												)
											VALUES
												(
													'$project_id',
													'$round->matchcode',
													'".addslashes(stripslashes($round->name))."',
													'$alias',
													'$round->round_date_first',
													'$round->round_date_last',
													'$round->id'
												)";
					$db->setQuery($query);

					if (!$db->query())
					{
						$result=false;
						echo PrintStepResult($result).'<br />';
						echo '<br />'.$db->getErrorMsg().' <br />';
					}
					else
					{
						$i++;
					}
				}
			}
			else // round already exists
			{
				$result=false;
				echo JText::sprintf(	'Notice: Round %1$s - %2$s already exists',
										'<b>'.$round->projectName.'</b>',
										'<b>'.$round->name.'</b>').'<br />';
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}

	if ($i==count($rounds)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><b style="color:'.$color.';" >';
	echo JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($rounds));
	echo '</b><br /><br />';

	return '';
}


/*
* step 5
*/
function TeamjoomleagueToProjectteam()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;
	echo JText::sprintf('Copying --> Table content of [%1$s] to [%2$s]','<b>joomleague_team_joomleague</b>','<b>joomleague_project_team</b>');
	$query='	SELECT	ts.name AS teamName,
						ts.id AS teamsID,
						t.id AS newTeamID,
						j.name AS projectName,
						tj.project_id AS tmp_old_project_id,
						tj.*
				FROM #__joomleague_team_joomleague AS tj
				LEFT JOIN #__joomleague_teams AS ts ON ts.id=tj.team_id
				LEFT JOIN #__joomleague_team AS t ON t.tmp_old_team_id=tj.team_id
				LEFT JOIN #__joomleague AS j ON j.id=tj.project_id
				ORDER by j.id';

	$db->setQuery($query);
	if ($teamtools=$db->loadObjectList()) // get old teamtools...
	{
		foreach ($teamtools as $teamtool)
		{
			$query='SELECT id FROM #__joomleague_project WHERE tmp_old_pid='.$teamtool->tmp_old_project_id;
			$db->setQuery($query);
			if ($project=$db->loadResult()) // get new project_id...
			{
				$project_id=$project;
			}
			else
			{
				$project_id=0;
				continue;
			}
			$query='SELECT id FROM #__joomleague_playground WHERE tmp_old_id='.$teamtool->standard_playground;
			$db->setQuery($query);
			if ($playground=$db->loadResult()) // get new playground_id...
			{
				$playground_id="'".$playground."'";
			}
			else
			{
				$playground_id='NULL';
			}
			$query='SELECT * FROM #__joomleague_division WHERE tmp_old_division_id='.$teamtool->division_id;
			$db->setQuery($query);
			if ($division=$db->loadResult()) // get new division_id...
			{
				$division_id="'".$division."'";
			}
			else
			{
				$division_id='NULL';
			}
			/*
			$query2='	SELECT	pt.team_id,
								pt.project_id,
								t.name
						FROM #__joomleague_project_team AS pt
						LEFT JOIN #__joomleague_team AS t ON t.id=pt.team_id
						LEFT JOIN #__joomleague_teams AS ts ON ts.id='.$teamtool->team_id.'
						WHERE (t.name=ts.name AND t.info=ts.info) AND pt.project_id='.$project_id;
			*/
			$query2="SELECT id FROM #__joomleague_project_team WHERE tmp_old_teamtool_id=$teamtool->id OR (team_id=$teamtool->newTeamID AND project_id=$project_id)";
			$db->setQuery($query2);
			if (!$object=$db->loadObject()) // check if project_team already exists in joomleague_project_team...
			{
				if (!$copied){echo '<br />';}
				$copied=true;
				$result=true;
				$unknowndata=false;
				if ($teamtool->projectName==''){$teamtool->projectName='Unknown Project';$unknowndata=true;}
				if ($teamtool->teamName==''){$teamtool->teamName='Unknown Team';$unknowndata=true;}
				if ($project_id==0){$unknowndata=true;}
				if ($unknowndata)
				{
					echo JText::sprintf(	'Copying teamtool OldProjectID %1$s - OldTeamToolID %2$s - %3$s - %4$s',
											'<i>'.$teamtool->tmp_old_project_id.'</i>',
											'<i>'.$teamtool->id.'</i>',
											'<span style="color:red; ">'.'<b>'.$teamtool->projectName.'</b>'.'</span>',
											'<i>'.$teamtool->teamName.'</i>'
											).'<br />';
				}
				else
				{
					$start_points=0;
					$points_finally=0;
					$neg_points_finally=0;
					$matches_finally=0;
					$won_finally=0;
					$draws_finally=0;
					$lost_finally=0;
					$homegoals_finally=0;
					$guestgoals_finally=0;
					$diffgoals_finally=0;
					$is_in_score=1;
					if (isset($teamtool->start_points)){$start_points=$teamtool->start_points;}
					if (isset($teamtool->points_finally)){$points_finally=$teamtool->points_finally;}
					if (isset($teamtool->neg_points_finally)){$neg_points_finally=$teamtool->neg_points_finally;}
					if (isset($teamtool->matches_finally)){$matches_finally=$teamtool->matches_finally;}
					if (isset($teamtool->won_finally)){$won_finally=$teamtool->won_finally;}
					if (isset($teamtool->draws_finally)){$draws_finally=$teamtool->draws_finally;}
					if (isset($teamtool->lost_finally)){$lost_finally=$teamtool->lost_finally;}
					if (isset($teamtool->homegoals_finally)){$homegoals_finally=$teamtool->homegoals_finally;}
					if (isset($teamtool->guestgoals_finally)){$guestgoals_finally=$teamtool->guestgoals_finally;}
					if (isset($teamtool->diffgoals_finally)){$diffgoals_finally=$teamtool->diffgoals_finally;}
					if (isset($teamtool->is_in_score)){$is_in_score=$teamtool->is_in_score;}
					$query="	INSERT INTO	#__joomleague_project_team
												(
													`project_id`,
													`team_id`,
													`division_id`,
													`start_points`,
													`points_finally`,
													`neg_points_finally`,
													`matches_finally`,
													`won_finally`,
													`draws_finally`,
													`lost_finally`,
													`homegoals_finally`,
													`guestgoals_finally`,
													`diffgoals_finally`,
													`is_in_score`,
													`admin`,
													`picture`,
													`notes`,
													`standard_playground`,
													`reason`,
													`tmp_old_teamtool_id`
												)
											VALUES
												(
													'$project_id',
													'$teamtool->newTeamID',
													$division_id,
													'$start_points',
													'$points_finally',
													'$neg_points_finally',
													'$matches_finally',
													'$won_finally',
													'$draws_finally',
													'$lost_finally',
													'$homegoals_finally',
													'$guestgoals_finally',
													'$diffgoals_finally',
													'$is_in_score',
													'$teamtool->admin',
													'$teamtool->picture',
													'".addslashes(stripslashes($teamtool->description))."',
													$playground_id,
													'".addslashes(stripslashes($teamtool->info))."',
													'$teamtool->id'
												)";
					$db->setQuery($query);
					if (!$db->query())
					{
						$result=false;
						echo '<br />'.$db->getErrorMsg().' <br />';
						echo '<br />'.$query2.'<br /><br />';
						return $result;
					}
					else
					{
						$i++;
					}
				}
			}
			else
			{
				$result=false;
				echo JText::sprintf(	'Notice: ProjectTeam id=(%1$s) team=[%2$s] project=[%3$s] already exists',
										'<b>'.$teamtool->id.'</b>',
										'<b>'.$teamtool->teamName.'</b>',
										'<b>'.$teamtool->projectName.'</b>').'<br />';
			}
		}
	}
	else
	{
		$result=false;
	}
	if ($i==count($teamtools)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><b style="color:'.$color.';" >';
	echo JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($teamtools));
	echo '</b><br /><br />';
	return '';
}

/*
* step 9
*/
function PlayertoolToTeamplayer()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;
	//$query='TRUNCATE TABLE `#__joomleague_project_position`'; $db->setQuery($query); $db->query();
	//$query='ALTER TABLE `#__joomleague_project_position`  AUTO_INCREMENT =1000'; $db->setQuery($query); $db->query();
	//$query='TRUNCATE TABLE `#__joomleague_team_player`'; $db->setQuery($query); $db->query();

	echo JText::sprintf('Copying --> Table content of [%1$s] to [%2$s]','<b>joomleague_playertool</b>','<b>joomleague_team_player</b>');
	$query='	SELECT	p.id AS newProjectID,
						p.name AS newProjectName,
						t.id AS newTeamID,
						pt.*
				FROM #__joomleague_playertool AS pt
					LEFT JOIN #__joomleague_project AS p ON p.tmp_old_pid=pt.project_id
					LEFT JOIN #__joomleague_team AS t ON t.tmp_old_team_id=pt.team_id
				ORDER by p.id,t.id';
	$db->setQuery($query);
	//echo '<pre>~'.print_r($query,true).'~</pre>';
	if ($playertools=$db->loadObjectList()) // get old playertools...
	{
		//echo '<pre>~'.print_r($playertools,true).'~</pre>';
		//$query='SELECT id,tmp_old_pos_id,name FROM #__joomleague_position WHERE parent_id > 0 AND persontype=1'; $db->setQuery($query);
        $query='SELECT id,tmp_old_pos_id,name FROM #__joomleague_position WHERE persontype=1'; $db->setQuery($query);
		$dPositions=$db->loadAssocList('tmp_old_pos_id');
		$query='SELECT id,tmp_old_player_id,firstname,lastname FROM #__joomleague_person'; $db->setQuery($query);
		$dPersons=$db->loadAssocList('tmp_old_player_id');
		foreach ($playertools as $playertool)
		{
			$query="	SELECT id
						FROM #__joomleague_team_player
						WHERE tmp_old_project_id=$playertool->project_id AND tmp_old_player_id=$playertool->player_id";
			$db->setQuery($query);
			if (!$teamPlayerID=$db->loadResult()) // check if player already exists in joomleague_team_player...
			{
				if (!$copied){echo '<br /><br />';}
				$copied=true;
				$result=true;

				$query="	SELECT id AS newProjectTeamID
							FROM #__joomleague_project_team
							WHERE team_id=$playertool->newTeamID AND project_id=$playertool->newProjectID";
				$db->setQuery($query);
				if (!$projectTeam=$db->loadObject()) // Get newProjectTeamID...
				{
					$unknowndata=true;
					$projectTeam->newProjectTeamID=0;
				}
				if (isset($dPersons[$playertool->player_id])) // Check if Person Record exists...
				{
					$unknowndata=false;
					$person_firstname=$dPersons[$playertool->player_id]['firstname'];
					$person_lastname=$dPersons[$playertool->player_id]['lastname'];
					$person_personID=$dPersons[$playertool->player_id]['id'];
				}
				else
				{
					$unknowndata=true;
					$person_firstname='Firstname';
					$person_lastname='Lastname';
					$person_personID=0;
				}
				
                if (isset($dPositions[$playertool->position_id])) // Get newPositionID...
				{
					$newPositionID=$dPositions[$playertool->position_id]['id'];
				}
				else
				{
					$newPositionID=0;
				}
                
				if ($unknowndata)
				{
					echo '<b style="color:red; ">'.JText::_('Old Team_player not found - ').'</b>';
					echo JText::sprintf(	'OldPrjID %1$s / OldTeamID %2$s / NewPrjTeamID %3$s / OldPlyID %4$s / PersID %5$s / %6$s / %7$s %8$s',
											'<b>'.$playertool->project_id. '</b>',
											'<b>'.$playertool->team_id.'</b>',
											'<b>'.$projectTeam->newProjectTeamID.'</b>',
											'<b>'.$playertool->player_id. '</b>',
											'<b>'.$person_personID. '</b>',
											'<b>'.$playertool->newProjectName.'</b>',
											'</span><span style="color:red; ">'.'<b>'.$person_firstname.'</b>'.'</span>',
											'<span style="color:red; ">'.'<b>'.$person_lastname.'</b>'.'</span>'
											).'<br />';
				}
				else
				{
					if (!empty($newPositionID))
					{
						$query="SELECT id FROM #__joomleague_project_position WHERE project_id=$playertool->newProjectID AND position_id=$newPositionID";
						$db->setQuery($query);
                        $projPosID=$db->loadResult();
						if (!$projPosID) // check if player_position already exists in joomleague_project_position...
						{
							$query="	INSERT INTO	#__joomleague_project_position
														(
															`project_id`,
															`position_id`
														)
													VALUES
														(
															'$playertool->newProjectID',
															'$newPositionID'
														)";
							$db->setQuery($query);
							//if ($playertool->newProjectID==26)
							//{
								$db->query();
							//}
							$projPosID=$db->insertid();
						}
					}
					//else
					//{
					//	echo 'HHEEEJJJ';
					//}
					$dPosition=(!empty($projPosID)) ? "'".$projPosID."'" : 'NULL';
                    
                    //echo JText::sprintf(	'%1$s Notice: %2$s dPosition-> %3$s projPosID-> %4$s',
					//					'<b style="color:orange; ">',
					//					'</b>',
					//					"<b>$dPosition</b>",
					//					"<b>$projPosID</b>").'<br />';
                                        
                                        
					$dJerseyNumber=(!empty($playertool->position_number)) ? "'".$playertool->position_number."'" : 'NULL';
					if (!isset($playertool->injury_date)){$playertool->injury_date=0;}
					if (!isset($playertool->injury_end)){$playertool->injury_end=0;}
					if (!isset($playertool->injury_detail)){$playertool->injury_detail='';}
					$playertool->published = 1;
					
/*
* developer: diddipoeler 
* date: change on 13.01.2011
* Bugtracker Backend Bug #579
* position_id to project_position_id					
*/					
					$query="	INSERT INTO	#__joomleague_team_player
												(
													`projectteam_id`,
													`person_id`,
													`project_position_id`,
													`jerseynumber`,
													`notes`,
													`injury`,
													`injury_date`,
													`injury_end`,
													`injury_detail`,
													`suspension`,
													`suspension_date`,
													`suspension_end`,
													`suspension_detail`,
													`picture`,
													`published`,
													`tmp_old_player_id`,
													`tmp_old_project_id`
												)
											VALUES
												(
													'$projectTeam->newProjectTeamID',
													'$person_personID',
													$dPosition,
													$dJerseyNumber,
													'".addslashes(stripslashes($playertool->description))."',
													'$playertool->injury',
													'$playertool->injury_date',
													'$playertool->injury_end',
													'".addslashes(stripslashes($playertool->injury_detail))."',
													'$playertool->suspension',
													'$playertool->suspension_date',
													'$playertool->suspension_end',
													'".addslashes(stripslashes($playertool->suspension_detail))."',
													'$playertool->picture',
													'$playertool->published',
													'$playertool->player_id',
													'$playertool->project_id'
												)";
					$db->setQuery($query);
					if (!$db->query())
					{
						$result=false;
						echo PrintStepResult($result).'<br />';
						echo '<br />'.$db->getErrorMsg().' <br />';
					}
					else
					{
						$i++;
					}
				}
			}
			else // team_player already exists
			{
				$result=false;
				if (!$copied){echo '<br /><br />';}
				echo JText::sprintf(	'%1$s Notice: %2$s Team_Player with ID %3$s already exists in project %4$s',
										'<b style="color:orange; ">',
										'</b>',
										"<b>$teamPlayerID</b>",
										"<b>$playertool->newProjectName</b>").'<br />';
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}
	if ($i==count($playertools)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><b style="color:'.$color.';" >'.JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($playertools)).'</b><br /><br />';
	return '';
}

function TeamstaffprojectToTeamstaff()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;
	//$query='TRUNCATE TABLE `#__joomleague_team_staff`'; $db->setQuery($query); $db->query();

	echo JText::sprintf('Copying --> Table content of [%1$s] to [%2$s]','<b>joomleague_teamstaff_project</b>','<b>joomleague_team_staff</b>');

/*
* developer: diddipoeler 
* date: change on 13.01.2011
* Bugtracker Backend Bug #579
* no position_id in old table joomleague_teamstaff_project					
*/     

	$query='	SELECT tsp.*,t.name,pr.firstname,pr.lastname,j.name as project
FROM #__joomleague_teamstaff_project AS tsp
inner JOIN #__joomleague_team_joomleague AS tj ON tj.id=tsp.projectteam_id
inner JOIN #__joomleague_team AS t ON tj.team_id=t.tmp_old_team_id
inner JOIN #__joomleague_person AS pr ON pr.tmp_old_player_id=tsp.person_id
inner join #__joomleague as j on j.id = tj.project_id
where tsp.position_id = 0
';
	
  $db->setQuery($query);
  $nostaffposition = $db->loadObjectList();
  
  if ( $nostaffposition )
  {
  echo '<br /><br />';
  foreach ( $nostaffposition as $nostaff )
		{
		echo '<b style="color:orange; ">'.JText::_('Team_staff position is empty - ').'</b>';
					echo JText::sprintf(	'Old-ID %9$s / Old-Prj-Name %1$s / Old-Team-Name %2$s / New-Prj-TeamID %3$s / Old-Ply-ID %4$s / Pers-ID %5$s / %6$s / %7$s %8$s',
											'<b>'.$nostaff->oldstaffProjectID. '</b>',
											'<b>'.$nostaff->project.'</b>',
											'<b>'.$nostaff->name.'</b>',
											'<b>'.$nostaff->person_id. '</b>',
											'<b>'.$nostaff->personID. '</b>',
											'<b>'.$nostaff->newProjectName.'</b>',
											'<span style="color:red; ">'.'<b>'.$nostaff->firstname.'</b>'.'</span>',
											'<span style="color:red; ">'.'<b>'.$nostaff->lastname.'</b>'.'</span>',
											'<b>('.$nostaff->teamstaff_id. ')</b>'
											).'<br />';
		}
		
  }
  

	$query='	SELECT tsp.*,
						tj.project_id AS oldstaffProjectID,
						p.id AS newProjectID,
						p.name AS newProjectName,
						t.id AS newTeamID,
						jpt.id AS newProjectTeamID,
						pr.id AS personID,
						pr.firstname AS firstname,
						pr.lastname AS lastname

				FROM #__joomleague_teamstaff_project AS tsp
				LEFT JOIN #__joomleague_team_joomleague AS tj ON tj.id=tsp.projectteam_id
				LEFT JOIN #__joomleague_project AS p ON p.tmp_old_pid=tj.project_id
				LEFT JOIN #__joomleague_team AS t ON tj.team_id=t.tmp_old_team_id
				LEFT JOIN #__joomleague_project_team AS jpt ON jpt.team_id=t.id AND jpt.project_id=p.id
				LEFT JOIN #__joomleague_person AS pr ON pr.tmp_old_player_id=tsp.person_id';
	
  $db->setQuery($query);
	
	if ($stafftools=$db->loadObjectList()) // get old stafftools...
	{
		

    //$query='SELECT id,tmp_old_pos_id FROM #__joomleague_position WHERE parent_id > 0 AND persontype=2 and tmp_old_pos_id > 0'; 
    $query='SELECT id,tmp_old_pos_id FROM #__joomleague_position WHERE persontype=2 and tmp_old_pos_id > 0';
    $db->setQuery($query);
		$dPositions=$db->loadAssocList('tmp_old_pos_id');
    

		$query='SELECT id,tmp_old_player_id,firstname,lastname FROM #__joomleague_person'; 
    $db->setQuery($query);
		$dPersons=$db->loadAssocList('tmp_old_player_id');
    		
    foreach ($stafftools as $stafftool)
		{
		

		
			$query="	SELECT id
						FROM #__joomleague_team_staff
						WHERE tmp_old_project_id=$stafftool->oldstaffProjectID AND tmp_old_player_id=$stafftool->person_id";
			
      $db->setQuery($query);
			
      if (!$teamStaffID=$db->loadResult()) // check if staff already exists in joomleague_team_staff...
			{
				if (!$copied){echo '<br /><br />';}
				$copied=true;
				$result=true;
				$unknowndata=false;
				if ($stafftool->newProjectName==''){$stafftool->newProjectName='Unknown Project';$unknowndata=true;}
				if ($stafftool->newProjectTeamID==''){$stafftool->newProjectTeamID='0';$unknowndata=true;}
				if ($stafftool->firstname==''){$stafftool->firstname='Unknown Firstname';$unknowndata=true;}
				if ($stafftool->lastname==''){$stafftool->lastname='Unknown Lastname';$unknowndata=true;}
				if ($stafftool->personID==''){$stafftool->personID=0;$unknowndata=true;}
				if ($stafftool->oldstaffProjectID==''){$stafftool->oldstaffProjectID=0;$unknowndata=true;}
				
        if (isset($dPositions[$stafftool->position_id])) // Get newPositionID...
				{
					$newPositionID=$dPositions[$stafftool->position_id]['id'];
				}
				else
				{
					$newPositionID=0;
				}
				
				
				if ($unknowndata)
				{
					echo '<b style="color:orange; ">'.JText::_('Old Team_staff not found - ').'</b>';
					echo JText::sprintf(	'Old-ID %9$s / Old-Prj-ID %1$s / Old-Team-ID %2$s / New-Prj-TeamID %3$s / Old-Ply-ID %4$s / Pers-ID %5$s / %6$s / %7$s %8$s',
											'<b>'.$stafftool->oldstaffProjectID. '</b>',
											'<b>'.$stafftool->projectteam_id.'</b>',
											'<b>'.$stafftool->newProjectTeamID.'</b>',
											'<b>'.$stafftool->person_id. '</b>',
											'<b>'.$stafftool->personID. '</b>',
											'<b>'.$stafftool->newProjectName.'</b>',
											'<span style="color:red; ">'.'<b>'.$stafftool->firstname.'</b>'.'</span>',
											'<span style="color:red; ">'.'<b>'.$stafftool->lastname.'</b>'.'</span>',
											'<b>('.$stafftool->teamstaff_id. ')</b>'
											).'<br />';
				}
				else
				{
					if (!empty($newPositionID))
					{
						$query="SELECT id FROM #__joomleague_project_position WHERE project_id=$stafftool->newProjectID AND position_id=$newPositionID";
						//if ($stafftool->newProjectID==26){echo '<pre>1~'.print_r($query,true).'~</pre>';}
						$db->setQuery($query);
                        $projPosID=$db->loadResult();
						if (!$projPosID) // check if position already exists in joomleague_project_position...
						{
							$query="	INSERT INTO	#__joomleague_project_position
														(
															`project_id`,
															`position_id`
														)
													VALUES
														(
															'$stafftool->newProjectID',
															$newPositionID
														)";
							$db->setQuery($query);
							//if ($stafftool->newProjectID==26){echo '<pre>1~'.print_r($query,true).'~</pre>';}
							//if ($stafftool->newProjectID==26)
							//{
							$db->query();
							//}
							$projPosID=$db->insertid();
							//if ($stafftool->newProjectID==26){echo '<pre>YY~'.print_r($projPosID,true).'~</pre>';}
						}
					//else
					//{
					//	echo 'JJJJAAAA'.$projPosID;
					//}
					}
					//else
					//{
					//	echo 'JJJEEEHHH';
					//}
					$dPosition=(!empty($projPosID)) ? "'".$projPosID."'" : 'NULL';
                    
                    //echo JText::sprintf(	'%1$s Notice: %2$s dPosition-> %3$s projPosID-> %4$s',
					//					'<b style="color:orange; ">',
					//					'</b>',
					//					"<b>$dPosition</b>",
					//					"<b>$projPosID</b>").'<br />';
                                                            
					//if ($stafftool->newProjectID==26){echo '<pre>XX~'.print_r($projPosID,true).'~</pre>';}

/*
* developer: diddipoeler 
* date: change on 13.01.2011
* Bugtracker Backend Bug #579
* position_id to project_position_id					
*/        

          $stafftool->published = 1;  
          $query="	INSERT INTO	#__joomleague_team_staff
												(
													`projectteam_id`,
													`person_id`,
													`project_position_id`,
													`notes`,
													`picture`,
													`published`,
													`tmp_old_player_id`,
													`tmp_old_project_id`
												)
											VALUES
												(
													'$stafftool->newProjectTeamID',
													'$stafftool->personID',
													$dPosition,
													'".addslashes(stripslashes($stafftool->description))."',
													'$stafftool->picture',
													'$stafftool->published',
													'$stafftool->person_id',
													'$stafftool->oldstaffProjectID'
												)";
					$db->setQuery($query);
					//if ($stafftool->newProjectID==26){echo '<pre>2~'.print_r($query,true).'~</pre>';}
					//if ($stafftool->newProjectID==26)
					//{
					if (!$db->query())
					{
						$result=false;
						echo PrintStepResult($result).'<br />';
						echo '<br />'.$db->getErrorMsg().' <br />';
					}
					else
					{
						$i++;
					}
					//}
				}
			}
			else // team_staff already exists
			{
				if (!$copied){echo '<br /><br />';}
				$result=false;
				echo JText::sprintf(	'%1$s Notice: %2$s Team_Staff with ID %3$s already exists in project %4$s',
										'<b style="color:red; ">',
										'</b>',
										"<b>$teamStaffID</b>",
										"<b>$stafftool->newProjectName</b>").'<br />';
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}
	if ($i==count($stafftools)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><b style="color:'.$color.';" >'.JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($stafftools)).'</b><br /><br />';
	return '';
}

function MatchesToMatch()
{
	$db = JFactory::getDBO();
	$i=0;
	$ii=0;
	$result=true;
	$copied=false;
	$oldProjectID=(-1);
	$totalOldMatchesCount=(-1);
	//$query='TRUNCATE TABLE `#__joomleague_match`'; $db->setQuery($query); $db->query();

	echo JText::sprintf('Copying --> Table content of [%1$s] to [%2$s]','<b>joomleague_matches</b>','<b>joomleague_match</b>').'<br /><br />';

	$query='SELECT * FROM #__joomleague_matches ORDER by project_id,round_id,match_number,match_id';
	$db->setQuery($query);
	if ($matches=$db->loadObjectList()) // get old matches...
	{
		$totalOldMatchesCount=count($matches);
		$query='SELECT id,name,tmp_old_team_id FROM #__joomleague_team'; $db->setQuery($query);
		$teams=$db->loadAssocList('tmp_old_team_id');
		$query='SELECT id,tmp_old_round_id FROM #__joomleague_round'; $db->setQuery($query);
		$rounds=$db->loadAssocList('tmp_old_round_id');
		$query='SELECT id,tmp_old_id FROM #__joomleague_playground'; $db->setQuery($query);
		$playgrounds=$db->loadAssocList('tmp_old_id');
		foreach ($matches as $match)
		{
			if ($oldProjectID!=$match->project_id)
			{
				$oldProjectID=$match->project_id;
				$query='SELECT * FROM #__joomleague_project WHERE tmp_old_pid='.$match->project_id;
				$db->setQuery($query);
				if ($project=$db->loadObject()) // get new project id...
				{
					$newProjectID=$project->id;
					if ($copied){echo JText::sprintf('Copied %1$s old records assigned to project','<b>'.$i.'</b>').'<br /><br />';}
					echo JText::sprintf('Copying matches of project %1$s','<b>'.$project->name.'</b>').'<br />';
					$copied=true;
					$i=0;
				}
			}
			if (isset($project) && $project) // found the project of the match
			{
				if (isset($rounds[$match->round_id]))
				{
					$newRoundID=$rounds[$match->round_id]['id'];
				}
				else
				{
					echo '<span style="color:red;">'.JText::sprintf('Skipping round %1$s as it doesn\'n exist',"<b>$match->round_id</b>").'</span><br />';
					continue; // skip as round was not found
				}

				if (!isset($teams[$match->matchpart1])) // get new team id of hometeam...
				{
					echo '<span style="color:orange;">'.JText::sprintf('Hometeam with ID %1$s not found in old matchID %2$s. Setting ProjectTeamID to NULL!',"<b>$match->matchpart1</b>","<b>$match->match_id</b>").'</span><br />';
					$projectTeamID1=null;
				}
				else
				{
					$query='SELECT id FROM #__joomleague_project_team WHERE team_id='.$teams[$match->matchpart1]['id'].' AND project_id='.$newProjectID; $db->setQuery($query);
					$projectTeamID1=$db->loadResult(); // get new project team id of hometeam...
				}

				if (!isset($teams[$match->matchpart2])) // get new team id of guestteam...
				{
					echo '<span style="color:orange;">'.JText::sprintf('Awayteam with ID %1$s not found in old matchID %2$s. Setting ProjectTeamID to NULL!',"<b>$match->matchpart2</b>","<b>$match->match_id</b>").'</span><br />';
					$projectTeamID2=null;
				}
				else
				{
					$query='SELECT id FROM #__joomleague_project_team WHERE team_id='.$teams[$match->matchpart2]['id'].' AND project_id='.$newProjectID; $db->setQuery($query);
					$projectTeamID2=$db->loadResult(); // get new project team id of guestteam...
				}

				if (!isset($match->matchpart1_result_ot))		{$match->matchpart1_result_ot=NULL;}
				if (!isset($match->matchpart2_result_ot))		{$match->matchpart2_result_ot=NULL;}
				if (!isset($match->matchpart1_legs))			{$match->matchpart1_legs=NULL;}
				if (!isset($match->matchpart2_legs))			{$match->matchpart2_legs=NULL;}
				if (!isset($match->matchpart1_result_decision))	{$match->matchpart1_result_decision=NULL;}
				if (!isset($match->matchpart2_result_decision))	{$match->matchpart2_result_decision=NULL;}
				if (!isset($match->matchpart1_bonus))			{$match->matchpart1_bonus=NULL;}
				if (!isset($match->matchpart2_bonus))			{$match->matchpart2_bonus=NULL;}
				if (!isset($match->matchpart1_result_split))	{$match->matchpart1_result_split=NULL;}
				if (!isset($match->matchpart2_result_split))	{$match->matchpart2_result_split=NULL;}
				if (!isset($match->match_number))				{$match->match_number=NULL;}
				if (!isset($match->playground_id))				{$match->playground_id=NULL;}

				//float team1_result
				if ($match->matchpart1_result!='0')
				{
					$team1_result=empty ($match->matchpart1_result) ? 'NULL' : "'".$match->matchpart1_result."'";
				}
				else
				{
					$team1_result="'0'";
				}

				//float team2_result
				if ($match->matchpart2_result!='0')
				{
					$team2_result=empty ($match->matchpart2_result) ? 'NULL' : "'".$match->matchpart2_result."'";
				}
				else
				{
					$team2_result="'0'";
				}

				//float team1_result_ot
				if ($match->matchpart1_result_ot!='0')
				{
					$team1_result_ot=empty ($match->matchpart1_result_ot) ? 'NULL' : "'".$match->matchpart1_result_ot."'";
				}
				else
				{
					$team1_result_ot="'0'";
				}

				//float team2_result_ot
				if ($match->matchpart2_result_ot!='0')
				{
					$team2_result_ot=empty ($match->matchpart2_result_ot) ? 'NULL' : "'".$match->matchpart2_result_ot."'";
				}
				else
				{
					$team2_result_ot="'0'";
				}

				//float team1_legs
				if ($match->matchpart1_legs!='0')
				{
					$team1_legs=empty ($match->matchpart1_legs) ? 'NULL' : "'".$match->matchpart1_legs."'";
				}
				else
				{
					$team1_legs="'0'";
				}

				//float team2_legs
				if ($match->matchpart2_legs!='0')
				{
					$team2_legs=empty ($match->matchpart2_legs) ? 'NULL' : "'".$match->matchpart2_legs."'";
				}
				else
				{
					$team2_legs="'0'";
				}

				//float team1_result_decision
				if ($match->matchpart1_result_decision!='0')
				{
					$team1_result_decision=empty ($match->matchpart1_result_decision) ? 'NULL' : "'".$match->matchpart1_result_decision."'";
				}
				else
				{
					$team1_result_decision="'0'";
				}

				//float team2_result_decision
				if ($match->matchpart2_result_decision!='0')
				{
					$team2_result_decision=empty ($match->matchpart2_result_decision) ? 'NULL' : "'".$match->matchpart2_result_decision."'";
				}
				else
				{
					$team2_result_decision="'0'";
				}

				//int team1_bonus
				if ($match->matchpart1_bonus!='0')
				{
					$team1_bonus=empty ($match->matchpart1_bonus) ? 'NULL' : "'".$match->matchpart1_bonus."'";
				}
				else
				{
					$team1_bonus="'0'";
				}

				//int team2_bonus
				if ($match->matchpart2_bonus!='0')
				{
					$team2_bonus=empty ($match->matchpart2_bonus) ? 'NULL' : "'".$match->matchpart2_bonus."'";
				}
				else
				{
					$team2_bonus="'0'";
				}

				//varchar team1_result_split
				if ($match->matchpart1_result_split!='')
				{
					$team1_result_split=empty ($match->matchpart1_result_split) ? 'NULL' : "'".$match->matchpart1_result_split."'";
				}
				else
				{
					$team1_result_split="''";
				}

				//varchar team2_result_split
				if ($match->matchpart2_result_split!='')
				{
					$team2_result_split=empty ($match->matchpart2_result_split) ? 'NULL' : "'".$match->matchpart2_result_split."'";
				}
				else
				{
					$team2_result_split="''";
				}

				//varchar match_number
				if ((!is_null($match->match_number)) && ($match->match_number!=''))
				{
					$match_number=empty ($match->match_number) ? 'NULL' : "'".$match->match_number."'";
				}
				else
				{
					$match_number='NULL';
				}

				//int playground_id				
				if (intval($match->playground_id))
				{
					//search new playgroundID
					if (isset($playgrounds[$match->playground_id]))
					{
						$playground_id = "'".$playgrounds[$match->playground_id]['id']."'";
					}
					else
					{
						$playground_id = 'NULL';
					}
				}
				else
				{
					$playground_id = 'NULL';
				}

				$query="	INSERT INTO	#__joomleague_match
											(
												`round_id`,
												`match_number`,
												`projectteam1_id`,
												`projectteam2_id`,
												`playground_id`,
												`match_date`,
												`team1_result`,
												`team2_result`,
												`team1_bonus`,
												`team2_bonus`,
												`team1_result_split`,
												`team2_result_split`,
												`match_result_type`,
												`team1_result_ot`,
												`team2_result_ot`,
												`alt_decision`,
												`team1_result_decision`,
												`team2_result_decision`,
												`decision_info`,
												`count_result`,
												`published`,
												`crowd`,
												`summary`,
												`show_report`,
												`match_result_detail`,
												`team1_legs`,
												`team2_legs`,
												`modified`,
												`modified_by`,
												`tmp_old_match_id`,
												`tmp_old_project_id`
											)
										VALUES
											(
												'$newRoundID',
												$match_number,
												'$projectTeamID1',
												'$projectTeamID2',
												$playground_id,
												'$match->match_date',
												$team1_result,
												$team2_result,
												$team1_bonus,
												$team2_bonus,
												$team1_result_split,
												$team2_result_split,
												'$match->match_result_type',
												$team1_result_ot,
												$team2_result_ot,
												'$match->alt_decision',
												$team1_result_decision,
												$team2_result_decision,
												'".addslashes(stripslashes($match->decision_info))."',
												'$match->count_result',
												'$match->published',
												'$match->crowd',
												'".addslashes(stripslashes($match->summary))."',
												'$match->show_report',
												'".addslashes(stripslashes($match->match_result_detail))."',
												$team1_legs,
												$team2_legs,
												'$match->modified',
												'$match->modified_by',
												'$match->match_id',
												'$match->project_id'
											)";
				$db->setQuery($query);
				if (!$db->query())
				{
					$result=false;
					echo PrintStepResult($result).'<br />';
					echo '<br />'.$db->getErrorMsg().' <br />';
				}
				else
				{
					$i++;
					$ii++;
				}
			}
			else
			{
				echo '<span style="color:red;">'.JText::sprintf('Skipping match with ID %1$s as project with assigned ID %2$s doesn\'n exist',"<b>$match->match_id</b>","<b>$match->project_id</b>").'</span><br />';
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}
	if ($copied){echo JText::sprintf('Copied %1$s old records assigned to project','<b>'.$i.'</b>').'<br /><br />';}
	if ($ii==count($matches)){$color='green';}elseif($ii==0){$color='red';}else{$color='orange';}
	echo '<b style="color:'.$color.';" >'.JText::sprintf('Totally copied %1$s of %2$s old records',$ii,count($matches)).'</b><br /><br />';

	return '';
}

function MatcheventsToMatchevent()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$oldProjectID=(-1);
	$oldEventID=(-1);
	$i=0;
	$ii=0;
	$totalOldMatcheEventsCount=0;
	//$query='TRUNCATE TABLE `#__joomleague_match_event`'; $db->setQuery($query); $db->query();
	echo JText::sprintf('Copying --> Table content of [%1$s] to [%2$s]','<b>joomleague_match_events</b>','<b>joomleague_match_event</b>').'<br />';
	$query='	SELECT	et.id AS newEventID,
						et.name AS eventName,
						me.*
				FROM #__joomleague_match_events AS me
				LEFT JOIN #__joomleague_eventtype AS et ON et.tmp_old_id=me.event_type_id
				ORDER by project_id,match_id,event_type_id';
	$db->setQuery($query);
	if ($matchevents=$db->loadObjectList()) // get old matchevents...
	{
		$query='SELECT id,tmp_old_team_id FROM #__joomleague_team'; $db->setQuery($query);
		$teams=$db->loadAssocList('tmp_old_team_id');
		$query='SELECT id,tmp_old_player_id FROM #__joomleague_person'; $db->setQuery($query);
		$persons=$db->loadAssocList('tmp_old_player_id');
		$query='SELECT id,tmp_old_match_id FROM #__joomleague_match'; $db->setQuery($query);
		$matches=$db->loadAssocList('tmp_old_match_id');
		foreach ($matchevents as $matchevent)
		{
			if ($oldEventID==$matchevent->event_id){continue;}
			$totalOldMatcheEventsCount++;
			$oldEventID=$matchevent->event_id;
			if ($oldProjectID!=$matchevent->project_id)
			{
				$oldProjectID=$matchevent->project_id;
				$query='SELECT * FROM #__joomleague_project WHERE tmp_old_pid='.$matchevent->project_id;
				$db->setQuery($query);
				if ($project=$db->loadObject()) // get new project id...
				{
					$newProjectID=$project->id;
					echo '<br />'.JText::sprintf('Copying matchevents of project %1$s','<b>'.$project->name.'</b>').'<br />';
				}
			}
			if (isset($project) && $project)
			{
				if (isset($teams[$matchevent->team_id]))
				{
					$newTeamID=$teams[$matchevent->team_id]['id'];
					$query='SELECT id FROM #__joomleague_project_team WHERE project_id='.$newProjectID.' AND team_id='.$newTeamID; $db->setQuery($query);
					$newProjectTeamID=$db->loadResult(); // get new project team id of matchevent...
				}
				else
				{
					echo '<span style="color:orange;">'.JText::sprintf('Team with ID %1$s not found in old matchevent ID %2$s. Setting ProjectTeamID to NULL!',"<b>$matchevent->team_id</b>","<b>$matchevent->event_id</b>").'</span><br />';
					$newProjectTeamID=null;
				}

				if (isset($matches[$matchevent->match_id]))
				{
					$newMatchID=$matches[$matchevent->match_id]['id'];
				}
				else
				{
					echo '<span style="color:red;">'.JText::sprintf('Skipping matchevent with ID %1$s as assigned match with ID %2$s doesn\'n exist',"<b>$matchevent->event_id</b>","<b>$matchevent->match_id</b>").'</span><br />';
					continue; // skip as match was not found
				}
				if (!isset($persons[$matchevent->player_id]))
				{
					$playerID1=null;
				}
				else
				{
					$playerID1tmp=$persons[$matchevent->player_id]['id'];
					$query='	SELECT *
								FROM #__joomleague_team_player
								WHERE person_id='.$playerID1tmp.' AND projectteam_id='.$newProjectTeamID; $db->setQuery($query);
					$player1=$db->loadObject(); // get new team player id of player1...
					if (!isset($player1->id))
					{
						$playerID1=null;
					}
					else
					{
						$playerID1=$player1->id;
					}
				}
				$query='SELECT * FROM #__joomleague_person WHERE tmp_old_player_id > 0 AND tmp_old_player_id='.$matchevent->player_id2; $db->setQuery($query);
				$player2=$db->loadObject(); // get new team id of player2...
				if (!isset($player2->id))
				{
					$playerID2=null;
				}
				else
				{
					$playerID2=$player2->id;
				}
				if (!empty($playerID1) && !empty($newMatchID) && !empty($newProjectTeamID) && !empty($matchevent->newEventID))
				{
					$query="	INSERT INTO	#__joomleague_match_event
												(
													`match_id`,
													`projectteam_id`,
													`teamplayer_id`,
													`teamplayer_id2`,
													`event_time`,
													`event_type_id`,
													`event_sum`,
													`notice`,
													`notes`
												)
											VALUES
												(
													'$newMatchID',
													'$newProjectTeamID',
													'$playerID1',
													'$playerID2',
													'$matchevent->event_time',
													'$matchevent->newEventID',
													'$matchevent->event_sum',
													'".addslashes(stripslashes($matchevent->notice))."',
													'".addslashes(stripslashes($matchevent->description))."'
												)";
					$db->setQuery($query);
					if (!$db->query())
					{
						$result=false;
						echo PrintStepResult($result).'<br />';
						echo '<br />'.$db->getErrorMsg().'<br />';
					}
				}
			}
			else
			{
				echo '<span style="color:red;">'.JText::sprintf('Skipping matchevent with ID %1$s as project with assigned ID %2$s doesn\'n exist',"<b>$matchevent->event_id</b>","<b>$matchevent->project_id</b>").'</span><br />';
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}
	echo '<br />';
	return '';
}

function MatchplayersToMatchplayer()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$oldMatchID=(-1);
	$oldPlayerID=(-1);
	$oldProjectID=(-1);
	$i=0;
	$ii=0;
	$totalOldMatchPlayersCount=0;
	//$query='TRUNCATE TABLE `#__joomleague_match_player`'; $db->setQuery($query); $db->query();

	echo JText::sprintf('Copying --> Table content of [%1$s] to [%2$s]','<b>joomleague_match_players</b>','<b>joomleague_match_player</b>').'<br />';

	$query='SELECT * FROM #__joomleague_match_players ORDER by match_id,team_id';
	$db->setQuery($query);
	if ($matchplayers=$db->loadObjectList()) // get old matchplayers...
	{
		$query='SELECT id,tmp_old_match_id,tmp_old_project_id FROM #__joomleague_match'; $db->setQuery($query);
		$matches=$db->loadAssocList('tmp_old_match_id');
		$query='SELECT id,tmp_old_player_id FROM #__joomleague_person WHERE tmp_old_player_id > 0'; $db->setQuery($query);
		$persons=$db->loadAssocList('tmp_old_player_id');
		$query='SELECT id,tmp_old_pos_id,name FROM #__joomleague_position'; $db->setQuery($query);
		$positions=$db->loadAssocList('tmp_old_pos_id');
		$query='SELECT id,name,tmp_old_pid FROM #__joomleague_project'; $db->setQuery($query);
		$projects=$db->loadAssocList('tmp_old_pid');

		foreach ($matchplayers as $matchplayer)
		{
			$totalOldMatchPlayersCount++;
			if ($oldMatchID!=$matchplayer->match_id)
			{
				$i=0;
				$oldMatchID=$matchplayer->match_id;
				if (isset($matches[$matchplayer->match_id]))
				{
					$newMatchID=$matches[$matchplayer->match_id]['id'];
					$tmpOldProjectID=$matches[$matchplayer->match_id]['tmp_old_project_id'];
					if ($oldProjectID!=$tmpOldProjectID)
					{
						$oldProjectID=$tmpOldProjectID;
						$projectName=$projects[$tmpOldProjectID]['name'];
						$project_id=$projects[$tmpOldProjectID]['id'];
						echo '<br />'.JText::sprintf('Copying substitutions in project [%1$s] - ','<b>'.$projectName.'</b>').$project_id.'<br /><br />';
						$query='SELECT id,position_id FROM #__joomleague_project_position WHERE project_id='.$project_id; $db->setQuery($query);
						$projectPositions=$db->loadAssocList('position_id');
					}
					echo JText::sprintf('MatchID %1$s - ','<b>'.$matches[$matchplayer->match_id]['id'].'</b>').$matchplayer->match_id.'<br />';
					$i++;
				}
				else
				{
					echo '<span style="color:red;">'.JText::sprintf('Skipping matchplayer-record with ID %1$s as match with assigned ID %2$s doesn\'n exist',"<b>$matchplayer->id</b>","<b>$matchplayer->match_id</b>").'</span><br /><br />';
					continue;
				}
			}
			$query='SELECT id FROM #__joomleague_team_player
					WHERE tmp_old_project_id='.$tmpOldProjectID.' AND person_id='.$persons[$matchplayer->player_id]['id'];
			$db->setQuery($query);
			$projectplayerID1=$db->loadResult(); // get new project_team id for player1

			if (isset($persons[$matchplayer->in_for]))
			{
				$query='	SELECT id FROM #__joomleague_team_player
							WHERE tmp_old_project_id='.$tmpOldProjectID.' AND person_id='.$persons[$matchplayer->in_for]['id'];
				$db->setQuery($query);
				$projectplayerID2=$db->loadResult(); // get new project_team id for player2
			}
			else
			{
				$projectplayerID2=0;
			}

			if (isset($positions[$matchplayer->position_id]))
			{
				$query="SELECT ppos.id AS pposID,ppos.project_id,ppos.position_id,pos.id AS posID,pos.name
						FROM #__joomleague_position AS pos
						LEFT JOIN #__joomleague_project_position AS ppos ON ppos.project_id=$project_id AND pos.id=ppos.position_id
						WHERE pos.id=".$positions[$matchplayer->position_id]['id'];
				$db->setQuery($query);
				$resultObject=$db->loadObject();
				if (empty($resultObject->pposID))
				{
					$query="INSERT INTO	#__joomleague_project_position (`project_id`,`position_id`)
									VALUES ('$project_id','".$positions[$matchplayer->position_id]['id']."')";
					$db->setQuery($query);
					$db->query();
				}
				$query="SELECT ppos.id AS pposID,ppos.project_id,ppos.position_id,pos.id AS posID,pos.name
						FROM #__joomleague_position AS pos
						LEFT JOIN #__joomleague_project_position AS ppos 
						ON ppos.project_id=$project_id 
						AND pos.id=ppos.position_id
						WHERE pos.id=".$positions[$matchplayer->position_id]['id'];

				$db->setQuery($query);
				$resultObject=$db->loadObject();
				$newPosID=$resultObject->pposID;
			}
			else
			{
				$newPosID=0;
			}

			$d_projectplayerID2=(!empty($projectplayerID2)) ? "'".$projectplayerID2."'" : 'NULL';
			$d_inOutTime=(!empty($matchplayer->in_out_time)) ? "'".$matchplayer->in_out_time."'" : 'NULL';

/*
* developer: diddipoeler 
* date: change on 13.01.2011
* Bugtracker Backend Bug #579
* position_id to project_position_id					
*/
			$query="	INSERT INTO	#__joomleague_match_player
										(
											`match_id`,
											`teamplayer_id`,
											`project_position_id`,
											`came_in`,
											`in_for`,
											`out`,
											`in_out_time`,
											`ordering`
										)
									VALUES
										(
											'$newMatchID',
											'$projectplayerID1',
											'$newPosID',
											'$matchplayer->came_in',
											'$projectplayerID2',
											'$matchplayer->out',
											$d_inOutTime,
											'$matchplayer->ordering'
										)";
			$db->setQuery($query);
			if (!$db->query())
			{
				$result=false;
				echo PrintStepResult($result).'<br />';
				echo '<br />'.$db->getErrorMsg().' <br />';
			}
		}
		$ii++;
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}
	echo '<br />';
	return '';
}

function MatchrefereesToMatchrefereeAndProjectReferee()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$oldProjectID=(-1);
	//$query='TRUNCATE TABLE `#__joomleague_match_referee`'; $db->setQuery($query); $db->query();
	//$query='TRUNCATE TABLE `#__joomleague_project_referee`'; $db->setQuery($query); $db->query();
	echo JText::sprintf(	'Copying --> Field content of [%1$s] to [%2$s] and [%3$s]',
							 '<b>joomleague_matches->project_referee_id</b>',
							 '<b>joomleague_project_referee</b>',
							 '<b>joomleague_match_referee</b>').'<br />';

	$query="SELECT id FROM #__joomleague_position WHERE name='JL_F_CENTER_REFEREE'"; $db->setQuery($query);
	$refereePosID=$db->loadResult(); // get the id of a referee position...
	$query='	SELECT
						m.match_id AS oldMatchID,
						m.referee_id AS oldRefereeID,
						m.project_id AS oldProjectID

				FROM #__joomleague_matches AS m
				WHERE m.referee_id > 0
				ORDER by m.project_id';

	$db->setQuery($query);
	if ($matches=$db->loadObjectList()) // get old matches which have a referee_id > 0...
	{
		$query='SELECT id,tmp_old_match_id FROM #__joomleague_match'; $db->setQuery($query);
		$dMatches=$db->loadAssocList('tmp_old_match_id');
		$query='SELECT id,tmp_old_referee_id FROM #__joomleague_person WHERE tmp_old_referee_id > 0'; $db->setQuery($query);
		$persons=$db->loadAssocList('tmp_old_referee_id');
		if(count($matches) > 0)
		{
			foreach ($matches as $match)
			{
				if ($oldProjectID!=$match->oldProjectID)
				{
					$i=0;
					$oldProjectID=$match->oldProjectID;
					$query='SELECT * FROM #__joomleague_project WHERE tmp_old_pid='.$match->oldProjectID;
					$db->setQuery($query);
					if ($project=$db->loadObject()) // get new project id...
					{
						$newProjectID=$project->id;
						echo '<br />'.JText::sprintf('Copying referees of project %1$s','<b>'.$project->name.'</b>').'<br /><br />';
						//add Center Referee position to project_position
						$query="	INSERT INTO	#__joomleague_project_position
													(
														`project_id`,
														`position_id`
													)
												VALUES
													(
														'$newProjectID',
														'$refereePosID'
													)";
						$db->setQuery($query);
						$db->query();
						$refereeProjectPosID=$db->insertid();
					}
				}
				if (isset($project) && $project)
				{
					if (isset($dMatches[$match->oldMatchID]))
					{
						$newMatchID=$dMatches[$match->oldMatchID]['id']; // get new match id of match...
					}
					else
					{
						$newMatchID=0;
					}
					if (isset($persons[$match->oldRefereeID]))
					{
						$refereeID=$persons[$match->oldRefereeID]['id'];
					}
					else
					{
						$refereeID=0;
					}
					$query='SELECT id FROM #__joomleague_project_referee WHERE project_id='.$newProjectID.' AND person_id='.$refereeID;
					$db->setQuery($query);
					if (!$db->loadResult()) // check if referee already exists in project_referee
					{

/*
* developer: diddipoeler 
* date: change on 13.01.2011
* Bugtracker Backend Bug #579
* position_id to project_position_id					
*/
						
            $query="	INSERT INTO	#__joomleague_project_referee
													(
														`project_id`,
														`person_id`,
														`project_position_id`
													)
												VALUES
													(
														'$newProjectID',
														'$refereeID',
														'$refereeProjectPosID'
													)";
						$db->setQuery($query);
						if (!$db->query()) // write into table project_referee
						{
							$result=false;
							echo '<br />'.$db->getErrorMsg().' <br />';
						}
					}
					$query='SELECT id FROM #__joomleague_project_referee WHERE project_id='.$newProjectID.' AND person_id='.$refereeID;
					$db->setQuery($query); // get correct id of referee from project_referee
					if (!$projectRefereeID=$db->loadResult()) // get projectRefereeID of referee
					{
						$projectRefereeID=0;
					}
					
/*
* developer: diddipoeler 
* date: change on 13.01.2011
* Bugtracker Backend Bug #579
* position_id to project_position_id
*/
					
					$query="	INSERT INTO	#__joomleague_match_referee
												(
													`match_id`,
													`project_referee_id`,
													`project_position_id`
												)
											VALUES
												(
													'$newMatchID',
													'$projectRefereeID',
													'$refereeProjectPosID'
												)";
					$db->setQuery($query);
					if (!$db->query()) // write into table match_referee
					{
						$result=false;
						echo PrintStepResult($result).'<br />';
						echo '<br />'.$db->getErrorMsg().' <br />';
					}
					else
					{
						$i++;
					}
				}
			}
		}
	}
	else
	{
		if(!isset($matches))
		{
			$result=false;
			echo PrintStepResult($result).'<br />';
			echo '<br />'.$db->getErrorMsg().' <br />';
		}
	}
	echo '<br />';

	return '';
}

function PredictionGameToPredictionGame()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;

	echo JText::sprintf(	'Copying --> Table content of [%1$s] to [%2$s]',
							'<b>joomleague_predictiongame</b>','<b>joomleague_prediction_game</b>');

	$query='SELECT * FROM #__joomleague_predictiongame ORDER by name'; $db->setQuery($query);
	if ($predictiongames=$db->loadObjectList()) // get old predictiongames...
	{
		foreach ($predictiongames as $predictiongame)
		{
			if (!$copied){echo '<br />';}
			$copied=true;
			$result=true;

			$query="	INSERT INTO	#__joomleague_prediction_game
										(
											`name`,
											`alias`,
											`master_template`,
											`auto_approve`,
											`notify_to`,
											`published`,
											`tmp_old_id`
										)
									VALUES
										(
											'$predictiongame->name',
											'".JFilterOutput::stringURLSafe($predictiongame->name)."',
											'$predictiongame->template',
											'$predictiongame->auto_approve',
											'$predictiongame->notify_to',
											'$predictiongame->published',
											'$predictiongame->id'
										)";
			$db->setQuery($query);
			if (!$db->query())
			{
				$result=false;
				echo PrintStepResult($result).'<br />';
				echo '<br />'.$db->getErrorMsg().' <br />';
			}
			else
			{
				$i++;
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}

	if ($i==count($predictiongames)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><b style="color:'.$color.';" >'.JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($predictiongames)).'</b><br /><br />';

	return '';
}

function PredictionGameAdminsToPredictionAdmin()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;
	echo JText::sprintf(	'Copying --> Table content of [%1$s] to [%2$s]',
							'<b>joomleague_predictiongame_admins</b>','<b>joomleague_prediction_admin</b>');
	$query='SELECT * FROM #__joomleague_predictiongame_admins'; $db->setQuery($query);
	if ($predictiongameadmins=$db->loadObjectList()) // get old predictiongame_admins...
	{
		foreach ($predictiongameadmins as $predictionadmin)
		{
			$query='SELECT id FROM #__joomleague_prediction_game WHERE tmp_old_id='.$predictionadmin->prediction_id; $db->setQuery($query);
			if ($predictiongame=$db->loadObject()) // get new prediction_id...
			{
				$prediction_id=$predictiongame->id;
				$query='SELECT id FROM #__joomleague_prediction_admin WHERE tmp_old_id='.$predictionadmin->id; $db->setQuery($query);
				if (! $object=$db->loadObject()) // check if predictionadmin already exists in joomleague_prediction_admin...
				{
					if (!$copied){echo '<br />';}
					$copied=true;
					$result=true;

					$query="	INSERT INTO	#__joomleague_prediction_admin
												(
													`prediction_id`,
													`user_id`,
													`tmp_old_id`,
													`tmp_old_pid`
												)
											VALUES
												(
													'$prediction_id',
													'$predictionadmin->user_id',
													'$predictionadmin->id',
													'$predictionadmin->project_id'
												)";
					$db->setQuery($query);
					if (!$db->query())
					{
						$result=false;
						echo PrintStepResult($result).'<br />';
						echo '<br />'.$db->getErrorMsg().' <br />';
					}
					else
					{
						$i++;
					}
				}
				else // predictionadmin already exists
				{
					$result=false;
					echo JText::sprintf('Notice: Prediction_admin %1$s already exists','<b>'.$predictionadmin->id.'</b>').'<br />';
				}
			}
			else
			{
				$prediction_id=0;
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}
	if ($i==count($predictiongameadmins)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><b style="color:'.$color.';" >'.JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($predictiongameadmins)).'</b><br /><br />';
	return '';
}

function PredictionGameProjectToPredictionProject()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;

	echo JText::sprintf(	'Copying --> Table content of [%1$s] to [%2$s]',
							'<b>joomleague_predictiongame_project</b>','<b>joomleague_prediction_project</b>');

	$query='SELECT * FROM #__joomleague_predictiongame_project ORDER BY project_id,prediction_id'; $db->setQuery($query);
	if ($predictiongameprojects=$db->loadObjectList()) // get old predictiongame_projects...
	{
		foreach ($predictiongameprojects as $predictionproject)
		{
			$query='SELECT id FROM #__joomleague_prediction_game WHERE tmp_old_id='.$predictionproject->prediction_id; $db->setQuery($query);
			if ($predictiongame=$db->loadObject()) // get new prediction_id...
			{
				$prediction_id=$predictiongame->id;
				$query='SELECT id FROM #__joomleague_project WHERE tmp_old_pid='.$predictionproject->project_id; $db->setQuery($query);
				if ($project=$db->loadObject()) // get new project_id...
				{
					$project_id=$project->id;
					$query='SELECT id FROM #__joomleague_prediction_project WHERE tmp_old_id='.$predictionproject->id; $db->setQuery($query);
					if (!$object=$db->loadObject()) // check if predictionproject already exists in joomleague_prediction_project...
					{
						if (!$copied){echo '<br />';}
						$copied=true;
						$result=true;
						if (!isset($predictionproject->overview)){$predictionproject->overview=0;}

						$query="	INSERT INTO	#__joomleague_prediction_project
													(
														`prediction_id`,
														`project_id`,
														`mode`,
														`overview`,
														`points_tipp`,
														`points_tipp_joker`,
														`points_tipp_champ`,
														`points_correct_result`,
														`points_correct_result_joker`,
														`points_correct_diff`,
														`points_correct_diff_joker`,
														`points_correct_draw`,
														`points_correct_draw_joker`,
														`points_correct_tendence`,
														`points_correct_tendence_joker`,
														`joker`,
														`joker_limit`,
														`champ`,
														`published`,
														`tmp_old_id`,
														`tmp_old_pid`
													)
												VALUES
													(
														'$prediction_id',
														'$project_id',
														'$predictionproject->mode',
														'$predictionproject->overview',
														'$predictionproject->tip_points_tip',
														'$predictionproject->tip_points_tip_joker',
														'$predictionproject->tip_points_tip_champ',
														'$predictionproject->tip_points_correct_result',
														'$predictionproject->tip_points_correct_result_joker',
														'$predictionproject->tip_points_correct_diff',
														'$predictionproject->tip_points_correct_diff_joker',
														'$predictionproject->tip_points_correct_draw',
														'$predictionproject->tip_points_correct_draw_joker',
														'$predictionproject->tip_points_correct_tendence',
														'$predictionproject->tip_points_correct_tendence_joker',
														'$predictionproject->tip_joker',
														'$predictionproject->tip_joker_limit',
														'$predictionproject->tip_champ',
														'1',
														'$predictionproject->id',
														'$predictionproject->project_id'
													)";
						$db->setQuery($query);
						if (!$db->query())
						{
							$result=false;
							echo PrintStepResult($result).'<br />';
							echo '<br />'.$db->getErrorMsg().' <br />';
						}
						else
						{
							$i++;
						}
					}
					else // prediction_project already exists
					{
						$result=false;
						echo JText::sprintf('Notice: Prediction_project %1$s already exists','<b>'.$predictionproject->id.'</b>').'<br />';
					}
				}
				else
				{
					$project_id=0;
				}
			}
			else
			{
				$prediction_id=0;
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}
	if ($i==count($predictiongameprojects)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><b style="color:'.$color.';" >'.JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($predictiongameprojects)).'</b><br /><br />';
	return '';
}

function TipMembersToPredictionMember()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;

	echo JText::sprintf(	'Copying --> Table content of [%1$s] to [%2$s]',
							 '<b>joomleague_tip_members</b>','<b>joomleague_prediction_member</b>');

	$query='SELECT * FROM #__joomleague_tip_members'; $db->setQuery($query);
	if ($predictiongamemembers=$db->loadObjectList()) // get old predictiongame_tip_members...
	{
		foreach ($predictiongamemembers as $predictiongamemember)
		{
			$query='SELECT prediction_id FROM #__joomleague_prediction_project WHERE tmp_old_pid='.$predictiongamemember->project_id; $db->setQuery($query);
			if ($prediction=$db->loadObject()) // get new predicition_id...
			{
				$prediction_id=$prediction->prediction_id;
				$copied=true;
				$result=true;
				$dfavteam=null;

				$query='SELECT id FROM #__joomleague_prediction_project WHERE tmp_old_pid='.$predictiongamemember->project_id; $db->setQuery($query);
				if ($predproject=$db->loadObject()) // check if predictionproject already exists in joomleague_prediction_project...
				{
					$predprojectID=$predproject->id;
				}
				else
				{
					$predprojectID=0;
				}

				$dfavteam='';
				if (!empty($predictiongamemember->fav_team))
				{
						$query='SELECT id FROM #__joomleague_project_team WHERE tmp_old_teamtool_id='.$predictiongamemember->fav_team;
						$db->setQuery($query);
						if ($project_team=$db->loadObject()) // get new team_id...
						{
							$dfavteam=$project_team->id;
						}
				}

				$dchamptip='';
				if (!empty($predictiongamemember->champ_tip))
				{
						$query='SELECT id FROM #__joomleague_project_team WHERE tmp_old_teamtool_id='.$predictiongamemember->champ_tip;
						$db->setQuery($query);
						if ($project_team=$db->loadObject()) // get new team_id...
						{
							$dchamptip=$project_team->id;
						}
				}

				//int fav_team
				if (!empty($dfavteam))
				{
					$fav_team=empty($dfavteam) ? "''" : "'".$predprojectID.','.$dfavteam."'";
				}
				else
				{
					$fav_team="''";
				}

				//int champ_tip
				if (!empty($dchamptip))
				{
					$champ_tipp=empty($dchamptip) ? "''" : "'".$predprojectID.','.$dchamptip."'";
				}
				else
				{
					$champ_tipp="''";
				}

				//varchar slogan
				$slogan=empty($predictiongamemember->slogan) ? 'NULL' : "'".$predictiongamemember->slogan."'";

				//varchar picture
				$picture=empty($predictiongamemember->picture) ? 'NULL' : "'".$predictiongamemember->picture."'";

				//varchar last_tip
				if ($predictiongamemember->last_tip!='0000-00-00 00:00:00')
				{
					$last_tipp=empty($predictiongamemember->last_tip) ? 'NULL' : "'".$predictiongamemember->last_tip."'";
				}
				else
				{
					$last_tipp="'0000-00-00 00:00:00'";
				}

				$query="	INSERT INTO	#__joomleague_prediction_member
											(
												`prediction_id`,
												`user_id`,
												`approved`,
												`fav_team`,
												`champ_tipp`,
												`slogan`,
												`reminder`,
												`receipt`,
												`admintipp`,
												`picture`,
												`last_tipp`,
												`tmp_old_id`,
												`tmp_old_pid`
											)
										VALUES
											(
												'$prediction_id',
												'$predictiongamemember->user_id',
												'$predictiongamemember->approved',
												$fav_team,
												$champ_tipp,
												$slogan,
												'$predictiongamemember->reminder',
												'$predictiongamemember->receipt',
												'$predictiongamemember->tipadmin',
												$picture,
												$last_tipp,
												'$predictiongamemember->id',
												'$predictiongamemember->project_id'
											)";
				$db->setQuery($query);
				if (!$db->query())
				{
					$result=false;
					echo PrintStepResult($result).'<br />';
					echo '<br />'.$db->getErrorMsg().' <br />';
				}
				else
				{
					$i++;
				}
			}
			else
			{
				$prediction_id=0;
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}

	if ($i==count($predictiongamemembers)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><br /><b style="color:'.$color.';" >'.JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($predictiongamemembers)).'</b><br /><br />';

	return '';
}

function TipResultsToPredictionResults()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;
	//$query='TRUNCATE TABLE `#__joomleague_prediction_result`'; $db->setQuery($query); $db->query();
	echo JText::sprintf('Copying --> Table content of [%1$s] to [%2$s]','<b>joomleague_tip_results</b>','<b>joomleague_prediction_result</b>');
	$query='SELECT * FROM #__joomleague_tip_results'; $db->setQuery($query);
	if ($predictiongametipresults=$db->loadObjectList()) // get old predictiongame_tip_results...
	{
		$query='SELECT prediction_id,tmp_old_pid FROM #__joomleague_prediction_project'; $db->setQuery($query);
		$dPredProject=$db->loadAssocList('tmp_old_pid');
		//echo '<pre>~'.print_r($dPredProject,true).'~</pre>';
		$query='SELECT id,tmp_old_pid FROM #__joomleague_project'; $db->setQuery($query);
		$dProjects=$db->loadAssocList('tmp_old_pid');
		$query='SELECT id,tmp_old_match_id FROM #__joomleague_match'; $db->setQuery($query);
		$dMatches=$db->loadAssocList('tmp_old_match_id');
		foreach ($predictiongametipresults as $predictiongametipresult)
		{
			if (isset($dPredProject[$predictiongametipresult->project_id]))
			{
				$prediction_id=$dPredProject[$predictiongametipresult->project_id]['prediction_id']; // get new prediction_id...
				if (isset($dProjects[$predictiongametipresult->project_id])) // get new project_id...
				{
					$project_id=$dProjects[$predictiongametipresult->project_id]['id']; // get new project id of predProject...
					if (isset($dMatches[$predictiongametipresult->match_id])) // get new match_id...
					{
						$match_id=$dMatches[$predictiongametipresult->match_id]['id'];
						if ($predictiongametipresult->tip!='0') //smallint tip
						{
							$tipp=empty($predictiongametipresult->tip) ? 'NULL' : "'".$predictiongametipresult->tip."'";
						}
						else
						{
							$tipp="'0'";
						}
						if ($predictiongametipresult->tip_home!='0') //smallint tip_home
						{
							$tipp_home=empty($predictiongametipresult->tip_home) ? 'NULL' : "'".$predictiongametipresult->tip_home."'";
						}
						else
						{
							$tipp_home="'0'";
						}
						if ($predictiongametipresult->tip_away!='0') //smallint tip_away
						{
							$tipp_away=empty($predictiongametipresult->tip_away) ? 'NULL' : "'".$predictiongametipresult->tip_away."'";
						}
						else
						{
							$tipp_away="'0'";
						}
						if ($predictiongametipresult->joker!='0') //tinyint joker
						{
							$joker=empty($predictiongametipresult->joker) ? 'NULL' : "'".$predictiongametipresult->joker."'";
						}
						else
						{
							$joker="'0'";
						}
						$copied=true;
						$result=true;
						$query="	INSERT INTO	#__joomleague_prediction_result
													(
														`prediction_id`,
														`user_id`,
														`project_id`,
														`match_id`,
														`tipp`,
														`tipp_home`,
														`tipp_away`,
														`joker`,
														`tmp_old_id`
													)
												VALUES
													(
														'$prediction_id',
														'$predictiongametipresult->user_id',
														'$project_id',
														'$match_id',
														$tipp,
														$tipp_home,
														$tipp_away,
														$joker,
														'$predictiongametipresult->id'
													)";
						$db->setQuery($query);
						if (!$db->query())
						{
							$result=false;
							echo PrintStepResult($result).'<br />';
							echo '<br />'.$db->getErrorMsg().' <br />';
						}
						else
						{
							$i++;
						}
					}
					else
					{
						$match_id=0;
					}
				}
				else
				{
					$project_id=0;
				}
			}
			else
			{
				$prediction_id=0;
			}
		}
	}
	else
	{
		$result=false;
		echo PrintStepResult($result).'<br />';
		echo '<br />'.$db->getErrorMsg().' <br />';
	}
	if ($i==count($predictiongametipresults)){$color='green';}elseif($i==0){$color='red';}else{$color='orange';}
	echo '<br /><br /><b style="color:'.$color.';" >'.JText::sprintf('JL_DB_UPDATE_COPIED_X_OF_Y_RECORDS',$i,count($predictiongametipresults)).'</b><br /><br />';
	return '';
}

// ------------------------------------------------------------------------------------------------------------------------


function checklistPredictionTemplates()
{
	$db = JFactory::getDBO();
	$result=true;
	$copied=false;
	$i=0;

	echo '<b>'.JText::_('Adding all templates to existing Prediction Games').'</b>';

	$query='SELECT id FROM #__joomleague_prediction_game'; $db->setQuery($query);

	if ($predictiongameIDs=$db->loadObjectList()) // get all predictiongame_ids...
	{
		foreach ($predictiongameIDs as $predictiongameID)
		{
			$prediction_id=$predictiongameID->id;
			$defaultpath=JPATH_COMPONENT_SITE.DS.'settings';
			$extensiontpath=JPATH_COMPONENT_SITE.DS.'extensions'.DS;
			$templatePrefix='prediction';

			if (!$prediction_id){return;}

			// get info from prediction game
			$query='SELECT master_template FROM #__joomleague_prediction_game WHERE id='.(int) $prediction_id;

			$db->setQuery($query);
			$params=$db->loadObject();

			// if it's not a master template,do not create records.
			if ($params->master_template){return true;}

			// otherwise,compare the records with the files // get records
			$query='SELECT template FROM #__joomleague_prediction_template WHERE prediction_id='.(int) $prediction_id;

			$db->setQuery($query);
			$records=$db->loadResultArray();
			if (empty($records)){$records=array();}

			// first check extension template folder if template is not default
			if ((isset($params->extension)) && ($params->extension!=''))
			{
				if (is_dir($extensiontpath.$params->extension.DS.'settings'))
				{
					$xmldirs[]=$extensiontpath.$params->extension.DS.'settings';
				}
			}

			// add default folder
			$xmldirs[]=$defaultpath.DS.'default';

			// now check for all xml files in these folders
			foreach ($xmldirs as $xmldir)
			{
				if ($handle=opendir($xmldir))
				{
					//check that each xml template has a corresponding record in the
					//database for this project. If not,create the rows with default values
					//from the xml file
					while ($file=readdir($handle))
					{
						if ($file!='.'&&$file!='..'&&strtolower(substr($file,(-3)))=='xml'&&
							strtolower(substr($file,0,strlen($templatePrefix)))==$templatePrefix)
						{
							$template=substr($file,0,(strlen($file)-4));

							if ((empty($records)) || (!in_array($template,$records)))
							{
								//template not present,create a row with default values
								$params=new JLParameter(null,$xmldir.DS.$file);

								//get the values
								$defaultvalues=array();
								foreach ($params->getGroups() as $key => $group)
								{
									foreach ($params->getParams('params',$key) as $param)
									{
										$defaultvalues[]=$param[5].'='.$param[4];
									}
								}
								$defaultvalues=implode('\n',$defaultvalues);

								$title=JText::_($params->name);
								$query ="	INSERT INTO #__joomleague_prediction_template (title,prediction_id,template,params)
												VALUES ('$title','$prediction_id','$template','$defaultvalues')";

								$db->setQuery($query);
								//echo error,allows to check if there is a mistake in the template file
								if (!$db->query())
								{
									//$this->setError($db->getErrorMsg());
									//return false;
								}
								array_push($records,$template);
							}
						}
					}
					closedir($handle);
				}
			}
		}
	}

	return '';
}

// ------------------------------------------------------------------------------------------------------------------------

?>
<hr>
<?php
	$mtime=microtime();
	$mtime=explode(" ",$mtime);
	$mtime=$mtime[1] + $mtime[0];
	$starttime=$mtime;

	$totalUpdateParts=16;
	setUpdatePart();

	$output1=JText::_('JL_DB_UPDATE');

	$output2='<span style="color:green; ">';
		$output2 .= JText::sprintf('JL_DB_UPDATE_TITLE',$lastVersion,$version,$updateFileDate,$updateFileTime);
	$output2 .= '</span>';
	JToolBarHelper::title($output1);
	echo '<p><h2 style="text-align:center; ">'.$output2.'</h2></p>';

	echo '<p><h3 style="text-align:center; color:red; ">';
		echo JText::_('JL_DB_UPDATE_VERIFY_TEXT');
	echo '</h3></p>';

	echo '<p style="text-align:center; ">'.JText::sprintf('JL_DB_UPDATE_TOTALSTEPS','<b>'.$totalUpdateParts.'</b>').'</p>';
	echo '<p style="text-align:center; ">'.JText::sprintf('JL_DB_UPDATE_STEP_OF_STEP','<b>'.getUpdatePart().'</b>','<b>'.$totalUpdateParts.'</b>').'</p>';

/**/
	if (getUpdatePart() < $totalUpdateParts)
	{
		// Add here a color transformation for <a> so it is easier to see that a new step has to be confirmed
		echo '<table align="center" width="80%" border="0"><tr><td width="50%">';
			$outStr='<h3 style="text-align:center; ">';
				$outStr .= '<a href="javascript:location.reload(true)" >';
					$outStr .= '<strong>';
						$outStr .= JText::sprintf('JL_DB_UPDATE_CLICK_HERE',getUpdatePart()+1,$totalUpdateParts);
					$outStr .= '</strong>';
				$outStr .= '</a>';
			$outStr .= '</h3>';
			if (getUpdatePart()%2 ==1)
			{
				echo $outStr.'</td><td width="50%">&nbsp;';
			}
			else
			{
				echo '&nbsp;</td><td width="50%">'.$outStr;
			}
			echo '</td></tr>';
		echo '</table>';

		echo '<p style="text-align:center; ">';
			echo '<b>';
				echo JText::sprintf('JL_DB_UPDATE_REMEMBER_TOTAL_STEPS_COUNT',$totalUpdateParts);
			echo '</b>';
			echo '<br />';
			echo JText::_('JL_DB_UPDATE_SCROLL_DOWN');
		echo '</p>';
		echo '<p style="text-align:center; ">';
			echo JText::_('JL_DB_UPDATE_INFO_UNKNOWN_ETC').'<br />';
			echo JText::_('JL_DB_UPDATE_INFO_JUST_INFOTEXT').'<br />';
		echo '</p>';
	}
	echo '<hr>';
/**/

	if (getUpdatePart()==1)
	{
		echo '<p>';
			echo '<h3>';
				echo '<span style="color:orange">';
					echo JText::sprintf(	'JL_DB_UPDATE_DELETE_WARNING',
											'</span><b><i><a href="index.php?option=com_user&task=logout">',
											'</i></b></a><span style="color:orange">');
				echo '</span>';
			echo '</h3>';
		echo '</p>';
		$JLTablesVersion=getVersion();
		if (($JLTablesVersion!='') && ($JLTablesVersion<'0.93'))
		{
			echo '<span style="color:red">';
				echo JText::_('JL_DB_UPDATE_ATTENTION');
				echo '<br /><br />';
				echo JText::_('You are updating from an older release of JoomLeague than 0.93!');
				echo '<br />';
				echo JText::sprintf('Actually your JoomLeague-MYSQL-Tables are ready for JoomLeague v%1$s','<b>'.$JLTablesVersion.'</b>');
				echo '<br />';
				echo JText::_('Update may not be completely sucessfull as we require JoomLeague-MYSQL-tables according to the release 0.93!');
			echo '</span><br />';
			echo '<span style="color:green">';
				echo JText::sprintf(	'It would be better to update your JoomLeague installation to v0.93 before you update to JoomLeague %1$s!',
										'<b>'.$version.'</b>');
			echo '</span><br /><br />';
			echo '<span style="color:red">'.JText::_('JL_DB_UPDATE_DANGER').'</span><br /><br />';
			echo '<span style="color:red">'.JText::_('This script also DELETES the content of some JoomLeague v1.5 related tables inside your database without warning to update them by using the data of the JoomLeague 0.93b tables!!!').'</span><br />';
			echo '<span style="color:red">'.JText::_('PLEASE use this script ONLY IF you REALLY know what you do!!!').'</span><br />';
		}
	}

	if (getUpdatePart()==2)
	{
		echo HandleVersion();
		echo '<hr>';
		echo HandlePositionEventtype();
		echo '<hr>';
		echo HandleTemplateConfig();
		echo '<hr>';
		echo addSportsType();
		echo '<hr>';
		echo TruncateTablesForDevelopment();
		echo '<hr>';

		echo Update_Tables($updates,'joomleague_project');
		echo Update_Tables($updates,'joomleague_person');
		echo Update_Tables($updates,'joomleague_playground');
		echo Update_Tables($updates,'joomleague_club');
		echo Update_Tables($updates,'joomleague_league');
		echo Update_Tables($updates,'joomleague_season');
		echo Update_Tables($updates,'joomleague_project_team');
		echo Update_Tables($updates,'joomleague_team');
		echo Update_Tables($updates,'joomleague_division');
		echo Update_Tables($updates,'joomleague_team_player');
		echo Update_Tables($updates,'joomleague_team_staff');
		echo Update_Tables($updates,'joomleague_position');
		echo Update_Tables($updates,'joomleague_eventtype');
		echo Update_Tables($updates,'joomleague_round');
		echo Update_Tables($updates,'joomleague_match');
		echo Update_Tables($updates,'joomleague_position_eventtype');
		echo Update_Tables($updates,'joomleague_prediction_game');
		echo Update_Tables($updates,'joomleague_prediction_admin');
		echo Update_Tables($updates,'joomleague_prediction_project');
		echo Update_Tables($updates,'joomleague_prediction_member');
		echo Update_Tables($updates,'joomleague_prediction_result');
		echo Update_Tables($updates,'joomleague_template_config');
		echo '<hr>';
		//echo addStandardsForSoccer().'<br />';
	}
	unset($updates);
	unset($updates2);

	if (getUpdatePart()==3)
	{
		if (tableExists('joomleague_leagues')){echo LeaguesToLeague(); echo '<hr>';}
		if (tableExists('joomleague_seasons')){echo SeasonsToSeason(); echo '<hr>';}
		if (tableExists('joomleague_eventtypes')){echo EventtypesToEventtype(); echo '<hr>';}
		if (tableExists('joomleague_positions')){echo PositionsToPosition(); echo '<hr>';}
		if (tableExists('joomleague_position_eventtypes')){echo PositionEventtypesToPositionEventtype(); echo '<hr>';}
		if (tableExists('joomleague_playgrounds')){echo PlaygroundsToPlayground(); echo '<hr>';}
	}

	if (getUpdatePart()==4)
	{
		if (tableExists('joomleague_clubs'))
		{
			echo ClubsToClub(); echo '<hr>';
			echo ConvertPlaygroundClubID(); echo '<hr>';
		}
		if (tableExists('joomleague_teams')){echo TeamsToTeam(); echo '<hr>';}
		if (tableExists('joomleague_players')){echo PlayersToPerson(); echo '<hr>';}
	}

	if (getUpdatePart()==5)
	{
		if (tableExists('joomleague_referees')){echo RefereesToPerson(); echo '<hr>';}
		if (tableExists('joomleague')){echo JoomleagueToProject(); echo '<hr>';}
		if (tableExists('joomleague_divisions')){echo DivisionsToDivision(); echo '<hr>';}
		if (tableExists('joomleague_pos_joomleague')){echo PosjoomleagueToProjectposition(); echo '<hr>';}
		if (tableExists('joomleague_rounds')){echo RoundsToRound(); echo '<hr>';}
		if (tableExists('joomleague_team_joomleague')){echo TeamjoomleagueToProjectteam(); echo '<hr>';}
	}

	if (getUpdatePart()==6)
	{
		echo UpdateTemplateProject_ids().'<br />';
	}

	if (getUpdatePart()==7)
	{
		echo UpdateMasterTemplateProject_ids().'<br />';
	}

	if (getUpdatePart()==8)
	{
		echo UpdateTemplateMasters().'<br />';
	}

	if (getUpdatePart()==9)
	{
		if (tableExists('joomleague_playertool')){echo PlayertoolToTeamplayer(); echo '<hr>';}
		if (tableExists('joomleague_teamstaff_project')){echo TeamstaffprojectToTeamstaff(); echo '<hr>';}
	}

	if (getUpdatePart()==10)
	{
		if (tableExists('joomleague_matches')){echo MatchesToMatch(); echo '<hr>';}
	}

	if (getUpdatePart()==11)
	{
		if (tableExists('joomleague_match_events')){echo MatcheventsToMatchevent(); echo '<hr>';}
	}

	if (getUpdatePart()==12)
	{
		if (tableExists('joomleague_match_players')){echo MatchplayersToMatchplayer(); echo '<hr>';}
	}

	if (getUpdatePart()==13)
	{
		if (tableExists('joomleague_matches')){echo MatchrefereesToMatchrefereeAndProjectReferee(); echo '<hr>';}
	}

	if (getUpdatePart()==14)
	{
		if (tableExists('joomleague_predictiongame'))
		{
			echo PredictionGameToPredictionGame(); echo '<hr>';
			echo checklistPredictionTemplates().'<br />'; echo '<hr>';
		}
		if (tableExists('joomleague_predictiongame_admins')){echo PredictionGameAdminsToPredictionAdmin(); echo '<hr>';}
		if (tableExists('joomleague_predictiongame_project')){echo PredictionGameProjectToPredictionProject(); echo '<hr>';}
		if (tableExists('joomleague_tip_members')){echo TipMembersToPredictionMember(); echo '<hr>';}
	}

	if (getUpdatePart()==15)
	{
		if (tableExists('joomleague_tip_results')){echo TipResultsToPredictionResults(); echo '<hr>';}
	}

	if (getUpdatePart()==$totalUpdateParts)
	{
		echo JText::_('Deleting temporary fields in the tables which were needed for the update routine!').'<br /><br />';
		echo Delete_Table_Columns($updates3,'joomleague_template_config');
		echo Delete_Table_Columns($updates3,'joomleague_prediction_result');
		echo Delete_Table_Columns($updates3,'joomleague_prediction_member');
		echo Delete_Table_Columns($updates3,'joomleague_prediction_project');
		echo Delete_Table_Columns($updates3,'joomleague_prediction_admin');
		echo Delete_Table_Columns($updates3,'joomleague_prediction_game');
		echo Delete_Table_Columns($updates3,'joomleague_position_eventtype');
		echo Delete_Table_Columns($updates3,'joomleague_match');
		echo Delete_Table_Columns($updates3,'joomleague_round');
		echo Delete_Table_Columns($updates3,'joomleague_eventtype');
		echo Delete_Table_Columns($updates3,'joomleague_position');
		echo Delete_Table_Columns($updates3,'joomleague_team_staff');
		echo Delete_Table_Columns($updates3,'joomleague_team_player');
		echo Delete_Table_Columns($updates3,'joomleague_division');
		echo Delete_Table_Columns($updates3,'joomleague_team');
		echo Delete_Table_Columns($updates3,'joomleague_project_team');
		echo Delete_Table_Columns($updates3,'joomleague_season');
		echo Delete_Table_Columns($updates3,'joomleague_league');
		echo Delete_Table_Columns($updates3,'joomleague_club');
		echo Delete_Table_Columns($updates3,'joomleague_playground');
		echo Delete_Table_Columns($updates3,'joomleague_person');
		echo Delete_Table_Columns($updates3,'joomleague_project');
		echo '<br />';
		echo '<hr>';

		echo updateVersion($version,$updatefilename).'<br />';
		echo '<hr>';

		echo '<p><h1 style="text-align:center; color:green; ">';
			echo JText::_('JL_DB_UPDATE_CONGRATULATIONS');
			echo '<br />';
			echo JText::_('JL_DB_UPDATE_ALL_STEPS_FINISHED');
			echo '<br />';
			echo JText::_('JL_DB_UPDATE_USE_NOW');
		echo '</h1></p>';

		setUpdatePart(0);
	}
	else
	{
		echo '<h3 style="text-align:center; ">';
			echo '<a href="javascript:location.reload(true)">';
				echo '<strong>';
					echo JText::sprintf('JL_DB_UPDATE_CLICK_HERE',getUpdatePart()+1,$totalUpdateParts).'<br />';
					echo JText::_('JL_DB_UPDATE_MAY_NEED_TIME').'<br />';
				echo '</strong>';
			echo '</a>';
		echo '</h3>';
		echo '<p style="text-align:center; ">';
			echo JText::sprintf('JL_DB_UPDATE_TIME_MEMORY_SET',$maxImportTime,$maxImportMemory).'<br />';
			echo JText::_('JL_DB_UPDATE_INFO_TIMEOUT_ERROR').'<br />';
			echo JText::_('JL_DB_UPDATE_INFO_LOCAL_UPDATE').'<br />';
		echo '</p>';
		echo '<h2 style="text-align:center; color:orange; ">';
			echo JText::_('JL_DB_UPDATE_BE_PATIENT');
		echo '</h2>';
	}
	if (JComponentHelper::getParams('com_joomleague')->get('show_debug_info',0))
	{
		echo '<center><hr>';
			echo JText::sprintf('Memory Limit is %1$s',ini_get('memory_limit')).'<br />';
			echo JText::sprintf('Memory Peak Usage was %1$s Bytes',number_format(memory_get_peak_usage(true),0,'','.')).'<br />';
			echo JText::sprintf('Time Limit is %1$s seconds',ini_get('max_execution_time')).'<br />';
			$mtime=microtime();
			$mtime=explode(" ",$mtime);
			$mtime=$mtime[1] + $mtime[0];
			$endtime=$mtime;
			$totaltime=($endtime - $starttime);
			echo JText::sprintf('This page was created in %1$s seconds',$totaltime);
		echo '<hr></center>';
	}

?>