<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\Provider;
use App\Entity\StockByCompany;
use App\Entity\Transaction;
use App\Repository\TransactionRepository;
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
final class TransactionController extends AbstractController
{
    private EntityManagerInterface $em;

    private SerializerInterface $serializer;

    private Transaction $transaction;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->em = $em;
        $this->serializer = $serializer;
    }

    /**
     * @throws BadRequestHttpException
     *
     * @Rest\Post("/transactions", name="createTransaction")
     * @IsGranted("ROLE_ADMIN")
     */
    public function create(Request $request): JsonResponse
    {
        $data = json_decode((string) $request->getContent());

        if ($data === null || ! is_object($data)) {
            throw new BadRequestHttpException('No data sent.');
        }

        $this->transaction = new Transaction();
        $this->em->persist($this->transaction);

        $update = $this->updateItem($data, true);
        if (! is_bool($update)) {
            $data = $this->serializer->serialize(['message' => $update], JsonEncoder::FORMAT);

            return new JsonResponse($data);
        }

        $data = $this->serializer->serialize($this->transaction, JsonEncoder::FORMAT, ['groups' => 'list_items']);

        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    /**
     * @throws BadRequestHttpException
     *
     * @Rest\Delete("/transactions/{id}", name="deleteTransaction")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(string $id, Request $request): JsonResponse
    {
        if (empty($id)) {
            throw new BadRequestHttpException('id cannot be empty');
        }

        try {
            $transaction = $this->em->getRepository(Transaction::class)->findOneBy(['id' => $id]);
            assert($transaction instanceof Transaction);

            // item not found
            if (empty($transaction)) {
                $data = $this->serializer->serialize([
                    'message' => 'Item not found ID="' . $id . '"',
                ], JsonEncoder::FORMAT);

                return new JsonResponse($data, Response::HTTP_NOT_FOUND, [], true);
            }

            $this->em->remove($transaction);

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
     * @Rest\Put("/transactions/{id}", name="updateTransaction")
     * @IsGranted("ROLE_ADMIN")
     */
    public function update(string $id, Request $request): JsonResponse
    {
        if (empty($id)) {
            throw new BadRequestHttpException('id cannot be empty');
        }

        try {
            $transaction = $this->em->getRepository(Transaction::class)->findOneBy(['id' => $id]);
            assert($transaction instanceof Transaction);

            // item not found
            if (empty($transaction)) {
                $data = $this->serializer->serialize([
                    'message' => 'Item not found ID="' . $id . '"',
                ], JsonEncoder::FORMAT);

                return new JsonResponse($data, Response::HTTP_NOT_FOUND, [], true);
            }

            $this->transaction = $transaction;

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

            $data = $this->serializer->serialize($this->transaction, JsonEncoder::FORMAT, ['groups' => 'list_items']);

            return new JsonResponse($data, Response::HTTP_OK, [], true);
        } catch (Throwable $th) {
            throw new BadRequestHttpException($th->getMessage(), $th);
        }
    }

    /**
     * @Rest\Get("/transactions", name="findAllTransactions")
     */
    public function findAll(): JsonResponse
    {
        $transactions = $this->em->getRepository(Transaction::class)->findBy([], ['id' => 'ASC']);
        $data = $this->serializer->serialize($transactions, JsonEncoder::FORMAT, ['groups' => 'list_items']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * method to update transaction, used in create or update
     *
     * @return mixed
     */
    private function updateItem(object $data, bool $updateStock = false)
    {
        $companyId = property_exists($data, 'companyId') ? (string) $data->companyId : '';
        $company = $this->em->getRepository(Company::class)->findOneBy(['id' => $companyId]);

        assert($company instanceof Company);
        // item not found
        if (empty($company)) {
            return [
                'message' => 'Company Item not found ID="' . $companyId . '"',
                'error' => Response::HTTP_NOT_FOUND,
            ];
        }

        $customer = null;
        if (property_exists($data, 'customerId') && $data->customerId !== null) {
            $customerId = (string) $data->customerId;
            $customer = $this->em->getRepository(Customer::class)->findOneBy(['id' => $customerId]);
            assert($customer instanceof Customer);
            // item not found
            if (empty($customer)) {
                return [
                    'message' => 'Customer Item not found ID="' . $customerId . '"',
                    'error' => Response::HTTP_NOT_FOUND,
                ];
            }
        }

        $provider = null;
        if (property_exists($data, 'providerId') && $data->providerId !== null) {
            $providerId = (string) $data->providerId;
            $provider = $this->em->getRepository(Provider::class)->findOneBy(['id' => $providerId]);
            assert($provider instanceof Provider);
            // item not found
            if (empty($provider)) {
                return [
                    'message' => 'Provider Item not found ID="' . $providerId . '"',
                    'error' => Response::HTTP_NOT_FOUND,
                ];
            }
        }

        if (empty($provider) && empty($customer)) {
            return [
                'message' => 'Provider or Customer Item must be provided',
                'error' => Response::HTTP_BAD_REQUEST,
            ];
        }

        $productId = property_exists($data, 'productId') ? (string) $data->productId : '';
        $product = $this->em->getRepository(Product::class)->findOneBy(['id' => $productId]);
        assert($product instanceof Product);

        // item not found
        if (empty($product)) {
            return [
                'message' => 'Product Item not found ID="' . $productId . '"',
                'error' => Response::HTTP_NOT_FOUND,
            ];
        }

        $quantity = property_exists($data, 'quantity') ? intval($data->quantity) : 0;

        $this->transaction->setCustomer($customer);
        $this->transaction->setProvider($provider);
        $this->transaction->setProduct($product);
        $this->transaction->setQuantity($quantity);
        $this->transaction->setCompany($company);

        //stock updates
        if ($updateStock) {
            $stockByCompany = $this->em->getRepository(StockByCompany::class)->findOneBy([
                'company' => $company,
                'product' => $product,
            ]);
            assert($stockByCompany instanceof StockByCompany);
            if ($stockByCompany !== null) {
                if ($customer !== null) { //it is a sale
                    $stockByCompany->setStock($stockByCompany->getStock() - $quantity);
                    $company->setBalance($company->getBalance() + $quantity * $product->getPrice());
                } elseif ($provider !== null) { //it is a purchase
                    $stockByCompany->setStock($stockByCompany->getStock() + $quantity);
                    $company->setBalance($company->getBalance() - $quantity * $product->getPrice());
                }
            }
        }

        $this->em->flush();

        return true;
    }

    /**
     * @Rest\Get("/transactions/getByCompany/{companyId}", name="getTransactionsByCompanyId")
     */
    public function getByCompany(string $companyId): JsonResponse
    {
        $repo = $this->em->getRepository(Transaction::class);
        assert($repo instanceof TransactionRepository);
        $stockByCompanys = $repo->findByCompanyId($companyId);

        $data = $this->serializer->serialize($stockByCompanys, JsonEncoder::FORMAT, ['groups' => 'list_items']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
