<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\SimpleCmsBundle;

use Symfony\Cmf\Bundle\CoreBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\DoctrinePhpcrMappingsPass;

class CmfSimpleCmsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        if ($container->hasExtension('jms_di_extra')) {
            $container->getExtension('jms_di_extra')->blackListControllerFile(__DIR__ . '/Controller/PageAdminController.php');
        }

        if (class_exists('Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\DoctrinePhpcrMappingsPass')) {
            $this->buildPhpcrCompilerPass($container);
        }

        if (class_exists('Doctrine\ORM\Version')) {
            $this->buildOrmCompilerPass($container);
        }
    }

    /**
     * Creates and registers compiler passes for PHPCR-ODM mapping if both the
     * phpcr-odm and the phpcr-bundle are present.
     *
     * @param ContainerBuilder $container
     */
    private function buildPhpcrCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(
            DoctrinePhpcrMappingsPass::createXmlMappingDriver(
                array(
                    realpath(__DIR__ . '/Resources/config/doctrine-phpcr') => 'Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\Phpcr',
                ),
                array('cmf_simple_cms.persistence.phpcr.manager_name'),
                false,
                array('CmfSimpleCmsBundle' => 'Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\Phpcr')
            )
        );
    }

    /**
     * Creates and registers compiler passes for ORM mappings if both doctrine
     * ORM and a suitable compiler pass implementation are available.
     *
     * @param ContainerBuilder $container
     */
    private function buildOrmCompilerPass(ContainerBuilder $container)
    {
        $doctrineOrmCompiler = $this->findDoctrineOrmCompiler();

        if (!$doctrineOrmCompiler) {
            return;
        }

        $container->addCompilerPass(
            $doctrineOrmCompiler::createXmlMappingDriver(
                array(
                    realpath(__DIR__ . '/Resources/config/doctrine-orm') => 'Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\Orm',
                ),
                array('cmf_simple_cms.persistence.orm.manager_name'),
                false,
                array('CmfSimpleCmsBundle' => 'Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\orm')
            )
        );
    }

    /**
     * Looks for a mapping compiler pass. If available, use the one from
     * DoctrineBundle (available only since DoctrineBundle 2.4 and Symfony 2.3)
     * Otherwise use the standalone one from CmfCoreBundle.
     *
     * @return boolean|string the compiler pass to use or false if no suitable
     *                        one was found
     */
    private function findDoctrineOrmCompiler()
    {
        if (class_exists('Symfony\Bridge\Doctrine\DependencyInjection\CompilerPass\RegisterMappingsPass')
            && class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')
        ) {
            return 'Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass';
        }

        if (class_exists('Symfony\Cmf\Bundle\CoreBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')) {
            return 'Symfony\Cmf\Bundle\CoreBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass';
        }

        return false;
    }
}
