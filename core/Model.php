<?php

abstract class Model
{
	/*
	 model validation errors
	 */
	protected $_errors=array();

	/*
	 table name for this model
	 */
	abstract function tableName();

	/**
	 PK column name for this model
	 */
	abstract function pkColumnName();

	/**
	 Rules for model attributes validation
	 */
	abstract function validatorRules();



	/**
	 * get array with DB record data that has PK = $pk.
	 */
	public function findByPk($pk)
	{	
		$sql = 'SELECT * FROM ' . $this->tableName() . ' WHERE ' . $this->pkColumnName() . '='.$pk.' LIMIT 1';
		$sth = new DataSource($sql);

        $result = $sth->getLine();

        return $result;
	}

	/**
	 * Find all DB records and generate array with arrays that filled by records data.
	 */
	public function findAll($where='1=1', $limit=null)
	{
		$sql = 'SELECT * FROM ' . $this->tableName() . ' WHERE ' . $where . ' ' . ($limit !== null ? ' LIMIT ' . $limit: '');
        $sth = new DataSource($sql);

		$models = array();

			while (($model = $sth->getLine()) !== false) {
				$models[] = $model;
			}

		return $models;
	}

    /**
     * Save model data.
     * If model is new PK is null - we generate INSERT SQL request.
     * If model data already exists in DB, PK > 0 - we generate UPDATE request.
     * Method return true if model data saved successfully. False if error.
     */
    public function save($validate=true)
    {
        if ($validate === true && $this->validate() === false) {
            return false;
        }

        $columns = $this->getClearColumns();

        $values = array();

        foreach ($columns as $column) {
            $values[] = $this->{$column};
        }

        $keyPosition = array_search($this->pkColumnName(), $columns);
        array_splice($columns, $keyPosition, 1);
        array_splice($values, $keyPosition, 1);

        if ((int)$this->{$this->pkColumnName()}>0) {
            $query = new Query("update");
            $query->addTable($this->tableName());
            foreach($columns as $key=>$column)
            {
                $query->addField($column, $values[$key]);
            }
            $query->where->add($this->pkColumnName()." = ".$this->{$this->pkColumnName()});
            $result = $query->exec();
        } else {
            $query = new Query("insert");
            $query->addTable($this->tableName());
            foreach($columns as $key=>$column)
            {
                $query->addField($column, $values[$key]);
            }
            $result = $query->exec();
            $this->ADDRESSID = $query->last_insert_id();
        }

        return $result;
    }


	/**
	 * Delete current model data from DB.
	 */
	public function delete($id)
	{
		$sql = 'DELETE FROM ' . $this->tableName() . ' WHERE ' . $this->pkColumnName() . '='.(int)$id.' LIMIT 1';
        $sth = new Sql();
		
		if ($sth->exec($sql)===true) {
			return true;
		}

		return false;
	}

	/**
	 * Model validation.
	 */
	public function validate()
	{
		$validator = new Validator($this);
		return $validator->validate();
	}

	/**
	 * Set model error.
	 */
	public function setError($attr, $error)
	{
		$this->_errors[$attr] = $error;
	}

	/**
	 * Get model errors.
	 */
	public function getErrors()
	{
		return $this->_errors;
	}

    /**
     * Special private method for get "clear" attributes of this model.
     * Clear attributes is all public attributes of this class.
     */
    private function getClearColumns()
    {
        $dirtyColumns = array_keys(get_class_vars(get_class($this)));
        $columns = array_filter($dirtyColumns, array($this, 'dirtyColumnsFilter'));

        return $columns;
    }

    /**
     * Special private method that used in array_filter function.
     * This method get $column and check first char.
     * If char is "_" this column exclude from array.
     */
    private function dirtyColumnsFilter($column)
    {
        if ($column[0] === '_') {
            return false;
        }

        return true;
    }

}
