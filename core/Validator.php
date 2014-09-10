<?php

class Validator
{
	/**
	 * Model instance
	 */
	protected $_model;

	/**
	 * Constructor. Receive model instance for validation.
	 */
	public function __construct(Model $model)
	{
		$this->_model = $model;
	}

	/**
	 * Validate all model attributes using model rules.
	 */
	public function validate()
	{
		$valid = true;
		$rules = $this->_model->validatorRules();

		if (empty($rules) === true) {
			return $valid;
		}

        foreach($rules as $lbl=>$rulez){
            foreach ($rulez as $rule) {
                $validatorName = 'validator' . ucfirst($rule[0]);
                if (method_exists($this, $validatorName) === true) {
                    if (call_user_func_array(array($this, $validatorName), array($lbl, $rule)) === false) {
                        $valid = false;
                    }
                } else {
                    throw new Exception("Validator '{$validatorName}' not found.");
                }
            }
        }

		return $valid;
	}

	/**
	 * Length validator. Receive attribute name and params for validation.
	 * Params can contain min and max properties that tell validator what string slength should be.
	 */
	public function validatorLength($attr, array $params)
	{
		$value = $this->_model->{$attr};

		if (isset($params['min']) === true && strlen($value) < $params['min']) {
			$this->_model->setError($attr, "Length not valid. Minimum {$params['min']}.");
			return false;
		}

		if (isset($params['max']) === true && strlen($value) > $params['max']) {
			$this->_model->setError($attr, "Length not valid. Maximum {$params['max']}.");
			return false;
		}

		return true;
	}

	/**
	 * Number validator.
	 * If attribute value is number - return true.
	 */
	public function validatorNumber($attr)
	{
		if (is_numeric($this->_model->{$attr}) === false) {
			$this->_model->setError($attr, 'Should be an number.');
			return false;
		}

		return true;
	}

	/**
	 * Required validator.
	 * If attribute value not empty - return true.
	 */
	public function validatorRequired($attr)
	{
		if (empty($this->_model->{$attr}) === true) {
			$this->_model->setError($attr, 'Required. Should contain some data.');
			return false;
		}

		return true;
	}
}
