<?php
class markergas_Setup extends object_InitDataSetup
{
	public function install()
	{
		try
		{
			$scriptReader = import_ScriptReader::getInstance();
			$scriptReader->executeModuleScript('markergas', 'init.xml');
		}
		catch (Exception $e)
		{
			echo "ERROR: " . $e->getMessage() . "\n";
			Framework::exception($e);
		}
		
		$mbs = uixul_ModuleBindingService::getInstance();
		$mbs->addImportInPerspective('website', 'markergas', 'website.perspective');
		$mbs->addImportInActions('website', 'markergas', 'website.actions');
		$result = $mbs->addImportform('website', 'modules_markergas/markergas');
		if ($result['action'] == 'create')
		{
			uixul_DocumentEditorService::getInstance()->compileEditorsConfig();
		}		
		change_PermissionService::getInstance()->addImportInRight('website', 'markergas', 'websiterights');
	}

	/**
	 * @return array<string>
	 */
	public function getRequiredPackages()
	{
		return array('modules_website');
	}
}