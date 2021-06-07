<?php
    include 'utils.php';
    $today = new DateTimeImmutable('today');
    $data_file = 'data/data.json';
    $data = json_decode(file_get_contents($data_file), true);
    $user = file_get_contents('data/current_user.txt');
    if($user == ''){
        header('location: index.php');
    }
    // if add or modify is submitted
    if(isset($_POST['submit_drink'])){
        $date = new DateTimeImmutable($_POST['date']);
        if($today >= $date && is_numeric($_POST['quantity'])){
            $state = 'added';
            $date_format = $date->format('Y-m-d');
            $quantity = (float) $_POST['quantity'];
            if(isset($data[$user][$date_format])){
                $state = 'modified';
            }
            $data[$user][$date_format] = $quantity;
            file_put_contents($data_file, json_encode($data));
            echo "<div id='pane'> Quantity ".$quantity."cl ".$state." for user ".$user." at date ".$date_format." !</div>";
            update_database($data_file);
        }
        else{
            echo "<div id='pane'> <h4> Wrong entry. </h4> </div>";
        }
    }
    // to display a given time unit
    if(isset($_POST['change_view'])){
        $time_unit = $_POST['time_unit'];
        if(is_numeric($_POST['offset'])){
            $offset = $_POST['offset'];
            if($time_unit == 'week' && ($offset == 0 || $offset == 1)){
                $time_unit = 'day';
                $offset = $today->format('w');
            }
            elseif($time_unit == 'month' && ($offset == 0 || $offset == 1)){
                $time_unit = 'week';
                $offset = weekOfMonth($today);
            }
        }
        else{
            $offset = 2;
        }
    }
    else{
        $time_unit = 'week';
        $offset = 2;
    }
?>

<!DOCTYPE html>
<html>

<head>
    <title>User page</title>
    <link rel="stylesheet" type="text/css" href="beertracker/style.css" media="screen"/>
    <link rel="stylesheet" type="text/css" href="style.css" media="screen"/>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

    <div id='pane'>
        <!-- title -->
        <h1> <a href = "" > Personal view </a> </h1>
        <!-- subtitle -->
        <h2> Of user <?php echo $user ?></h2>

    <!-- here some statistics -->

        <h3> Scores evolution for the last <?php echo $offset." ".$time_unit.'s' ?>: </h3>
        <?php display_scores_comparison($data[$user], $offset, $time_unit); ?>
        <p>
            <form method=POST action='user.php'>
                <legend id='time_unit'> select another time scale:</legend>
                <select name='time_unit' id='time_unit'>
                    <option value='day'> day </option>
                    <option value='week'> week </option>
                    <option value='month'> month </option>
                    <option value='year'> year </option>
                </select>
                <input type='text' name='offset' placeholder="the last ... *time*">
                <input class='button' type='submit' name='change_view' value='select'>
            </form>
        </p>

    <!-- here the 'add a drink' tab -->

        <h3> Add or modify a drink: </h3>
        <form method='POST' action='user.php'>
        <p> Enter the drink's date: <input type="date" name="date" value="<?php echo date('d.m.y'); ?>" />,<br/> </p>
        <p> and the drink's quantity: <input type="text" name="quantity"/> cl.<br/> </p>
        <input class='button_big' type='submit' name='submit_drink' value='&#127866; Submit the drink !'/>
        </form>

    <!-- in a footer, create a link to the home page -->

    <footer>
        <nav>
            <ul>
                <li><a href="redirect.php"> Go to home page </a></li>
            </ul>
        </nav>
    </footer>
    </div>
    
</body>
</html>