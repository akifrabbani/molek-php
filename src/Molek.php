<?php

namespace Akifrabbani\Molek;

class Molek
{

    private $_rule = [];

    /**
     * Create a new Molek Instance
     */
    public function __construct($rule = [])
    {
        $this->_rule = $rule;
    }

    /**
     * Converts rule to seconds
     *
     * @param array $rule Rule for time difference calculation
     *
     * @return int Seconds of differences
     * @return array Seconds of differenes and amount
     */

    private static function timeToSec($rule, $value_is_amount = false) {
        $sec_diff = 0;

        $target = 0;
        $seconds = 0;

        // check if ruled is sec or minutes/hours, if minutes, convert to sec
        if (isset($rule['second'])) {
            $seconds = 0;
            $target = $rule['second'];
        } elseif (isset($rule['minute'])) {
            $seconds = 60;
            $target = $rule['minute'];
        } elseif (isset($rule['hour'])) {
            $seconds = 3600;
            $target = $rule['hour'];
        } elseif (isset($rule['day'])) {
            $seconds = 86400;
            $target = $rule['day'];
        }

        if ($value_is_amount) {
            return ['diff' => $seconds, 'price' => $target];
        } else {
            $sec_diff = round($target * $seconds);
            return $sec_diff;
        }
    }

    /**
     * Calculate only seconds within operation time
     *
     * @param DateTime $start Start datetime
     * @param DateTime $end End datetime
     * @param string $operation_hour_start Operation hour start time
     * @param string $operation_hour_end Operation hour end time
     * @param int $operation_hours_a_day Operation hours in integer
     *
     * @return int Seconds of operation time
     */
    
    private static function operationDayToSec($start, $end, $operation_hour_start, $operation_hour_end, $operation_hours_a_day) {
        $start_at = clone $start;
        $end_at = clone $end;
     
        if ($start_at->format('Y-m-d') === $end_at->format('Y-m-d')) {
            $operation_start_at = new DateTime($start_at->format('Y-m-d') . ' ' . $operation_hour_start . ':00');
            $operation_end_at = new DateTime($end_at->format('Y-m-d') . ' ' . $operation_hour_end . ':00');
     
            if ($start_at > $operation_start_at) {
                $operation_start_at = $start_at;
            }
     
            if ($end_at < $operation_end_at) {
                $operation_end_at = $end_at;
            }
     
            return strtotime($operation_end_at->format('Y-m-d H:i:s')) - strtotime($operation_start_at->format('Y-m-d H:i:s'));
        } elseif ($start_at->format('Y-m-d') !== $end_at->format('Y-m-d')) {
            $is_adjacent_date = (clone $start_at)->modify('+1 day')->format('Y-m-d') === $end_at->format('Y-m-d');
     
            $first_day_operation_start_at = new DateTime($start_at->format('Y-m-d') . ' ' . $operation_hour_start . ':00');
            $first_day_operation_end_at = new DateTime($start_at->format('Y-m-d') . ' ' . $operation_hour_end . ':00');
     
            if ($start_at > $first_day_operation_start_at) {
                $first_day_operation_start_at = $start_at;
            }
     
            if ($start_at > $first_day_operation_end_at) {
                $first_day_operation_end_at = $start_at;
            }
     
            $last_day_operation_start_at = new DateTime($end_at->format('Y-m-d') . ' ' . $operation_hour_start . ':00');
            $last_day_operation_end_at = new DateTime($end_at->format('Y-m-d') . ' ' . $operation_hour_end . ':00');
     
            if ($end_at < $last_day_operation_start_at) {
                $last_day_operation_start_at = $end_at;
            }
     
            if ($end_at < $last_day_operation_end_at) {
                $last_day_operation_end_at = $end_at;
            }
     
            $first_day_seconds = strtotime($first_day_operation_end_at->format('Y-m-d H:i:s')) - strtotime($first_day_operation_start_at->format('Y-m-d H:i:s'));
            $last_day_seconds = strtotime($last_day_operation_end_at->format('Y-m-d H:i:s')) - strtotime($last_day_operation_start_at->format('Y-m-d H:i:s'));
     
            if ($is_adjacent_date) {
                return $first_day_seconds + $last_day_seconds;
            } else {
                $first_day_after_at = new DateTime((clone $start_at)->modify('+1 day')->format('Y-m-d') . ' ' . $operation_hour_start . ':00');
                $last_day_before_at = new DateTime((clone $end_at)->modify('-1 day')->format('Y-m-d') . ' ' . $operation_hour_end . ':00');
     
                if ($first_day_after_at->format('Y-m-d') === $last_day_before_at->format('Y-m-d')) {
                    return  $first_day_seconds + ($operation_hours_a_day * 3600) + $last_day_seconds;
                } else {
                    $date_diff = $first_day_after_at->diff($last_day_before_at);
     
                    return $first_day_seconds + (($date_diff->days + 1) * $operation_hours_a_day * 3600) + $last_day_seconds;
                }
            }
     
        }
    }

    /**
     * Calculate based on two dates
     *
     * @param DateTime $start Start datetime
     * @param DateTime $end End datetime
     * @param boolean $return_detailed True to return detailed array
     *
     * @return double Price
     */

    function calculate($start_date, $end_date, $return_detailed = false) {
        $final_price = 0;

        $start_time = $start_date->getTimestamp();
        $end_time = $end_date->getTimestamp();

        // calculate duration differences
        if (isset($this->_rule['operation'])) {
            if (isset($this->_rule['operation']['start']) && isset($this->_rule['operation']['end'])) {

                $operation_hours_a_day = abs(strtotime($this->_rule['operation']['end']) - strtotime($this->_rule['operation']['start'])) / (60 * 60);

                $start_date_x = clone $start_date;
                $end_date_x = clone $end_date;
                
                $interval = $this->operationDayToSec($start_date_x, $end_date_x, $this->_rule['operation']['start'], $this->_rule['operation']['end'], $operation_hours_a_day);
            } else {
                throw new Exception('Not enough arguement for operation.');
            }
        } else {
            // use normal time
            $interval  = abs($end_time - $start_time);
        }

        $start_interval = $interval;

        // set start price
        if (isset($this->_rule['start'])) $final_price += $this->_rule['start'];

        // calculate max
        if (isset($this->_rule['max'])) {
            if (count($this->_rule['max']) != count($this->_rule['max'], COUNT_RECURSIVE)) {
                foreach ($this->_rule['max'] as $max_rule) {
                    
                    if (!isset($max_rule['price'])) {
                        throw new Exception('No price set for max rule.');
                    }

                    $sec_diff = $this->timeToSec($max_rule);

                    if ($interval >= $sec_diff) {
                        $interval = 0;
                        $final_price = $max_rule['price'];
                    }
                
                }
            } else {
                throw new Exception('Max argument must be a multidimensional array.');
            }
        }

        // set by first price
        if (isset($this->_rule['first'])) {
            if (count($this->_rule['first']) != count($this->_rule['first'], COUNT_RECURSIVE)) {
                foreach ($this->_rule['first'] as $first_rule) {
                    if ($interval > 0) {
                        if (!isset($first_rule['price'])) {
                            throw new Exception('No price set for first rule.');
                        }

                        $sec_diff = $this->timeToSec($first_rule);

                        $interval -= $sec_diff;
                        $final_price = $first_rule['price'];
                    }
                }
            } else {
                throw new Exception('First argument must be a multidimensional array.');
            }
        }

        // set normal interval calculate difference
        if (isset($this->_rule['interval']) && $interval > 0) {
            $sec_diff = $this->timeToSec($this->_rule['interval'], true);

            if ($sec_diff['diff'] != 0) {
                while ($interval > 0) {
                    $interval -= $sec_diff['diff'];
                    $final_price += $sec_diff['price'];

                }
            }
        }

        if ($return_detailed) {
            return ['duration' => $start_interval, 'price' => $final_price];
        } else {
            return $final_price;
        }
    }
}
