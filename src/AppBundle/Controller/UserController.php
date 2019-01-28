<?php
/**
 * Created by PhpStorm.
 * User: FAT3665
 * Date: 27/01/2019
 * Time: 00:59
 */

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Entity\User;

class UserController extends Controller
{

    /**
     * @Route("/user/register", name="user_register")
     * @Method({"POST"})
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $user->setEmail($request->get('email'));
        $user->setName($request->get('name'));
        $user->setCreationdate(new \DateTime());

        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($user);
        $em->flush();
        return new JsonResponse(
            ['user' => $user->getName()],
            Response::HTTP_CREATED,
            ['generation_date' => date('Y-m-d H:i:s')]
        );
    }

    /**
     * @Route("/user/login", name="user_login")
     * @Method({"POST"})
     */
    public function loginAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('AppBundle:User')
            ->findOneBy(
                ["email"=>$request->get('email'),
                "password"=>$request->get('password')]
            );

        if (empty($user)) {
            return $this->userNotFound();
        }
        $user->setLastlogin(new \DateTime());

        $em->persist($user);
        $em->flush();
        return new JsonResponse(
            ['message' => 'User connected',
            'userId'=> $user->getId()],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/user/{userId}", name="get_user")
     * @Method({"GET"})
     */
    public function getUserAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $user = $em
            ->getRepository('AppBundle:User')
            ->find($request->get('userId'));

        if (empty($user)) {
            return $this->userNotFound();
        }

        $qb = $em->createQueryBuilder();
        $qb->select('count(c)')
            ->from('AppBundle:Commission', 'c')
            ->where('c.iduser = '.$user->getId());
        $commissions = $qb->getQuery()->getSingleScalarResult();


        $formatted = [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'last_login' => $user->getLastlogin(),
            'commissions' => $commissions,
        ];

        return new JsonResponse(
            $formatted,
            Response::HTTP_OK,
            ['generation_date' => date('Y-m-d H:i:s')]
        );
    }

    private function userNotFound()
    {
        return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
    }
}