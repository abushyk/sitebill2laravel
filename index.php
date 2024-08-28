<?php
error_reporting(E_ERROR | E_WARNING);
error_reporting(E_ALL);
ini_set('display_errors','On');

if(!file_exists('../inc/db.inc.php') || !file_exists('../settings.ini.php')){
    exit();
}

define('SITEBILL_DOCUMENT_ROOT', rtrim($_SERVER['DOCUMENT_ROOT'], '/'));
require_once(SITEBILL_DOCUMENT_ROOT . '../inc/db.inc.php');



require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/debugger.class.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/logger.class.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/dbc.php';
require_once(SITEBILL_DOCUMENT_ROOT . '/third/smarty/Smarty.class.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill.php');
$smarty = new Smarty;
$sitebill = new SiteBill();

$smarty->template_dir = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $sitebill->getConfigValue('theme');
$smarty->cache_dir = SITEBILL_DOCUMENT_ROOT . '/cache/smarty';
$smarty->compile_dir = SITEBILL_DOCUMENT_ROOT . '/cache/compile';

require_once(__DIR__ . '/lib/FolderStructureCreator.php');
require_once(__DIR__ . '/lib/ModelsHelper.php');

$targetFolder = __DIR__.'/result/';
$FolderStructureCreator = new FolderStructureCreator();
$FolderStructureCreator->createFolderStructure($FolderStructureCreator->getFolderStructure(), $targetFolder);

$ModelsHelper = new ModelsHelper();

$models = $ModelsHelper->getModels();
$models = $ModelsHelper->formatModels($models);
dump($models);

$ModelsHelper->createMigrations($models, $targetFolder);




echo 1;

exit;