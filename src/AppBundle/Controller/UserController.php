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
        $em = $this->get('doctrine.orm.entity_manager');
        $params = array();
        $content = $request->getContent();
        if (!empty($content)) {
            $params = json_decode($content, true);
        }

        $user = $em
            ->getRepository('AppBundle:User')
            ->findOneBy(
                ["email"=>$request->get('email')]
            );

        if (!empty($user)) {
            return new JsonResponse(
                ['message' => 'User exist'],
                Response::HTTP_CONFLICT,
                ['Access-Control-Allow-Origin' => '*' ]
            );
        }

        $user = new User();
        $user->setEmail($params['email']);
        $user->setName($params['name']);
        $user->setPassword($params['password']);
        $user->setCreationdate(new \DateTime());


        $em->persist($user);
        $em->flush();
        return new JsonResponse(
            [   'status' => Response::HTTP_CREATED,
                'message' => 'OK',
                'user' => $user->getName()],
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/user/login", name="user_login")
     * @Method({"POST"})
     */
    public function loginAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $params = array();
        $content = $request->getContent();
        if (!empty($content)) {
            $params = json_decode($content, true);
        }
        $user = $em
            ->getRepository('AppBundle:User')
            ->findOneBy(
                ["email"=> $params['email'],
                "password"=> $params['password']]
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
            ->where('c.user = '.$user->getId());
        $commissions = $qb->getQuery()->getSingleScalarResult();


        $formatted = [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'last_login' => $user->getLastlogin(),
            'commissions' => $commissions,
        ];

        return $this->jsonResponse($formatted, Response::HTTP_OK);
    }

    /**
     * @Route("/commissions/{userId}", name="get_user_commission")
     * @Method({"GET"})
     */
    public function getCommissionsAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $user = $em
            ->getRepository('AppBundle:User')
            ->find($request->get('userId'));

        $listCommissions = $em
            ->getRepository('AppBundle:Commission')
            ->findBy(array('user' => $user));

        if (empty($listCommissions)) {
            return $this->userNotFound();
        }

        $output = array();
        foreach ($listCommissions as $c) {
            $output[] = [
                'id' => $c->getId(),
                'cashback' => $c->getCashback(),
                'date' => $c->getDate()->format('Y-m-d')
            ];
        }

        return $this->jsonResponse($output, Response::HTTP_OK);
    }


    private function userNotFound()
    {
        return new JsonResponse(
            ['message' => 'User not found'],
            Response::HTTP_NOT_FOUND,
            ['generation_date' => date('Y-m-d H:i:s'),
            'Access-Control-Allow-Origin' => '*' ]
        );
    }

    private function jsonResponse($output, $status)
    {
        return new JsonResponse(
            $output,
            $status,
            ['generation_date' => date('Y-m-d H:i:s'),
                'Access-Control-Allow-Origin' => '*' ]
        );
    }
}