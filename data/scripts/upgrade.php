<?php declare(strict_types=1);

namespace Next;

use Omeka\Module\Exception\ModuleCannotInstallException;
use Omeka\Stdlib\Message;

/**
 * @var Module $this
 * @var \Laminas\ServiceManager\ServiceLocatorInterface $services
 * @var string $newVersion
 * @var string $oldVersion
 *
 * @var \Omeka\Api\Manager $api
 * @var \Omeka\Settings\Settings $settings
 * @var \Doctrine\DBAL\Connection $connection
 * @var \Doctrine\ORM\EntityManager $entityManager
 * @var \Omeka\Mvc\Controller\Plugin\Messenger $messenger
 */
$plugins = $services->get('ControllerPluginManager');
$api = $plugins->get('api');
$settings = $services->get('Omeka\Settings');
$connection = $services->get('Omeka\Connection');
$messenger = $plugins->get('messenger');
$entityManager = $services->get('Omeka\EntityManager');

$config = include dirname(__DIR__, 2) . '/config/module.config.php';

if (version_compare($oldVersion, '3.1.2.9', '<')) {
    $message = new Message(
        'Some features were moved and improved in a new module %sBulk Edit%s: trim metadata and deduplicate metadata. They are still available in module Next.', // @translate
        '<a href="https://gitlab.com/Daniel-KM/Omeka-S-module-BulkEdit">',
        '</a>'
    );

    $message->setEscapeHtml(false);
    $messenger->addSuccess($message);
}

if (version_compare($oldVersion, '3.1.2.12', '<')) {
    $message = new Message(
        'Some features were moved and improved in a new module %sBlock Plus%s: SearchForm block and SimplePage block. They are still available in module Next.', // @translate
        '<a href="https://gitlab.com/Daniel-KM/Omeka-S-module-BlockPlus">',
        '</a>'
    );

    $message->setEscapeHtml(false);
    $messenger->addSuccess($message);
}

if (version_compare($oldVersion, '3.1.2.13', '<')) {
    $sql = <<<SQL
UPDATE site_setting SET id = "next_search_used_terms" WHERE `id` = "search_used_terms";
SQL;
    $connection->executeStatement($sql);

    $settings->set('next_breadcrumbs_property_itemset',
        $config['next']['settings']['next_breadcrumbs_property_itemset'] ?? null);

    $siteSettings = $services->get('Omeka\Settings\Site');
    /** @var \Omeka\Api\Representation\SiteRepresentation[] $sites */
    $sites = $api->search('sites')->getContent();
    foreach ($sites as $site) {
        $siteSettings->setTargetId($site->id());
        $siteSettings->set('next_breadcrumbs_crumbs',
            $config['next']['site_settings']['next_breadcrumbs_crumbs'] ?? null);
    }
}

if (version_compare($oldVersion, '3.1.2.14', '<')) {
    $settings->set(
        'next_property_itemset',
        $settings->get('next_breadcrumbs_property_itemset', $config['next']['settings']['next_property_itemset'] ?? null)
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

if (version_compare($oldVersion, '3.3.2.32', '<')) {
    $message = new Message(
        'Some features were moved and improved in modules %1$sAdvanced Search%5$s, %2$sBlock Plus%5$s, %3$sBulk Edit%5$s, and %4$sEasy Admin%5$s, in particular Select in used properties and classes, SearchForm block, Simple Mirror Page block, automatic trimming and deduplication.', // @translate
        '<a href="https://gitlab.com/Daniel-KM/Omeka-S-module-AdvancedSearch" target="_blank">',
        '<a href="https://gitlab.com/Daniel-KM/Omeka-S-module-BlockPlus" target="_blank">',
        '<a href="https://gitlab.com/Daniel-KM/Omeka-S-module-BulkEdit" target="_blank">',
        '<a href="https://gitlab.com/Daniel-KM/Omeka-S-module-EasyAdmin" target="_blank">',
        '</a>'
    );

    $message->setEscapeHtml(false);
    $messenger->addWarning($message);

    // Option moved to module Block Plus.
    $sql = <<<'SQL'
UPDATE site_page_block
SET layout = "mirrorPage"
WHERE layout = "simplePage";
SQL;
    $connection->executeStatement($sql);

    $siteSettings = $services->get('Omeka\Settings\Site');
    /** @var \Omeka\Api\Representation\SiteRepresentation[] $sites */
    $siteIds = $api->search('sites', [], ['initialize' => false, 'returnScalar' => 'id'])->getContent();
    foreach ($siteIds as $siteId) {
        $siteSettings->setTargetId($siteId);
        // Option moved to module Advanced Search Plus.
        $siteSettings->set('advancedsearchplus_restrict_used_terms',
            $siteSettings->get('next_search_used_terms', true));
        $siteSettings->delete('next_search_used_terms');
    }
}

if (version_compare($oldVersion, '3.3.40', '<')) {
    $message = new Message(
        'The better identification of xml files was moved to new module %1$sXml Viewer%2$s.', // @translate
        '<a href="https://gitlab.com/Daniel-KM/Omeka-S-module-XmlViewer" target="_blank">',
        '</a>'
    );

    $message->setEscapeHtml(false);
    $messenger->addWarning($message);
}

if (version_compare($oldVersion, '3.3.41', '<')) {
    $message = new Message(
        'The option to select columns in admin/item/browse was disabled. Use https://github.com/omeka/omeka-s/pull/1497 if needed.' // @translate
    );

    $message->setEscapeHtml(false);
    $messenger->addWarning($message);
}

if (version_compare($oldVersion, '3.3.41', '<')) {
    $message = new Message(
        'The helper "Breadcrumbs" was moved to new module %1$sMenu%2$s. Upgrade is automatic.', // @translate
        '<a href="https://gitlab.com/Daniel-KM/Omeka-S-module-Menu" target="_blank">',
        '</a>'
    );
    $message->setEscapeHtml(false);
    $messenger->addWarning($message);
}

if (version_compare($oldVersion, '3.3.42', '<')) {
    $message = new Message(
        'The helper to manage cron tasks was moved and improved to new module %1$sEasy Admin%2$s. Upgrade is automatic.', // @translate
        '<a href="https://gitlab.com/Daniel-KM/Omeka-S-module-EasyAdmin" target="_blank">',
        '</a>'
    );
    $message->setEscapeHtml(false);
    $messenger->addWarning($message);
}

if (false && version_compare($oldVersion, '3.3.44', '<')) {
    $translator = $services->get('MvcTranslator');

    /** @var \Omeka\Module\Manager $moduleManager */
    $moduleManager = $services->get('Omeka\ModuleManager');
    $advancedSearch = $moduleManager->getModule('AdvancedSearch');
    if ($advancedSearch) {
        $advancedSearchVersion = $advancedSearch->getIni('version');
        if (version_compare($advancedSearchVersion, '3.3.6.16', '<')) {
            $message = new Message(
                $translator->translate('This module requires module "%1$s" version "%2$s" or greater.'), // @translate
                'Advanced Search', '3.3.6.16'
            );
            throw new ModuleCannotInstallException((string) $message);
        }
    }
}

if (version_compare($oldVersion, '3.3.45', '<')) {
    $settings->set('menu_property_itemset', $settings->get('next_property_itemset'));
    $settings->delete('next_property_itemset');

    $message = new Message(
        'The helper "PrimaryItemSet" was moved to module %1$sBlock Plus%2$s.', // @translate
        '<a href="https://gitlab.com/Daniel-KM/Omeka-S-module-Menu" target="_blank">',
        '</a>'
    );
    $message->setEscapeHtml(false);
    $messenger->addWarning($message);
}

if (version_compare($oldVersion, '3.4.45', '<')) {
    $message = new Message(
        'The helper "IsHomePage()" was moved to modules %1$sBlock Plus%2$s and %3$sMenu%2$s.', // @translate
        '<a href="https://gitlab.com/Daniel-KM/Omeka-S-module-BlockPlus" target="_blank">',
        '</a>',
        '<a href="https://gitlab.com/Daniel-KM/Omeka-S-module-Menu" target="_blank">'
    );
    $message->setEscapeHtml(false);
    $messenger->addWarning($message);
}

if (version_compare($oldVersion, '3.4.46', '<')) {
    $message = new Message(
        'The button "Public view" was moved to module %1$sEasy Admin%2$s.', // @translate
        '<a href="https://gitlab.com/Daniel-KM/Omeka-S-module-EasyAdmin" target="_blank">',
        '</a>'
    );
    $message->setEscapeHtml(false);
    $messenger->addWarning($message);
}

if (version_compare($oldVersion, '3.4.47', '<')) {
    $message = new Message(
        'The helpers "PreviousResource()" and "NextResource()" were moved to modules %1$sBlock Plus%2$s and %3$sEasy Admin%2$s.', // @translate
        '<a href="https://gitlab.com/Daniel-KM/Omeka-S-module-BlockPlus" target="_blank">',
        '</a>',
        '<a href="https://gitlab.com/Daniel-KM/Omeka-S-module-EasyAdmin" target="_blank">'
    );
    $message->setEscapeHtml(false);
    $messenger->addWarning($message);
}
