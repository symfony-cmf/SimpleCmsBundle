<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\SimpleCmsBundle;

use Symfony\Cmf\Bundle\SimpleCmsBundle\DependencyInjection\Compiler\AppendRouteBasepathPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\DoctrinePhpcrMappingsPass;
use Symfony\Cmf\Component\Routing\DependencyInjection\Compiler\RegisterRouteEnhancersPass;

class CmfSimpleCmsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AppendRouteBasepathPass());

        if ($container->hasExtension('jms_di_extra')) {
            $container->getExtension('jms_di_extra')->blackListControllerFile(__DIR__.'/Controller/PageAdminController.php');
        }

        if (class_exists('Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\DoctrinePhpcrMappingsPass')) {
            $container->addCompilerPass(
                DoctrinePhpcrMappingsPass::createXmlMappingDriver(
                    array(
                        realpath(__DIR__.'/Resources/config/doctrine-phpcr') => 'Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\Phpcr',
                    ),
                    array('cmf_simple_cms.persistence.phpcr.manager_name'),
                    false,
                    array('CmfSimpleCmsBundle' => 'Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\Phpcr')
                )
            );
        }

        // Allow tagged route enhancers
        $container->addCompilerPass(new RegisterRouteEnhancersPass('cmf_simple_cms.dynamic_router'));
    }
}
