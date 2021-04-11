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
use function is_int;
use function is_object;
use function is_string;
use function property_exists;
use function Safe\json_decode;

/**
 * @Rest\Route("/api")
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
final class CompanyController extends AbstractController
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
     * @Rest\Post("/companies", name="createCompany")
     * @IsGranted("ROLE_ADMIN")
     */
    public function createAction(Request $request): JsonResponse
    {
        $data = json_decode((string) $request->getContent());

        if ($data === null || ! is_object($data)) {
            throw new BadRequestHttpException('No data sent.');
        }

        if (! property_exists($data, 'name')) {
            throw new BadRequestHttpException('name cannot be empty.');
        }

        $name = (string) $data->name;

        $balance = property_exists($data, 'balance') ? intval($data->balance) : 0;
        $country = property_exists($data, 'country') ? (string) $data->country : 'France';

        $company = new Company();
        $company->setBalance($balance);
        $company->setCountry($country);
        $company->setName($name);
        $this->em->persist($company);

        // adding all product to this company
        foreach ($this->em->getRepository(Product::class)->findAll() as $product) {
            $stockByCompany = new StockByCompany();
            $stockByCompany->setCompany($company);
            $stockByCompany->setProduct($product);
            $stockByCompany->setStock(0);
            $this->em->persist($stockByCompany);
        }

        $this->em->flush();
        $data = $this->serializer->serialize($company, JsonEncoder::FORMAT, ['groups' => 'list_items']);

        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    /**
     * @throws BadRequestHttpException
     *
     * @Rest\Delete("/companies/{id}", name="deleteCompany")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(string $id, Request $request): JsonResponse
    {
        if (empty($id)) {
            throw new BadRequestHttpException('id cannot be empty');
        }

        try {
            $company = $this->em->getRepository(Company::class)->findOneBy(['id' => $id]);

            // item not found
            if (empty($company)) {
                $data = $this->serializer->serialize([
                    'message' => 'Item not found ID="' . $id . '"',
                ], JsonEncoder::FORMAT);

                return new JsonResponse($data, Response::HTTP_NOT_FOUND, [], true);
            }

            assert($company instanceof Company);

            $this->em->remove($company);

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
     * @Rest\Put("/companies/{id}", name="updateCompany")
     * @IsGranted("ROLE_ADMIN")
     */
    public function update(string $id, Request $request): JsonResponse
    {
        if (empty($id)) {
            throw new BadRequestHttpException('id cannot be empty');
        }

        try {
            $company = $this->em->getRepository(Company::class)->findOneBy(['id' => $id]);

            // item not found
            if (empty($company)) {
                $data = $this->serializer->serialize([
                    'message' => 'Item not found ID="' . $id . '"',
                ], JsonEncoder::FORMAT);

                return new JsonResponse($data, Response::HTTP_NOT_FOUND, [], true);
            }

            assert($company instanceof Company);

            $data = json_decode((string) $request->getContent());

            if ($data === null || ! is_object($data)) {
                throw new BadRequestHttpException('No data sent.');
            }

            if (! property_exists($data, 'name') || empty($data->name)) {
                throw new BadRequestHttpException('name cannot be empty.');
            }

            property_exists($data, 'name') && is_string($data->name) ? $company->setName($data->name) : false;
            property_exists($data, 'balance') && (is_string($data->balance) || is_int($data->balance)) ? $company->setBalance(intval($data->balance)) : false;
            property_exists($data, 'country') && is_string($data->country) ? $company->setCountry($data->country) : false;

            $this->em->flush();
            $data = $this->serializer->serialize($company, JsonEncoder::FORMAT, ['groups' => 'list_items']);

            return new JsonResponse($data, Response::HTTP_OK, [], true);
        } catch (Throwable $th) {
            throw new BadRequestHttpException($th->getMessage(), $th);
        }
    }

    /**
     * @Rest\Get("/companies", name="findAllCompanies")
     */
    public function findAllAction(): JsonResponse
    {
        $companies = $this->em->getRepository(Company::class)->findBy([], ['name' => 'ASC']);
        $data = $this->serializer->serialize($companies, JsonEncoder::FORMAT, ['groups' => 'list_items']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
