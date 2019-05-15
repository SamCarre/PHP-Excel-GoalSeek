<?php

namespace samcarre\PHPExcelGoalSeek;

use samcarre\PHPExcelGoalSeek\Exceptions\PHPExcelGoalSeekException;

class PHPExcelGoalSeek
{
    var $debug;

    public function __construct()
    {
        $this->debug = false;
    }

    public function newLine() {
        return '<br />';
    }

    public function calculate($functionGS, $goal, $decimal_places,
      $incremental_modifier = 1, $max_loops_round = 0, $max_loops_dec = 0,
      $lock_min = array('num' => null, 'goal' => null), $lock_max = array('num' => null, 'goal' => null),
      $slope = null, $randomized = false, $start_from = 0.1)
    {
        $debug = $this->debug;

        if (empty($functionGS)) {
            throw new PHPExcelGoalSeekException('Function callback expected');
        }

        $max_loops_round++;

        $maximum_acceptable_difference = 0.1;//If goal found has more than this difference, return null as it is not found

        if ($max_loops_round > 100) {
            if ($debug) {
                echo 'Goal never reached'.$this->newLine();
            }
            return false;
        }

        if ($debug) {
            echo 'Iteration '.$max_loops_round.'; min value = '.$lock_min['num'].'; max value = '.$lock_max['num'].'; slope '.$slope.$this->newLine();
        }

        //If I have the goal  limited to a unit, I seek decimals
        if ($lock_min['num'] !== null && $lock_max['num'] !== null && abs(abs($lock_max['num']) - abs($lock_min['num'])) <= 1) {

            //No decimal , return result
            if ($lock_min['num'] == $lock_max['num']) {
                return $lock_min['num'];
            }

            //Seek decimals
            foreach (range(1, $decimal_places, 1) as $decimal) {
                $decimal_step = 1 / pow(10, $decimal);

                $difference = abs(round(abs($lock_max['num']), $decimal) - round(abs($lock_min['num']), $decimal));

                while ($difference - ($decimal_step / 10) > $decimal_step && $max_loops_dec < (2000 * $decimal_places)) {
                    $max_loops_dec++;

                    $aux_obj_num = round(($lock_min['num'] + $lock_max['num']) / 2, $decimal);
                    $aux_obj = $this->$functionGS($aux_obj_num);

                    if ($debug) {
                        echo 'Decimal iteration '.$max_loops_dec.'; min value = '.$lock_min['num'].'; max value = '.$lock_max['num'].'; value '.$aux_obj.$this->newLine();
                    }

                    //Like when I look without decimals
                    if ($aux_obj == $goal) {
                        $lock_min['num'] = $aux_obj_num;
                        $lock_min['goal'] = $aux_obj;

                        $lock_max['num'] = $aux_obj_num;
                        $lock_max['goal'] = $aux_obj;
                    }

                    $going_up = false;
                    $going_down = false;
                    if ($aux_obj < $goal) {
                        $going_up = true;
                    }
                    if ($aux_obj > $goal) {
                        $going_up = false;
                    }
                    if ($slope == -1) {
                        $going_up = !$going_up;
                    }

                    if ($going_up) {
                        if ($lock_min['num'] !== null && $aux_obj_num < $lock_min['num']) {
                            $lock_max['num'] = $lock_min['num'];
                            $lock_max['goal'] = $lock_min['goal'];
                        }

                        $lock_min['num'] = $aux_obj_num;
                        $lock_min['goal'] = $aux_obj;
                    }

                    if (!$going_up) {
                        if ($lock_max['num'] !== null && $lock_max['num'] < $aux_obj_num) {
                            $lock_min['num'] = $lock_max['num'];
                            $lock_min['goal'] = $lock_max['goal'];
                        }

                        $lock_max['num'] = $aux_obj_num;
                        $lock_max['goal'] = $aux_obj;
                    }
                    //End Like when I look without decimals

                    $difference = abs(round(abs($lock_max['num']), $decimal) - round(abs($lock_min['num']), $decimal));
                }//End while
            }//End foreach


        if ($max_loops_dec > 2000 * $decimal_places) {
            if ($debug) {
                echo 'Goal never reached'.$this->newLine();
            }
            return;
        }

            if (!is_nan($lock_min['goal']) && abs(abs($lock_min['goal']) - abs($goal)) < $maximum_acceptable_difference) {
                return round($lock_min['num'], $decimal_places - 1);
            } else {
                if ($debug) {
                    echo 'Goal reached not enough'.$this->newLine();
                }
                return;
            }
        }

    //First iteration, try with zero
    if ($lock_min['num'] === null && $lock_max['num'] === null) {
        $aux_obj_num = $start_from;
    }
    //Lower limit found, searching higher limit with * 10
    elseif ($lock_min['num'] !== null && $lock_max['num'] === null) {
        if ($lock_min['num'] == $start_from) {
            $aux_obj_num = 1;
        } else {
            $aux_obj_num = $lock_min['num'] * (10 / $incremental_modifier);
        }
    }
    //Higher limit found, searching lower limit with * -10
    elseif ($lock_min['num'] === null && $lock_max['num'] !== null) {
        if ($lock_max['num'] == $start_from) {
            $aux_obj_num = -1;
        } else {
            $aux_obj_num = $lock_max['num'] * (10 / $incremental_modifier);
        }
    }
    //I have both limits, searching between them without decimals
    elseif ($lock_min['num'] !== null && $lock_max['num'] !== null) {
        $aux_obj_num = round(($lock_min['num'] + $lock_max['num']) / 2);
    }

        if ($aux_obj_num != $start_from) {
            $aux_obj = $this->$functionGS($aux_obj_num);
            if ($debug) {
                echo 'Testing '.$aux_obj_num.' with value '.$aux_obj.$this->newLine();
            }
        } else {
            $aux_obj = $this->$functionGS($aux_obj_num);
            if ($debug) {
                echo 'Testing (with initial value) '.$aux_obj_num.' with value '.$aux_obj.$this->newLine();
            }
        }

        if ($slope == null) {
            $aux_slope = $this->$functionGS($aux_obj_num + 0.1);

            if (is_nan($aux_slope) || is_nan($aux_obj)) {
                $slope = null; //If slope is null
            } elseif ($aux_slope - $aux_obj > 0) {
                $slope = 1;
            } else {
                $slope = -1;
            }
        }

        //Test if formule can give me non valid values, i.e.: sqrt of negative value
        if (!is_nan($aux_obj)) {
            //Is goal without decimals?
            if ($aux_obj == $goal) {
                $lock_min['num'] = $aux_obj_num;
                $lock_min['goal'] = $aux_obj;

                $lock_max['num'] = $aux_obj_num;
                $lock_max['goal'] = $aux_obj;
            }

            $going_up = false;
            $going_down = false;
            if ($aux_obj < $goal) {
                $going_up = true;
            }
            if ($aux_obj > $goal) {
                $going_up = false;
            }
            if ($slope == -1) {
                $going_up = !$going_up;
            }

            if ($going_up) {
                if ($lock_min['num'] !== null && $aux_obj_num < $lock_min['num']) {
                    $lock_max['num'] = $lock_min['num'];
                    $lock_max['goal'] = $lock_min['goal'];
                }

                $lock_min['num'] = $aux_obj_num;
                $lock_min['goal'] = $aux_obj;
            }

            if (!$going_up) {
                if ($lock_max['num'] !== null && $lock_max['num'] < $aux_obj_num) {
                    $lock_min['num'] = $lock_max['num'];
                    $lock_min['goal'] = $lock_max['goal'];
                }

                $lock_max['num'] = $aux_obj_num;
                $lock_max['goal'] = $aux_obj;
            }
        } else {
            if (($lock_min['num'] === null && $lock_max['num'] === null) || $randomized) {
                $nuevo_start_from = rand(-500, 500);

                return $this->calculate($functionGS, $goal, $decimal_places, $incremental_modifier + 1, $max_loops_round, $max_loops_dec, $lock_min, $lock_max, $slope, true, $nuevo_start_from, $debug);
            } //First iteration is null

            if ($lock_min['num'] !== null && abs(abs($aux_obj_num) - abs($lock_min['num'])) < 1) {
                $lock_max['num'] = $aux_obj_num;
            }
            if ($lock_max['num'] !== null && abs(abs($aux_obj_num) - abs($lock_max['num'])) < 1) {
                $lock_min['num'] = $aux_obj_num;
            }

            return $this->calculate($functionGS, $goal, $decimal_places, $incremental_modifier + 1, $max_loops_round, $max_loops_dec, $lock_min, $lock_max, $slope, $randomized, $start_from, $debug);
        }

        return $this->calculate($functionGS, $goal, $decimal_places, $incremental_modifier, $max_loops_round, $max_loops_dec, $lock_min, $lock_max, $slope, $randomized, $start_from, $debug);
    }
}
