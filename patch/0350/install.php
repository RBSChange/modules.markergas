<?php
/**
 * markergas_patch_0350
 * @package modules.markergas
 */
class markergas_patch_0350 extends patch_BasePatch
{

	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		$this->execChangeCommand('compile-locales', array('markergas'));
		
		$mbs = uixul_ModuleBindingService::getInstance();
		$mbs->addImportInPerspective('website', 'markergas', 'website.perspective');
		$mbs->addImportInActions('website', 'markergas', 'website.actions');
		$result = $mbs->addImportform('website', 'modules_markergas/markergas');
		if ($result['action'] == 'create')
		{
			uixul_DocumentEditorService::getInstance()->compileEditorsConfig();
		}
		f_permission_PermissionService::getInstance()->addImportInRight('website', 'markergas', 'websiterights');
	}
}