<?php
namespace Next;

use Omeka\Stdlib\Message;
use Omeka\Mvc\Controller\Plugin\Messenger;

/**
 * @var Module $this
 * @var \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
 * @var string $newVersion
 * @var string $oldVersion
 *
 * @var \Doctrine\DBAL\Connection $connection
 * @var \Doctrine\ORM\EntityManager $entityManager
 * @var \Omeka\Api\Manager $api
 */
$services = $serviceLocator;
$settings = $services->get('Omeka\Settings');
$config = require dirname(dirname(__DIR__)) . '/config/module.config.php';
$connection = $services->get('Omeka\Connection');
$entityManager = $services->get('Omeka\EntityManager');
$plugins = $services->get('ControllerPluginManager');
$api = $plugins->get('api');
$space = strtolower(__NAMESPACE__);

if (version_compare($oldVersion, '3.1.2.9', '<')) {
    $message = new Message(
        'Some features were moved and improved in a new module %sBulkEdit%s: trim metadata and deduplicate metadata. They are still available in module Next.', // @translate
        '<a href="https://github.com/Daniel-KM/Omeka-S-module-BulkEdit">',
        '</a>'
    );

    $message->setEscapeHtml(false);
    $messenger = new Messenger();
    $messenger->addSuccess($message);
}

if (version_compare($oldVersion, '3.1.2.12', '<')) {
    $message = new Message(
        'Some features were moved and improved in a new module %sBlockPlus%s: SearchForm block and SimplePage block. They are still available in module Next.', // @translate
        '<a href="https://github.com/Daniel-KM/Omeka-S-module-BlockPlus">',
        '</a>'
    );

    $message->setEscapeHtml(false);
    $messenger = new Messenger();
    $messenger->addSuccess($message);
}

if (version_compare($oldVersion, '3.1.2.13', '<')) {
    $sql = <<<SQL
UPDATE site_setting SET id = "next_search_used_terms" WHERE `id` = "search_used_terms";
SQL;
    $connection->exec($sql);

    $settings->set('next_breadcrumbs_property_itemset',
        $config['next']['settings']['next_breadcrumbs_property_itemset']);

    $siteSettings = $services->get('Omeka\Settings\Site');
    /** @var \Omeka\Api\Representation\SiteRepresentation[] $sites */
    $sites = $api->search('sites')->getContent();
    foreach ($sites as $site) {
        $siteSettings->setTargetId($site->id());
        $siteSettings->set('next_breadcrumbs_crumbs',
            $config['next']['site_settings']['next_breadcrumbs_crumbs']);
    }
}

if (version_compare($oldVersion, '3.1.2.14', '<')) {
    $settings->set(
        'next_property_itemset',
        $settings->get('next_breadcrumbs_property_itemset', $config['next']['settings']['next_property_itemset'])
    );
    $settings->delete('next_breadcrumbs_property_itemset');
}
