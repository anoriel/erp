<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Product;
use App\Entity\StockByCompany;
use App\Repository\StockByCompanyRepository;
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
use function is_bool;
use function is_object;
use function property_exists;
use function Safe\json_decode;

/**
 * @Rest\Route("/api")
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
final class StockByCompanyController extends AbstractController
{
    private EntityManagerInterface $em;

    private SerializerInterface $serializer;

    private StockByCompany $stockByCompany;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->em = $em;
        $this->serializer = $serializer;
    }

    /**
     * @throws BadRequestHttpException
     *
     * @Rest\Post("/stocksByCompany", name="createStockByCompany")
     * @IsGranted("ROLE_ADMIN")
     */
    public function create(Request $request): JsonResponse
    {
        $data = json_decode((string) $request->getContent());

        if ($data === null || ! is_object($data)) {
            throw new BadRequestHttpException('No data sent.');
        }

        $this->stockByCompany = new StockByCompany();
        $this->em->persist($this->stockByCompany);

        $update = $this->updateItem($data);
        if (! is_bool($update)) {
            $data = $this->serializer->serialize(['message' => $update], JsonEncoder::FORMAT);

            return new JsonResponse($data);
        }

        $data = $this->serializer->serialize($this->stockByCompany, JsonEncoder::FORMAT, ['groups' => 'list_items']);

        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    /**
     * @throws BadRequestHttpException
     *
     * @Rest\Delete("/stocksByCompany/{id}", name="deleteStockByCompany")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(string $id, Request $request): JsonResponse
    {
        if (empty($id)) {
            throw new BadRequestHttpException('id cannot be empty');
        }

        try {
            $stockByCompany = $this->em->getRepository(StockByCompany::class)->findOneBy(['id' => $id]);

            // item not found
            if (empty($stockByCompany)) {
                $data = $this->serializer->serialize([
                    'message' => 'Item not found ID="' . $id . '"',
                ], JsonEncoder::FORMAT);

                return new JsonResponse($data, Response::HTTP_NOT_FOUND, [], true);
            }

            assert($stockByCompany instanceof StockByCompany);

            $this->em->remove($stockByCompany);

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
     * @Rest\Put("/stocksByCompany/{id}", name="updateStockByCompany")
     * @IsGranted("ROLE_ADMIN")
     */
    public function update(string $id, Request $request): JsonResponse
    {
        if (empty($id)) {
            throw new BadRequestHttpException('id cannot be empty');
        }

        try {
            $stockByCompany = $this->em->getRepository(StockByCompany::class)->findOneBy(['id' => $id]);

            // item not found
            if (empty($stockByCompany)) {
                $data = $this->serializer->serialize([
                    'message' => 'Item not found ID="' . $id . '"',
                ], JsonEncoder::FORMAT);

                return new JsonResponse($data, Response::HTTP_NOT_FOUND, [], true);
            }

            assert($stockByCompany instanceof StockByCompany);

            $this->stockByCompany = $stockByCompany;

            $data = json_decode((string) $request->getContent());

            if ($data === null || ! is_object($data)) {
                throw new BadRequestHttpException('No data sent.');
            }

            $update = $this->updateItem($data);
            if (! is_bool($update)) {
                $data = $this->serializer->serialize([
                    'message' => $update->message,
                ], JsonEncoder::FORMAT);

                return new JsonResponse($data, $update->error, [], true);
            }

            $data = $this->serializer->serialize($this->stockByCompany, JsonEncoder::FORMAT, ['groups' => 'list_items']);

            return new JsonResponse($data, Response::HTTP_OK, [], true);
        } catch (Throwable $th) {
            throw new BadRequestHttpException($th->getMessage(), $th);
        }
    }

    /**
     * @Rest\Get("/stocksByCompany", name="findAllStocksByCompany")
     */
    public function findAll(): JsonResponse
    {
        $stocksByCompany = $this->em->getRepository(StockByCompany::class)->findBy([], ['id' => 'ASC']);
        $data = $this->serializer->serialize($stocksByCompany, JsonEncoder::FORMAT, ['groups' => 'list_items']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * method to update stockByCompany, used in create or update
     *
     * @return mixed
     */
    private function updateItem(object $data)
    {
        $companyId = property_exists($data, 'companyId') ? (string) $data->companyId : '';
        $company = $this->em->getRepository(Company::class)->findOneBy(['id' => $companyId]);
        // item not found
        if (empty($company)) {
            return [
                'message' => 'Company Item not found ID="' . $companyId . '"',
                'error' => Response::HTTP_NOT_FOUND,
            ];
        }

        assert($company instanceof Company);

        $productId = property_exists($data, 'productId') ? (string) $data->productId : '';
        $product = $this->em->getRepository(Product::class)->findOneBy(['id' => $productId]);

        // item not found
        if (empty($product)) {
            return [
                'message' => 'Product Item not found ID="' . $productId . '"',
                'error' => Response::HTTP_NOT_FOUND,
            ];
        }

        assert($product instanceof Product);

        $quantity = property_exists($data, 'quantity') ? intval($data->quantity) : 0;

        $this->stockByCompany->setCompany($company);
        $this->stockByCompany->setProduct($product);
        $this->stockByCompany->setStock($quantity);

        $this->em->flush();

        return true;
    }

    /**
     * @Rest\Get("/stocksByCompany/getByCompany/{companyId}", name="getStocksByCompanyId")
     */
    public function getByCompany(string $companyId): JsonResponse
    {
        $repo = $this->em->getRepository(StockByCompany::class);
        assert($repo instanceof StockByCompanyRepository);
        $stockByCompanys = $repo->findByCompanyId($companyId);

        $data = $this->serializer->serialize($stockByCompanys, JsonEncoder::FORMAT, ['groups' => 'list_items']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
