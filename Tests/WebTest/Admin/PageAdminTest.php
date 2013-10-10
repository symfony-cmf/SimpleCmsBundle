<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Tests\WebTest\Admin;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

class PageAdminTest extends BaseTestCase
{
    public function setUp()
    {
        $this->db('PHPCR')->loadFixtures(array(
            'Symfony\Cmf\Bundle\SimpleCmsBundle\Tests\Resources\DataFixtures\Phpcr\LoadPageData',
        ));
        $this->client = $this->createClient();
    }

    public function testPageList()
    {
        $crawler = $this->client->request('GET', '/admin/cmf/simplecms/page/list');
        $res = $this->client->getResponse();
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertCount(1, $crawler->filter('html:contains("homepage")'));
    }

    public function testPageEdit()
    {
        $crawler = $this->client->request('GET', '/admin/cmf/simplecms/page/test/page/homepage/edit');
        $res = $this->client->getResponse();
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertCount(2, $crawler->filter('input[value="Homepage"]'));
    }

    public function testPageShow()
    {
        $crawler = $this->client->request('GET', '/admin/cmf/simplecms/page/test/page/homepage/show');
        $res = $this->client->getResponse();
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertCount(1, $crawler->filter('html:contains("Homepage")'));
    }

    public function testPageCreate()
    {
        $crawler = $this->client->request('GET', '/admin/cmf/simplecms/page/create');
        $res = $this->client->getResponse();
        $this->assertEquals(200, $res->getStatusCode());

        $button = $crawler->selectButton('Create');
        $form = $button->form();
        $node = $form->getFormNode();
        $actionUrl = $node->getAttribute('action');
        $uniqId = substr(strchr($actionUrl, '='), 1);

        $form[$uniqId.'[parent]'] = '/test/page';
        $form[$uniqId.'[name]'] = 'foo-page';
        $form[$uniqId.'[title]'] = 'Foo Page';
        $form[$uniqId.'[label]'] = 'Foo Page';

        $this->client->submit($form);
        $res = $this->client->getResponse();

        // If we have a 302 redirect, then all is well
        $this->assertEquals(302, $res->getStatusCode());
    }
}
