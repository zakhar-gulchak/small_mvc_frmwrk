<?php

	class Sql
	{
        /**
         * @var PDO
         */
        private static $_pdo = false;
				
		private static function _connect()
		{
            try
            {
                Sql::$_pdo = new PDO('mysql:dbname=' . SQL_DB . ';host=' . SQL_HOST, SQL_USER, SQL_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"));
            }
            catch(PDOException $e)
            {
                echo $e->getMessage();
            }
		}
	
		public static function exec($cmd, $params = null)
		{
            if(!Sql::$_pdo)
				Sql::_connect();

			if(is_object($cmd))
            {
                /** @var $cmd Query */
                $params = $cmd->getParams();
                $cmd = $cmd->get();
            }

            $statement = Sql::$_pdo->prepare($cmd);

            return $statement->execute($params);
		}
	
		public static function getLine($cmd, $params = null)
		{
            if(!Sql::$_pdo)
                Sql::_connect();

            if(is_object($cmd))
            {
                /** @var $cmd Query */
                $obj = $cmd;
                $cmd = $cmd->get();
                $params = $obj->getParams();
            }

            $statement = Sql::$_pdo->prepare($cmd);
            /**
             * @var $statement PDOStatement
             */
            $statement->execute($params);

            return $statement->fetch(PDO::FETCH_ASSOC);
		}

        public static function getCursor($query, $calcRows = false)
        {
            if(!Sql::$_pdo)
                Sql::_connect();

            $params = null;

            if(is_object($query))
            {
                /** @var $query Query */
                if($calcRows)
                    $query->queryMod = 'SQL_CALC_FOUND_ROWS ' . $query->queryMod;

                $obj = $query;
                $query = $query->get();
                $params = $obj->getParams();
            }

            $statement = Sql::$_pdo->prepare($query);
            $statement->execute($params);
            return $statement;
        }
		
		public static function getLastInsertId()
		{
			return Sql::$_pdo->lastInsertId();
		}
		
		public static function getFoundRowsTotal()
		{
			$data = Sql::getLine('select FOUND_ROWS() as rows');
            return $data['rows'];
		}
	}