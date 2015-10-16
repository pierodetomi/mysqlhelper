<?php

	class MySqlHelper {
		private $db;
		
		public $server;
		
		public $user;

		public $password;
		
		public $database;
		
		public function MySqlHelper($server, $user, $password, $database) {
			$this->server = $server;
			$this->user = $user;
			$this->password = $password;
			$this->database = $database;
		}
		
		public function open() {
			$this->db = new mysqli(
				$this->server,
				$this->user,
				$this->password,
				$this->database);
		}
		
		public function close() {
			$this->db->close();
		}
		
		public function execSpWithResults($resultItemTypeName, $spName, array $spParams = NULL) {
			$sql = "CALL ".$spName."(";
			$parameterTypes = "";
			
			if($spParams != NULL) {
				$parametersString = $this->getParametersString($spParams, $parameterTypes);
				$sql .= $parametersString;
			}
			
			$sql .= ");";
			
			$statement = $this->db->prepare($sql);
			
			if($spParams != NULL) {
				$bindParams = array();
				
				for($i = 0; $i < count($spParams); $i++) {
					$bindParams[$i] = &$spParams[$i];
				}
				
				array_unshift($bindParams, $parameterTypes);
				
				call_user_func_array(array($statement, "bind_param"), $bindParams);
			}
			
			$statement->execute();
			
			$properties = get_class_vars($resultItemTypeName);
			$results = array();
			
			$paramIndex = 0;
			
			foreach($properties as $name=>$value) {
				$results[$paramIndex] = &$$name;
				$paramIndex++;
			}
			
			call_user_func_array(
				array($statement, "bind_result"),
				$results);
			
			$resultItems = array();
			
			while($statement->fetch()) {
				$resultItem = $this->createResultItemOfType($resultItemTypeName, $properties, $results);
				array_push($resultItems, $resultItem);
			}
			
			return $resultItems;
		}
		
		private function createResultItemOfType($resultItemTypeName, $properties, $results) {
			$resultItem = new $resultItemTypeName();
			
			$fieldIndex = 0;
			
			foreach($properties as $propertyName => $propertyValue) {
				$resultItem->$propertyName = $results[$fieldIndex];
				$fieldIndex++;
			}
			
			return $resultItem;
		}
		
		private function getParametersString($parametersArray, &$parameterTypes) {
			$parameterIndex = 0;
			$parametersString = "";
			
			foreach($parametersArray as $parameterValue) {
				$parameterTypes .= $this->decodeParameterTypeByValue($parameterValue);
				$parametersString .= ($parameterIndex == 0 ? "?" : ", ?");
				
				$parameterIndex++;
			}
			
			return $parametersString;
		}
		
		private function decodeParameterTypeByValue($parameterValue) {
			$parameterType = gettype($parameterValue);
			
			switch($parameterType) {
				case integer: return "i";
				case string: return "s";
				default: return "s";
			}
		}
		
		private function prepareParameterValueForStatement($parameterValue) {
			$parameterType = gettype($parameterValue);
			
			switch($parameterType) {
				case integer: return $parameterValue;
				case string: return "'".$parameterValue."'";
				default: return "'".$parameterValue."'";
			}
		}
	}

?>