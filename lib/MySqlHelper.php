<?php

	require("SqlWhere.php");
	require("ParameterType.php");

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
		
		public function loadDataFromStoredProcedure($resultItemTypeName, $spName, array $spParams = NULL) {
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
			
			return $this->bindResultsAndCreateResultItems($statement, $resultItemTypeName);
		}
		
		public function loadDataFromTableOrView($resultItemTypeName, $tableName, SqlWhere $where = NULL) {
			$sql = "SELECT ";
			
			$properties = get_class_vars($resultItemTypeName);
			$propertyIndex = 0;
			
			foreach($properties as $propertyName => $propertyValue) {
				$sql .= ($propertyIndex == 0 ? $propertyName : ", ".$propertyName);
				$propertyIndex++;
			}
			
			$sql .= " FROM ".$tableName;
			
			if($where == NULL) $sql .= ";";
			else $sql .= " WHERE ".$where->getSql().";";
			
			$statement = $this->db->prepare($sql);
			
			if($where != NULL) {
				$values = $where->getBindParamArray();
				$arr = array();
				
				for($i = 0; $i < count($values); $i++) {
					if($i == 0) $arr[$i] = $values[$i];
					else $arr[$i] = &$values[$i];
				}
				
				call_user_func_array(array($statement, "bind_param"), $arr);
			}
			
			$statement->execute();
			
			return $this->bindResultsAndCreateResultItems($statement, $resultItemTypeName);
		}
		
		private function bindResultsAndCreateResultItems($statement, $resultItemTypeName) {
			$properties = get_class_vars($resultItemTypeName);
			
			$results = array();
			$propertyIndex = 0;
			
			foreach($properties as $propertyName => $propertyValue) {
				$results[$propertyIndex] = &$$propertyName;
				$propertyIndex++;
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
				$parameterTypes .= $this::decodeParameterTypeByValue($parameterValue);
				$parametersString .= ($parameterIndex == 0 ? "?" : ", ?");
				
				$parameterIndex++;
			}
			
			return $parametersString;
		}
		
		public static function decodeParameterTypeByValue($parameterValue) {
			$parameterType = gettype($parameterValue);
			
			switch($parameterType) {
				case integer: return ParameterType::INTEGER;
				case string: return ParameterType::STRING;
				default: return ParameterType::STRING;
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