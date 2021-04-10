<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Safe\Exceptions\JsonException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function Safe\json_decode;
use function Safe\json_encode;
use function var_dump;

abstract class AbstractControllerWebTestCase extends WebTestCase
{
    /**
     * client used in test cases
     */
    protected KernelBrowser $client;

    /**
     * entity manager used in test cases
     */
    protected EntityManager $em;
    /**
     * Doctrine manager used in test cases
     */
    protected Registry $doctrine;

    protected function setUp(): void
    {
        self::bootKernel();
        self::ensureKernelShutdown();
        $this->client = static::createClient();

        $this->doctrine = static::$container->get('doctrine');
        $this->em = $this->doctrine->getManager();
    }

    /**
     * @param mixed[] $data
     *
     * @throws JsonException
     */
    protected function JSONRequest(string $method, string $uri, array $data = []): void
    {
        $this->client->request($method, $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
    }

    /**
     * @return mixed
     *
     * @throws JsonException
     */
    protected function assertJSONResponse(Response $response, int $expectedStatusCode)
    {
        if ($expectedStatusCode !== $response->getStatusCode()) {
            var_dump($this->client->getResponse());
        }

        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());

        return json_decode($response->getContent(), true);
    }

    /**
     * @throws JsonException
     */
    protected function login(string $username = UserFixtures::DEFAULT_USER_LOGIN, string $password = UserFixtures::DEFAULT_USER_PASSWORD): void
    {
        $this->client->request(Request::METHOD_POST, '/api/security/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['username' => $username, 'password' => $password]));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed
     *
     * @throws JsonException
     */
    public function assertCreated(string $method, string $url, array $data = [])
    {
        $this->JSONRequest($method, $url, $data);

        return $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_CREATED);
    }

    /**
     * @return mixed
     *
     * @throws JsonException
     */
    public function assertBadRequest(string $method, string $url)
    {
        $this->JSONRequest($method, $url);

        return $this->assertJSONResponse(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @return mixed
     *
     * @throws JsonException
     */
    public function assertUnauthorized(string $method, string $url)
    {
        $this->JSONRequest($method, $url);

        return $this->assertJSONResponse(
            $this->client->getResponse(),
            Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * @return mixed
     *
     * @throws JsonException
     */
    public function assertForbidden(string $method, string $url)
    {
        $this->JSONRequest($method, $url);

        return $this->assertJSONResponse(
            $this->client->getResponse(),
            Response::HTTP_FORBIDDEN
        );
    }

    /**
     * @return mixed
     *
     * @throws JsonException
     */
    public function assertNotFound(string $method, string $url)
    {
        $this->JSONRequest($method, $url);

        if ($this->client->getResponse()->getStatusCode() !== Response::HTTP_NOT_FOUND) {
            var_dump($this->client->getResponse());
        }

        return $this->assertJSONResponse(
            $this->client->getResponse(),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed
     *
     * @throws JsonException
     */
    public function assertOk(string $method, string $url, array $data = [])
    {
        $this->JSONRequest($method, $url, $data);

        if ($this->client->getResponse()->getStatusCode() !== Response::HTTP_OK) {
            var_dump($this->client->getResponse()->getContent());
        }

        return $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_OK);
    }
}
