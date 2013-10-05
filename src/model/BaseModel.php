<?php
class BaseModel
{
	protected $db;
	protected $app;

	function __construct( $db, $app )
	{
		$this->db = $db;
		$this->app = $app;
	}
 
}