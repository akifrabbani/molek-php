<?php
namespace AkifRabbani\Molek;

use DateTime;

class Molek
{
    protected $ruleset;

    /**
     * Create a new Molek Instance
     */

    public function __construct($ruleset = [])
    {
        $this->ruleset = $ruleset;
    }

     /**
     * Set ruleset used by the instance
     *
     * @param array $ruleset Ruleset for calculation
     */

   public function setRuleset($ruleset)
    {
        $this->ruleset = $ruleset;
    }

    /**
     * Converts a time value (minutes / hours / days / months) to seconds
     *
     * @param string $type Time type
     * @param int $value Time value
     *
     * @return int Time in seconds
     */

    protected function timeToSeconds($type, $value)
    {
        if ($type === 'second') {
            return $value;
        } elseif ($type === 'minute') {
            return $value * 60;
        } elseif ($type === 'hour') {
            return $value * 60 * 60;
        } elseif ($type === 'day') {
            return $value * 24 * 60 * 60;
        } elseif ($type === 'month') {
            return $value * 30 * 24 * 60 * 60;
        }
    }

    /**
     * Checks provided date within the rule's days or dates
     *
     * @param array $rule Rule
     * @param DateTime $date Date
     *
     * @return bool True or false
     */
    protected function checkDateWithinDaysOrDates($date, $rule)
    {
        $first_flag = true;
        $second_flag = true;

        if (isset($rule['days']) && !in_array(strtolower($date->format('D')), $rule['days'])) {
            $first_flag = false;
        }

        if (isset($rule['dates']) && !in_array($date->format('Y-m-d'), $rule['dates'])) {
            $second_flag = false;
        }

        return $first_flag || $second_flag;
    }

    /**
     * Get a rule from the first ruleset
     *
     * @param int $duration Time duration
     * @param DateTime $date Date for rule selection
     *
     * @return array|null Rule or null
     */

    protected function getFirstRule($duration, $date)
    {
        if (!isset($this->ruleset['first'])) return null;

        $rules = $this->ruleset['first'];
        $selected_rule = null;

        foreach ($rules as $key => $rule) {
            $rule_duration = $this->timeToSeconds($rule['type'], $rule['duration']);

            if ($this->checkDateWithinDaysOrDates($date, $rule)) {
                if ($duration >= $rule_duration) {
                    $selected_rule = [
                        'duration' => $rule_duration,
                        'price' => $rule['price']
                    ];
                }
            }
        }

        return $selected_rule;
    }

    /**
     * Get a rule from the normal ruleset
     *
     * @param DateTime $date Date for rule selection
     *
     * @return array|null Rule or null
     */

    protected function getNormalRule($date)
    {
        if (!isset($this->ruleset['normal'])) return null;

        $rules = $this->ruleset['normal'];
        $selected_rule = null;

        foreach ($rules as $key => $rule) {
            $rule_interval = $this->timeToSeconds($rule['type'], $rule['interval']);

            if ($this->checkDateWithinDaysOrDates($date, $rule)) {
                $selected_rule = [
                    'interval' => $rule_interval,
                    'price' => $rule['price']
                ];
            }
        }

        return $selected_rule;
    }

    /**
     * Get a rule from the max ruleset
     *
     * @param int $duration Time duration
     * @param DateTime $date Date for rule selection
     *
     * @return array|null Rule or null
     */

    protected function getMaxRule($duration, $date)
    {
        if (!isset($this->ruleset['max'])) return null;

        $rules = $this->ruleset['max'];
        $selected_rule = null;

        foreach ($rules as $key => $rule) {
            $rule_duration = $this->timeToSeconds($rule['type'], $rule['duration']);

            if ($this->checkDateWithinDaysOrDates($date, $rule)) {
                if ($duration >= $rule_duration) {
                    $selected_rule = [
                        'duration' => $rule_duration,
                        'price' => $rule['price']
                    ];
                }
            }
        }

        return $selected_rule;
    }

    /**
     * Calculate the day's price based on provided rules
     *
     * @param int $duration Time duration
     * @param array|null $first_rule First rule
     * @param array|null $normal_rule Normal rule
     * @param array|null $max_rule Max rule
     *
     * @return string Price
     */

    protected function calculateDayPrice($duration, $first_rule, $normal_rule, $max_rule)
    {
        $price = '0.00';

        if ($max_rule !== null) {
            if ($duration >= $max_rule['duration']) {
                return bcadd($max_rule['price'], 0, 2);
            }
        }

        if ($first_rule !== null) {
            $price = bcadd($price, $first_rule['price'], 2);
            $duration -= $first_rule['duration'];
        }

        if ($normal_rule !== null) {
            $price = bcadd($price, bcmul(bcdiv($duration, $normal_rule['interval'], 10), $normal_rule['price'], 10), 2);
        }

        return $price;
    }

    /**
     * Calculate the duration and price between two dates (inclusive)
     *
     * @param DateTime $start_at Start datetime
     * @param DateTime $end_at End datetime
     * @param bool $detailed_result Flag for returning detailed result
     *
     * @return array Result of total duration & price (and list of days if $detailed_result is true)
     */

    public function calculate($start_at, $end_at, $detailed_result = false)
    {
        $ruleset = $this->ruleset;

        $total_duration = 0;
        $total_price = '0.00';

        if (isset($ruleset['base_price'])) {
            $total_price = bcadd($total_price, $ruleset['base_price'], 2);
        }

        $operation_hour_start = '00:00';
        $operation_hour_end = '24:00';

        if (isset($ruleset['operation_hours'])) {
            $operation_hour_start = isset($ruleset['operation_hours']['start']) ? $ruleset['operation_hours']['start'] : $operation_hour_start;
            $operation_hour_end = isset($ruleset['operation_hours']['end']) ? $ruleset['operation_hours']['end'] : $operation_hour_end;
        }

        $operation_hours_a_day = (strtotime($operation_hour_end) - strtotime($operation_hour_start)) / 3600;

        $days = [];

        if ($start_at->format('Y-m-d') === $end_at->format('Y-m-d')) {
            $operation_start_at = new DateTime($start_at->format('Y-m-d') . ' ' . $operation_hour_start . ':00');
            $operation_end_at = new DateTime($end_at->format('Y-m-d') . ' ' . $operation_hour_end . ':00');

            if ($start_at > $operation_start_at) {
                $operation_start_at = $start_at;
            }

            if ($end_at < $operation_end_at) {
                $operation_end_at = $end_at;
            }

            if ($end_at < $operation_start_at) {
                $operation_end_at = $operation_start_at;
            }

            $duration = $operation_end_at->getTimestamp() - $operation_start_at->getTimestamp();

            $first_rule = $this->getFirstRule($duration, $start_at);
            $normal_rule = $this->getNormalRule($start_at);
            $max_rule = $this->getMaxRule($duration, $start_at);

            $price = $this->calculateDayPrice($duration, $first_rule, $normal_rule, $max_rule);

            $total_duration = $duration;
            $total_price = bcadd($total_price, $price, 2);

            $days[] = [
                'start_at' => $operation_start_at->format('Y-m-d H:i:s'),
                'end_at' => $operation_end_at->format('Y-m-d H:i:s'),
                'price' => $total_price
            ];
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

            $first_day_duration = $first_day_operation_end_at->getTimestamp() - $first_day_operation_start_at->getTimestamp();
            $last_day_duration = $last_day_operation_end_at->getTimestamp() - $last_day_operation_start_at->getTimestamp();

            $first_day_first_rule = $this->getFirstRule($first_day_duration, $start_at);
            $first_day_normal_rule = $this->getNormalRule($start_at);
            $first_day_max_rule = $this->getMaxRule($first_day_duration, $start_at);

            $first_day_price = $this->calculateDayPrice($first_day_duration, $first_day_first_rule, $first_day_normal_rule, $first_day_max_rule);

            $last_day_normal_rule = $this->getNormalRule($end_at);
            $last_day_max_rule = $this->getMaxRule($last_day_duration, $end_at);

            $last_day_price = $this->calculateDayPrice($last_day_duration, null, $last_day_normal_rule, $first_day_max_rule);

            $total_duration = $first_day_duration + $last_day_duration;;
            $total_price = bcadd($total_price, bcadd($first_day_price, $last_day_price, 2), 2);

            $days[] = [
                'start_at' => $first_day_operation_start_at->format('Y-m-d H:i:s'),
                'end_at' => $first_day_operation_end_at->format('Y-m-d H:i:s'),
                'price' => $first_day_price
            ];

            if (!$is_adjacent_date) {
                $in_between_date = (clone $start_at)->modify('+1 day');

                while (true) {
                    $in_between_duration = $operation_hours_a_day * 3600;

                    $in_between_normal_rule = $this->getNormalRule($in_between_date);
                    $in_between_max_rule = $this->getMaxRule($in_between_duration, $in_between_date);

                    $in_between_price = $this->calculateDayPrice($in_between_duration, null, $in_between_normal_rule, $in_between_max_rule);

                    $total_duration += $in_between_duration;
                    $total_price = bcadd($total_price, $in_between_price, 2);

                    $days[] = [
                        'start_at' => $in_between_date->format('Y-m-d') . ' ' . $operation_hour_start . ':00',
                        'end_at' => $in_between_date->format('Y-m-d') . ' ' . $operation_hour_end . ':00',
                        'price' => $in_between_price
                    ];

                    $in_between_date->modify('+1 day');
                    if ($in_between_date->format('Y-m-d') === $end_at->format('Y-m-d')) break;
                }
            }

            $days[] = [
                'start_at' => $last_day_operation_start_at->format('Y-m-d H:i:s'),
                'end_at' => $last_day_operation_end_at->format('Y-m-d H:i:s'),
                'price' => $last_day_price
            ];
        }

        $result = [
            'total_duration' => $total_duration,
            'total_price' => $total_price
        ];

        if ($detailed_result) {
            $result = array_merge(['days' => $days], $result);
        }

        return $result;
    }
}