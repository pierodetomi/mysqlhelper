<?php
	/* ----------------------------------------------------------------
							Some examples of use
	---------------------------------------------------------------- */

	require("lib/MySqlHelper.php");
	require("models/Person.php");

	$dbHelper = new MySqlHelper("127.0.0.1", "root", "", "TestDb");
	$dbHelper->open();
	
	$people = $dbHelper->execSpWithResults(Person.__CLASS__, "getPersonById", array(2));
	
	echo "getPersonById<br />--------";
	var_dump($people[0]);
	echo "<br /><br />";
	
	$people = $dbHelper->execSpWithResults(Person.__CLASS__, "findPersonByIdOrName", array(2, "er"));
	
	echo "findPersonByIdOrName<br />--------";
	var_dump($people);
	echo "<br /><br />";
	
	$people = $dbHelper->execSpWithResults(Person.__CLASS__, "getPeople");
	
	echo "getPeople<br />--------";
	var_dump($people);
	
	$dbHelper->close();
	
?>