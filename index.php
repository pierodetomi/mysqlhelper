<?php
	/* ----------------------------------------------------------------
							Some examples of use
	---------------------------------------------------------------- */

	require("lib/MySqlHelper.php");
	require("lib/ConditionType.php");
	require("models/Person.php");
	
	$dbHelper = new MySqlHelper("127.0.0.1", "root", "", "TestDb");
	$dbHelper->open();
	
	// Load data from a stored procedure (passing 1 parameter)
	$people = $dbHelper->loadDataFromStoredProcedure(Person.__CLASS__, "getPersonById", array(2));
	
	echo "getPersonById<br />";
	echo json_encode($people[0]);
	echo "<br /><br />";
	
	// Load data from a stored procedure (passing 2 parameters)
	$people = $dbHelper->loadDataFromStoredProcedure(Person.__CLASS__, "findPersonByIdOrName", array(2, "er"));
	
	echo "findPersonByIdOrName<br />";
	echo json_encode($people);
	echo "<br /><br />";
	
	// Load data from a stored procedure (without parameters)
	$people = $dbHelper->loadDataFromStoredProcedure(Person.__CLASS__, "getPeople");
	
	echo "getPeople<br />";
	echo json_encode($people);
	echo "<br /><br />";
	
	// Load data from a table
	$people = $dbHelper->loadDataFromTableOrView(Person.__CLASS__, "Person");
	
	echo "Person table data<br />";
	echo json_encode($people);
	echo "<br /><br />";
	
	// Load data from a view
	$people = $dbHelper->loadDataFromTableOrView(Person.__CLASS__, "AllPeople");
	
	echo "AllPeople view data<br />";
	echo json_encode($people);
	echo "<br /><br />";

	$where = new SqlWhere();
	$where
		->addCondition(ConditionType::NONE, "first_name", "=", "Piero", ParameterType::STRING)
		->addCondition(ConditionType::_OR, "first_name", "=", "Marco", ParameterType::STRING);
	
	// Load data from a view
	$people = $dbHelper->loadDataFromTableOrView(Person.__CLASS__, "AllPeople", $where);
	
	echo "AllPeople view data (filtered)<br />";
	echo json_encode($people);
	echo "<br /><br />";
	
	$where = new SqlWhere();
	$where
		->addCondition(ConditionType::NONE, "first_name", "LIKE", "%er%", ParameterType::STRING);
	
	// Load data from a view
	$people = $dbHelper->loadDataFromTableOrView(Person.__CLASS__, "AllPeople", $where);
	
	echo "AllPeople view data (filtered with LIKE condition)<br />";
	echo json_encode($people);
	echo "<br /><br />";
	
	$dbHelper->close();
	
?>