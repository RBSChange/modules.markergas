<?php
class markergas_persistentdocument_markergas extends markergas_persistentdocument_markergasbase
{
	/**
	 * @return String
	 */
	public function getProductname()
	{
		$value = parent::getProductname();
		if ($value)
		{
			return unserialize($value);
		}
		return null;
	}
	
	/**
	 * @param String $value
	 */
	public function setProductname($value)
	{
		Framework::debug(__METHOD__ . ' ' . var_export($value, true));
		if (is_array($value) && count($value) > 0)
		{
			Framework::debug(__METHOD__ . ' OK: '.serialize($value));
			parent::setProductname(serialize($value));
		}
		else
		{
			Framework::debug(__METHOD__ . ' KO');
			parent::setProductname(null);
		}
	}

	/**
	 * @return Array<String, mixed>
	 */
	public function getSpecificProperties()
	{
		return array(
			'login' => $this->getLogin(),
			'password' => $this->getPassword(),
			'gaSiteId' => $this->getGaSiteId(),
			'useEcommerce' => $this->getUseEcommerce() ? 'true' : 'false',
			'billingmodes' => implode(',', DocumentHelper::getIdArrayFromDocumentArray($this->getBillingmodesArray())),
			'productname' => $this->getProductname(),
			'category' => $this->getCategory()
		);
	}
	
	/**
	 * param Array<String, mixed> $value
	 */
	public function setSpecificProperties($value)
	{
		$this->setLogin(isset($value['login']) ? $value['login'] : null);
		$this->setPassword(isset($value['password']) ? $value['password'] : null);
		$this->setGaSiteId(isset($value['gaSiteId']) ? $value['gaSiteId'] : null);
		$this->setUseEcommerce(isset($value['useEcommerce']) && $value['useEcommerce'] == 'true');
		$this->removeAllBillingmodes();
		if (isset($value['billingmodes']))
		{
			foreach (explode(',', $value['billingmodes']) as $id)
			{
				$this->addBillingmodes(DocumentHelper::getDocumentInstance($id));
			}
		}
		$this->setProductname(isset($value['productname']) ? $value['productname'] : null);
		$this->setCategory(isset($value['category']) ? $value['category'] : null);
	}
}