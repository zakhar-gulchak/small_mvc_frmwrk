<?php

	class Where
	{
		public $policy = 'and';
		protected $_conditions;
        protected $_params = array();

		public function __construct($conditions = null, $policy = null)
		{
			if(is_string($conditions))
				$this->_conditions[] = $conditions;
			else if(is_array($conditions))
				$this->_conditions = $conditions;
				
			if(!empty($policy))
			{
				$this->policy = $policy;
			}
		}
		
		public function add($condition, $params = null)
		{
			if(is_object($condition))
            {
                /** @var $condition Where */
				if($condition->isEmpty())
					return;

                $params = $condition->getParams();
            }

            if($params)
            {

                if(is_string($condition))
                {
                    $new = array();
                    foreach($params as $k => $v)
                    {
                        $key = rand(100000, 999999);
                        $new[':value' . $key] = $v;
                        $condition = str_replace($k, ':value' . $key, $condition);
                    }
                    $params = $new;
                }

                $this->_params = array_merge($this->_params, $params);
            }

            $this->_conditions[] = $condition;
        }
		
		public function get()
		{
			$this->policy = $this->policy?strtolower($this->policy):'and';

			$result = '';
			
				foreach($this->_conditions as $condition)
				{
					$result .= ' ( ';
					if(is_object($condition))
					{
                        /** @var $condition Where */
						$result .= $condition->get();
					}
					else
					{
						$result .= $condition;
					}
					$result .= ' ) ';
					$result .= $this->policy;
				}
				$result = substr($result, 0, strlen($result) - strlen($this->policy) - 1);
			
			return $result;
		}

        public function getParams()
        {
            return $this->_params;
        }
		
		public function isEmpty()
		{
			return !(count($this->_conditions) > 0);
		}
	}
	