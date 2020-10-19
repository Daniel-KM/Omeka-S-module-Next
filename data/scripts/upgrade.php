<?php declare(strict_types=1);
namespace Next;

use Omeka\Mvc\Controller\Plugin\Messenger;
use Omeka\Stdlib\Message;

/**
 * @var Module $this
 * @var \Laminas\ServiceManager\ServiceLocatorInterface $serviceLocator
 * @var string $newVersion
 * @var string $oldVersion
 *
 * @var \Doctrine\DBAL\Connection $connection
 * @var \Doctrine\ORM\EntityManager $entityManager
 * @var \Omeka\Api\Manager $api
 */
$services = $serviceLocator;
$settings = $services->get('Omeka\Settings');
$config = require dirname(__DIR__, 2) . '/config/module.config.php';
$connection = $services->get('Omeka\Connection');
$entityManager = $services->get('Omeka\EntityManager');
$plugins = $services->get('ControllerPluginManager');
$api = $plugins->get('api');
$space = strtolower(__NAMESPACE__);

if (version_compare($oldVersion, '3.1.2.9', '<')) {
    $message = new Message(
        'Some features were moved and improved in a new module %sBulkEdit%s: trim metadata and deduplicate metadata. They are still available in module Next.', // @translate
        '<a href="https://gitlab.com/Daniel-KM/Omeka-S-module-BulkEdit">',
        '</a>'
    );

    $message->setEscapeHtml(false);
    $messenger = new Messenger();
    $messenger->addSuccess($message);
}

if (version_compare($oldVersion, '3.1.2.12', '<')) {
    $message = new Message(
        'Some features were moved and improved in a new module %sBlockPlus%s: SearchForm block and SimplePage block. They are still available in module Next.', // @translate
        '<a href="https://gitlab.com/Daniel-KM/Omeka-S-module-BlockPlus">',
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

if (version_compare($oldVersion, '3.1.2.30', '<')) {
    $siteSettings = $services->get('Omeka\Settings\Site');
    /** @var \Omeka\Api\Representation\SiteRepresentation[] $sites */
    $sites = $api->search('sites')->getContent();
    foreach ($sites as $site) {
        $siteSettings->setTargetId($site->id());
        $string = $siteSettings->get('next_breadcrumbs_prepend');
        $siteSettings->set('next_breadcrumbs_prepend',
            $this->filterBreadcrumbsPrepend($string));
    }
}

if (version_compare($oldVersion, '3.1.2.31', '<')) {
    $siteSettings = $services->get('Omeka\Settings\Site');
    /** @var \Omeka\Api\Representation\SiteRepresentation[] $sites */
    $sites = $api->search('sites')->getContent();
    foreach ($sites as $site) {
        $siteSettings->setTargetId($site->id());
        $crumbs = $siteSettings->get('next_breadcrumbs_crumbs', []);
        $homepage = array_search('homepage', $crumbs);
        if ($homepage !== false) {
            unset($crumbs[$homepage]);
            $siteSettings->set('next_breadcrumbs_crumbs', $crumbs);
            $siteSettings->set('next_breadcrumbs_homepage', true);
        }
    }
}
