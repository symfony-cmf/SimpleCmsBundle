<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Initializer;

use PHPCR\Util\NodeHelper;
use PHPCR\Util\PathHelper;

use Doctrine\Bundle\PHPCRBundle\Initializer\InitializerInterface;
use Doctrine\ODM\PHPCR\DocumentManager;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
use Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\Phpcr\Page;

class HomepageInitializer implements InitializerInterface
{
    private $basePath;

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * {@inheritDoc}
     */
    public function init(ManagerRegistry $registry)
    {
        /** @var $dm DocumentManager */
        $dm = $registry->getManagerForClass('Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\Phpcr\Page');
        if ($dm->find(null, $this->basePath)) {
            return;
        }

        $session = $dm->getPhpcrSession();
        NodeHelper::createPath($session, PathHelper::getParentPath($this->basePath));

        $page = new Page();
        $page->setId($this->basePath);
        $page->setLabel('Home');
        $page->setTitle('Homepage');
        $page->setBody('Autocreated Homepage');

        $dm->persist($page);
        $dm->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'CmfSimpleCmsBundle Homepage';
    }
}
