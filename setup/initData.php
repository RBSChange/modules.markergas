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
		
		f_permission_PermissionService::getInstance()->addImportInRight('website', 'markergas', 'websiterights');
	}

	/**
	 * @return array<string>
	 */
	public function getRequiredPackages()
	{
		return array('modules_website');
	}
}