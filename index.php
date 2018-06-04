<html>
    <body>
<?php
/*
 * Author: Christopher Cox
 * Email: chris15588@gmail.com
 * File: index.php
 * Date: 6/1/2018
 * Description: This is the index file for my PHP final.
 *   This file's job is to print out what is in the database,
 *   and possibly add more functionality later.
 */

// Includes
require_once('config.php');
require_once('dbmanagement.php');

// Easy way for same page redirects
$fileName = pathinfo(__FILE__, PATHINFO_FILENAME) . ".php";

// Attempt to connect to database
$con = connectToDatabase();

// Make sure we were able to connect to the database
if (is_null($con))
    die("Unable to connect to the database!<br />\n");

// To hold lists
$lists = getLists($con);

// Attempt to retrieve list data
if (is_null($lists))
    die("Unable to retrieve list data!<br />\n");

// Check for different 'modes' using what is set in the link
// First check to see if any modes have been set at all
if (filter_has_var(INPUT_GET, 'm')) {
    
    // A mode has been set, so get what the mode is
    $mode = strtolower(filter_input(INPUT_GET, 'm', FILTER_SANITIZE_SPECIAL_CHARS));
    
    // Now for a large chain of if/elseif statements to handle all
    // the various modes
    if (strcmp($mode, 'te') == 0) {
        
        // BEGIN table editor mode
        
        if (!filter_has_var(INPUT_GET, 'id')) {
?>
        <h3>No ID was specified!</h3>
        <a href="<?php echo $fileName; ?>">Go Back</a>
<?php
            
        } else {
        
            // Retrieve the ID
            $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    
            // Get the selected table's ID
            if (!listExists($con, $id)) {
?>
        <h3>The specified list doesn't exist!</h3>
        <a href="<?php echo $fileName; ?>">Go Back</a>
<?php
            } else {
                
                // If we get here, then we know it is safe to display the table editor
                // for the specified table
?>
        <h3>Edit List ID: <?php echo $id; ?></h3>
        <form action="?id=<?php echo $id; ?>&m=tec" method="post">
        
            Description: <input type="text" name="description" value="<?php echo $lists[$id]; ?>"><br />
            <input type="submit" value="submit">
        
        </form>
<?php
                
            }
            
        }
        
        // END table editor mode
        
    } else if (strcmp($mode, 'tec') == 0) {
        
        // BEGIN table edit complete mode
        
        // Make sure an ID was specified
        if (!filter_has_var(INPUT_GET, 'id')) {
?>
        <h3>No ID was specified!</h3>
        <a href="<?php echo $fileName; ?>">Go Back</a>
<?php
            
        } else {
        
            // Retrieve the ID
            $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    
            // Get the selected table's ID
            if (!listExists($con, $id)) {
?>
        <h3>The specified list doesn't exist!</h3>
        <a href="<?php echo $fileName; ?>">Go Back</a>
<?php
            } else {
             
                // Now we must check to see if the data has been properly sent
                if (!filter_has_var(INPUT_POST, 'description')) {
?>
        <h3>Data is missing from the form!</h3><br />
        <a href="<?php echo $fileName; ?>">Go Back</a>
<?php
                } else {
                    
                    // If we get here, we know everything is good, and can sanitize
                    // the input and add it to the database
                    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
                    
                    // Send it to the database
                    if (updateTaskList($con, $id, $description)) {
?>
        <h3>The table has been updated successfully!</h3>
        <a href="<?php echo $fileName; ?>">Go Back</a>
<?php
                    } else {
?>
        <h3>There was an error updating the table!</h3>
        <a href="<?php echo $fileName; ?>">Go Back</a>
<?php
                    }
                    
                }
                
            }
            
        }
        
        // END table edit complete mode
        
    } else if (strcmp($mode, 'at') == 0) {
        
        // BEGIN add table mode
        
        // Check to see if a description was sent over POST
        if (!filter_has_var(INPUT_POST, 'description')) {
?>
        <h3>Something went wrong with the form!</h3>
        <a href="<?php echo $fileName; ?>">Go Back</a>
<?php          
        } else {
            
            // In this else statement, we know we have a description,
            // so all we must do is filter it, then send it to the database
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
            
            if (createList($con, $description)) {
?>
        <h3>The new list has been created successfully!</h3>
        <a href="<?php echo $fileName; ?>">Go Back</a>
<?php
            } else {
?>
        <h3>Something went wrong while creating the new list!</h3>
        <a href="<?php echo $fileName; ?>">Go Back</a>
<?php              
            }
            
        }
        
        // END add table mode
        
    } else if (strcmp($mode, 'tv') == 0) {
        
        // BEGIN table view mode

        // Make sure an ID was specified
        if (!filter_has_var(INPUT_GET, 'id')) {
?>
        <h3>No ID was specified!</h3>
        <a href="<?php echo $fileName; ?>">Go Back</a>
<?php
            
        } else {
        
            // Retrieve the ID
            $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    
            // Get the selected table's ID
            if (!listExists($con, $id)) {
?>
        <h3>The specified list doesn't exist!</h3>
        <a href="<?php echo $fileName; ?>">Go Back</a>
<?php
            } else {
                
                // If we get here, then we know an ID was sent, and it points
                // to an existing list
                
?>
        <!-- A nice header :) -->
        <h3>Task List: <?php echo $lists[$id]; ?></h3>
        
<?php
                
                // From here we print out the list of tasks, if there are any
                // Pull all task items for selected list
                $taskItems = getListItems($con, $id);
    
                // Check to see if it was empty
                if (!(count($taskItems) == 0)) {
        
        // Now for some crazy formatting! Time to put tables in tables!
?>

        <table>
            <tr>
                <th>Task ID</th>
                <th>Description</th>
                <th>Completed</th>
                <th>Meta</th>
            </tr>

<?php        
                foreach($taskItems as $rowID => $dataArray) {
?>
                    
            <tr>
                <td><?php echo $rowID ?></td>
                <td><?php echo $dataArray['description']; ?></td>
                <td><?php echo $dataArray['completed']; ?></td>
                <td><a href="?id=<?php echo $rowID; ?>&m=ie&t=<?php echo $id; ?>">Edit</a> <a href="?id=<?php echo $rowID; ?>&m=ir&t=<?php echo $id; ?>">Delete</a></td>
            </tr>            
        
<?php                               
                } // end of foreach
?>

        </table>
        
<?php
        
                } else { // END of count check 
?>

            There are no tasks for this list!<br />
            
<?php
                }
                
                // While we are on this "window" of the page, we will make it
                // possible to add new items to this task this. This makes
                // It easy to select a list and add items to it
?>
        
        <h3>Add an item to this list</h3>
        <form action="<?php echo $fileName; ?>?id=<?php echo $id ?>&m=ia" method="post">
        
            Task Description: <input type="text" name="description"><br />
            Completed: <input type="text" name="completed" size="1" maxlength="1"><br />
            <input type="submit">
        
        </form>
        <br />
        <a href="<?php echo $fileName; ?>">Go Back</a>
        
<?php                
            } // End of list exists else
            
        } // End of ID existance check 
        // END table view mode
    } else if (strcmp($mode, 'ia') == 0) {
        
        // BEGIN item add mode
        
        // in order for us to add an item, we need a few things:
        // - list ID (to know where to add the item)
        // - list item description
        // - list item completion state
        // If we have all these things, then we can add it to the database!
        
        // Check to see if we have a list ID
        if (!filter_has_var(INPUT_GET, 'id') || !filter_has_var(INPUT_POST, 'description')
            || !filter_has_var(INPUT_POST, 'completed')) {
?>
        <h3>Something went wrong when processing the form!</h3>
        <a href="<?php echo $fileName; ?>">Go Back</a>
<?php          
        } else {
            
            // At this point, we know we have everything we need, so
            // we can add the item to the database after some filtering
            $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
            $completed = filter_input(INPUT_POST, 'completed', FILTER_SANITIZE_SPECIAL_CHARS);
            
            // Attempt to add to the database
            if (createListItem($con, $id, $description, $completed)) {
?>
            
        <h3>The task item has been added to '<?php echo $lists[$id]; ?>' successfully!</h3>
        <a href="<?php echo $fileName; ?>?id=<?php echo $id; ?>&m=tv">Go Back</a>
                
<?php                
            } else {
?>
        <h3>There was an error adding a task item to '<?php echo $lists[$id]; ?>'!</h3>
        <a href="<?php echo $fileName; ?>?id=<?php echo $id; ?>&m=tv">Go Back</a>
<?php              
            }
            
        }
        
        // END item add mode
        
    } else if (strcmp($mode, 'ie') == 0) {
        
        // BEGIN item edit mode
        
        // As usual, we must check to make sure all the data needed is present
        // In order to edit, we need:
        // - The task ID
        // and that's it!
        // (I also passed along the tasklist ID for easiness)
        
        if (!filter_has_var(INPUT_GET, 'id') || !filter_has_var(INPUT_GET, 't')) {
?>
        <h3>Something went wrong when processing the form!</h3>
        <a href="<?php echo $fileName; ?>">Go Back</a>
<?php
        } else {
            
            // Since we are here, we can now gather the data and post the form
            // for the user to edit the task
            $taskID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
            $id = filter_input(INPUT_GET, 't', FILTER_SANITIZE_NUMBER_INT);
            
            // Check to make sure the task exists!
            if (!taskExists($con, $taskID)) {
?>
        <h3>The specified task doesn't exist!</h3>
        <a href="<?php echo $fileName; ?>?id=<?php echo $id; ?>&m=tv">Go Back</a>
<?php              
            } else {
                
                // If we get here, we know the task exists, so we have everything we
                // need. All we have to do is gather the data, then generate the
                // editing form
                $taskData = getTaskItem($con, $taskID);
?>
        <h3>Edit Task from '<?php echo $lists[$id]; ?>'</h3>
        
        <form action="<?php echo $fileName; ?>?id=<?php echo $taskID ?>&t=<?php echo $id ?>&m=iec" method="post">
        
            Description: <input type="text" name="description" value="<?php echo $taskData['description']; ?>"><br />
            Completed: <input type="text" name="completed" value="<?php echo $taskData['completed']; ?>" maxlength="1" size="1"><br />
            <input type="submit" value="submit">
        
        </form>
        
        <a href="<?php echo $fileName; ?>?id=<?php echo $id; ?>&m=tv">Go Back</a>
<?php
                
            }
            
        }
        
        // END item edit mode
        
    } else if (strcmp($mode, 'iec') == 0) {
        
        // BEGIN item edit completed mode
        
        // As usual, we need to do our basic checks to make sure we have all the data
        if (!filter_has_var(INPUT_GET, 'id') || !filter_has_var(INPUT_GET, 't')
           || !filter_has_var(INPUT_POST, 'description') || !filter_has_var(INPUT_POST, 'completed')) {
?>
        <h3>Something went wrong when processing the form!</h3>
        <a href="<?php echo $fileName; ?>">Go Back</a>
<?php          
        } else {
            
            // If we get here, then we know we at least have data to work with.
            // Filter it out and store it in variables
            $id = filter_input(INPUT_GET, 't', FILTER_SANITIZE_NUMBER_INT);
            $taskID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS);
            $completed = filter_input(INPUT_POST, 'completed', FILTER_SANITIZE_SPECIAL_CHARS);
            
            // Attempt to update the task item
            if (updateTaskItem($con, $taskID, $description, $completed)) {
?>
        <h3>The task item has been updated successfully!</h3>
        <a href="<?php echo $fileName; ?>?id=<?php echo $id; ?>&m=tv">Go Back</a>
<?php
            } else {
?>
        <h3>There was an error while updating the task item!</h3>
        <a href="<?php echo $fileName; ?>?id=<?php echo $id; ?>&m=tv">Go Back</a>
<?php    
            }
            
        }
        
        // END item edit completed mode
        
    } else if (strcmp($mode, 'ir') == 0) {
        
        // BEGIN item remove mode
        
        // All we need to check for is a task item id
        // and the task list id
        if (!filter_has_var(INPUT_GET, 'id') || !filter_has_var(INPUT_GET, 't')) {
?>
        <h3>Something went wrong when processing the form!</h3>
        <a href="<?php echo $fileName; ?>">Go Back</a>
<?php
        } else {
            
            // Here we just generate a basic form asking the user if they
            // really want to delete a task item.. Basic stuff
            
            // Filter in the information
            $taskID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
            $id = filter_input(INPUT_GET, 't', FILTER_SANITIZE_NUMBER_INT);
?>
  
        <h3>Task Deletion Confirmation</h3>
        <form action="<?php echo $fileName; ?>?id=<?php echo $taskID; ?>&t=<?php echo $id; ?>&m=irc" method="post">
        
            Are you sure you want to delete task ID:<?php echo $taskID; ?>?<br />
            <input type="submit" value="submit">
        
        </form>
        <a href="<?php echo $fileName; ?>?id=<?php echo $id; ?>&m=tv">Go Back</a>
<?php
            
        }
        
        // END item remove mode
        
    } else if (strcmp($mode, 'irc') == 0) {
        
        // BEGIN item removal confirmed mode
        
        // Make sure we have the necessary information in the link
        if (!filter_has_var(INPUT_GET, 'id') || !filter_has_var(INPUT_GET, 't')) {
?>
        <h3>Something went wrong when processing the form!</h3>
        <a href="<?php echo $fileName; ?>">Go Back</a>
<?php
        } else {
            
            // Now we can gather the necessary data and delete the task
            $id = filter_input(INPUT_GET, 't', FILTER_SANITIZE_NUMBER_INT);
            $taskID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
            
            // Attempt to delete the task item
            if (deleteTaskItem($con, $taskID)) {
?>
        <h3>The task item has been deleted successfully!</h3>
        <a href="<?php echo $fileName; ?>?id=<?php echo $id; ?>&m=tv">Go Back</a>
<?php              
            } else {
?>
        <h3>There was an error deleting the task item!</h3>
        <a href="<?php echo $fileName; ?>?id=<?php echo $id; ?>&m=tv">Go Back</a>
<?php              
            }
            
        }
        
        // END item removal confirmed mode
        
    } else if (strcmp($mode, 'td') == 0) {
        
        // BEGIN task list delete mode
        
        // Make sure we have the necessary data
        if (!filter_has_var(INPUT_GET, 'id')) {
?>
        <h3>Something went wrong when processing the form!</h3>
        <a href="<?php echo $fileName; ?>">Go Back</a>
<?php
        } else {
            
            // If we get here, then we have everything we need
            // to delete the task list. We just need a confirmation
            $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
?>
        
        <h3>Task List Deletion Confirmation</h3>
        
        <form action="<?php echo $fileName; ?>?id=<?php echo $id; ?>&m=tdc" method="post">
        
            Are you sure you want to delete table ID: <?php echo $id; ?> and all of its tasks?<br />
            <input type="submit" value="confirm">
        
        </form>
        
<?php            
        }
        
        // END task list delete mode
        
    } else if (strcmp($mode, 'tdc') == 0) {
        
        // BEGIN task list delete confirmed mode
        
        // Check to see if an ID was passed over or not
        if (!filter_has_var(INPUT_GET, 'id')) {
?>
        <h3>Something went wrong when processing the form!</h3>
        <a href="<?php echo $fileName; ?>">Go Back</a>
<?php
        } else {
            
            // If we get here, then we can delete the task list
            // and all of it's parts
            $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
            
            // Attempt to delete the task list and its entities
            if (deleteTaskList($con, $id)) {
?>
        <h3>The task list has been deleted successfully!</h3>
        <a href="<?php echo $fileName; ?>">Go Back</a>
<?php
            } else {
?>
        <h3>There was an error deleting the task list!</h3>
        <a href="<?php echo $fileName; ?>">Go Back</a>
<?php
            }
            
        }
        
        // End task list delete confirmed mode
        
    }
    
} else {
        
// Print out all lists
?>
        <h2>List Table<br />
        Select a List or Edit</h2>
        
        <table style="border: 1px solid black;">
            <tr>
                <th>Id</th>
                <th>List</th>
                <th>Meta</th>
            </tr>
            
<?php

    // Retrieve all list data and print it out
    $listArray = getLists($con);

    // Loop through and print them all
    foreach ($listArray as $key => $value) {
?>
    
            <tr>
                <td><?php echo $key; ?></td>
                <td><a href="?id=<?php echo $key; ?>&m=tv"><?php echo $value; ?></a></td>
                <td><a href="?id=<?php echo $key; ?>&m=te">Edit</a> <a href="?id=<?php echo $key; ?>&m=td">Delete</a></td>
            </tr>
    
<?php  
    }
?>
            
        </table>
        
        <h3>Add a table</h3>
        <!-- Create a form for adding more tables -->
        <form action="<?php echo "$fileName?m=at"; ?>" method="post">
            Description: <input type="text" name="description">
            <input type="submit" value="submit">
        </form>
        
<?php
} // End check if mode has been set
    
?>
    </body>
    
</html>