<?php
class markergas_MarkergasService extends website_MarkerService
{
	/**
	 * @var markergas_MarkergasService
	 */
	private static $instance;

	/**
	 * @return markergas_MarkergasService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}

	/**
	 * @return markergas_persistentdocument_markergas
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_markergas/markergas');
	}

	/**
	 * Create a query based on 'modules_markergas/markergas' model
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_markergas/markergas');
	}

	/**
	 * @return Array
	 */
	public function getAvailableWebsitesForEachMarker()
	{
		$permissionService = f_permission_PermissionService::getInstance();
		$currentUser = users_BackenduserService::getInstance()->getCurrentBackEndUser();
		
		$dataArray = array();
		foreach ($this->createQuery()->find() as $marker)
	    {
	    	$website = $marker->getWebsite();
	    	if ($marker instanceof markergas_persistentdocument_markergas && $permissionService->hasPermission($currentUser, 'modules_website.ViewGAStatistics.website', $website->getId()))
	    	{
				foreach ($marker->getLangsArray() as $lang)
				{
	    			$dataArray[$marker->getId()][$website->getLabel()][] = $lang;
				}
	    	}
	    }
		return $dataArray;
	}

	/**
	 * @param markergas_persistentdocument_markergas $marker
	 * @return website_persistentdocument_website[]
	 */
	public function getRelatedWebsitesAndLangsByMarkergas($marker)
	{
		$websitesAndLangs = array();
		$website = $marker->getWebsite();
		foreach ($marker->getLangsArray() as $lang)
		{
			$websitesAndLangs[$website->getLabel()][] = $lang;
		}
		return $websitesAndLangs;
	}

	/**
	 * @param Array<String, String[]> $websitesAndLangs
	 * @return unknown
	 */
	public function getLabelFromWebsitesAndLangs($websitesAndLangs)
	{
		$websitesLabels = array();
    	foreach ($websitesAndLangs as $label => $langs)
    	{
    		$websitesLabels[] = $label . ' (' . implode(', ', $langs) . ')';
    	}
    	return implode(', ', $websitesLabels);
	}

	/**
	 * @param order_persistentdocument_order $order
	 * @param markergas_persistentdocument_markergas $marker
	 * @return String
	 */
	public function getEcommercePlainMarker($order, $marker)
	{
		$template = TemplateLoader::getInstance()
			->setMimeContentType(K::HTML)
			->setPackageName('modules_markergas')
			->load('Markergas-ecommercetracker-Inc');
		$template->setAttribute('order', $order);
		$template->setAttribute('products', $this->getProducts($order, $marker));
		$html = $template->execute();
		return $html;
	}

	/**
	 * @param order_persistentdocument_order $order
	 * @param markergas_persistentdocument_markergas $marker
	 * @return Array
	 */
	private function getProducts($order, $marker)
	{
		$products = array();
		foreach ($order->getLineArray() as $line)
		{
			$product = array();
			$product['ref'] = $line->getCodeReference();
			$product['price'] = $line->getAmountWithTax();
			$product['quantity'] = $line->getQuantity();
			$product['productName'] = $this->getProductName($line, $marker);
			$product['category'] = $this->getCategory($line, $order, $marker);
			$products[] = $product;
		}
		return $products;
	}

	/**
	 * @param order_persistentdocument_orderline $line
	 * @param markergas_persistentdocument_markergas $marker
	 */
	private function getProductName($line, $marker)
	{
		$product = DocumentHelper::getDocumentInstance($line->getProductId());
		
		list(, $model) = explode('_', $product->getDocumentModelName());
		$productNameMask = $marker->getProductname();
		$productNameMask = $productNameMask[$model];
		
		$productFields = array();
		preg_match_all('/\{([a-zA-Z0-9]+(\/[a-zA-Z0-9]+)*)\}/', $productNameMask, $productFields);

		$replacements = array();
		foreach ($productFields[1] as $f)
		{
			$path = explode('/', $f);
			$value = $product;
			foreach ($path as $property)
			{
				$getter = 'get'.ucfirst($property);
				if (f_util_ClassUtils::methodExists($value, $getter))
				{
					$value = f_util_ClassUtils::callMethodOn($value, $getter);
				}
				else
				{
					$value = '';
					break;
				}
			}
			$replacements['{'.$f.'}'] = $value;
		}
		
		$productNameMask = str_replace(array_keys($replacements), array_values($replacements), $productNameMask);

		return $productNameMask;
	}

	/**
	 * @param order_persistentdocument_orderline $line
	 * @param order_persistentdocument_order $order
	 * @param markergas_persistentdocument_markergas $marker
	 */
	private function getCategory($line, $order, $marker)
	{
		$categoryMask = $marker->getCategory();
		$product = DocumentHelper::getDocumentInstance($line->getProductId());

		$askedShelves = array();
		preg_match_all(':{([0-9]+)}:', $categoryMask, $askedShelves);
		$website = $order->getWebsite();
		$shelf = $product->getPrimaryShelf($website);
		$shelfLabels = catalog_ShelfService::getInstance()->createQuery()->add(Restrictions::ancestorOf($shelf->getId()))->setProjection(Projections::property('label'))->findColumn('label');
		array_unshift($shelfLabels, $shelf->getLabel());
		
		$replace = array();
		foreach ($askedShelves[1] as $level)
		{
			$case = count($shelfLabels)-$level;
			if (isset($shelfLabels[$case]))
			{
				$replace['{'.$level.'}'] = $shelfLabels[$case];
			}
		}

		$categoryMask = str_replace(array_keys($replace), array_values($replace), $categoryMask);
		$categoryMask = preg_replace(':{[0-9]+}:', '', $categoryMask);

		return $categoryMask;
	}
}