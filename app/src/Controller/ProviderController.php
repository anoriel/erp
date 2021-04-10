<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Provider;
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
use function is_object;
use function is_string;
use function property_exists;
use function Safe\json_decode;

/**
 * @Rest\Route("/api")
 * @IsGranted("IS_AUTHENTICATED_FULLY")
 */
final class ProviderController extends AbstractController
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
     * @Rest\Post("/providers", name="createProvider")
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

        $address = property_exists($data, 'address') ? (string) $data->address : '';
        $country = property_exists($data, 'country') ? (string) $data->country : 'France';

        $provider = new Provider();
        $provider->setAddress($address);
        $provider->setCountry($country);
        $provider->setName($name);
        $this->em->persist($provider);
        $this->em->flush();
        $data = $this->serializer->serialize($provider, JsonEncoder::FORMAT, ['groups' => 'list_items']);

        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    /**
     * @throws BadRequestHttpException
     *
     * @Rest\Delete("/providers/{id}", name="deleteProvider")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(string $id, Request $request): JsonResponse
    {
        if (empty($id)) {
            throw new BadRequestHttpException('id cannot be empty');
        }

        try {
            $provider = $this->em->getRepository(Provider::class)->findOneBy(['id' => $id]);
            assert($provider instanceof Provider);

            // item not found
            if (empty($provider)) {
                $data = $this->serializer->serialize([
                    'message' => 'Item not found ID="' . $id . '"',
                ], JsonEncoder::FORMAT);

                return new JsonResponse($data, Response::HTTP_NOT_FOUND, [], true);
            }

            $this->em->remove($provider);

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
     * @Rest\Put("/providers/{id}", name="updateProvider")
     * @IsGranted("ROLE_ADMIN")
     */
    public function update(string $id, Request $request): JsonResponse
    {
        if (empty($id)) {
            throw new BadRequestHttpException('id cannot be empty');
        }

        try {
            $provider = $this->em->getRepository(Provider::class)->findOneBy(['id' => $id]);
            assert($provider instanceof Provider);

            // item not found
            if (empty($provider)) {
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

            property_exists($data, 'name') && is_string($data->name) ? $provider->setName($data->name) : false;
            property_exists($data, 'address') && is_string($data->address) ? $provider->setAddress($data->address) : false;
            property_exists($data, 'country') && is_string($data->country) ? $provider->setCountry($data->country) : false;

            $this->em->flush();
            $data = $this->serializer->serialize($provider, JsonEncoder::FORMAT, ['groups' => 'list_items']);

            return new JsonResponse($data, Response::HTTP_OK, [], true);
        } catch (Throwable $th) {
            throw new BadRequestHttpException($th->getMessage(), $th);
        }
    }

    /**
     * @Rest\Get("/providers", name="findAllProviders")
     */
    public function findAll(): JsonResponse
    {
        $providers = $this->em->getRepository(Provider::class)->findBy([], ['name' => 'ASC']);
        $data = $this->serializer->serialize($providers, JsonEncoder::FORMAT, ['groups' => 'list_items']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
