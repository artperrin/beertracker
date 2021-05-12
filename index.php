<?php
    include 'utils.php';
    $today = new DateTimeImmutable('today');
    $data_file = 'data/data.json';
    update_database($data_file);
    $data = json_decode(file_get_contents($data_file), true);
    $users = array_keys($data);
    // if shortcut was taken
    if(isset($_POST['submit_drink']) && is_numeric($_POST['drank_today'])){
        $today_format = $today->format('d.m.y');
        if(count($users)>1){
            $drinker = $_POST['user'];
        }
        else{
            $drinker = end($users);
        }
        if(isset($data[$drinker][$today_format])){
            $data[$drinker][$today_format] += (float) $_POST['drank_today'];
        }
        else{
            $data[$drinker][$today_format] = (float) $_POST['drank_today'];
        }
        file_put_contents($data_file, json_encode($data));
        echo "<div id='pane'> Quantity ".$_POST['drank_today']."cl added for user ".$drinker." !</div>";
    }
    // to display a given time unit
    if(isset($_POST['change_view'])){
        $time_unit = $_POST['time_unit'];
    }
    else{
        $time_unit = 'week';
    }
?>

<!DOCTYPE html>
<html>

<head>
    <title>&#127866 Beer tracker</title>
    <link rel="stylesheet" type="text/css" href="style.css" media="screen"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

    <div id='pane'>
    
    <!-- title -->
    <h1> <a href = "" > Beer tracker </a> </h1>
    <!-- subtitle -->
    <h2> Keep track of your beer's consumption </h2>

    <!-- here a fast way to input a drink -->
    <?php
        if(count($users)>0){
            echo "<h3> Fast entry: </h3>";
            echo "<p>";
            echo "<form method='POST' action='index.php'> Today, ";
            if(count($users)>1){
                // modify existing user if any
                echo "<select name='user' id='user'>";
                // make each type selectable
                foreach($users as $user){
                    echo "<option value='".$user."'> ".$user."</option>";
                }
                echo "</select>";
            }
            else{
                echo "I ";
            }
            echo "drank <input type='text' name='drank_today'/> cl ! ";
            echo "<input class='button' type='submit' name='submit_drink' value='submit'/>";
            echo "</form>";
            echo "</p>";
        }
        else{
            echo "<div id='pane'><h4> There is no user, please add at least one in the <a href='admin.php'> admin page. </a> </h4></div>";
        }
    ?>

    <!-- in the following section, display the week's statistics for each users -->

        <h3> Scores this <?php echo $time_unit; ?>: </h3>

        <!-- display current week's score for each user as a table -->

        <?php
            // display as a table
            echo "<p>";
            echo "<table style='width:100%'>";
            echo "<tr>"; // first row (headers: user name)
            echo "<th> Metric </th>";
            foreach($users as $user){
                echo "<th>".$user."</th>";
            }
            echo "</tr>"; // end of first row
            echo "<tr>";
            echo "<td> Cumulated quantity (cl) </td>"; // second row
            foreach($users as $user){
                $res = get_quantity($data[$user], $today, $time_unit);
                echo "<td>".$res['quantity']."</td>";
            }
            echo "</tr>";
            echo "<tr>";
            echo "<td> Quantity (cl) per day </td>"; // second row
            foreach($users as $user){
                $res = get_quantity($data[$user], $today, $time_unit);
                echo "<td>".round($res['quantity']/$res['count'],2)."</td>";
            }
            echo "</tr>";
            echo "</table>";
            echo "</p>";
        ?>

        <p>
            <form method=POST action='index.php'>
                <legend id='time_unit'> select another time scale:</legend>
                <select name='time_unit' id='time_unit'>
                    <option value='day'> today </option>
                    <option value='week'> this week </option>
                    <option value='month'> this month </option>
                    <option value='year'> this year </option>
                </select>
                <input class='button' type='submit' name='change_view' value='select'>
            </form>
        </p>

    <!-- below the statistics, create a user's selector and redirect to the complete stat page -->

        <h3> Select a user: </h3>
        <!-- here simple radio buttons and a submit -->
        <?php
            // display the available tasks
            if(count($users)>0){
                // Task selection
                echo "<form method='POST' action='user.php'>";
                echo "<p> <fieldset>";
                foreach($users as $user){
                    echo "<input type='radio' name='user_name' value='".$user."' id='".$user."'>";
                    echo "<label for='".$user."'> ".$user." </label><br/>";
                }
                echo "</fieldset> </p>";
                echo "<input class='button_big' type='submit' name='go_to_user' value='Go to user page'/>";
                echo "</form>";
            }
            else{
                echo "<div id='pane'><h4> There is no user, please add at least one in the <a href='admin.php'> admin page. </a> </h4></div>";
            }
        ?>

    <!-- in a footer, create a link to the admin page -->

    <footer>
        <nav>
            <ul>
                <li><a href="admin.php"> Go to admin page </a></li>
            </ul>
        </nav>
    </footer>
    </div>
    
</body>
</html>