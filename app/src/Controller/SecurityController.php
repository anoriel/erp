<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

use function assert;

/**
 * @Route("/api")
 */
final class SecurityController extends AbstractController
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

  /**
   * @Route("/security/login", name="login")
   */
    public function loginAction(): JsonResponse
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $userClone = clone $user;
        $userClone->setPassword('');
        $data = $this->serializer->serialize($userClone, JsonEncoder::FORMAT);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

  /**
   * @throws RuntimeException
   *
   * @Route("/security/logout", name="logout")
   */
    public function logoutAction(): void
    {
        throw new RuntimeException('This should not be reached!');
    }
}
