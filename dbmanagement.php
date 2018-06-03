<?php

/*
 * Author: Christopher Cox
 * Email: chris15588@gmail.com
 * File: dbmanagement.php
 * Date: 6/1/2018
 * Description: This file contains all the workings with
 *   the database. Here we will have functions for gathering
 *   and updating database information
 */

// (JIC): Import config
require_once('config.php');

// Function for connecting to database
// If we are unable to connect to the database, the function will
// return NULL
function connectToDatabase() {
    
    $con = new mysqli(DB_ADDRESS, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
    
    if ($con->connect_error)
        return NULL;
    else
        return $con;
    
}

// A function for getting all lists and their descriptions
// An active mysql connection must be passed as an argument
// It must also be the OOP version
// Will return NULL if there was an error
function getLists($connection) {
        
    // Generate the SQL
    $sql = "SELECT * FROM list";
    // Execute it on the server
    $result = $connection->query($sql);
    
    // Check for any errors
    if (mysqli_connect_errno())
        return NULL;
    
    // Create an empty array to hold all our values
    $listData = [];
    
    // Loop through and gather data from database
    if ($result->num_rows > 0)
        while ($row = $result->fetch_assoc())
            $listData[$row['id']] = $row['description'];
    
    // Return our new data
    return $listData;
    
}

// A function for retrieving a specified list's items.
// You must specify the list by its ID, and supply
// the database connection as the first argument
// Will return NULL if there was an error
function getListItems($connection, $listID) {
    
    // (JIC)
    $listID = $connection->real_escape_string($listID);
    
    // Generate the SQL
    $sql = "SELECT * FROM listitem WHERE list_id = $listID";
    // Execute it on the server
    $result = $connection->query($sql);
    
    // Check for any errors
    if (mysqli_connect_errno())
        return NULL;
    
    // Create empty array for holding return data
    $listItemData = [];
    
    // Loop through query data
    while ($row = $result->fetch_assoc()) {
        
        // Build assoc. array to store inside item data array
        $tempArray = [
            'list_id'           =>  $row['list_id'],
            'description'       =>  $row['description'],
            'completed'         =>  $row['completed'],
        ];
        
        // Store inside item data array by it's own ID
        $listItemData[$row['id']] = $tempArray;

    }
    
    // Return the new data
    return $listItemData;
    
}

// A function for creating lists on the database
// You must supply the connection, and a list description.
// The ID will be automatically generated. The function will return
// FALSE if there was an error, and TRUE if there wasn't
function createList($connection, $listDesc) {
    
    // (JIC)
    $listDesc = $connection->real_escape_string($listDesc);
    
    // Generate the SQL
    $sql = "INSERT INTO list (description) VALUES ('$listDesc')";
    
    // Execute the query
    return $connection->query($sql);
    
}

// A function for creating list items in the database
// You just supply the connection, the list ID, the
// description, completed (Y or N)
function createListItem($connection, $listID, $desc, $comp) {
    
    // Escape all statements
    $listID = $connection->real_escape_string($listID);
    $desc = $connection->real_escape_string($desc);
    $comp = $connection->real_escape_string($comp);
    
    // Generate the SQL
    $sql = "INSERT INTO listitem (list_id, description, completed) " .
        "VALUES ($listID, '$desc', '$comp')";
    
    // Execute the query
    return $connection->query($sql);
    
}

// A function for selecting a specific task item by its ID.
// This is useful for the edit pages because it allows you
// to pull everything one just one task
// Will return null if there was an error
function getTaskItem($connection, $taskID) {
    
    // (JIC)
    $taskID = $connection->real_escape_string($taskID);
    
    // Generate the SQL
    $sql = "SELECT * FROM listitem WHERE id = $taskID";
    
    $result = $connection->query($sql);
    
    // Check for any errors
    if (mysqli_connect_errno())
        return NULL;
    
    $row = $result->fetch_assoc();
    
    // Build array to return
    $taskData = [
        'id'            => $row['id'],
        'list_id'       => $row['list_id'],
        'description'   => $row['description'],
        'completed'     => $row['completed'],
    ];
    
    return $taskData;
    
}

// A function for updating a selected task item by its
// ID. The function will return false if an error occurred
function updateTaskItem($connection, $taskID, $description, $completed) {
    
    // (JIC)
    $taskID = $connection->real_escape_string($taskID);
    $description = $connection->real_escape_string($description);
    $completed = $connection->real_escape_string($completed);
    
    // Generate the SQL
    $sql = "UPDATE listitem SET description = '$description', completed = '$completed' WHERE id = $taskID";
    
    return $connection->query($sql);
    
}

// A function for updating a selected task list by its ID
// The function will return false if there was an error
function updateTaskList($connection, $listID, $description) {
    
    // (JIC)
    $listID = $connection->real_escape_string($listID);
    $description = $connection->real_escape_string($description);
    
    // Generate the SQL
    $sql = "UPDATE list set description = '$description' WHERE id = $listID";
    
    return $connection->query($sql);
    
}

// A function for deleting a selected task by its ID.
// The function will return false if an error occurred
function deleteTaskItem($connection, $taskID) {
    
    // (JIC)
    $taskID = $connection->real_escape_string($taskID);
    
    // Generate the SQL
    $sql = "DELETE FROM listitem WHERE id = $taskID";
    
    return $connection->query($sql);
    
}

// A function for checking to see if a list exists via
// its ID. Returns true or false
function listExists($connection, $listID) {
    
    // (JIC)
    $listID = $connection->real_escape_string($listID);

    // Generate the SQL
    $sql = "SELECT * FROM list WHERE id = $listID";
    
    $result = $connection->query($sql);
    
    return (count($result->fetch_assoc()) > 0);
    
}

// A function for checking to see if a task exists
// via its ID. Returns true of false
function taskExists($connection, $taskID) {
    
    // (JIC)
    $taskID = $connection->real_escape_string($taskID);
    
    // Generate the SQL
    $sql = "SELECT * FROM listitem WHERE id = $taskID";
    
    $result = $connection->query($sql);
    
    return (count($result->fetch_assoc()) > 0);
}

// A function for deleteing a task list and all of its
// tasks from the database. Will return false if there
// was an error
function deleteTaskList($connection, $listID) {
    
    // (JIC)
    $listID = $connection->real_escape_string($listID);
    
    $sql = "DELETE FROM listitem WHERE list_id = $listID";
    $taskDeletion = $connection->query($sql);
    
    $sql = "DELETE FROM list WHERE id = $listID";
    $taskListDeletion = $connection->query($sql);
    
    return ($taskDeletion && $taskListDeletion);
    
}

?>