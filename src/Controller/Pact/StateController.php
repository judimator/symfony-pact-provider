<?php

declare(strict_types=1);

namespace App\Controller\Pact;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StateController extends AbstractController
{
    #[Route('/states', name: 'post_state', methods: ['POST'])]
    public function postState(EntityManagerInterface $em, Request $request): Response
    {
        $content = $request->getContent();
        $params = json_decode($content, true);

        switch ($params['state']) {
            case 'Product exists':
                $em->getConnection()->executeQuery('DELETE FROM products;');

                ['sku' => $sku, 'name' => $name] = $params['params'];

                $em->persist(new Product($name, (string) $sku));
                $em->flush();

                break;
        }

        return new Response('OK');
    }
}
