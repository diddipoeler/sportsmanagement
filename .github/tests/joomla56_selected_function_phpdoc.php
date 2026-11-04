<?php
/**
 * Verify function-level PHPDoc in the Joomla 5/6 files updated together.
 *
 * Usage: php .github/tests/joomla56_selected_function_phpdoc.php
 *
 * This focuses on the current migration batch; the separate all-PHP metadata
 * audit remains responsible for file-level headers throughout the project.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$paths = [
    'site/src/Controller/ResultsController.php',
    'site/src/Model/AboutModel.php',
    'site/src/Model/AllclubsModel.php',
    'site/src/Model/AllpersonsModel.php',
    'site/src/Model/IcalModel.php',
    'site/src/Controller/EditpersonController.php',
    'site/src/Controller/PredictionentryController.php',
    'site/src/Controller/AjaxController.php',
    'site/src/Controller/MatchesController.php',
    'site/src/Controller/PredictionrankingController.php',
    'site/src/Controller/PredictionuserController.php',
    'site/src/Model/RankingModel.php',
    'site/src/Model/PredictionuserModel.php',
    'site/src/Model/PredictionentryModel.php',
    'site/src/Model/ClubinfoModel.php',
    'site/src/Model/UpdsportsmanagementModel.php',
    'site/src/Controller/UpdsportsmanagementController.php',
    'site/src/Service/SportsManagementSiteApplicationResolver.php',
    'admin/src/Model/AgegroupsModel.php',
    'admin/src/Controller/AgegroupsController.php',
    'admin/src/Controller/EventtypesController.php',
    'admin/src/Model/ClubsModel.php',
    'admin/src/View/Clubs/HtmlView.php',
    'admin/src/Model/EventtypesModel.php',
    'admin/src/Model/DivisionsModel.php',
    'admin/src/Model/SeasonsModel.php',
    'admin/src/Model/ProjectsModel.php',
    'admin/src/Model/MatchesModel.php',
    'admin/src/Model/TeamsModel.php',
    'admin/src/Model/PlaygroundsModel.php',
    'admin/src/Controller/SeasonsController.php',
    'admin/src/Controller/DivisionsController.php',
    'admin/src/Controller/LeaguesController.php',
    'admin/src/Controller/DatabasetoolController.php',
    'modules/mod_sportsmanagement_count_rekord/helper.php',
    'admin/src/Model/ClubnamesModel.php',
    'modules/mod_sportsmanagement_birthday/src/Helper/BirthdayHelper.php',
    'modules/mod_sportsmanagement_ranking/src/Helper/RankingHelper.php',
    'modules/mod_sportsmanagement_club_birthday/src/Helper/ClubBirthdayHelper.php',
    'modules/mod_sportsmanagement_navigation_menu/src/Helper/NavigationMenuHelper.php',
    'modules/mod_sportsmanagement_gcalendar/helper.php',
    'modules/mod_sportsmanagement_projectmap/src/Helper/ProjectMapHelper.php',
    'modules/mod_sportsmanagement_sports_type_statistics/src/Helper/SportsTypeStatisticsHelper.php',
    'modules/mod_sportsmanagement_trainingsdata/src/Helper/TrainingsDataHelper.php',
    'modules/mod_sportsmanagement_uefawertung/src/Helper/UefaWertungHelper.php',
    'modules/mod_sportsmanagement_uefawertung/helper.php',
    'admin/src/Field/AvatarfromcomponentField.php',
    'modules/mod_sportsmanagement_calendar/src/Helper/CalendarHelper.php',
    'modules/mod_sportsmanagement_liveticker/src/Helper/LivetickerHelper.php',
];
$errors = [];
$methods = 0;

foreach ($paths as $path) {
    $source = file_get_contents($root . '/' . $path);

    if ($source === false) {
        $errors[] = "$path: cannot read file";
        continue;
    }

    $tokens = token_get_all($source);

    foreach ($tokens as $index => $token) {
        if (!is_array($token) || $token[0] !== T_FUNCTION) {
            continue;
        }

        // Anonymous closures cannot have a named-method PHPDoc contract.
        $next = $index + 1;

        while (isset($tokens[$next])
            && ((is_array($tokens[$next]) && $tokens[$next][0] === T_WHITESPACE)
                || $tokens[$next] === '&')) {
            ++$next;
        }

        if (!isset($tokens[$next]) || !is_array($tokens[$next])
            || $tokens[$next][0] !== T_STRING) {
            continue;
        }

        ++$methods;
        $previous = $index - 1;

        while ($previous >= 0) {
            $candidate = $tokens[$previous];
            $type = is_array($candidate) ? $candidate[0] : null;

            if ($type === T_WHITESPACE
                || in_array($type, [T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC, T_FINAL, T_ABSTRACT], true)) {
                --$previous;
                continue;
            }

            break;
        }

        if ($previous < 0 || !is_array($tokens[$previous])
            || $tokens[$previous][0] !== T_DOC_COMMENT) {
            $errors[] = "$path: missing PHPDoc for " . $tokens[$next][1]
                . '() at line ' . $token[2];
        }
    }
}

foreach ($errors as $error) {
    fwrite(STDERR, $error . PHP_EOL);
}

printf("Checked %d named functions in %d Joomla 5/6 files: %d missing PHPDoc.\n",
    $methods, count($paths), count($errors));
exit($errors === [] ? 0 : 1);
