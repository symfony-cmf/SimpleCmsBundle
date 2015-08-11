<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Tests\Functional\Doctrine\Orm;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Sonata\CoreBundle;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\Orm\Page;

class PageTest extends BaseTestCase
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Page
     */
    private $basePage;

    public function setUp()
    {
        $this->em = $this->db('ORM')->getOm();

        $page = new Page(array('add_locale_pattern' => true));
        $page->setName('base-page-name');
        $page->setTitle('Base Page Title');
        $page->setLabel('Base Page Label');
        $page->setBody('This is body');
        $page->setPublishable(false);
        $page->setPublishStartDate(new \DateTime('2013-06-18'));
        $page->setPublishEndDate(new \DateTime('2013-06-18'));
        $page->setExtras(array(
            'extra_1' => 'foobar',
            'extra_2' => 'barfoo',
        ));

        $this->em->persist($page);
        $this->em->flush();
        $this->em->clear();

        $this->basePage = $page;
    }

    public function testPage()
    {
        $page = new Page(array('add_locale_pattern' => true));
        $page->setParent($this->basePage);
        $page->setName('page-name');
        $page->setTitle('Page Title');
        $page->setLabel('Page Label');
        $page->setBody('This is body');
        $page->setPublishable(false);
        $page->setPublishStartDate(new \DateTime('2013-06-18'));
        $page->setPublishEndDate(new \DateTime('2013-06-18'));
        $page->setExtras(array(
            'extra_1' => 'foobar',
            'extra_2' => 'barfoo',
        ));

        $this->em->persist($this->basePage);
        $this->em->persist($page);
        $this->em->flush();
        $this->em->refresh($page);

        $this->assertNotNull($page);
        $this->assertTrue($page->getOption('add_locale_pattern'));
        $this->assertEquals('Page Title', $page->getTitle());
        $this->assertEquals('Page Label', $page->getLabel());
        $this->assertEquals('This is body', $page->getBody());
        $this->assertEquals(array(
            'extra_1' => 'foobar',
            'extra_2' => 'barfoo',
        ), $page->getExtras());

        // test publish start and end
        $publishStartDate = $page->getPublishStartDate();
        $publishEndDate = $page->getPublishEndDate();

        $this->assertInstanceOf('\DateTime', $publishStartDate);
        $this->assertInstanceOf('\DateTime', $publishEndDate);
        $this->assertEquals('2013-06-18', $publishStartDate->format('Y-m-d'));
        $this->assertEquals('2013-06-18', $publishEndDate->format('Y-m-d'));
    }
}
