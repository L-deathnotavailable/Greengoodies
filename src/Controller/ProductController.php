<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_product_index')]
    public function index(): Response
    {
        return $this->render('product/index.html.twig');
    }

    #[Route('/product/{id}', name: 'app_product_show', requirements: ['id' => '\d+'])]
    public function show(Product $product): Response
    {
        if (!$product->isPublished()) {
            throw $this->createNotFoundException('Produit non disponible.');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
}