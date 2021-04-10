<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Product;
use App\Entity\StockByCompany;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

use function assert;
use function intval;
use function is_object;
use function is_string;
use function property_exists;
use function Safe\json_decode;

/**
 * @Rest\Route("/api")
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
final class ProductController extends AbstractController
{
    private EntityManagerInterface $em;

    private SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->em = $em;
        $this->serializer = $serializer;
    }

    /**
     * @throws BadRequestHttpException
     *
     * @Rest\Post("/products", name="createProduct")
     * @IsGranted("ROLE_ADMIN")
     */
    public function create(Request $request): JsonResponse
    {
        $data = json_decode((string) $request->getContent());

        if ($data === null || ! is_object($data)) {
            throw new BadRequestHttpException('No data sent.');
        }

        if (! property_exists($data, 'name')) {
            throw new BadRequestHttpException('name cannot be empty.');
        }

        $name = (string) $data->name;

        $price = property_exists($data, 'price') ? intval($data->price) : 0;

        $product = new Product();
        $product->setName($name);
        $product->setPrice($price);
        $this->em->persist($product);

        // adding product to all companies
        foreach ($this->em->getRepository(Company::class)->findAll() as $company) {
            $stockByCompany = new StockByCompany();
            $stockByCompany->setCompany($company);
            $stockByCompany->setProduct($product);
            $stockByCompany->setStock(0);
            $this->em->persist($stockByCompany);
        }

        $this->em->flush();
        $data = $this->serializer->serialize($product, JsonEncoder::FORMAT, ['groups' => 'list_items']);

        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    /**
     * @throws BadRequestHttpException
     *
     * @Rest\Delete("/products/{id}", name="deleteProduct")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(string $id, Request $request): JsonResponse
    {
        if (empty($id)) {
            throw new BadRequestHttpException('id cannot be empty');
        }

        try {
            $product = $this->em->getRepository(Product::class)->findOneBy(['id' => $id]);
            assert($product instanceof Product);

            // item not found
            if (empty($product)) {
                $data = $this->serializer->serialize([
                    'message' => 'Item not found ID="' . $id . '"',
                ], JsonEncoder::FORMAT);

                return new JsonResponse($data, Response::HTTP_NOT_FOUND, [], true);
            }

            $this->em->remove($product);

            $this->em->flush();
            $data = $this->serializer->serialize(['id' => $id], JsonEncoder::FORMAT);

            return new JsonResponse($data, Response::HTTP_OK, [], true);
        } catch (Throwable $th) {
            throw new BadRequestHttpException($th->getMessage(), $th);
        }
    }

    /**
     * @throws BadRequestHttpException
     *
     * @Rest\Put("/products/{id}", name="updateProduct")
     * @IsGranted("ROLE_ADMIN")
     */
    public function update(string $id, Request $request): JsonResponse
    {
        if (empty($id)) {
            throw new BadRequestHttpException('id cannot be empty');
        }

        try {
            $product = $this->em->getRepository(Product::class)->findOneBy(['id' => $id]);
            assert($product instanceof Product);

            // item not found
            if (empty($product)) {
                $data = $this->serializer->serialize([
                    'message' => 'Item not found ID="' . $id . '"',
                ], JsonEncoder::FORMAT);

                return new JsonResponse($data, Response::HTTP_NOT_FOUND, [], true);
            }

            $data = json_decode((string) $request->getContent());

            if ($data === null || ! is_object($data)) {
                throw new BadRequestHttpException('No data sent.');
            }

            if (! property_exists($data, 'name') || empty($data->name)) {
                throw new BadRequestHttpException('name cannot be empty.');
            }

            property_exists($data, 'name') && is_string($data->name) ? $product->setName($data->name) : false;
            property_exists($data, 'price') ? $product->setPrice(intval($data->price)) : false;

            $this->em->flush();
            $data = $this->serializer->serialize($product, JsonEncoder::FORMAT, ['groups' => 'list_items']);

            return new JsonResponse($data, Response::HTTP_OK, [], true);
        } catch (Throwable $th) {
            throw new BadRequestHttpException($th->getMessage(), $th);
        }
    }

    /**
     * @Rest\Get("/products", name="findAllProducts")
     */
    public function findAll(): JsonResponse
    {
        $products = $this->em->getRepository(Product::class)->findBy([], ['name' => 'ASC']);
        $data = $this->serializer->serialize($products, JsonEncoder::FORMAT, ['groups' => 'list_items']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
