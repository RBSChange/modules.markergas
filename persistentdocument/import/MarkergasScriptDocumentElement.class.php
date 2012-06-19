<?php
class markergas_MarkergasScriptDocumentElement extends import_ScriptDocumentElement
{
	/**
	 * @return markergas_persistentdocument_markergas
	 */
	protected function initPersistentDocument()
	{
		return markergas_MarkergasService::getInstance()->getNewDocumentInstance();
	}
}