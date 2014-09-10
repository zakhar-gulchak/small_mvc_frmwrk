<?php


class Address extends Model
{
	public $ADDRESSID;
        public $LABEL;
        public $STREET;
        public $HOUSENUMBER;
        public $POSTALCODE;
        public $CITY;
        public $COUNTRY;

    /**
     * Information about table name for Model class.
     */
    public function tableName() { return 'ADDRESS'; }

    /**
     * Information about PK column name for Model class.
     */
    public function pkColumnName() { return 'ADDRESSID'; }

	/**
	 * Rules for model attributes validation
	 */
	public function validatorRules()
	{
		return array(
			'LABEL'=>array(array('length', 'max'=>100),array('required')),
			'STREET'=>array(array('length', 'max'=>100)),
			'HOUSENUMBER'=>array(array('length', 'max'=>10)),
			'POSTALCODE'=>array(array('length', 'max'=>6)),
			'CITY'=>array(array('length', 'max'=>100)),
			'COUNTRY'=>array(array('length', 'max'=>100)),
		);
	}
}
