<?php
/**
 * Round-robin tournament helper bundled with SportsManagement.
 *
 * @version    0.21
 * @package    Sportsmanagement
 * @subpackage helpers
 * @file       class.roundrobin.php
 * @author     Felix Stiehler
 * @copyright  Copyright (c) 2009 Felix Stiehler
 * @license    MIT License; see the original notice below
 */
////////////////////////////////////////////////////////////////////////////////////
// +-------------------------------------------------------------------------------+
// | class.roundrobin.php                                                          |
// +-------------------------------------------------------------------------------+
// | Author        Felix Stiehler                                                  |
// | Version       0.21                                                            |
// | Last modified 26/07/2009                                                      |
// | Email         hide@address.com                                                |
// | Licence       MIT license - http://opensource.org/licenses/mit-license.php    |
// +-------------------------------------------------------------------------------+
// | The MIT License                                                               |
// |                                                                               |
// | Copyright (c) <2009> <Felix Stiehler>                                         |
// |                                                                               |
// | Permission is hereby granted, free of charge, to any person obtaining a copy  |
// | of this software and associated documentation files (the "Software"), to deal |
// | in the Software without restriction, including without limitation the rights  |
// | to use, copy, modify, merge, publish, distribute, sublicense, and/or sell     |
// | copies of the Software, and to permit persons to whom the Software is         |
// | furnished to do so, subject to the following conditions:                      |
// |                                                                               |
// | The above copyright notice and this permission notice shall be included in    |
// | all copies or substantial portions of the Software.                           |
// |                                                                               |
// | THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR    |
// | IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,      |
// | FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE   |
// | AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER        |
// | LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, |
// | OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN     |
// | THE SOFTWARE.                                                                 |
// +-------------------------------------------------------------------------------+
////////////////////////////////////////////////////////////////////////////////////

defined('_JEXEC') or die('Restricted access');

class roundrobin
{
    public $finished;
    public $error;
    public $matchdays_created;
    public $raw_matches_created;
    public $free_ticket;
    public $free_ticket_identifer;
    public $matches;

    private $match_pointer;
    private $matchday_pointer;
    private $teams;
    private $teams_1;
    private $teams_2;

    /**
     * PHP 8 compatible constructor.
     *
     * @param array|null $passed_teams Teams which play the tournament.
     */
    public function __construct($passed_teams = null)
    {
        $this->initialize($passed_teams);
    }

    /**
     * Historical PHP 4 constructor retained for callers invoking it explicitly.
     *
     * @deprecated Use __construct().
     */
    public function roundrobin($passed_teams = null)
    {
        $this->initialize($passed_teams);
    }

    private function initialize($passed_teams = null): void
    {
        $this->teams = $passed_teams;
        $this->finished = false;
        $this->error = '';
        $this->matchdays_created = false;
        $this->raw_matches_created = false;
        $this->free_ticket = true;
        $this->free_ticket_identifer = 'Free ticket';
        $this->matchday_pointer = 0;
        $this->match_pointer = 0;
        $this->matches = [];
        $this->teams_1 = null;
        $this->teams_2 = null;
    }

    public function pass_teams($passed_teams)
    {
        $this->teams = $passed_teams;

        return true;
    }

    public function create_matches()
    {
        if (!$this->valid_team_array()) {
            return false;
        }

        $this->matches = [];

        if (count($this->teams) % 2) {
            $split = (int) ceil(count($this->teams) / 2);
            $this->teams_1 = array_slice($this->teams, 0, $split);
            $this->teams_2 = array_slice($this->teams, $split);
            $this->teams_2[] = $this->free_ticket_identifer;
        } else {
            $split = (int) (count($this->teams) / 2);
            $this->teams_1 = array_slice($this->teams, 0, $split);
            $this->teams_2 = array_slice($this->teams, $split);
        }

        for ($i = 2; $i < (count($this->teams_1) * 2); $i++) {
            $this->save_matchday();
            $this->rotate();
        }
        $this->save_matchday();

        $this->finished = true;
        $this->raw_matches_created = false;
        $this->matchdays_created = true;
        $this->clear_pointer();

        return $this->matches;
    }

    private function valid_team_array()
    {
        if (!is_array($this->teams) || count($this->teams) < 2) {
            $this->error = 'Not enough teams in array shape passed';
            $this->finished = false;
            $this->raw_matches_created = false;
            $this->matchdays_created = false;
            $this->matches = [];
            $this->clear_pointer();

            return false;
        }

        return true;
    }

    private function clear_pointer()
    {
        $this->matchday_pointer = 0;
        $this->match_pointer = 0;

        return true;
    }

    private function save_matchday()
    {
        $matches = [];

        for ($i = 0; $i < count($this->teams_1); $i++) {
            if (
                $this->free_ticket
                || ($this->teams_1[$i] != $this->free_ticket_identifer
                    && $this->teams_2[$i] != $this->free_ticket_identifer)
            ) {
                $matches[] = [$this->teams_1[$i], $this->teams_2[$i]];
            }
        }

        $this->matches[] = $matches;

        return true;
    }

    private function rotate()
    {
        $temp = $this->teams_1[1];

        for ($i = 1; $i < (count($this->teams_1) - 1); $i++) {
            $this->teams_1[$i] = $this->teams_1[$i + 1];
        }

        $this->teams_1[count($this->teams_1) - 1] = end($this->teams_2);

        for ($i = count($this->teams_2) - 1; $i > 0; $i--) {
            $this->teams_2[$i] = $this->teams_2[$i - 1];
        }

        $this->teams_2[0] = $temp;

        return true;
    }

    public function create_raw_matches()
    {
        if (!$this->valid_team_array()) {
            return false;
        }

        $this->matches = [];

        for ($i = 0; $i < count($this->teams); $i++) {
            for ($i2 = $i + 1; $i2 < count($this->teams); $i2++) {
                $this->matches[] = [$this->teams[$i], $this->teams[$i2]];
            }
        }

        $this->finished = true;
        $this->raw_matches_created = true;
        $this->matchdays_created = false;
        $this->clear_pointer();

        return $this->matches;
    }

    public function next_match()
    {
        if ($this->raw_matches_created) {
            if (isset($this->matches[$this->match_pointer])) {
                $this->match_pointer++;

                return $this->matches[$this->match_pointer - 1];
            }

            return false;
        }

        if ($this->matchdays_created) {
            if (isset($this->matches[$this->matchday_pointer - 1][$this->match_pointer])) {
                $this->match_pointer++;

                return $this->matches[$this->matchday_pointer - 1][$this->match_pointer - 1];
            }

            return false;
        }

        $this->error = 'No matches created yet.';

        return false;
    }

    public function next_matchday()
    {
        if ($this->raw_matches_created) {
            $this->error = 'No matchdays created within last action.';

            return false;
        }

        if ($this->matchdays_created) {
            if (isset($this->matches[$this->matchday_pointer])) {
                $this->matchday_pointer++;
                $this->match_pointer = 0;

                return $this->matches[$this->matchday_pointer - 1];
            }

            return false;
        }

        $this->error = 'No matches created yet.';

        return false;
    }

    public function generateRRSchedule(array $players, $rand = false)
    {
        $numPlayers = count($players);

        if ($numPlayers % 2) {
            $players[] = null;
            $numPlayers++;
        }

        $numSets = $numPlayers - 1;
        $numMatches = (int) ($numPlayers / 2);
        $matchups = [];

        for ($j = 0; $j < $numSets; $j++) {
            $halves = array_chunk($players, $numMatches);
            $halves[1] = array_reverse($halves[1]);

            for ($i = 0; $i < $numMatches; $i++) {
                $matchups[$j][$i][0] = $halves[0][$i];
                $matchups[$j][$i][1] = $halves[1][$i];
            }

            $first = array_shift($players);
            $players[] = array_shift($players);
            array_unshift($players, $first);
        }

        if ($rand) {
            foreach ($matchups as &$match) {
                shuffle($match);
            }
            unset($match);
            shuffle($matchups);
        }

        return $matchups;
    }
}
