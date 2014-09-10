<?php

class Request
{
	/*
	 * HTTP request name
	 */
	private $_requestMethod;

	/**
	 * current request URL elements
	 */
	private $_urlElements=array();

	/**
	 * current request data
	 */
	private $_inputData=array();

	public function __construct()
	{
		$this->_requestMethod = $_SERVER['REQUEST_METHOD'];

		if (isset($_SERVER['REQUEST_URI'])) {
	        	$this->_urlElements = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
		}
	}

	public function getUrlElements()
	{
		return $this->_urlElements;
	}

	public function getRequestMethod()
	{
		return $this->_requestMethod;
	}

	public function getData()
	{
		if (empty($this->_inputData) === true) {
			switch ($this->getRequestMethod()) {
				case 'DELETE':
				case 'PUT':
					parse_str(file_get_contents('php://input'), $data);
					$this->_inputData = $data;
					break;
				case 'POST':
                    parse_str(file_get_contents('php://input'), $data);
                    $this->_inputData = $data;
					//$this->_inputData = $_POST;
					break;
				default:
				case 'GET':
					$this->_inputData = $_GET;
					break;
			}
		}

		return $this->_inputData;
	}
}
