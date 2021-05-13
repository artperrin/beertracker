<?php

    function cal_days_in_month_bis($month, $year){
        // calculate number of days in a month
        return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
    } 

    function get_days_of_a_week(int $week_number, int $year_number, int $stop){
        $today = new DateTime('today');
        $week = array();
        for($d = 1 ; $d <= $stop ; $d++){
            $day = clone $today->setISODate($year_number, $week_number, $d);
            $week[$d] = $day->format('d.m.y');
        }
        return $week;
    }

    function get_days_of_a_month(int $month_number, int $year_number){
        $today = new DateTime('today');
        $month = array();
        $stop = cal_days_in_month_bis($month_number, $year_number);
        for($d = 0 ; $d < $stop ; $d++){
            $day = clone $today->setDate($year_number, $month_number, $d);
            if($day <= $today){
                $month[$d] = $day->format('d.m.y');
            }
        }
        return $month;
    }

    function get_days_of_a_year(int $year_number, $stop){
        $today = new DateTime('today');
        $year = array();
        $current_month_number = 1;
        $current_day = 0;
        for($d = 0 ; $d < $stop ; $d++){
            $days_of_given_month = cal_days_in_month_bis($current_month_number, $year_number);
            if($current_day > $days_of_given_month-1){
                $current_month_number++;
                $current_day = 0;
            }
            $day = clone $today->setDate($year_number, $current_month_number, $current_day);
            $year[$d] = $day->format('d.m.y');
            $current_day++;
        }
        unset($year[0]);
        return $year;
    }

    function get_quantity(array $user_data, $date, string $time_unit){
        // from $data, count all the entries for one $user on a given $time_unit
        $res = 0;
        $count = 1;
        $date_format = $date->format('d.m.y');
        if($time_unit == 'day' && isset($user_data[$date_format])){
            $res = $user_data[$date_format];
        }
        elseif($time_unit == 'week'){
            $week_number = $date->format('W');
            $year_number = $date->format('Y');
            if($year_number == date('Y') && $week_number == date('W')){
                $stop = (int) date('w');
            }
            else{
                $stop = 7;
            }
            $count = $stop;
            $week = get_days_of_a_week($week_number, $year_number, $stop);
            foreach($week as $day_of_week){
                if(isset($user_data[$day_of_week])){
                    $res += $user_data[$day_of_week];
                }
            }
        }
        elseif($time_unit == 'month'){
            $month_number = $date->format('m');
            $year_number = $date->format('Y');
            $month = get_days_of_a_month($month_number, $year_number);
            $count = count($month);
            foreach($month as $day_of_month){
                if(isset($user_data[$day_of_month])){
                    $res += $user_data[$day_of_month];
                }
            }
        }
        elseif($time_unit == 'year'){
            $year_number = $date->format('Y');
            if($year_number == date('Y')){
                $stop = date('z') + 1;
            }
            else{
                $last_day = new DateTimeImmutable($year_number.'-12-31');
                $stop = $last_day->format('z') + 1;
            }
            $count = $stop;
            $year = get_days_of_a_year($year_number, $stop);
            foreach($year as $day_of_year){
                if(isset($user_data[$day_of_year])){
                    $res += $user_data[$day_of_year];
                }
            }
        }
        return array('quantity' => round($res,2), 'count' => $count);
    }

    function update_database(string $data_file){
        // parse the database to remove all entries with zeros
        $data = json_decode(file_get_contents($data_file), true);
        foreach($data as $user => &$date){
            foreach($date as $time => $quantity){
                if($quantity == 0){
                    unset($data[$user][$time]);
                }
            }
            // sort by dates
            ksort($date);
        }
        file_put_contents($data_file, json_encode($data, JSON_FORCE_OBJECT));
    }

    function display_scores_comparison(array $data, $offset, $time_unit){
        if($time_unit == 'day'){
            $format = 'l d M';
        }
        elseif($time_unit == 'week'){
            $format = 'W';
        }
        elseif($time_unit == 'month'){
            $format = 'M Y';
        }
        elseif($time_unit == 'year'){
            $format = 'Y';
        }
        $today = new DateTimeImmutable('today');
        $time_scale = array();
        for($idx = 1 ; $idx < $offset ; $idx++){
            $modifier = '-'.$idx.' '.$time_unit;
            $date = clone $today->modify($modifier);
            $time_scale[$idx-1] = $date;
        }
        $time_scale = array_reverse($time_scale);
        $time_scale[$offset] = $today;
        $quantities = array();
        $per_days = array();
        $means = array();
        $means[0] = 0;
        $idx = 0;
        foreach($time_scale as $date){
            $temp = get_quantity($data, $date, $time_unit);
            $quantities[$idx] = $temp['quantity'];
            $per_days[$idx] = $temp['quantity']/$temp['count'];
            $means[$idx+1] = ($means[$idx] + $quantities[$idx])/($idx+1);
            $idx++;
        }
        unset($means[0]);
        // display as a table
        echo "<div id='pane'>";
        echo "<table class='a' style='width:100%'>";
        echo "<tr>"; // first row (headers: user name)
        echo "<th> Metrics </th>";
        foreach($time_scale as $date){
            echo "<th>".$date->format($format)."</th>";
        }
        echo "</tr>"; // end of first row
        echo "<tr>";
        echo "<td> Quantity (l) </td>"; // second row
        foreach($quantities as $val){
            echo "<td>".($val/100)."</td>";
        }
        echo "</tr>";
        echo "<tr>";
        echo "<td> Cumulated mean (l/".$time_unit.") </td>"; // second row
        foreach($means as $val){
            echo "<td>".round($val/100,2)."</td>";
        }
        echo "</tr>";
        echo "<tr>";
        echo "<td> Equivalent per day (cl/day) </td>"; // second row
        foreach($per_days as $val){
            echo "<td>".round($val,2)."</td>";
        }
        echo "</tr>";
        echo "</table>";
        echo "</div>";
    }

    function weekOfMonth($date) {
        $week_of_the_year = $date->format('W');
        $first_of_month = new DateTimeImmutable('first day of this month');
        $week_of_first_of_month = $first_of_month->format('W');
        return $week_of_the_year - $week_of_first_of_month + 1;
    }

?>