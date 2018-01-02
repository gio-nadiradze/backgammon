<?php
class backgammon {
	
	// board
	private $board = array(
		1 => array(2,2),
		2 => array(0,0),
		3 => array(0,0),
		4 => array(0,0),
		5 => array(0,0),
		6 => array(5,1),
		7 => array(0,0),
		8 => array(3,1),
		9 => array(0,0),
		10 => array(0,0),
		11 => array(0,0),
		12 => array(5,2),
		13 => array(5,1),
		14 => array(0,0),
		15 => array(0,0),
		16 => array(0,0),
		17 => array(3,2),
		18 => array(0,0),
		19 => array(5,2),
		20 => array(0,0),
		21 => array(0,0),
		22 => array(0,0),
		23 => array(0,0),
		24 => array(2,1)
	);
		
	// active player
	private $player = 1;
	
	// died
	private $died = array(
		1 => 0,
		2 => 0
	);
	
	// start outing
	private $outing = array(
		1 => 0,
		2 => 0
	);
	
	// if game end
	private $win = false;
	
	// roll up dice
	private function getDice() {
		$one = random_int(1, 6);
		$two = random_int(1, 6);
		return array($one, $two);
	}
	
	// get opponent id
	private function opponent() {
		$player = $this->player;
		if($player == 1) {
			return 2;
		} else {
			return 1;
		}
	}
	
	// check outing status
	private function checkOut($p) {
		$board = $this->board;
		
		if($this->died[$p] > 0) {
			return false;
		}
		
		if($p == 1) {
			foreach($board as $key => $val) {
				if($key > 6 && $val[1] == $p && $val[0] > 0) {
					return false;
				}
			}
		
			return true;
		}
		
		if($p == 2) {
			foreach($board as $key => $val) {
				if($key < 19 && $val[1] == $p && $val[0] > 0) {
					return false;
				}
			}
		
			return true;
		}

	}
	
	// fix board
	private function fixBoard() {
		$board = $this->board;
		
		foreach($board as $k => $v) {
			if($v[0] != 0 && $v[1] == 0) {
				$this->board[$k][0] = 0;
			}
			
			if($v[0] == 0 && $v[1] != 0) {
				$this->board[$k][1] = 0;
			}
		}
	}
	
	private function checkWin() {
		$board = $this->board;
		
		$p1 = 0;
		$p2 = 0;
		
		foreach($board as $k => $v) {
			if($v[1] == 1 && $v[0] > 0) {
				$p1 += $v[0];
			}
			
			if($v[1] == 2 && $v[0] > 0) {
				$p2 += $v[0];
			}
		}
		
		if($p1 == 0) { return 1; }
		if($p2 == 0) { return 2; }
		
		return false;
	}
	
	// check if outing is possible
	private function checkLess($dice) {
		$player = $this->player;
		$board = $this->board;
		
		$max_taken = 6;
		// get max slot number
		if($player == 1) {
			for($i = 6; $i >= 1; $i--) {
				if($board[$i][1] == $player && $board[$i][0] > 0) {
					$max_taken = $i;
					break;
				}
			}
		} else {
			
			for($i = 19; $i <= 24; $i++) {
				$new_pos = 25 - $i;
				if($board[$i][1] == $player && $board[$i][0] > 0) {
					$max_taken = $new_pos;
					break;
				}
			}
			
		}
		
		if($dice > $max_taken) {
			return $max_taken;
		} else {
			return false;
		}
		
	}
	
	// available moves
	private function getAvalMove($dice) {
		
		$this->fixBoard();
		
		$avals = array();
		$player = $this->player;
		
		// check outing status
		$outing = $this->checkOut($player);
		if($outing === true) {
			$this->outing[$player] = 1;
		} else {
			$this->outing[$player] = 0;
		}

		$board = $this->board;
		$died = $this->died;
		$outing = $this->outing;
		$opponent = $this->opponent();
		
		// check board
		if($died[$player] == 0) {
			for($i = 1; $i <= 24; $i++) {
				
				$slot = $board[$i];
				if($slot[1] == $player && $slot[0] > 0) {
	
					if($player == 1) {
						$new_pos = $i - $dice;
					} else {
						$new_pos = $i + $dice;
					}
				
					if($new_pos > 24 || $new_pos < 1) {
						$new_pos = 0;
					}
					
					if($new_pos != 0) {
						$new = $board[$new_pos];
					
						if($new[1] != $opponent || $new[0] == 1) {
							$avals[] = $i.'-'.$new_pos;
						}
					}
				}
			}
		} else {
			
			// check if player has died
			if($player == 1) {
				
				for($i = 19; $i <= 24; $i++) {
					$new = $board[$i];
					
					if($new[1] != $opponent || $new[0] == 1) {
						
						$new_slot_val = 25 - $i;
						
						if($dice == $new_slot_val) {
							$avals[] = '0-'.$i;
						}
					}
				}
				
			} else {
				
				for($i = 1; $i <= 6; $i++) {
					$new = $board[$i];
					
					if($new[1] != $opponent || $new[0] == 1) {
						if($dice == $i) {
							$avals[] = '0-'.$i;
						}
					}
				}
				
			}
			
		}
		
		// check if outing
		if($outing[$player] == 1) {
			if($player == 1) {
				for($i = 6; $i >= 1; $i--) {
					
					$new = $board[$i];
						
					$subAval = $this->checkLess($dice);
						
					if(($i == $dice && $new[1] == $player && $new[0] > 0) || $subAval !== false ) {
							
						if($i == $dice) {
							$avals[] = $i.'-0';
						} else {
							$avals[] = $subAval.'-0';
						}
					}
					
				}
			} else {
				for($i = 19; $i <= 24; $i++) {
					$new = $board[$i];
					
					$new_pos = 25 - $i;

					$subAval = $this->checkLess($dice);
					if(($new_pos == $dice && $new[1] == $player && $new[0] > 0) || $subAval !== false ) {
							
						if($new_pos == $dice) {
							$avals[] = $i.'-0';
						} else {
							$subAvalNew = 25 - $subAval;
							$avals[] = $subAvalNew.'-0';
						}
					}
					
				}
			}
		}
		
		$win = $this->checkWin();
		$this->win = $win;
		
		return $avals;
	}
	
	// play and update Board
	private function updateBoard($move) {
		
		// clear and fix board
		$this->fixBoard();
		
		$player = $this->player;
		$opponent = $this->opponent();
		
		$move = explode('-', $move);
		
		if($move[0] != 0) {
			$this->board[$move[0]][0] -= 1;
		}
		
		if($move[1] != 0) {
			$this->board[$move[1]][0] += 1;
			
			// check if dead
			if($this->board[$move[1]][1] == $opponent) {
				$this->board[$move[1]][0] -= 1;
				$this->died[$opponent] += 1;
			}
			
			// check if alive
			if($move[0] == 0) {
				$this->died[$player] -= 1;
			}
			
			// update table row id
			if($this->board[$move[1]][1] != $player) {
				$this->board[$move[1]][1] = $player;
			}
		}
		
		// clear and fix board
		$this->fixBoard();
	}
	
	// start Play
	public function play() {
		$result = '';
		$final = array();
		
		$i = 1;
		while(true) {
		
			$dice = $this->getDice();

			// collect result
			
			$result .= $dice[0].':'.$dice[1].'-';
			
			if($dice[0] != $dice[1]) {
				
				$recheck = 0;
				
				// normal game
				$avals = $this->getAvalMove($dice[0]);
				if(!empty($avals)) {
					$pick = $avals[array_rand($avals, 1)];
				} else {
					$pick = null;
				}
				
				if(!empty($pick)) {
					$result .= str_replace('-', '=', $pick).'&';
				
					// update board
					$this->updateBoard($pick);
					
				} else {
					//$result .= '0=0&';
					$recheck = 1;
				}
				
				
				// clear vars
				$pick = null;
				$avals = null;
				
				$avals = $this->getAvalMove($dice[1]);
				if(!empty($avals)) {
					$pick = $avals[array_rand($avals, 1)];
				} else {
					$pick = null;
				}
				
				if(!empty($pick)) {
					$result .= str_replace('-', '=', $pick).'&';
					
					// update board
					$this->updateBoard($pick);
					
				} else {
					$result .= '0=0,';
				}
				
				if($recheck == 1) {
					$avals = $this->getAvalMove($dice[0]);
					if(!empty($avals)) {
						$pick = $avals[array_rand($avals, 1)];
					} else {
						$pick = null;
					}
					
					if(!empty($pick)) {
						$result .= str_replace('-', '=', $pick).'&';
					
						// update board
						$this->updateBoard($pick);
						
					} else {
						//$result .= '0=0,';
					}
				}
				
				$result = substr($result, 0, -1).',';
				
			} else {
				
				// 4x game
				for($i = 1; $i <=4; $i++) {
					// clear vals
					$pick = null;
					$avals = null;
					
					$avals = $this->getAvalMove($dice[0]);
					if(!empty($avals)) {
						$pick = $avals[array_rand($avals, 1)];
					} else {
						$pick = null;
					}
					
					if(!empty($pick)) {
						$result .= str_replace('-', '=', $pick).'&';
						
						// update board
						$this->updateBoard($pick);

					} else {
						$result .= '0=0,';
						break;
					}
				}
				
				$result = substr($result, 0, -1).',';
			}
			
			// update player
			if($this->player == 1) {
				$this->player = 2;
			} else {
				$this->player = 1;
			}
			
			if($this->win !== false) {
				$result = substr($result, 0, -1);
				
				$final[0] = $result;
				$final[1] = $this->win;
				
				return $final;

			}
			
			$i++;
		}
	}
}

// Start Play
$bg = new backgammon;
$hand = $bg->play();

$ar = explode(',', $hand[0]);

$i = 1;
foreach($ar as $r) {
	echo 'PLAYER '.$i.' => '.$r.'<br />';
	if($i == 1) { $i = 2; } else { $i = 1; }
}
