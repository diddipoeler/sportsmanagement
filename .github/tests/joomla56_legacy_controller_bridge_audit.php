<?php
/**
 * Joomla 5/6 legacy administrator controller bridge audit.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

$controllerDirectory = dirname(__DIR__, 2) . '/admin/controllers';
$checked = 0;
$errors = [];

$validatedBridges = [
    'sportsmanagement.php', 'sportsmanagements.php',
    'smextxmleditor.php', 'smextxmleditors.php',
    'smquote.php', 'smquotes.php',
    'jlextassociation.php', 'jlextassociations.php',
    'jlextcountry.php', 'jlextcountries.php',
    'jlextfederation.php', 'jlextfederations.php',
    'jlextindividualsport.php', 'jlextindividualsportes.php',
    'predictiongame.php', 'predictiongames.php',
    'predictiongroup.php', 'predictiongroups.php',
    'predictionmember.php', 'predictionmembers.php',
    'predictionround.php', 'predictionrounds.php',
    'predictiontemplate.php', 'predictiontemplates.php',
    'project.php', 'projects.php',
    'projectteam.php', 'projectteams.php',
    'projectposition.php', 'projectpositions.php',
    'projectreferee.php', 'projectreferees.php',
    'joomleagueimport.php', 'joomleagueimports.php', 'jlxmlimport.php',
    'jsmgcalendar.php', 'jsmgcalendars.php', 'jsmgcalendarimport.php',
    'jlextdbbimport.php', 'jlextdfbkeyimport.php',
    'jlextdfbnetplayerimport.php', 'jlextlmoimports.php',
    'jlextprofleagimport.php', 'jlextsisimport.php',
    'jlexthandballnet.php',
    'smimageimport.php', 'smimageimports.php',
    'smquotetxt.php', 'transifex.php',
    'github.php', 'githubinstall.php', 'installhelper.php',
    'agegroup.php', 'agegroups.php', 'currentseason.php',
    'databasetool.php', 'databasetools.php',
    'ajax.php', 'ajaxcalls.php', 'ajax.json.php', 'imagehandler.php',
    'league.php', 'leagues.php', 'club.php', 'clubs.php',
    'team.php', 'teams.php', 'template.php', 'templates.php',
    'treeto.php', 'treetomatch.php', 'treetonode.php', 'treetos.php',
    'eventtype.php', 'eventtypes.php', 'division.php', 'divisions.php',
    'position.php', 'positions.php', 'round.php', 'rounds.php',
    'season.php', 'seasons.php', 'match.php', 'matches.php',
    'player.php', 'players.php', 'playground.php', 'playgrounds.php',
    'rosterposition.php', 'rosterpositions.php',
    'statistic.php', 'statistics.php', 'sportstype.php', 'sportstypes.php',
    'teamplayer.php', 'teamplayers.php',
    'clubname.php', 'clubnames.php', 'cpanel.php',
    'extrafield.php', 'extrafields.php', 'jlxmlexports.php',
    'predictionproject.php', 'teamtrainingsdata.php',
    'teamtrainingsdatas.php', 'update.php',
];

$availableFiles = array_map('basename', glob($controllerDirectory . '/*.php') ?: []);
$missingFromAudit = array_diff($availableFiles, $validatedBridges);
$missingFromDirectory = array_diff($validatedBridges, $availableFiles);

foreach ($missingFromAudit as $name) {
    $errors[] = "$name: controller missing from bridge audit";
}
foreach ($missingFromDirectory as $name) {
    $errors[] = "$name: audited controller missing from directory";
}

foreach ($validatedBridges as $name) {
    $file = $controllerDirectory . '/' . $name;
    $source = @file_get_contents($file);

    if ($source === false) {
        $errors[] = "$name: unreadable";
        continue;
    }

    // The administrator compatibility bridges in this regression set must keep their metadata.
    foreach (['@version', '@author', '@copyright', '@license'] as $tag) {
        if (!str_contains(substr($source, 0, 800), $tag)) {
            $errors[] = "$name: missing $tag";
        }
    }

    ++$checked;
    if (str_contains($source, "JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/")) {
        $errors[] = "$name: absolute native controller path remains";
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

echo "Audited $checked administrator compatibility bridges.\n";
