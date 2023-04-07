<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/categories', name: 'categories_')]
class CategoriesController extends AbstractController
{
    #[Route('/{slug}', name: 'list')]
    public function details(Categories $category, ProductsRepository $productsRepository): Response
    {
        // on va chercher la liste des produits de la catégorie

        $products = $productsRepository->findProdcutsPaginated(1, $category->getSlug(), 3);

        return $this->render('categories/list.html.twig', compact('category','products'));
    }
}
