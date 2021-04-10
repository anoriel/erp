<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Ramsey\Uuid\Uuid;
use Safe\Exceptions\JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function assert;
use function count;
use function uniqid;

final class CustomerControllerTest extends AbstractControllerWebTestCase
{
  /**
   * repository used in test cases, must be set in the setUp() part of inherited class
   */
    protected CustomerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->em->getRepository(Customer::class);
    }

  /**
   * @throws JsonException
   */
    public function testCreateCustomer(): void
    {
      // test that sending no message will result to a bad request HTTP code.
        $this->JSONRequest(Request::METHOD_POST, '/api/customers');
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_UNAUTHORIZED);

      // test that sending a request while not having the role "ROLE_ADMIN" will result to a forbidden HTTP code.
        $this->login(UserFixtures::DEFAULT_USER_LOGIN, UserFixtures::DEFAULT_USER_PASSWORD);
        $this->JSONRequest(Request::METHOD_POST, '/api/customers');
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_FORBIDDEN);

      // test that sending a request while begin authenticated will result to a created HTTP code.
        $this->login(UserFixtures::USER_LOGIN_ROLE_ADMIN, UserFixtures::USER_PASSWORD_ROLE_ADMIN);
        $this->JSONRequest(Request::METHOD_POST, '/api/customers', ['name' => 'François Pignon', 'address' => '5 chemin du puit', 'country' => 'France']);
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_CREATED);
      // test that sending no message will result to a bad request HTTP code.
        $this->JSONRequest(Request::METHOD_POST, '/api/customers');
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

  /**
   * @throws JsonException
   */
    public function testUpdateCustomer(): void
    {
      // false id to use in test (not existing in database)
        $id = Uuid::uuid4();

      // test sending a request without being authenticated will result to a unauthorized HTTP code.
        $this->JSONRequest(Request::METHOD_PUT, '/api/customers/' . $id);
        $this->assertJSONResponse(
            $this->client->getResponse(),
            Response::HTTP_UNAUTHORIZED
        );

      // test sending a request while not having the role "ROLE_ADMIN" will result to a forbidden HTTP code.
        $this->login(UserFixtures::DEFAULT_USER_LOGIN, UserFixtures::DEFAULT_USER_PASSWORD);
        $this->JSONRequest(Request::METHOD_PUT, '/api/customers/' . $id);
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_FORBIDDEN);

      // test a request while begin authenticated but with a customer Id not in database : result to HTTP_NOT_FOUND code.
        $this->login(UserFixtures::USER_LOGIN_ROLE_ADMIN, UserFixtures::USER_PASSWORD_ROLE_ADMIN);
        $this->JSONRequest(Request::METHOD_PUT, '/api/customers/' . $id);
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_NOT_FOUND);

      // test a request while begin authenticated with a real customer and try to update : result to a OK HTTP code.
        $this->login(UserFixtures::USER_LOGIN_ROLE_ADMIN, UserFixtures::USER_PASSWORD_ROLE_ADMIN);
      // load an existing customer
        $customer = $this->repository->findOneBy(['name' => 'François Pignon']); //load item created in the testCreateCustomer
        assert($customer instanceof Customer);

    //we will change the country for test, so we save it to put it back later
        $currentValue = $customer->getCountry();
        $newValue = uniqid();
        $this->JSONRequest(Request::METHOD_PUT, '/api/customers/' . $customer->getId(), ['name' => $customer->getName(), 'address' => $customer->getAddress(), 'country' => $newValue]);
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_OK);

        $this->em->refresh($customer);
        $this->assertEquals($newValue, $customer->getCountry());
        $customer->setCountry($currentValue);
        $this->em->flush();
    }

  /**
   * @throws JsonException
   */
    public function testFindAllCustomers(): void
    {
      // test that sending a request without being authenticated will result to a unauthorized HTTP code.
        $this->assertUnauthorized(Request::METHOD_GET, '/api/customers');

        $customers = $this->em->getRepository(Customer::class)->findBy([], ['name' => 'ASC']);

      // test that sending a request while begin authenticated will result to a OK HTTP code.
        $this->login(UserFixtures::DEFAULT_USER_LOGIN, UserFixtures::DEFAULT_USER_PASSWORD);
        $this->client->request(Request::METHOD_GET, '/api/customers');
        $response = $this->client->getResponse();
        $content = $this->assertJSONResponse($response, Response::HTTP_OK);
        $this->assertEquals(count($customers), count($content));
    }

  /**
   * @throws JsonException
   */
    public function testDeleteCustomer(): void
    {
      // false id to use in test (not existing in database)
        $id = Uuid::uuid4();

        $urlToUse = '/api/customers/' . $id;

      // test sending a request without being authenticated will result to a unauthorized HTTP code.
        $this->assertUnauthorized(Request::METHOD_DELETE, $urlToUse);

      // test sending a request while not having the role "ROLE_ADMIN" will result to a forbidden HTTP code.
        $this->login(UserFixtures::DEFAULT_USER_LOGIN, UserFixtures::DEFAULT_USER_PASSWORD);
        $this->assertForbidden(Request::METHOD_DELETE, $urlToUse);

      // test a request while begin authenticated but with a customer Id not in database : result to HTTP_NOT_FOUND code.
        $this->login(UserFixtures::USER_LOGIN_ROLE_ADMIN, UserFixtures::USER_PASSWORD_ROLE_ADMIN);
        $this->assertNotFound(Request::METHOD_DELETE, $urlToUse);

      // test a request while begin authenticated with a real customer and try to update : result to a OK HTTP code.
      // load an existing customer
        $customer = $this->repository->findOneBy(['name' => 'François Pignon']); //load item created in the testCreateCustomer
        assert($customer instanceof Customer);

        $this->assertOk(Request::METHOD_DELETE, '/api/customers/' . $customer->getId());
        $response = $this->client->getResponse();
        $content = $this->assertJSONResponse($response, Response::HTTP_OK);
        $this->assertEquals($customer->getId(), $content['id']);

      //we add the item again
        $this->em->persist($customer);
        $this->em->flush();
    }
}
