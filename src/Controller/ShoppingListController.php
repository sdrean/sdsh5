<?php


namespace App\Controller;


use App\Entity\Product;
use App\Entity\ShoppingList;
use App\Entity\ShoppingListItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Zone;

/**
 * @Route("/api")
 * Class ShoppingListController
 * @package App\Controller
 */
class ShoppingListController extends AbstractController
{
    /**
     * @Route("/zones", name="api_zones")
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function getZones(EntityManagerInterface $em)
    {
        $list = $em
            ->getRepository('App\\Entity\\Zone')
            ->findBy([],[
                'order' => 'ASC'
        ]);

        $return = [];
        if(count($list) > 0){
            /** @var Zone $zone */
            foreach($list as $zone){
                $return[] = [
                    'id' => $zone->getId(),
                    'name' => $zone->getName(),
                    'icon' => $zone->getIcon(),
                    'color' => $zone->getColor(),
                ];
            }
        }

        return new JsonResponse([
            'valid' => true,
            'result' => $return
        ]);
    }

    /**
     * @Route("/products", name="api_products")
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function getProducts(EntityManagerInterface $em)
    {
        $list = $em
            ->getRepository('App\\Entity\\Product')
            ->findBy([],[
                'name' => 'ASC'
            ]);
        $return = [];
        if(count($list) > 0){
            /** @var Product $product */
            foreach($list as $product){
                $return[] = [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'zone' => $product->getZone()->getId()
                ];
            }
        }
        return new JsonResponse([
            'valid' => true,
            'result' => $return
        ]);
    }

    /**
     * @Route("/product/add", name="api_add_product")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function addProduct(Request $request, EntityManagerInterface $em)
    {
        $params = array();
        $content = $request->getContent();

        if (!empty($content)) {
            $params = json_decode($content, true);
        }

        if (!array_key_exists('name',$params)) {
            return new JsonResponse(["valid" => false, "error" => "Missing name parameter"]);
        }

        if (!array_key_exists('zone',$params)) {
            return new JsonResponse(["valid" => false, "error" => "Missing zone parameter"]);
        }

        $zone = $em->getRepository('App\\Entity\\Zone')->find($params['zone']);

        $product = new Product();
        $product
            ->setName($params['name'])
            ->setZone($zone);
        $em->persist($product);
        $em->flush();

        return new JsonResponse([
            'valid' => true,
            'result' => [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'zone' => $product->getZone()->getId()
            ]
        ]);
    }

    /**
     * @Route("/shopping-list/current")
     * @param EntityManagerInterface $em
     * @return JsonResponse
     * @throws \Exception
     */
    public function getCurrentList(EntityManagerInterface $em)
    {
        $shoppingListUtil = $em->getRepository('App\\Entity\\ShoppingList');
        $currentList = $shoppingListUtil->findCurrentList();
        if($currentList == null){
            $currentList = new ShoppingList();
            $currentList->setStatus('OPEN');
            $currentList->setUpdateDate(new \DateTime());
            $currentList->setCreateDate(new \DateTime());
            $em->persist($currentList);
            $em->flush();
        }

        $items = $em->getRepository('App\\Entity\\ShoppingListItem')->findCurrentListItem($currentList);

        $return  = [
            'id' => $currentList->getId(),
            'status' => $currentList->getStatus(),
            'items' => []
        ];

        if(count($items) > 0){
            /** @var ShoppingListItem $item */
            foreach ($items as $item){
                $return['items'][] = [
                    'id' => $item->getId(),
                    'product' => $item->getProduct()->getName()
                ];
            }
        }

        // Product retreiving
        $products = $em->getRepository('App\\Entity\\Product')->findAllForJson();

        // Zone retreiving
        $zones = $em->getRepository('App\\Entity\\Zone')->findAllForJson();

        return new JsonResponse([
            'valid' => true,
            'products' => $products,
            'zones' => $zones,
            'shoppingList' => $return
        ]);
    }

    /**
     * @Route("/shopping-list/product/remove")
     * @param EntityManagerInterface $em
     * @param Request $request
     * @return JsonResponse
     */
    public function removeFromList(EntityManagerInterface $em, Request $request)
    {
        $params = array();
        $content = $request->getContent();

        if (!empty($content)) {
            $params = json_decode($content, true);
        }

        if (!array_key_exists('shoppingListItemId',$params)) {
            return new JsonResponse(["valid" => false, "error" => "Missing shopping list item parameter"]);
        }

        $shoppingListItem = $em
            ->getRepository('App\\Entity\\ShoppingListItem')
            ->find($params['shoppingListItemId']);

        if($shoppingListItem == null){
            return new JsonResponse(["valid" => false, "error" => "Shopping list item not found"]);
        }

        $em->remove($shoppingListItem);
        $em->flush();

        return new JsonResponse(['valid' => true, 'result' => 'OK']);
    }

    /**
     * @Route("/shopping-list/product/add")
     * @param EntityManagerInterface $em
     * @param Request $request
     * @return JsonResponse
     */
    public function addToList(EntityManagerInterface $em, Request $request)
    {
        $params = array();
        $content = $request->getContent();

        if (!empty($content)) {
            $params = json_decode($content, true);
        }

        if (!array_key_exists('shoppingListId',$params)) {
            return new JsonResponse(["valid" => false, "error" => "Missing shopping list parameter"]);
        }

        if (!array_key_exists('productId',$params)) {
            return new JsonResponse(["valid" => false, "error" => "Missing product parameter"]);
        }

        $shoppingList = $em->getRepository('App\\Entity\\ShoppingList')->find($params['shoppingListId']);
        /** @var Product $product */
        $product = $em->getRepository('App\\Entity\\Product')->find($params['productId']);

        $shoppingListItem = new ShoppingListItem();
        $shoppingListItem->setStatus('NEW');
        $shoppingListItem->setShoppingList($shoppingList);
        $shoppingListItem->setProduct($product);
        $em->persist($shoppingListItem);
        $em->flush();

        $order = str_pad($shoppingListItem->getProduct()->getZone()->getOrder(),10,'0',STR_PAD_LEFT).
            '-'.$shoppingListItem->getProduct()->getName();

        return new JsonResponse([
            'valid' => true,
            'result' => [
                'id' => $product->getId(),
                'product' => $product->getName(),
                'zoneId' => $product->getZone()->getId(),
                'status' => $shoppingListItem->getStatus(),
                'order' => $order
            ]
        ]);
    }

    /**
     * @Route("/shopping-list/product/status")
     * @param EntityManagerInterface $em
     * @param Request $request
     * @return JsonResponse
     */
    public function changeStatus(EntityManagerInterface $em, Request $request)
    {
        $params = array();
        $content = $request->getContent();

        if (!empty($content)) {
            $params = json_decode($content, true);
        }

        if (!array_key_exists('shoppingListItemId',$params)) {
            return new JsonResponse(["valid" => false, "error" => "Missing shopping list parameter"]);
        }

        if (!array_key_exists('status',$params)) {
            return new JsonResponse(["valid" => false, "error" => "Missing new status for product parameter"]);
        }

        /** @var ShoppingListItem $shoppingListItem */
        $shoppingListItem = $em
            ->getRepository('App\\Entity\\ShoppingListItem')
            ->find($params['shoppingListItemId']);

        if($shoppingListItem == null){
            return new JsonResponse(["valid" => false, "error" => "Shopping list item not found"]);
        }

        $shoppingListItem->setStatus($params['status']);

        $em->persist($shoppingListItem);
        $em->flush();

        return new JsonResponse(["valid" => true, "result" => $params['status']]);
    }
}
