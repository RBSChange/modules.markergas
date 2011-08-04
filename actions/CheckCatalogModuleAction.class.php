<?php
class markergas_CheckCatalogModuleAction extends change_Action
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		if(ModuleService::getInstance()->isInstalled('modules_catalog'))
		{
			echo 'true';
		}
		else
		{
			echo 'false';
		}
		return change_View::NONE ;
	}
}