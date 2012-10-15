<?php
namespace EasyBib\Services\Test;

use EasyBib\Services\OneAll;
use EasyBib\Services\OneAll\User;

class OneAllTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Test accept
     */
    public function testAccept()
    {
        $oneall = new OneAll('pub', 'priv', 'subdomain');
        $this->assertInstanceOf('EasyBib\\Services\\OneAll', $oneall->accept($this->setupClient()));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The user did not authenticate successfully
     * @expectedExceptionCode 401
     */
    public function testGetConnectionFailure()
    {
        $oneall = $this->getMock(
            'EasyBib\\Services\\OneAll',
            array('makeRequest',),
            array('pub', 'priv', 'subdomain',)
        );

        $oneall->expects($this->once())
            ->method('makeRequest')
            ->will($this->returnValue($this->getGetConnectionResponse('failure')));

        $oneall->accept($this->setupClient())->getConnection('some-token');
    }

    /**
     * Test connection request.
     *
     * @return void
     */
    public function testGetConnectionSuccess()
    {
        $oneall = $this->getMock(
            'EasyBib\\Services\\OneAll',
            array('makeRequest',),
            array('pub', 'priv', 'subdomain',)
        );

        $oneall->expects($this->once())
            ->method('makeRequest')
            ->will($this->returnValue($this->getGetConnectionResponse()));

        $connection = $oneall->accept($this->setupClient())
            ->getConnection('8875cb47-9b2e-40f9-8ae0-8428c06937a9');

        $this->assertInternalType('object', $connection);
        $this->assertObjectHasAttribute('connection', $connection);
        $this->assertObjectHasAttribute('user', $connection);
    }

    public function testUserEntity()
    {
        $oneall = $this->getMock(
            'EasyBib\\Services\\OneAll',
            array('makeRequest',),
            array('pub', 'priv', 'subdomain',)
        );

        $oneall->expects($this->once())
            ->method('makeRequest')
            ->will($this->returnValue($this->getGetConnectionResponse()));

        $connection = $oneall->accept($this->setupClient())
            ->getConnection('8875cb47-9b2e-40f9-8ae0-8428c06937a9');

        $user = new User($connection);
        $this->assertInternalType('array', $user->getEmails());
        $this->assertTrue(count($user->getEmails()) == 0);
    }

    /**
     * Setup a `\HTTP_Request2_Response` with the given type!
     *
     * @param string $type 'success' or 'failure'
     *
     * @return \HTTP_Request2_Response
     */
    protected function getGetConnectionResponse($type = 'success')
    {
        $response = new \HTTP_Request2_Response('HTTP/1.1 200');
        $json     = file_get_contents(dirname(dirname(dirname(__DIR__))) . '/fixtures/getConnection/' . $type . '.json');
        $response->appendBody($json);
        return $response;
    }

    /**
     * Create a HTTP client and mock adapter.
     *
     * @return \HTTP_Request2
     */
    protected function setupClient()
    {
        $client = new \HTTP_Request2;
        $client->setAdapter("HTTP_Request2_Adapter_Mock");
        return $client;
    }
}
