<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Tests\Resources\DataFixtures\Orm;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\Orm\Page;

class LoadPageData implements FixtureInterface
{
    protected function getContent($filename)
    {
        return file_get_contents(__DIR__.'/content/'.$filename);
    }

    public function load(ObjectManager $manager)
    {
        $base = new Page();
        $base->setName('page');
        $base->setTitle('Simple Cms');
        $base->setLabel('Simple Cms');
        $base->setBody('Simple Cms');
        $manager->persist($base);

        $page = new Page();
        $page->setName('homepage');
        $page->setTitle('Homepage');
        $page->setLabel('Homepage');
        $page->setPublishable(true);
        $page->setParent($base);
        $page->setBody($this->getContent('homepage.html'));
        $manager->persist($page);

        $page = new Page();
        $page->setName('french-page');
        $page->setTitle('French Page');
        $page->setLabel('French Page');
        $page->setPublishable(true);
        $page->setBody($this->getContent('french-page.html'));
        $page->setParent($base);
        $manager->persist($page);

        $page = new Page();
        $page->setName('no-locale-prefix');
        $page->setTitle('No Locale Prefix');
        $page->setLabel('No Locale Prefix');
        $page->setPublishable(true);
        $page->setParent($base);
        $page->setBody($this->getContent('no-locale-prefix.html'));
        $page->setParent($base);
        $manager->persist($page);

        $manager->flush();
    }
}
