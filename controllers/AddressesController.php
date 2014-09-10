<?php

class AddressesController
{
	/**
	 *  Result data for render
	 */
	protected $_result = array(
		'error'=>false,
		'errorMessage'=>null,
		'validationErrors'=>array(),
		'data'=>array(),
	);

	/**
	 * Controller contructor.
	 */
	public function __construct()
	{
		// Set content-type
		header('Content-type: application/json');
	}

	/**
	 * Method used for control get (read) operation.
	 */
	public function getAction($id=null)
	{

		$model = new Address;

		if ($id !== null) {
			$model = $this->loadModel($id);

			$this->_result['data'] = $model;
		} else {

			$this->_result['data'] =  $model->findAll();
		}

		$this->renderResult();
	}

    /**
     * Method used for control put (update) operation.
     */
	public function putAction($id=null)
	{
        $model = new Address();
        if(isset($id))
        {
            // Loading data from a table to prevent data cleaning by new values
            $addr_ar = $this->loadModel($id);
            $model->ADDRESSID = $id;
            $model->LABEL = $addr_ar['LABEL'];
            $model->STREET = $addr_ar['STREET'];
            $model->HOUSENUMBER = $addr_ar['HOUSENUMBER'];
            $model->POSTALCODE = $addr_ar['POSTALCODE'];
            $model->CITY = $addr_ar['CITY'];
            $model->COUNTRY = $addr_ar['COUNTRY'];
        }

        $this->saveModel($model);

        $this->renderResult();
	}

	/**
	 * Method used for control delete operation.
	 */
	public function deleteAction($id)
	{
		$model = new Address();

		if ($model->delete($id) === false) {
			$this->_result['error'] = true;
			$this->_result['errorMessage'] = 'Record not deleted.';
            header('HTTP/1.1 204 No Content');
		} else {
			$this->_result['data'] = 'DELETED.';
		}

		$this->renderResult();
	}

    /**
     * Method used for control post (insert) operation.
     */
	public function postAction()
	{
        $model = new Address;

        $this->saveModel($model);

        $this->renderResult();
	}

    /**
     * Method used for save model data.
     */
	protected function saveModel(Address $model)
	{
        $rqst= new Request();
        $data = $rqst->getData();
        $model->LABEL = $data['LABEL'];
        $model->STREET = $data['STREET'];
        $model->HOUSENUMBER = $data['HOUSENUMBER'];
        $model->POSTALCODE = $data['POSTALCODE'];
        $model->CITY = $data['CITY'];
        $model->COUNTRY = $data['COUNTRY'];

        $result = $model->save();
		if ($result === false) {
			$this->_result['error'] = true;
			$this->_result['errorMessage'] = 'Record not updated.';
			$this->_result['validationErrors'] = $model->getErrors();
            header('HTTP/1.1 400 Bad Request');
		} else {
			 $this->_result['data'] = $model;
            header('HTTP/1.1 201 Created');
            header('Location: /addresses/'.$model->{$model->pkColumnName()}.'/');
		}
	}

	/**
	 * Render result in JSON format.
	 */
	protected function renderResult()
	{
		echo json_encode($this->_result);
	}

	/**
	 * Special protected function for load Address model.
	 * If model not found - generate exception with message about this.
	 */
	protected function loadModel($id)
	{
		$model = new Address;
        $result=$model->findByPk($id);
		if ($result === false) {
            header('HTTP/1.1 404 Not Found');
			throw new Exception('Record not found.');
		}

		return $result;
	}
}