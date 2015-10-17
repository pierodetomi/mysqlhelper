<?php

	class SqlWhere {
		private $parameterTypes;
		
		private $parameterValues;
		
		private $whereSql;
		
		public function SqlWhere() {
			$this->parameterTypes = "";
			$this->whereSql = "";
			$this->parameterValues = array();
		}
		
		public function addCondition($conditionType, $fieldName, $operator, $filterValue, $parameterType) {
			switch($conditionType) {
				case ConditionType::NONE:
					$this->whereSql .= $fieldName." ".$operator." ?";
					break;
					
				case ConditionType::_AND:
					$this->whereSql .= " AND ".$fieldName." ".$operator." ?";
					break;
					
				case ConditionType::_OR:
					$this->whereSql .= " OR ".$fieldName." ".$operator." ?";
					break;
			}
			
			$this->parameterTypes .= $parameterType;
			
			$paramsCount = count($this->parameterValues);
			$this->parameterValues[$paramsCount] = $filterValue;
			
			return $this;
		}
		
		public function getSql() {
			return $this->whereSql;
		}
		
		public function getBindParamArray() {
			array_unshift($this->parameterValues, $this->parameterTypes);
			return $this->parameterValues;
		}
	}

?>