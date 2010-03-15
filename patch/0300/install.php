<?php
/**
 * markergas_patch_0300
 * @package modules.markergas
 */
class markergas_patch_0300 extends patch_BasePatch
{
    /**
     * Returns true if the patch modify code that is versionned.
     * If your patch modify code that is versionned AND database structure or content,
     * you must split it into two different patches.
     * @return Boolean true if the patch modify code that is versionned.
     */
	public function isCodePatch()
	{
		return true;
	}
 
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		f_permission_PermissionService::getInstance()->addImportInRight('website', 'markergas', 'websiterights');
		exec("change.php compile-roles");
	}

	/**
	 * @return String
	 */
	protected final function getModuleName()
	{
		return 'markergas';
	}

	/**
	 * @return String
	 */
	protected final function getNumber()
	{
		return '0300';
	}
}