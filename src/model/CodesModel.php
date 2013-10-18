<?php

/**
 * Code model
 */


// $sql = "SELECT DISTINCT type FROM code ORDER BY type";

class CodesModel extends BaseModel
{

	public $lastSQL; 

	public function getSingleCode($code)
	{
		$sql = "SELECT * FROM `code` WHERE code = ?";	
		return $this->db->fetchAssoc($sql, array((string) $code));	
	}

	public function getParentType($code)
	{
		$sql = "SELECT parent_type FROM `code` WHERE code = ?";	
		$return = $this->db->fetchAssoc($sql, array((string) $code));	
		return $return['parent_type'];
	}

	public function getForceParent($code)
	{
		$sql = "SELECT force_parent FROM `code` WHERE code = ?";	
		$return = $this->db->fetchAssoc($sql, array((string) $code));	
		return $return['force_parent'];
	}

	public function fetchCodes($type)
	{
		$sql = "SELECT * FROM code WHERE type = ?";

		return $this->db->fetchAll($sql, array((string) $type));	
	}

	/**
	 * Returns just an array of TYPE codes (useful for dropdowns)
	 * @return [array] Array of codesd
	 */
	public function fetchTypeCodes($type = "TYPE")
	{
		$sql = "SELECT code, description FROM code WHERE type = ?";

		$codes = $this->db->fetchAll($sql, array((string) $type));	

		foreach ( $codes as $code )
		{
			$return[$code['code']] = $code['description'];
		}

		$return['NONE'] = "No parent";
		return $return;
	}

	public function fetchTypes()
	{
		$sql = "SELECT * FROM code WHERE type = 'TYPE'";

		return $this->db->fetchAll($sql, array((string) $type));	
	}


	public function fetchCodesByTypeParent($type, $parent)
	{
		$sql = "SELECT * FROM code WHERE parent_type = ? and parent = ?";

		return $this->db->fetchAll($sql, array((string) $type, (string) $parent));	
	}
}