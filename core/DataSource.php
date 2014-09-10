<?php

	class DataSource
	{
        private $_calcRows;
        /**
         * @var PDOStatement
         */
        private $_result;
		
		public $rowCount = 0;
        public $totalRows = 0;
        public $resultType = MYSQL_ASSOC;
		
		public function __construct($data, $calcRows = false)
		{
            $this->_calcRows = $calcRows;
            $this->_query($data);
		}
		
		protected function _query($string)
		{
            $this->_result = Sql::getCursor($string, $this->_calcRows);
            if($this->_result)
            {
                $this->rowCount = count($this->_result);
                if($this->_calcRows)
                    $this->totalRows = Sql::getFoundRowsTotal();
            }
            else
            {
                $this->rowCount = 0;
            }
		}

		function getLine()
		{
            if(!$this->_result)
                return false;

            return $this->_result->fetch(PDO::FETCH_ASSOC);
		}

        public function serialize()
        {
            $results = array();

			while(($data = $this->getLine()) !== false)
				$results[] = $data;

            return json_encode($results);
        }
	}
