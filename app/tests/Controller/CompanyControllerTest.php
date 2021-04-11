<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use App\Entity\Company;
use App\Repository\CompanyRepository;
use Ramsey\Uuid\Uuid;
use Safe\Exceptions\JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function assert;
use function count;
use function uniqid;

final class CompanyControllerTest extends AbstractControllerWebTestCase
{
  /**
   * repository used in test cases, must be set in the setUp() part of inherited class
   */
    protected CompanyRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->em->getRepository(Company::class);
    }

  /**
   * @throws JsonException
   */
    public function testCreateCompany(): void
    {
      // test that sending no message will result to a bad request HTTP code.
        $this->JSONRequest(Request::METHOD_POST, '/api/companies');
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_UNAUTHORIZED);

      // test that sending a request while not having the role "ROLE_ADMIN" will result to a forbidden HTTP code.
        $this->login(UserFixtures::DEFAULT_USER_LOGIN, UserFixtures::DEFAULT_USER_PASSWORD);
        $this->JSONRequest(Request::METHOD_POST, '/api/companies');
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_FORBIDDEN);

      // test that sending a request while begin authenticated will result to a created HTTP code.
        $this->login(UserFixtures::USER_LOGIN_ROLE_ADMIN, UserFixtures::USER_PASSWORD_ROLE_ADMIN);
        $this->JSONRequest(Request::METHOD_POST, '/api/companies', ['name' => 'Microsoft', 'balance' => 50, 'country' => 'U.S.A.']);
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_CREATED);

      // test that sending no message will result to a bad request HTTP code.
        $this->JSONRequest(Request::METHOD_POST, '/api/companies');
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

  /**
   * @throws JsonException
   */
    public function testUpdateCompany(): void
    {
      // false id to use in test (not existing in database)
        $id = Uuid::uuid4();

      // test sending a request without being authenticated will result to a unauthorized HTTP code.
        $this->JSONRequest(Request::METHOD_PUT, '/api/companies/' . $id);
        $this->assertJSONResponse(
            $this->client->getResponse(),
            Response::HTTP_UNAUTHORIZED
        );

      // test sending a request while not having the role "ROLE_ADMIN" will result to a forbidden HTTP code.
        $this->login(UserFixtures::DEFAULT_USER_LOGIN, UserFixtures::DEFAULT_USER_PASSWORD);
        $this->JSONRequest(Request::METHOD_PUT, '/api/companies/' . $id);
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_FORBIDDEN);

      // test a request while begin authenticated but with a company Id not in database : result to HTTP_NOT_FOUND code.
        $this->login(UserFixtures::USER_LOGIN_ROLE_ADMIN, UserFixtures::USER_PASSWORD_ROLE_ADMIN);
        $this->JSONRequest(Request::METHOD_PUT, '/api/companies/' . $id);
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_NOT_FOUND);

      // test a request while begin authenticated with a real company and try to update : result to a OK HTTP code.
        $this->login(UserFixtures::USER_LOGIN_ROLE_ADMIN, UserFixtures::USER_PASSWORD_ROLE_ADMIN);
      // load an existing company
        $company = $this->repository->findOneBy(['name' => 'Microsoft']); //load item created in the testCreateCompany
        assert($company instanceof Company);

      //we will change the country for test, so we save it to put it back later
        $currentValue = $company->getCountry();
        $newValue = uniqid();
        $this->JSONRequest(Request::METHOD_PUT, '/api/companies/' . $company->getId(), ['name' => $company->getName(), 'address' => $company->getBalance(), 'country' => $newValue]);
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_OK);

        $this->em->refresh($company);
        $this->assertEquals($newValue, $company->getCountry());
        $company->setCountry($currentValue);
        $this->em->flush();
    }

  /**
   * @throws JsonException
   */
    public function testFindAllCompanies(): void
    {
      // test that sending a request without being authenticated will result to a unauthorized HTTP code.
        $this->assertUnauthorized(Request::METHOD_GET, '/api/companies');

        $list = $this->em->getRepository(Company::class)->findBy([], ['name' => 'ASC']);

      // test that sending a request while begin authenticated will result to a OK HTTP code.
        $this->login(UserFixtures::DEFAULT_USER_LOGIN, UserFixtures::DEFAULT_USER_PASSWORD);
        $this->client->request(Request::METHOD_GET, '/api/companies');
        $response = $this->client->getResponse();
        $content = $this->assertJSONResponse($response, Response::HTTP_OK);
        $this->assertEquals(count($list), count($content));
    }

  /**
   * @throws JsonException
   */
    public function testDeleteCompany(): void
    {
      // false id to use in test (not existing in database)
        $id = Uuid::uuid4();

        $urlToUse = '/api/companies/' . $id;

      // test sending a request without being authenticated will result to a unauthorized HTTP code.
        $this->assertUnauthorized(Request::METHOD_DELETE, $urlToUse);

      // test sending a request while not having the role "ROLE_ADMIN" will result to a forbidden HTTP code.
        $this->login(UserFixtures::DEFAULT_USER_LOGIN, UserFixtures::DEFAULT_USER_PASSWORD);
        $this->assertForbidden(Request::METHOD_DELETE, $urlToUse);

      // test a request while begin authenticated but with a company Id not in database : result to HTTP_NOT_FOUND code.
        $this->login(UserFixtures::USER_LOGIN_ROLE_ADMIN, UserFixtures::USER_PASSWORD_ROLE_ADMIN);
        $this->assertNotFound(Request::METHOD_DELETE, $urlToUse);

      // test a request while begin authenticated with a real company and try to update : result to a OK HTTP code.
      // load an existing company
        $company = $this->repository->findOneBy(['name' => 'Microsoft']); //load item created in the testCreateCompany
        assert($company instanceof Company);

        $this->assertOk(Request::METHOD_DELETE, '/api/companies/' . $company->getId());
        $response = $this->client->getResponse();
        $content = $this->assertJSONResponse($response, Response::HTTP_OK);
        $this->assertEquals($company->getId(), $content['id']);

      //we add the item again
        $this->em->persist($company);
        $this->em->flush();
    }
}
