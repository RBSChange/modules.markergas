<?php
/**
 * @package modules.markergas
 * @method markergas_MarkergasService getInstance()
 */
class markergas_MarkergasService extends website_MarkerService
{
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
		return $this->getPersistentProvider()->createQuery('modules_markergas/markergas');
	}
	
	/**
	 * @param markergas_persistentdocument_markergas $document
	 * @param string $actionType
	 * @param array $formProperties
	 */
	public function addFormProperties($document, $propertiesNames, &$formProperties)
	{
		parent::addFormProperties($document, $propertiesNames, $formProperties);
		if (ModuleService::getInstance()->isInstalled('catalog'))
		{
			$this->addEcomFormProperties($document, $propertiesNames, $formProperties);
		}
	}

	/**
	 * @param markergas_persistentdocument_markergas $document
	 * @param string $actionType
	 * @param array $formProperties
	 */
	protected function addEcomFormProperties($document, $propertiesNames, &$formProperties)
	{
		$fieldModels = array();
		// Productname fields.
		
		foreach (catalog_ModuleService::getInstance()->getProductModelsThatMayAppearInCarts() as $modelInstance)
		{
			if ($modelInstance instanceof f_persistentdocument_PersistentDocumentModel)
			{
				$module = $modelInstance->getModuleName();
				$document = $modelInstance->getDocumentName();
				$label = LocaleService::getInstance()->trans('m.'.$module.'.document.'.$document.'.document-name', array('ucf'));
				$modelInfo = array('module' => $module, 'document' => $document, 'label' => $label, 'infos' => array());
				$service = $modelInstance->getDocumentService();
				$prefix = '';
				foreach ($service->getPropertyNamesForMarkergas() as $mn => $mnInfo) 
				{
					list($module1, $document1) = explode('/', $mn);
					$l1 = '--- ' . LocaleService::getInstance()->trans('m.'.$module1.'.document.'.$document1.'.document-name', array('ucf'));
					$modelInfo['infos'][$l1] = '';
					foreach ($mnInfo as $l2 => $d2) 
					{
						$modelInfo['infos'][$prefix.$l2] = $d2;
					}
					$prefix .= ' ';
				}
				$fieldModels[$module . '/' . $document] = $modelInfo;
			}
		}
		$formProperties['ecomproduct'] = $fieldModels;
		
		
		$category = array();
		$prefixlabel = LocaleService::getInstance()->trans('m.markergas.bo.general.shelf-level', array('ucf'));
		
		$treeService = TreeService::getInstance();
		$shelfService = catalog_ShelfService::getInstance();
		$max = 1;
		foreach (catalog_TopshelfService::getInstance()->createQuery()->find() as $topShelf)
		{
			$descendants = $shelfService->createQuery()->add(Restrictions::descendentOf($topShelf->getId()))->find();
			foreach ($descendants as $descendant)
			{
				$node = $treeService->getInstanceByDocument($descendant);
				$max = max(($node->getLevel()), $max);
			}
		}				
		
		for ($i = 1; $i <= $max; $i++)
		{
			$category[$prefixlabel . ' -' . $i] = '{'.$i.'}';
		}		
		$formProperties['ecomcategory'] = $category;
	}

	/**
	 * @return Array
	 */
	public function getAvailableWebsitesForEachMarker()
	{
		$permissionService = change_PermissionService::getInstance();
		$currentUser = users_UserService::getInstance()->getCurrentBackEndUser();
		
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
	 * @param boolean $includeTaxes
	 * @return string
	 */	
	public function getEcommercePlainHeadMarker($order, $marker, $includeTaxes = true)
	{
		$template = TemplateLoader::getInstance()->setMimeContentType('html')
			->setPackageName('modules_markergas')
			->load('Markergas-ecommercetracker-IncHead');
		$template->setAttribute('order', $order);
		$template->setAttribute('includeTaxes', $includeTaxes);
		$template->setAttribute('products', $this->getProducts($order, $marker, $includeTaxes));
		$html = $template->execute();
		return $html;
	}
	
	/**
	 * @param order_persistentdocument_order $order
	 * @param markergas_persistentdocument_markergas $marker
	 * @return Array
	 */
	private function getProducts($order, $marker, $includeTaxes)
	{
		$products = array();
		foreach ($order->getLineArray() as $line)
		{
			$product = array();
			$product['ref'] = $line->getCodeReference();
			$product['price'] =  $includeTaxes ? $line->getUnitPriceWithTax() : $line->getUnitPriceWithoutTax();
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
		$productNameMaskArray = $marker->getProductnameAsArray();
		if (isset($productNameMaskArray[$model]))
		{
			$productNameMask = $productNameMaskArray[$model];
			$productFields = array();
			if (preg_match_all('/\{([a-zA-Z0-9]+(\/[a-zA-Z0-9]+)*)\}/', $productNameMask, $productFields))
			{
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
					$replacements['{'.$f.'}'] = f_util_HtmlUtils::textToHtml(strval($value));
				}
				$productNameMask = str_replace(array_keys($replacements), array_values($replacements), $productNameMask);	
			}
		}
		else
		{
			$productNameMask = '';
		}
		
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
		if ($categoryMask !== null)
		{
			$product = catalog_persistentdocument_product::getInstanceById($line->getProductId());
			$askedShelves = array();
			if (preg_match_all(':{([0-9]+)}:', $categoryMask, $askedShelves))
			{
				$website = $order->getWebsite();
				$shop = catalog_ShopService::getInstance()->getDefaultByWebsite($website);
				$shelf = $product->getShopPrimaryShelf($shop);
				$shelfLabels = catalog_ShelfService::getInstance()->createQuery()
					->add(Restrictions::ancestorOf($shelf->getId()))
					->setProjection(Projections::property('label'))
					->findColumn('label');
				array_unshift($shelfLabels, $shelf->getLabel());
				
				$replace = array();
				foreach ($askedShelves[1] as $level)
				{
					$case = count($shelfLabels)-$level;
					if (isset($shelfLabels[$case]))
					{
						$replace['{'.$level.'}'] =  f_util_HtmlUtils::textToHtml(strval($shelfLabels[$case]));
					}
				}
		
				$categoryMask = str_replace(array_keys($replace), array_values($replace), $categoryMask);
				$categoryMask = preg_replace(':{[0-9]+}:', '', $categoryMask);
			}
			return $categoryMask;
		}
		return '';
	}
}