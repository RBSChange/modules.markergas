<?php
/**
 * @package modules.markergas
 */
class markergas_lib_cMarkergasEditorTagReplacer extends f_util_TagReplacer
{
	protected function preRun()
	{
		$models = array();
		$fields = array();
		$fieldModels = array();
		$suggestions = array();
		if (ModuleService::getInstance()->isInstalled('payment'))
		{
			// Connector models.
			$model = f_persistentdocument_PersistentDocumentModel::getInstance('payment', 'connector');
			$models = $model->getChildrenNames();
			
			// Suggestions.
			if (ModuleService::getInstance()->isInstalled('catalog'))
			{
				// Productname fields.
				foreach (catalog_ModuleService::getInstance()->getProductModelsThatMayAppearInCarts() as $model)
				{
					list(, $module, $document) = explode('_', str_replace('/', '_', $model));
					$productLocale = '&modules.'.$module.'.document.'.$document.'.';
					
					$fieldModels[] = '{module: "'.$module.'", document: "'.$document.'"}';
					$field = '<xul:row anonid="mProductnameRow_'.$module.'_'.$document.'"><xul:clabel control="markergas_markergas_productname_'.$module.'_'.$document.'" value="&amp;modules.markergas.document.markergas.Productname; ('.str_replace('&', '&amp;', $productLocale).'Document-name;)" /><xul:cfield anonid="mProductname_'.$module.'_'.$document.'" id="markergas_markergas_productname_'.$module.'_'.$document.'" name="mProductname_'.$module.'_'.$document.'" fieldtype="longtext" hidehelp="true" class="template">';
					
					$service = f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName($model)->getDocumentService();
					foreach ($service->getPropertyNamesForMarkergas() as $model => $properties)
					{
						list($module1, $document1) = explode('/', $model);
						$productLocale = '&modules.'.$module1.'.document.'.$document1.'.';
						$field .= '<xul:menuitem label="--- '.$productLocale.'Document-name;" value="" />';
						foreach ($properties as $name => $path)
						{
							$field .= '<xul:menuitem label="'.$productLocale.ucfirst($name).';" cvar="{'.$path.'}" tooltiptext="{'.$path.'}" />';
						}
					}
					
					$field .= '</xul:cfield></xul:row>';
					$fields[] = $field;
				}
				
				// Category field.
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
				$suggestions = array();
				for ($i = 1; $i <= $max; $i++)
				{
					$suggestions[] = '<xul:menuitem label="&modules.markergas.bo.general.Shelf-level; -'.$i.'" cvar="{'.$i.'}" tooltiptext="{'.$i.'}" />';
				}
			}
		}
		
		$this->setReplacement('PAYMENT_MODELS', str_replace('/', '_', implode(',', $models)));
		$this->setReplacement('PRODUCTNAME_FIELDS', implode('', $fields));
		$this->setReplacement('PRODUCTNAME_FIELDS_MODELS', implode(',', $fieldModels));
		$this->setReplacement('CATEGORY_REPLACEMENT_SUGGESTIONS', implode('', $suggestions));
	}
}