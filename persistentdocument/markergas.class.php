<?php
class markergas_persistentdocument_markergas extends markergas_persistentdocument_markergasbase
{
	/**
	 * @return string
	 */
	public function getProductnameAsJSON()
	{
		$value = $this->getProductname();
		if (empty($value))
		{
			return '';
		}
		return JsonService::getInstance()->encode(unserialize($value));
	}
	
	/**
	 * @return array
	 */
	public function getProductnameAsArray()
	{
		$value = $this->getProductname();
		if (empty($value))
		{
			return array();
		}
		return unserialize($value);
	}
	
	/**
	 * @param string $productnameAsJSON
	 */
	public function setProductnameAsJSON($productnameAsJSON)
	{
		if (empty($productnameAsJSON))
		{
			$this->setProductname(null);
		}
		else
		{
			$this->setProductname(serialize(JsonService::getInstance()->decode($productnameAsJSON)));
		}
	}	
}