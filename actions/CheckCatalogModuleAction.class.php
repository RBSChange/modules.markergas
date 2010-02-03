<?php
class markergas_CheckCatalogModuleAction extends f_action_BaseAction
{
	/**
	 * @param Context $context
	 * @param Request $request
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
		return View::NONE ;
	}
}