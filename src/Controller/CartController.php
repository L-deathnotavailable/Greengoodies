<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(RequestStack $requestStack, ProductRepository $productRepository): Response
    {
        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        $cartItems = [];
        $total = 0;

        foreach ($cart as $productId => $quantity) {
            $product = $productRepository->find($productId);

            if (!$product) {
                continue;
            }

            $cartItems[] = [
                'product' => $product,
                'quantity' => $quantity,
                'lineTotal' => $product->getPrice() * $quantity,
            ];

            $total += $product->getPrice() * $quantity;
        }

        return $this->render('cart/index.html.twig', [
            'items' => $cartItems,
            'total' => $total,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add', requirements: ['id' => '\d+'])]
    public function add(Product $product, RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        $productId = $product->getId();

        if (!isset($cart[$productId])) {
            $cart[$productId] = 1;
        } else {
            $cart[$productId]++;
        }

        $session->set('cart', $cart);

        $this->addFlash('success', 'Produit ajouté au panier.');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove', requirements: ['id' => '\d+'])]
    public function remove(Product $product, RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        $productId = $product->getId();

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/clear', name: 'app_cart_clear')]
    public function clear(RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        $session->remove('cart');

        $this->addFlash('success', 'Panier vidé.');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/checkout', name: 'app_cart_checkout', methods: ['POST'])]
    public function checkout(
        RequestStack $requestStack,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        // Si pas connecté -> on garde le panier en session et on redirige vers login
        if (!$user instanceof User) {
            $this->addFlash('error', 'Merci de vous connecter avant de valider votre commande.');
            return $this->redirectToRoute('app_login');
        }

        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        if (empty($cart)) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart');
        }

        $order = new Order();
        $order->setUser($user);
        $order->setStatus('created');

        $total = 0;

        foreach ($cart as $productId => $quantity) {
            $product = $productRepository->find($productId);

            if (!$product || !$product->isPublished()) {
                continue;
            }

            $orderItem = new OrderItem();
            $orderItem->setParentOrder($order);
            $orderItem->setProduct($product);
            $orderItem->setQuantity($quantity);
            $orderItem->setUnitPrice($product->getPrice());

            $entityManager->persist($orderItem);

            $total += $product->getPrice() * $quantity;
        }

        $order->setTotal($total);

        $entityManager->persist($order);
        $entityManager->flush();

        // on vide le panier après création de la commande
        $session->remove('cart');

        $this->addFlash('success', 'Votre commande a bien été enregistrée.');

        return $this->redirectToRoute('app_account');
    }
}