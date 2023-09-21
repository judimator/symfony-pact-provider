<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'get_product_by_sku', methods: ['GET'])]
    public function getProductBySku(Request $request, ProductRepository $repository): Response
    {
        if (!$request->query->has('sku')) {
            return new Response('Bad Request', Response::HTTP_BAD_REQUEST);
        }

        $product = $repository->getBySku((string) $request->query->get('sku'));

        return new JsonResponse($product->toArray());
    }
}
