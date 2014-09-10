<?php

	class Query
	{
		protected $query_types = array('select', 'insert', 'update', 'delete');
		protected $queryString;
        protected $_params = array();
		/** @var Sql */
        protected $sql;
        protected $unionAllFlag = false;
        protected $_nullIfEmpty = array();
		
		public $type;
		public $queryMod;
		public $tables = array();
		public $fields;
		public $values;
        /**
         * @var Where
         */
		public $where;
		public $policy;
		public $groupBy;
        public $having;
		public $orderBy;
		public $limit;

        /**
         * @var Query[]
         */
        protected $union = array();
		
		public $calcRows = false;
		
		public function __construct($params = null)
		{
			if(is_string($params))
			{
				if(!in_array(strtolower($params), $this->query_types))
				{
					$this->queryString = $params;
				}
				else
					$this->type = strtolower($params); 
			}
			else if(is_array($params))
			{
				if(!empty($params['type']))
					$this->type = $params['type'];
					
				if(!empty($params['tables']))
				{
					if(is_array($params['tables']))
						$this->tables = $params['tables'];
					else
						$this->addTable($params['tables']);
				}

				if(!empty($params['fields']))
				{
					if(is_array($params['fields']))
						$this->fields = $params['fields'];
					else
						$this->fields[] = $params['fields'];
				}

				if(!empty($params['values']))
				{
					if(is_array($params['values']))
						$this->values = $params['values'];
					else
						$this->values[] = $params['values'];
				}

				if(!empty($params['order']))
					$this->orderBy = $params['order'];

				if(!empty($params['limit']))
					$this->limit = $params['limit'];
			}
			
//			$this->tables = array();
			$this->where = new Where();
		}
		
		public function addTable($table)
		{
			array_push($this->tables, $table);

            return $this;
		}
		
		public function union($query)
		{
			array_push($this->union, $query);

            return $this;
		}

        public function unionAll($query)
		{
			array_push($this->union, $query);
            $this->unionAllFlag = true;

            return $this;
		}
		
		public function addField($field, $value = null, $nullIfEmpty = false)
		{
			if(!is_array($this->fields))
			{
				if(!empty($this->fields))
				{
					$backup = $this->fields;
					$this->fields = array();
					$this->fields[] = $backup;
				}
				else
				{
					$this->fields = array();
				}
			}
			
			$this->fields[] = $field;

			if(!is_array($this->values))
			{
				if(!empty($this->values))
				{
					$backup = $this->values;
					$this->values = array();
					$this->values[] = $backup;
				}
				else
				{
					$this->values = array();
				}
			}

            if($this->type == 'insert' || $this->type == 'update')
            {
                if(!$nullIfEmpty || ($nullIfEmpty && (int)$value))
                {
                    $this->values[] = $value;
                    $this->_params[':' . $field] = $value;

                }
                else
                {
                    $this->values[] = 'inline:NULL';
                }
            }

            return $this;
		}
		
		public function Get()
		{			
			if(!empty($this->queryString))
				return $this->queryString;
			
			$this->type = strtolower($this->type);
			
			$result = $this->type.' ';
			
			if(!empty($this->queryMod))
				$result .= $this->queryMod.' ';
						
			$table_result = '';
            if(is_array($this->tables))
			{
				foreach($this->tables as $table)
				{
					$table_result .= $table.', ';
				}
				$table_result = substr($table_result, 0, strlen($table_result)-2);
			}
			else
			{
				$table_result = $this->tables;
			}
			
			switch($this->type)
			{
				case 'select':

                    $field_result = '';
					if(is_array($this->fields))
					{
						foreach($this->fields as $field)
						{
							$field_result .= $field.', ';
						}
						$field_result = substr($field_result, 0, strlen($field_result)-2);
					}
					else
					{
						$field_result = $this->fields;
					}
					
					if($this->calcRows)
						$result .= 'SQL_CALC_FOUND_ROWS ';
					$result .= $field_result;
					if(!empty($table_result))
						$result .= ' from '.$table_result;
				break;
				
				case 'delete':
					$result .= ' from '.$table_result;
				break;
				
				case 'insert':
					$result .= ' into ';
					$result .= $table_result.' (';
					
					$field_result = '';
                    if(is_array($this->fields))
					{
						foreach($this->fields as $field)
						{
							$field_result .= $field.', ';
						}
						$field_result = substr($field_result, 0, strlen($field_result)-2);
					}
					else
					{
						$field_result = $this->fields;
					}
					
					$result .= $field_result.' ) values ( ';
					
					if(is_array($this->values))
					{
						for($i = 0; $i < count($this->values); $i++)
						{
							if(
								(strpos($this->values[$i], 'inline:') === false)
							)
								$result .= "'".addslashes($this->values[$i])."', ";
							else
							{
								$this->values[$i] = preg_replace('/^inline:(.*)/', '\1', $this->values[$i]);
								$result .= $this->values[$i].", ";
							}						
						}
						
						$result = substr($result, 0, strlen($result)-2);
					}
					
					$result .= ' )';
					
				break;
				
				case 'update':
					$result .= $table_result.' set ';
					
					if(is_array($this->fields))
					{
						for($i = 0; $i < count($this->fields); $i++)
						{
							if(
								(strpos($this->values[$i], 'inline:') === false)
							)
							{
								//$result .= $this->fields[$i]." = '".addslashes($this->values[$i])."', ";
                                $result .= $this->fields[$i] . " = :" . $this->fields[$i] . ", ";
							}
							else
							{
								$this->values[$i] = preg_replace('/^inline:(.*)/', '\1', $this->values[$i]);
								$result .= $this->fields[$i]." = ".$this->values[$i].", ";
							}
						}
						
						$result = substr($result, 0, strlen($result)-2);
					}
				break;
			}
			
			if(!($this->where->isEmpty()))
			{
				if($this->policy)
					$this->where->policy = $this->policy;
				$result .= ' where ' . $this->where->get();
                $this->_params = array_merge($this->_params, $this->where->getParams());
			}
					
			if(!empty($this->groupBy))
				$result .= ' group by '.$this->groupBy;

            if(!empty($this->having))
				$result .= ' having '.$this->having;
				
			if(!empty($this->orderBy))
				$result .= ' order by '.$this->orderBy;
				
			if(!empty($this->limit))
				$result .= ' limit '.$this->limit;

            $first_union = true;

			foreach($this->union as $union)
			{
                if(($first_union) && empty($field_result) && empty($table_result))
                    $result = $union->get();
                else
                {
                    if($this->unionAllFlag)
                        $result .= ' UNION ALL '.($union->Get());
                    else
                        $result .= ' union '.($union->Get());
                }

                $first_union = false;
			}
			
			return $result;
		}

        public function getParams()
        {
            return $this->_params;
        }
		
		public function exec()
		{			
			Sql::exec($this);
		}
		
		public function last_insert_id()
		{
			return Sql::getLastInsertId();
		}

        public function __toString()
        {
            return $this->Get();
        }
	}