<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Tests\WebTest\TestApp;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

class HttpStatusCodeTest extends BaseTestCase
{
    public function provideStatusCodeTest()
    {
        return array(
            array('orm', '/', 301),
            array('orm', '/en/homepage'),
            array('orm', '/en/french-page'),
            array('orm', '/no-locale-prefix'),
            array('phpcr', '/', 301),
            array('phpcr', '/en/homepage'),
            array('phpcr', '/en/french-page'),
            array('phpcr', '/no-locale-prefix'),
        );
    }

    /**
     * @dataProvider provideStatusCodeTest
     */
    public function testStatusCode($persistanceLayer, $url, $expectedStatusCode = 200)
    {
        if ('phpcr' === $persistanceLayer) {
            $this->db('PHPCR')->loadFixtures(array(
                'Symfony\Cmf\Bundle\SimpleCmsBundle\Tests\Resources\DataFixtures\Phpcr\LoadPageData',
            ));
        } elseif ('orm' === $persistanceLayer) {
            $this->db('ORM')->loadFixtures(array(
                'Symfony\Cmf\Bundle\SimpleCmsBundle\Tests\Resources\DataFixtures\Orm\LoadPageData',
            ));
        }

        $client = $this->createClient();
        $client->request('GET', $url);

        $response = $client->getResponse();

        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
    }
}
