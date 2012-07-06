<?php
/**
 * @package modules.markergas
 * @method markergas_ListAvailablesitesService getInstance()
 */
class markergas_ListAvailablesitesService implements list_ListItemsService
{
	/**
	 * Returns an array of list_Item representing the available "websites" withe their markergas id.
	 * @return list_Item[]
	 */
	public function getItems()
	{
		$mgs = markergas_MarkergasService::getInstance();
		$dataArray = $mgs->getAvailableWebsitesForEachMarker();	
		
		$itemArray = array();
		foreach ($dataArray as $markerId => $websitesAndLangs)
		{
			$itemArray[] = new list_Item($mgs->getLabelFromWebsitesAndLangs($websitesAndLangs), $markerId);
		}
		return $itemArray;
	}
}