<?php
/**
 * markergas_PreferencesScriptDocumentElement
 * @package modules.markergas.persistentdocument.import
 */
class markergas_PreferencesScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return markergas_persistentdocument_preferences
     */
    protected function initPersistentDocument()
    {
    	return markergas_PreferencesService::getInstance()->getNewDocumentInstance();
    }
}