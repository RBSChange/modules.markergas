<?php
/**
 * markergas_patch_0301
 * @package modules.markergas
 */
class markergas_patch_0301 extends patch_BasePatch
{
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		// Implement your patch here.
		echo $this->execChangeCommand("compile-blocks");
		echo $this->execChangeCommand("compile-locales", array("markergas"));
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
		return '0301';
	}
}