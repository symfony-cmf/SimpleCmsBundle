<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

use PHPCR\Util\PathHelper;

class CmfSimpleCmsExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        // process the configuration of CmfCoreExtension
        $config = $this->processConfiguration(
            new Configuration(),
            $container->getParameterBag()->resolveValue($container->getExtensionConfig($this->getAlias()))
        );

        if (empty($config['persistence']['phpcr']['enabled'])
            && empty($config['persistence']['orm']['enabled'])
        ) {
            return;
        }

        $prependConfig = array(
            'chain' => array(
                'routers_by_id' => array(
                    'router.default' => 0,
                    'cmf_routing.dynamic_router' => -100,
                )
            ),
            'dynamic' => array(
                'enabled' => true,
            )
        );

        if (!empty($config['persistence']['phpcr']['enabled'])) {
            $prependConfig = $this->prependPhpcr($prependConfig, $config);
        }

        $container->prependExtensionConfig('cmf_routing', $prependConfig);
    }

    private function prependPhpcr(array $prependConfig, array $config)
    {
        if (isset($config['persistence']['phpcr']['basepath'])
            && '/cms/simple' !== $config['persistence']['phpcr']['basepath']
        ) {
            $prependConfig['dynamic']['persistence']['phpcr']['route_basepaths'] = array($config['persistence']['phpcr']['basepath']);
        }

        return $prependConfig;
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if ($config['persistence']['phpcr']) {
            $this->loadPhpcr($config['persistence']['phpcr'], $loader, $container);

            if ($config['use_menu']) {
                $this->loadPhpcrMenu($config, $loader, $container);
            }
        }

        if ($config['persistence']['orm']) {
            $this->loadOrm($config['persistence']['orm'], $loader, $container);

            if ($config['use_menu']) {
                $this->loadOrmMenu($config, $loader, $container);
            }
        }
    }

    private function loadPhpcr($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $loader->load('services-phpcr.xml');
        $loader->load('migrator-phpcr.xml');

        $prefix = $this->getAlias() . '.persistence.phpcr';

        $container->setParameter($prefix . '.basepath', $config['basepath']);

        $container->setParameter($prefix . '.menu_basepath', PathHelper::getParentPath($config['basepath']));

        if ($config['use_sonata_admin']) {
            $this->loadSonataAdmin($config, $loader, $container);
        } elseif (isset($config['sonata_admin'])) {
            throw new InvalidConfigurationException('Do not define sonata_admin options when use_sonata_admin is set to false');
        }

        $container->setParameter($prefix . '.manager_name', $config['manager_name']);

        $container->setParameter($prefix . '.document.class', $config['document_class']);
    }

    private function loadPhpcrMenu($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if ('auto' === $config['use_menu'] && !isset($bundles['CmfMenuBundle'])) {
            return;
        }

        $loader->load('menu-phpcr.xml');
    }

    private function loadOrm($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $prefix = $this->getAlias() . '.persistence.orm';

        $container->setParameter($prefix . '.manager_name', $config['manager_name']);
        $container->setParameter($prefix . '.entity.class', $config['entity_class']);
    }

    private function loadOrmMenu($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if ('auto' === $config['use_menu'] && !isset($bundles['CmfMenuBundle'])) {
            return;
        }

        $loader->load('menu-orm.xml');
    }

    private function loadSonataAdmin($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if ('auto' === $config['use_sonata_admin'] && !isset($bundles['SonataDoctrinePHPCRAdminBundle'])) {
            return;
        }

        $container->setParameter($this->getAlias() . '.persistence.phpcr.admin.sort',
            isset($config['sonata_admin'])
            ? $config['sonata_admin']['sort']
            : false
        );

        $loader->load('admin-phpcr.xml');
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://cmf.symfony.com/schema/dic/simplecms';
    }
}
