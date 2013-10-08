<?php
class BaseModel
{
	protected $db;
	protected $app;

	function __construct( $db, Silex\Application $application )
	{
		$this->db = $db;
		$this->app = $application;
	}
 
}