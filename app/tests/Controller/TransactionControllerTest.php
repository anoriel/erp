<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use App\Entity\Company;
use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use Ramsey\Uuid\Uuid;
use Safe\Exceptions\JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function assert;
use function count;
use function rand;

final class TransactionControllerTest extends AbstractControllerWebTestCase
{
  /**
   * repository used in test cases, must be set in the setUp() part of inherited class
   */
    protected TransactionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->em->getRepository(Transaction::class);
    }

  /**
   * @throws JsonException
   */
    public function testCreateTransaction(): void
    {
        $urlToUse = '/api/transactions';

      // test sending a request without being authenticated will result to a unauthorized HTTP code.
        $this->assertUnauthorized(Request::METHOD_POST, $urlToUse);

      // test that sending a request while not having the role "ROLE_ADMIN" will result to a forbidden HTTP code.
        $this->login(UserFixtures::DEFAULT_USER_LOGIN, UserFixtures::DEFAULT_USER_PASSWORD);
        $this->assertForbidden(Request::METHOD_POST, $urlToUse);

      // test that sending a request while begin authenticated will result to a created HTTP code.
        $companies = $this->em->getRepository(Company::class)->findBy([], ['name' => 'ASC'], 1);
        $company = $companies[0];
        assert($company instanceof Company);
        $customers = $this->em->getRepository(Customer::class)->findBy([], ['name' => 'ASC'], 1);
        $customer = $customers[0];
        assert($customer instanceof Customer);
        $products = $this->em->getRepository(Product::class)->findBy([], ['name' => 'ASC'], 1);
        $product = $products[0];
        assert($product instanceof Product);

        $this->login(UserFixtures::USER_LOGIN_ROLE_ADMIN, UserFixtures::USER_PASSWORD_ROLE_ADMIN);
        $this->assertCreated(Request::METHOD_POST, $urlToUse, ['companyId' => $company->getId(), 'customerId' => $customer->getId(), 'productId' => $product->getId(), 'quantity' => rand(5, 10)]);

      // test that sending no message will result to a bad request HTTP code.
        $this->assertBadRequest(Request::METHOD_POST, $urlToUse);
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

  /**
   * @throws JsonException
   */
    public function testUpdateTransaction(): void
    {
      // false id to use in test (not existing in database)
        $id = Uuid::uuid4();
        $urlToUse = '/api/transactions/' . $id;

      // test sending a request without being authenticated will result to a unauthorized HTTP code.
        $this->assertUnauthorized(Request::METHOD_PUT, $urlToUse);

      // test sending a request while not having the role "ROLE_ADMIN" will result to a forbidden HTTP code.
        $this->login(UserFixtures::DEFAULT_USER_LOGIN, UserFixtures::DEFAULT_USER_PASSWORD);
        $this->assertForbidden(Request::METHOD_PUT, $urlToUse);

      // test a request while begin authenticated but with a transaction Id not in database : result to HTTP_NOT_FOUND code.
        $this->login(UserFixtures::USER_LOGIN_ROLE_ADMIN, UserFixtures::USER_PASSWORD_ROLE_ADMIN);
        $this->assertNotFound(Request::METHOD_PUT, $urlToUse);

      // test a request while begin authenticated with a real transaction and try to update : result to a OK HTTP code.
        $this->login(UserFixtures::USER_LOGIN_ROLE_ADMIN, UserFixtures::USER_PASSWORD_ROLE_ADMIN);
      // load an existing transaction
        $transactions = $this->em->getRepository(Transaction::class)->findBy([], ['id' => 'ASC'], 1);
        $transaction = $transactions[0];
        assert($transaction instanceof Transaction);

      //we will change the quantity for test, so we save it to put it back later
        $currentValue = $transaction->getQuantity();
        $newValue = $currentValue * 2;
        $this->assertOk(Request::METHOD_PUT, '/api/transactions/' . $transaction->getId(), ['companyId' => $transaction->getCompany()->getId(), 'customerId' => $transaction->getCustomer() !== null ? $transaction->getCustomer()->getId() : null, 'providerId' => $transaction->getProvider() !== null ? $transaction->getProvider()->getId() : null, 'productId' => $transaction->getProduct()->getId(), 'quantity' => $newValue]);

        $this->em->refresh($transaction);
        $this->assertEquals($newValue, $transaction->getQuantity());
        $transaction->setQuantity($currentValue);
        $this->em->flush();
    }

  /**
   * @throws JsonException
   */
    public function testFindAllTransactions(): void
    {
        $url = '/api/transactions';

      // test that sending a request without being authenticated will result to a unauthorized HTTP code.
        $this->assertUnauthorized(Request::METHOD_GET, $url);

        $list = $this->em->getRepository(Transaction::class)->findBy([], ['id' => 'ASC']);

      // test that sending a request while begin authenticated will result to a OK HTTP code.
        $this->login(UserFixtures::DEFAULT_USER_LOGIN, UserFixtures::DEFAULT_USER_PASSWORD);
        $content = $this->assertOk(Request::METHOD_GET, $url);
        $this->assertEquals(count($list), count($content));
    }

  /**
   * @throws JsonException
   */
    public function testDeleteTransaction(): void
    {
      // false id to use in test (not existing in database)
        $id = Uuid::uuid4();

        $urlToUse = '/api/transactions/' . $id;

      // test sending a request without being authenticated will result to a unauthorized HTTP code.
        $this->assertUnauthorized(Request::METHOD_DELETE, $urlToUse);

      // test sending a request while not having the role "ROLE_ADMIN" will result to a forbidden HTTP code.
        $this->login(UserFixtures::DEFAULT_USER_LOGIN, UserFixtures::DEFAULT_USER_PASSWORD);
        $this->assertForbidden(Request::METHOD_DELETE, $urlToUse);

      // test a request while begin authenticated but with a transaction Id not in database : result to HTTP_NOT_FOUND code.
        $this->login(UserFixtures::USER_LOGIN_ROLE_ADMIN, UserFixtures::USER_PASSWORD_ROLE_ADMIN);
        $this->assertNotFound(Request::METHOD_DELETE, $urlToUse);

      // test a request while begin authenticated with a real transaction and try to update : result to a OK HTTP code.
      // load an existing transaction
        $transactions = $this->em->getRepository(Transaction::class)->findBy([], ['id' => 'ASC'], 1);
        $transaction = $transactions[0];
        assert($transaction instanceof Transaction);

        $this->assertOk(Request::METHOD_DELETE, '/api/transactions/' . $transaction->getId());
        $response = $this->client->getResponse();
        $content = $this->assertJSONResponse($response, Response::HTTP_OK);
        $this->assertEquals($transaction->getId(), $content['id']);

      //we add the item again
        $this->em->persist($transaction);
        $this->em->flush();
    }
}
