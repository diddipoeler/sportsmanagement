<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage helpers
 * @file       class.roundrobin.php
 * @author     Felix Stiehler
 * @copyright  Copyright (c) <2009> <Felix Stiehler>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
////////////////////////////////////////////////////////////////////////////////////
// +-------------------------------------------------------------------------------+
// | class.roundrobin.php                                                          |
// +-------------------------------------------------------------------------------+
// | Author        Felix Stiehler                                                  |
// | Version       0.21                                                            |
// | Last modified 26/07/2009                                                      |
// | Email         hide@address.com                                              |
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



// Class to create round robin matches. Can generate matchdays or not. Can generate
// 'free ticket matches' when there is an uneven number of teams given.
// Provides access to the matches in raw array format or via iterators (next_matchday(),
// next_match()).

// The core algorithm for matchdays is bases on an very nice algorithm
// that is described here: http://groups.google.com/group/net.works/msg/1f132ad5803e82a5

// Have fun!

defined('_JEXEC') or die('Restricted access');

class roundrobin
{
  
    /**
     * Is true when rounds have been created properly by using 'create_matches()'
     * or 'create_raw_matches()'
     *
     * Default value is false
     *
     * @access public
     * @var    boolean
     */   
    public $finished;
   
   
    /**
     * Holds the latest error message if there was one
     *
     * Default value is ''
     *
     * @access public
     * @var    string
     */
    public $error;
  
  
    /**
     * Is true when the last action was a successful run of 'create_matches'
     *
     * Default value is false
     *
     * @access public
     * @var    boolean
     */
    public $matchdays_created;
  
  
    /**
     * Is true when the last action was a successful run of 'create_raw_matches'
     *
     * Default value is false
     *
     * @access public
     * @var    boolean
     */
    public $raw_matches_created;
   
   
     /**
     * When there is an uneven number of teams, either one free ticket match per matchday can be created
     * or the match is ignored
     *
     * When true matches against 'free_ticket' are created
     * If false those matches will be excluded from the 'matches' property
     *
     * Default value is true
     *
     * @access public
     * @var    boolean
     */
    public $free_ticket;
  
  
    /**
     * Holds the string that identifies a free ticket
     *
     * Default value is 'free_ticket'
     *
     * @access public
     * @var    string
     */
    public $free_ticket_identifer;
  
  
    /**
     * Holds the Pointer to the next match to be returned by next_match()
     *
     * Default value is 0
     *
     * @access private
     * @var    integer
     */
    private $match_pointer;
  
  
    /**
     * Holds the Pointer to the next matchday to be returned by next_matchday()
     *
     * Default value is 0
     *
     * @access private
     * @var    integer
     */
    private $matchday_pointer;
  
  
  
     /**
     * Holds the teams that play against each other
     *
     * Default value is null
     *
     * @access private
     * @var    array
     */
    private $teams;
  
  
    /**
     * Holds one half of the teams that play against each other
     *
     * Default value is null
     *
     * @access private
     * @var    array
     */
    private $teams_1;
  
  
    /**
     * Holds one half of the teams that play against each other
     *
     * Default value is null
     *
     * @access private
     * @var    array
     */
    private $teams_2;
   
       
    /**
     * Holds the matches with the teams that go against each other after
     * successfully executing 'create_round_robin()'
     *
     * A match is an array containing the 2 opponents.
     * A matchday is represented by an array of match arrays
     *
     * When 'create_matches' called, $matches contains an array of the matchdays
     * When 'create_raw_matches' calles, $matches contains an array of matches
     *
     * Default value is an empty array
     *
     * @access public
     * @var    array
     */
    public $matches;
        
      
    /**
     * Constructor.
     *
     * If an array holding the teams got passed it assignes them to the
     * $teams property.
     *
     * If not the teams have to be passed by using the 'pass_teams()' function.
     *
     * @access public
     * @param  array $passed_teams the teams which play
     */
   
    public function roundrobin($passed_teams = null)
    {
        $this->teams = $passed_teams;
      
        //default properties
        $this->finished = false;
        $this->error = '';
        $this->matchdays_created = false;
        $this->raw_matches_created = false;
        $this->free_ticket = true;
        $this->free_ticket_identifer = 'Free ticket';
        $this->matchday_pointer = 0;
        $this->match_pointer = 0;
        $this->matches = array();                      
    }
  
  
    /**
     * Alternative way to pass the teams (unlike with the contructor)
     *
     * @access public
     * @param  array $passed_teams the teams which play
     * @return true
     */
  
    public function pass_teams($passed_teams)
    {
        $this->teams = $passed_teams;
        return true;  
    }
  
      
    /**
     * Creates the matches for the tournament which are stored in $matches.
     *
     * Does not start if $teams isn't an array or empty.
     *
     * @access public
     * @return false when error occured or the $matches array when successful;
     */
    public function create_matches()
    {
        if (!$this->valid_team_array()) {
            return false;
        }
           
        //clear $matches
        $this->matches = array();
      
        // create the two seperated arrays for the rotating algorithm
        if (count($this->teams) % 2) {          
            // when uneven number of teams
            $this->teams_1 = array_slice($this->teams, 0, ceil(count($this->teams)/2));
            $this->teams_2 = array_slice($this->teams, ceil(count($this->teams)/2));
            $this->teams_2[] = $this->free_ticket_identifer;      
        }
        else {
            $this->teams_1 = array_slice($this->teams, 0, count($this->teams)/2);
            $this->teams_2 = array_slice($this->teams, count($this->teams)/2);         
        }

        //start rotating / saving
        for ($i = 2; $i < (count($this->teams_1) * 2); $i++){
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
  
  
    /**
     * Inserts one matchday into the $matches array
     *
     * Takes care if matches with free tickets should be included
     *
     * @access private
     * @return true;
     */
    private function save_matchday()
    {
        for ($i = 0; $i < count($this->teams_1); $i++) {
            if ($this->free_ticket || ($this->teams_1[$i] != $this->free_ticket_identifer
                && $this->teams_2[$i] != $this->free_ticket_identifer)
            ) {
                $matches_tmp[] = array($this->teams_1[$i], $this->teams_2[$i]);
            } 
        }  
        $this->matches[] = $matches_tmp;
        return true;
    }
  
    /**
     * Rotates the 2 opponent arrays $teams_1, $teams_2 to create the next matchday matches
     *
     * Based on an algorithm described here: http://groups.google.com/group/net.works/msg/1f132ad5803e82a5
     *
     * @access private
     * @return true;
     */  
    private function rotate()
    {
        $temp = $this->teams_1[1];
        for($i = 1; $i < (count($this->teams_1) - 1); $i++) {
            $this->teams_1[$i] = $this->teams_1[$i + 1];
        }
        $this->teams_1[count($this->teams_1) - 1] = end($this->teams_2);
        for($i = (count($this->teams_2) - 1); $i > 0; $i--) {
            $this->teams_2[$i] = $this->teams_2[$i - 1];
        }
        $this->teams_2[0] = $temp;
        return true;
    }
  
  
    /**
     * Creates matches everybody against everybody without matchdays.
     * Free tickets will be ignored
     *
     * @access public
     * @return false when error occured, the match array when true
     */
   
    public function create_raw_matches()
    {
        if (!$this->valid_team_array()) {
            return false;
        }
          
        $this->matches = array();
      
        for ($i = 0; $i < count($this->teams); $i++) {
            for ($i2 = $i + 1; $i2 < count($this->teams); $i2++) {
                $this->matches[] = array($this->teams[$i], $this->teams[$i2]);
            }
        }
      
        $this->finished = true;      
        $this->raw_matches_created = true;
        $this->matchdays_created = false;
        $this->clear_pointer();
      
        return $this->matches;  
    }
  
  
    /**
     * Test whether $teams holds a valid array
     *
     * When an error occurs, the class goes back into start shape
     * This is probably the only error that might occure during a attempt
     * of generating matches
     *
     * @access private
     * @return false when not, true when valid
     */
   
    private function valid_team_array()
    {
        if (!is_array($this->teams) || count($this->teams) < 2) {
            $this->error = 'Not enough teams in array shape passed';
                      
            // going back to start shape
            $this->finished = false;
            $this->raw_matches_created = false;
            $this->matchdays_created = false;
            $this->matches = array();
            $this->clear_pointer();
            return false;
        }
        return true;  
    }
  
  
    /**
     * Clears the pointer to proceed in using the next() functions
     * after a new match generation
     *
     * @access private
     * @return true
     */  
    private function clear_pointer()
    {
        $this->matchday_pointer = 0;
        $this->match_pointer = 0;
        return true;
    }
  
  
    /**
     * Returns the next match array  according to 'match_pointer'
     * When 'matchdays_created' is true it also refers to where '$matchday_pointer' is
     * If 'raw_matches_created' is true, it simply returns the next array in matches
     *
     * When there are no more matches to return, false is returned
     *
     * @access public
     * @return array the match array or false
     */   
    public function next_match()
    {
        if ($this->raw_matches_created) {
            if (isset($this->matches[$this->match_pointer])) {
                $this->match_pointer++;
                return $this->matches[$this->match_pointer - 1];  
            } 
            else {
                return false;
            }
        } 
        elseif($this->matchdays_created) {
            if (isset($this->matches[$this->matchday_pointer - 1][$this->match_pointer])) {
                $this->match_pointer++;
                return $this->matches[$this->matchday_pointer - 1][$this->match_pointer - 1];
            }  
            else {
                return false;
            }
        } 
        else {
            $this->error = 'No matches created yet.';
            return false;
        }           
    }
  
  
    /**
     * Returns the next matchday array  according to 'matchday_pointer'
     *
     * When there are no more matchdays to return, false is returned
     *
     * @access public
     * @return array the matchday array or false
     */

    public function next_matchday()
    {
        if ($this->raw_matches_created) {
            $this->error = "No matchdays created within last action.";
            return false;
        }
        elseif($this->matchdays_created) {
            if (isset($this->matches[$this->matchday_pointer])) {
                $this->matchday_pointer++;
                $this->match_pointer = 0;
                return $this->matches[$this->matchday_pointer - 1];
            }
            else {
                return false;
            }
        }
        else {
            $this->error = 'No matches created yet.';
            return false;
        }
    }
  
    function generateRRSchedule(array $players, $rand = false)
    {
        $numPlayers = count($players);

        // add a placeholder if the count is odd
        if($numPlayers%2) {
                $players[] = null;
                $numPlayers++;
        }

        // calculate the number of sets and matches per set
        $numSets = $numPlayers-1;
        $numMatches = $numPlayers/2;

        $matchups = array();

        // generate each set
        for($j = 0; $j < $numSets; $j++) {
                // break the list in half
                $halves = array_chunk($players, $numMatches);
                // reverse the order of one half
                $halves[1] = array_reverse($halves[1]);
                // generate each match in the set
            for($i = 0; $i < $numMatches; $i++) {
                // match each pair of elements
                $matchups[$j][$i][0] = $halves[0][$i];
                $matchups[$j][$i][1] = $halves[1][$i];
            }
                // remove the first player and store
                $first = array_shift($players);
                // move the second player to the end of the list
                $players[] = array_shift($players);
                // place the first item back in the first position
                array_unshift($players, $first);
        }

        // shuffle the results if desired
        if($rand) {
            foreach($matchups as &$match) {
                shuffle($match);
            }
                shuffle($matchups);
        }

        return $matchups;
    }
        
}  
?>
