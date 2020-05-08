<?php

//========== Global Parameters ==========

$msgIndex = 0;

$targetDB = '';
$querytype = 'sql';
$inputQuery = '';

$tableName = '';
$selection = '';

$errorMsg = array('');
$successMsg = array('');
$defaultTables = ['information_schema', 'mysql', 'performance_schema', 'sakila', 'sys', 'world'];

//========== Database Connection ==========

$servername = "localhost";
$username = "root";
$password = "Password1!";
$dbname = "information_schema";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//========== Button Actions ==========

if(isset($_POST['submit']))
{
  $selection = $_POST['sqldblist'];

  if($selection !== 'Select Database') {$targetDB = $selection;}

  // Create connection
  $conn = new mysqli($servername, $username, $password, $targetDB);
  // Check connection
  if ($conn->connect_error) {
      die($conn->connect_error);
  }

  $search_result = null;

  $inputQuery = trim($_POST['inputQuery']);

  if (strpos(strtolower('###'.$inputQuery), 'create database'))
  {
      //updateMessages('error', 'Database creation not allowed on this platform.');
  }
  else if (strpos(strtolower('###'.$inputQuery), 'drop database'))
  {
      //updateMessages('error', 'Database deletion not allowed on this platform.');
  }
  // else
  {
      if (0)//(mysqli_multi_query($conn, str_replace('<br>', '', $inputQuery)))
    {
        do
        {   
            //check first result
            if ($result = mysqli_store_result())
            {
                $search_result = $result; echo $inputQuery;
                //free the result and move on to next query
                mysqli_free_result($result);
            }
            else
            {
                updateMessages('error', $conn->error);
            }

            $success = mysqli_next_result($conn); echo $success;
            if (!$success)
            {
                updateMessages('error', $conn->error);
            }
            else 
            {
                $search_result = mysqli_store_result($conn);
            }
        }
        while ($success);
    }

      //$search_result = mysqli_store_result($conn);
      $search_result = $conn->query($inputQuery);
      if(is_bool($search_result) and $search_result)
      {
        $operation = substr($inputQuery, 0, strpos($inputQuery, ' '));
        updateMessages('success', ucfirst($operation).' operation successfully executed.');
      }
  }

  //retrieve column names to display in output table
  $col_names = '';
  if (strpos(strtolower('###'.substr(trim($inputQuery), 0, 7)), 'select'))
  {
      preg_match('/(?<=select )(.*)(?= from)/', $inputQuery, $regexResults);
      $col_names = $regexResults[0];
  }
  if (strpos(strtolower('###'.substr(trim($inputQuery), 0, 7)), 'show'))
  {
      $col_names = 'show';
  }

  if($col_names == '*' or strtolower($col_names) == 'show')
  {

      if (strtolower($col_names) == 'show'){$q = rtrim($inputQuery, ';');}
      else
      {
          $q = $inputQuery;
          if (strpos($q, 'limit')) # remove any occurence of 'limit'
          {
              $q = substr($q, 0, strpos($q, 'limit'));
          }
          
          $q = rtrim($q, ';').' limit 1';
      }

      $col_names = '';
      if ($result = mysqli_query($conn, $q))
      {
        // Get field information for all fields
          while ($fieldinfo = mysqli_fetch_field($result))
          {
              $col_names .= $fieldinfo->name.' ';
          }
          // Free result set
          mysqli_free_result($result);
      }
      else
      {
          updateMessages('error', $conn->error);
      }
  }

  $columns = explode(" ", trim($col_names));
}

//========== Functions ==========
function updateMessages($msgStatus, $msg)
{
    GLOBAL $msgIndex;
    GLOBAL $successMsg;
    GLOBAL $errorMsg;

    if($msg != '')
    {
        $msgIndex += 1;
        if ($msgStatus == 'success') {array_push($successMsg, $msgIndex.'. '.$msg);}
        else {array_push($errorMsg, $msgIndex.'. '.$msg);}
    }
}

?>

<!------------- HTML ------------->

<!DOCTYPE html>
<html>
<script src = "print.js"></script>
    <head>
        <title>Relational Algebra Interpreter for SQL</title>
        <link href = 'style.css' rel = 'stylesheet'>
    </head>

    <body>
      <h2>Relational Algebra Interpreter for SQL</h2>

      <section class="block-of-text" style="display: none;">
        <button class="collapsible">See Example Usage</button>
        <div class="content">
          <fieldset class = "side">
            <legend>Sample Database</legend>
              
          </fieldset>

          <fieldset class = "side">
            <legend>Sample Updates</legend>
              
          </fieldset>

          <fieldset class = "side">
            <legend>Sample Queries</legend>
              
          </fieldset>
        </div>
      </section>

      <form action = "sandbox.php" method = "post" id = "options">

      <!-- QUERY OPTIONS SECTION -->

        <section class = "block-of-text">
          <fieldset>
            <legend>Target Database</legend>

              <!-- populate drop-down list -->
              <?php
              
              $list = '<option name = "sqldblist">Select Database</option>';

              //Extract list of all databases
              $q = 'show databases;';
              if ($dblist = mysqli_query($conn, $q))
              {
                while($row = mysqli_fetch_array($dblist))
                {
                  $val = $row['Database'];
                  if (array_search($val, $defaultTables) === false) # exclude defauls mysql tables
                  {
                    $list .= '<option';
                    $list .= $val == $targetDB ? ' selected = \'selected\'>' : '>';
                    $list .= $val.'</option>';
                  }
                }
                mysqli_free_result($dblist);
              }
              ?>

              <select name = "sqldblist">
                <?php echo $list; ?>
              </select>

              <br>

          </fieldset>

        </section>
        <!-- INPUT SECTION -->
        <section class = "block-of-text">
          <fieldset>
            <legend>Input</legend>
                <textarea class = "FormElement" name = "inputQuery" id = "input" cols = "40" rows = "10" placeholder = "Type Query Here"><?php echo $inputQuery; ?></textarea>

                <br>
                <button type = "button" alt="Selection" width="20" height="20" onclick = "printRA('input','σ()')"> <img src = "selection.png"> </button>
                <button type = "button" alt="Projection" width="20" height="20" onclick = "printRA('input','Π()')"> <img src = "projection.png"> </button>
                <button type = "button" alt="Union" width="20" height="20" onclick = "printRA('input','∪')"> <img src = "union.png"> </button>
                <button type = "button" alt="Intersection" width="20" height="20" onclick = "printRA('input','∩')"> <img src = "intersection.png"> </button>
                <button type = "button" alt="Difference" width="20" height="20" onclick = "printRA('input','-')"> <img src = "diff.png"> </button>
                <button type = "button" alt="Cartesian" width="20" height="20" onclick = "printRA('input','×')"> <img src = "cartesian.png"> </button>
                <button type = "button" alt="Divide" width="20" height="20" onclick = "printRA('input','÷')"> <img src = "divide.png"> </button>
                <button type = "button" alt="Join" width="31" height="33" onclick = "printRA('input','⨝()')"> <img src = "join.png"> </button>
                <button type = "button" alt="And" width="20" height="20" onclick = "printRA('input','∧')"> <img src = "and.png"> </button>
                <button type = "button" alt="Or" width="20" height="20" onclick = "printRA('input','∨')"> <img src = "or.png"> </button>
                <button type = "button" alt="Not" width="20" height="20" onclick = "printRA('input','˜')"> <img src = "not.png"> </button>
                <br>
                <input type = "submit" id = "submit" name = "submit" value = "Submit" onclick = "return checkInput();">

          </fieldset>
        </section>
      </form>

      <!-- OUTPUT SECTION -->
      <form action = "sandbox.php" method = "post">

        <section class = "block-of-text">
          <fieldset>
            <legend>Output</legend>

              <?php $messages = array_merge($successMsg, $errorMsg); asort($messages); ?>
                <?php foreach ($messages as $msg):?>
                    <b><?php if ($msg !== '') { echo $msg.'<br>';} ?></b>
                <?php endforeach; ?>

                <br>

                <?php if($search_result and !is_bool($search_result)): ?>

                    <table>
                        <!-- table header -->
                        <tr>
                            <?php foreach ($columns as $col):?>
                                <th><?php echo trim($col, ",");?></th>
                            <?php endforeach; ?>
                        </tr>

                        <!-- populate table -->
                        <?php if ($search_result and $search_result != ''):?>
                            <?php while($row = mysqli_fetch_array($search_result)):?>
                                <tr>
                                    <?php foreach ($columns as $col):?>
                                        <td><?php echo $row[trim($col, ",")];?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endwhile;?>
                        <?php endif?>
                    </table>

                <?php endif?>
                
          </fieldset>
        </section>
      </form>

      <section class = "block-of-text">
        <a href="sandbox.php"><input type = "submit" name = "reset" value = "Reset Page"/></a>
      </section>

      <?php $conn->close(); ?>

      <script src = "effects.js"></script>
                                     
    </body>
</html>
