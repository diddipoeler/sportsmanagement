<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2008. All Rights Reserved.
 *
 *  Modified by julien.vonthron@gmail.com to suit joomleague extension for joomla
 * 
 *  Modified by gladiator.sp@gmail.com on 20-03-2009 to improve standard algorithm for round-robins  
 *  to more complex and better Porter-Berger Algorithm  
 *
 *  This file is part of OBBLM.
 *
 *  OBBLM is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  OBBLM is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
class RRobin {

    /* Properties */
    
    public $tour = array();
    public $tot_games = 0;
    
    // Pair 2->n/2.
    private $upper = array();
    private $lower = array();
    // Pair 1.
    private $fixed = 0;     # Locked competitor.
    private $rot_out = 1;   # Rotated out competitor to play against locked competitor.
    
    /* Methods */
    
    /**
     * Create Round-Robin schedule
     *
     * @param array competitors list
     * @return boolean result
     */
    public function create($list = array()) {
        
        // Test input
        if (!is_array($list))
            return false;
        $n = count($list);
        if ($n < 3)
            return false;
        
        // Other
        $this->tot_games = ($n/2)*($n-1);
        
        # Initial array content
        # Gladiator 20-03-2009
        #
        if ($n % 2) { # Odd
        
        	$this->lower = array();
        	$this->upper = array();
        
            $n++;
            
            for ($i = 0; $i < ($n/2)-1; $i++) 
			{				
				array_push($this->upper, $this->calcUpperElement($i, $n));           			            
   			}
   			
   			for ($i = 0; $i < ($n/2)-1; $i++) 
			{
				array_push($this->lower, $this->calcLowerElement($i, $n));            			            
   			}
			
			$this->fixed = -1; # fixed player as ghost player
			$this->rot_out = 0;			                             
        }
        else { # Even
        
        	$this->lower = array();
        	$this->upper = array();
        
			for ($i = 0; $i < ($n/2)-1; $i++) 
			{
				array_push($this->upper, $this->calcUpperElement($i, $n));			             			  
   			}
   			
   			for ($i = 0; $i < ($n/2)-1; $i++) 
			{
				array_push($this->lower, $this->calcLowerElement($i, $n));       			            
   			}   			         
			   
	   		$this->fixed = $n - 1;
	   		$this->rot_out = 0;
        }
        
        # Generate games
        # Gladiator 20-03-2009
        #
        for ($round = 0; $round < $n-1; $round++) 
		{
            $this->tour[$round] = array();
            if ($this->rot_out != -1) # If not ghost player.
            {
            	if ($this->fixed != -1)
            	{
            		if ($this->rot_out >= $n/2) # if rot_out has white color => is supposed to play home
            		{
                		array_push($this->tour[$round], array($list[$this->rot_out], $list[$this->fixed]));
					}		
            		else
            		{
                		array_push($this->tour[$round], array($list[$this->fixed], $list[$this->rot_out]));
            		}
            	}
            	else
            	{
            		array_push($this->tour[$round], array($list[$this->rot_out]));
            	}
            }   	
            for ($i = 0; $i < count($this->upper); $i++)
            {
                if ($this->upper[$i] != -1 && $this->lower[$i] != -1) # If not ghost player.
                { 
                	if ($i % 2 == 0)
					{ 
                    	array_push($this->tour[$round], array($list[$this->upper[$i]], $list[$this->lower[$i]]));
    				}
					else
					{					
				 		array_push($this->tour[$round], array($list[$this->lower[$i]], $list[$this->upper[$i]]));
	 				}
 				}
 			}
 			
            $this->rotate();
        }
        
        return true;
    }
    
    // Rotate arrays
    private function rotate() {
        array_unshift($this->upper, $this->rot_out); 		// insert rot_out at the beginig of upper
        array_push($this->lower, array_pop($this->upper));  // pop element from upper and push it on lower
        $this->rot_out = array_shift($this->lower); 		// rot_out = last element of lower
    }
    
    # Gladiator 20-03-2009
    private function calcUpperElement($i, $n)
    {
    	# "i" Even:
    	if ($i%2 != 1)
    	{
    		$x = $n/2;
    		$y = $i / 2;
    	}
    	# "i" Odd
    	else if ($i%2 == 1)
    	{
    		$x = $n;
    		$y = ($i + 1)/2;
    	}
		
    	return ($x - $y - 1);
    }

	# Gladiator 20-03-2009
    private function calcLowerElement($i, $n)
    {
    	# "i" Even
    	if ($i%2 != 1)
    	{
    		$x = $n/2;
    		$y = ($i/2) + 1;
    	}
    	# "i" Odd
    	else if ($i%2 == 1)
    	{
    		$x = 1;
    		$y = ($i+1)/2;
    	}

    	return  ($x + $y - 1);
    }
    
    function getSchedule($nb_legs = 1)
    {
    	$result = array();
   		for( $i = 0; $i < $nb_legs; $i++) {
   			$result = array_merge($result, $this->getLeg($i));
    	}
    	return $result;
    }
    
    function getLeg($leg_number)
    {
      $nb_single_rounds = count($this->tour);
      $leg = array();
      foreach ($this->tour AS $k => $round) {
        foreach ($round as $game) {
        	if ($leg_number % 2) { // return leg, reverse games
            	$leg[$k + $nb_single_rounds*$leg_number][] = array_reverse($game);
        	}
        	else { 
				$leg[$k + $nb_single_rounds*$leg_number][] = $game;
			}
        }
      }
      
      if ($leg_number % 2) { // reverse rounds in leg      
   	  		$leg = array_reverse($leg);
      }
      
      return $leg;
    }
}
?>
