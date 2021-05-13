<?php
    // initialize
    $data_file = 'data/data.json';
    $data = json_decode(file_get_contents($data_file), true);
    $users = array_keys($data);
    // add user
    echo "<div id=pane>";
    if(isset($_POST['submit_add_user'])){
        $new_user_name = $_POST['new_user'];
        // if the user to be added is not an empty string
        if(strcmp($new_user_name, '') != 0){
            // add the new key to data array
            $data[$new_user_name] = array();
            // write the new file
            file_put_contents($data_file, json_encode($data, JSON_FORCE_OBJECT));
            echo "New user ".$new_user_name." added ! <br>";
        }
    }
    // modify user
    if(isset($_POST['submit_modify_user'])){
        // set the variables
        $modify_user_name = $_POST['user_to_modify'];
        $new_user_name = $_POST['user_new_name'];
        // store the previous data and unset it
        $previous_data = $data[$modify_user_name];
        unset($data[$modify_user_name]);
        // write the new file
        $data[$new_user_name] = $previous_data;
        file_put_contents($data_file, json_encode($data));
        echo "User ".$modify_user_name." modified as ".$new_user_name." ! <br>";
    }
    // remove user
    if(isset($_POST['submit_remove_user'])){
        // set the variables
        $remove_user_name = $_POST['user_to_remove'];
        // kick the user from the dataset
        unset($data[$remove_user_name]);
        // write the new file
        file_put_contents($data_file, json_encode($data, JSON_FORCE_OBJECT));
        echo "User ".$remove_user_name." removed ! <br>";
    }
    // show reset confirmation
    if(isset($_POST["reset_one"])){
        $class = 'visible';
    }
    else{
        $class = 'hidden';
    }
    // to reset all data
    if(isset($_POST['reset_two'])){
        $reset_data = array();
        // write the *empty* data
        file_put_contents($data_file, json_encode($reset_data, JSON_FORCE_OBJECT));
        echo "All data have been erased. <br>";
    }
    echo "</div>";
?>

<!DOCTYPE html>
<html>

<head>
    <title>&#x1F6BF Admin page </title>
    <link rel="stylesheet" type="text/css" href="beertracker/style.css" media="screen"/>
    <link rel="stylesheet" type="text/css" href="style.css" media="screen"/>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div id='pane'>

        <h1> <a href = "" > Admin page </a> </h1>

        <form method='POST' action='admin.php'>
            <p>
                Add a new user:<input type='text' name='new_user' /><input class='button' type='submit' name='submit_add_user' value='Add' />
            </p>

        <?php
            if(count($users)>0){
                // modify existing user if any
                echo "<label for='user_to_modify'> Modify a user: </label>";
                echo "<select name='user_to_modify' id='user_to_modify'>";
                // make each type selectable
                foreach($users as $user){
                    echo "<option value='".$user."'> ".$user."</option>";
                }
                echo "</select>";
                echo "<p> New name <input type='text' name='user_new_name' />";
                echo "<input class='button' type='submit' name='submit_modify_user' value='Modify user' /></p>";
                // remove type
                echo "<p><label for='user_to_remove'> Remove a user: </label>";
                echo "<select name='user_to_remove' id='user_to_remove'>";
                // make each type selectable
                foreach($users as $user){
                    echo "<option value='".$user."'> ".$user." </option>";
                }
                echo "</select>";
                echo "<input class='button' type='submit' name='submit_remove_user' value='Remove user' /></p>";
            }
        ?>

            <input class='button_big' type='submit' name='reset_one' value='&#9888; Reset all &#9888;'/>
        <?php
            // display configuration mode if needed
            if($class == 'visible'){
                echo "<p> <input class='button_big' type='submit' name='reset_two' value='Sure ?' /> </p>";
            }
        ?>
        </form>
        <p> Please refresh this page after each changes. </p>

    <footer>
            <nav>
                <ul>
                    <li><a href="index.php"> Return to homepage </a></li>
                </ul>
            </nav>
    </footer>
    </div>
    
</body>
</html>