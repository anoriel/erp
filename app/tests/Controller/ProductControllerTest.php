<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Ramsey\Uuid\Uuid;
use Safe\Exceptions\JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function assert;
use function count;

final class ProductControllerTest extends AbstractControllerWebTestCase
{
  /**
   * repository used in test cases, must be set in the setUp() part of inherited class
   */
    protected ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->em->getRepository(Product::class);
    }

  /**
   * @throws JsonException
   */
    public function testCreateProduct(): void
    {
      // test that sending no message will result to a bad request HTTP code.
        $this->JSONRequest(Request::METHOD_POST, '/api/products');
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_UNAUTHORIZED);

      // test that sending a request while not having the role "ROLE_ADMIN" will result to a forbidden HTTP code.
        $this->login(UserFixtures::DEFAULT_USER_LOGIN, UserFixtures::DEFAULT_USER_PASSWORD);
        $this->JSONRequest(Request::METHOD_POST, '/api/products');
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_FORBIDDEN);

      // test that sending a request while begin authenticated will result to a created HTTP code.
        $this->login(UserFixtures::USER_LOGIN_ROLE_ADMIN, UserFixtures::USER_PASSWORD_ROLE_ADMIN);
        $this->JSONRequest(Request::METHOD_POST, '/api/products', ['name' => 'Scie à onglet', 'price' => 15, 'stock' => 200]);
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_CREATED);
      // test that sending no message will result to a bad request HTTP code.
        $this->JSONRequest(Request::METHOD_POST, '/api/products');
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

  /**
   * @throws JsonException
   */
    public function testUpdateProduct(): void
    {
      // false id to use in test (not existing in database)
        $id = Uuid::uuid4();

      // test sending a request without being authenticated will result to a unauthorized HTTP code.
        $this->JSONRequest(Request::METHOD_PUT, '/api/products/' . $id);
        $this->assertJSONResponse(
            $this->client->getResponse(),
            Response::HTTP_UNAUTHORIZED
        );

      // test sending a request while not having the role "ROLE_ADMIN" will result to a forbidden HTTP code.
        $this->login(UserFixtures::DEFAULT_USER_LOGIN, UserFixtures::DEFAULT_USER_PASSWORD);
        $this->JSONRequest(Request::METHOD_PUT, '/api/products/' . $id);
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_FORBIDDEN);

      // test a request while begin authenticated but with a product Id not in database : result to HTTP_NOT_FOUND code.
        $this->login(UserFixtures::USER_LOGIN_ROLE_ADMIN, UserFixtures::USER_PASSWORD_ROLE_ADMIN);
        $this->JSONRequest(Request::METHOD_PUT, '/api/products/' . $id);
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_NOT_FOUND);

      // test a request while begin authenticated with a real product and try to update : result to a OK HTTP code.
        $this->login(UserFixtures::USER_LOGIN_ROLE_ADMIN, UserFixtures::USER_PASSWORD_ROLE_ADMIN);
      // load an existing product
        $product = $this->repository->findOneBy(['name' => 'Scie à onglet']); //load item created in the testCreateProduct
        assert($product instanceof Product);

      //we will change the price for test, so we save it to put it back later
        $currentValue = $product->getPrice();
        $newValue = $currentValue * 2;
        $this->JSONRequest(Request::METHOD_PUT, '/api/products/' . $product->getId(), ['name' => $product->getName(), 'price' => $newValue]);
        $this->assertJSONResponse($this->client->getResponse(), Response::HTTP_OK);

        $this->em->refresh($product);
        $this->assertEquals($newValue, $product->getPrice());
        $product->setPrice($currentValue);
        $this->em->flush();
    }

  /**
   * @throws JsonException
   */
    public function testFindAllProducts(): void
    {
      // test that sending a request without being authenticated will result to a unauthorized HTTP code.
        $this->assertUnauthorized(Request::METHOD_GET, '/api/products');

        $products = $this->em->getRepository(Product::class)->findBy([], ['name' => 'ASC']);

      // test that sending a request while begin authenticated will result to a OK HTTP code.
        $this->login(UserFixtures::DEFAULT_USER_LOGIN, UserFixtures::DEFAULT_USER_PASSWORD);
        $this->client->request(Request::METHOD_GET, '/api/products');
        $response = $this->client->getResponse();
        $content = $this->assertJSONResponse($response, Response::HTTP_OK);
        $this->assertEquals(count($products), count($content));
    }

  /**
   * @throws JsonException
   */
    public function testDeleteProduct(): void
    {
      // false id to use in test (not existing in database)
        $id = Uuid::uuid4();

        $urlToUse = '/api/products/' . $id;

      // test sending a request without being authenticated will result to a unauthorized HTTP code.
        $this->assertUnauthorized(Request::METHOD_DELETE, $urlToUse);

      // test sending a request while not having the role "ROLE_ADMIN" will result to a forbidden HTTP code.
        $this->login(UserFixtures::DEFAULT_USER_LOGIN, UserFixtures::DEFAULT_USER_PASSWORD);
        $this->assertForbidden(Request::METHOD_DELETE, $urlToUse);

      // test a request while begin authenticated but with a product Id not in database : result to HTTP_NOT_FOUND code.
        $this->login(UserFixtures::USER_LOGIN_ROLE_ADMIN, UserFixtures::USER_PASSWORD_ROLE_ADMIN);
        $this->assertNotFound(Request::METHOD_DELETE, $urlToUse);

      // test a request while begin authenticated with a real product and try to update : result to a OK HTTP code.
      // load an existing product
        $product = $this->repository->findOneBy(['name' => 'Scie à onglet']); //load item created in the testCreateProduct
        assert($product instanceof Product);

        $this->assertOk(Request::METHOD_DELETE, '/api/products/' . $product->getId());
        $response = $this->client->getResponse();
        $content = $this->assertJSONResponse($response, Response::HTTP_OK);
        $this->assertEquals($product->getId(), $content['id']);

      //we add the item again
        $this->em->persist($product);
        $this->em->flush();
    }
}
