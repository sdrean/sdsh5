<?php

namespace App\Controller;

use App\Entity\Coordinates;
use App\Entity\Device;
use App\Entity\Point;
use App\Entity\PurchaseType;
use App\Entity\Receipt;
use App\Entity\ShoppingList;
use App\Entity\ShoppingListItem;
use App\Service\ShoppingListManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/api")
 * Class ApiController
 * @package App\Controller
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/getinit", name="get_init")
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function getInit(EntityManagerInterface $em, ShoppingListManager $shoppingListManager)
    {
        $ptUtil = $em->getRepository('App\\Entity\\PurchaseType');
        $all = $ptUtil->findBy([],['PurchaseOrder' => 'ASC']);

        $returnPT = [
            [
                'PurchaseTypeId' => 0,
                'PurchaseTypeName' => 'Sélectionner un type'
            ]
        ];
        if(count($all) > 0){
            /** @var PurchaseType $pu */
            foreach($all as $pu){
                $returnPT[] = [
                    'PurchaseTypeId' => $pu->getId(),
                    'PurchaseTypeName' => $pu->getPurchaseName(),
                    'StatColor' => $pu->getStatColor()
                ];
            }

        } else {
            return new JsonResponse(['valid' => false]);
        }

        $receiptUtil = $em->getRepository('App\\Entity\\Receipt');
        $list = $receiptUtil->findReceiptByMonth(date('Y-m'));

        $returnReceipt = [];
        if(count($list) > 0){
            /** @var Receipt $receipt */
            foreach ($list as $receipt){
                $returnReceipt[]  = [
                    'PurchaseType' => $receipt->getPurchaseType()->getPurchaseName(),
                    'PurchaseAmount' => $receipt->getAmount(),
                    'PurchaseDate' => $receipt->getPurchaseDate()->format('d/m/Y'),
                    'StatColor' => $receipt->getPurchaseType()->getStatColor(),
                ];
            }
        }

        $walletUtil = $em->getRepository('App\\Entity\\Wallet');
        $amount = $walletUtil->findByMonth(date('Y-m'));

        // Zone retreiving
        $zones = $em->getRepository('App\\Entity\\Zone')->findAllForJson();

        // Product retreiving
        $products = $em->getRepository('App\\Entity\\Product')->findAllForJson();

        // Current shopping list
        $shoppingList = $shoppingListManager->retreiveCurrentList();

        return new JsonResponse([
            'valid' => true,
            'purchaseTypes' => $returnPT,
            'receipt' => $returnReceipt,
            'walletAmount' => $amount,
            'zones' => $zones,
            'products' => $products,
            'shoppingList' => $shoppingList,
        ]);
    }

    /**
     * @Route("/getpurchasetype", name="get_purchase_type")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return JsonResponse
     * @throws \Exception
     */
    public function getPurchaseType(Request $request, EntityManagerInterface $em)
    {

        $ptUtil = $em->getRepository('App\\Entity\\PurchaseType');
        $all = $ptUtil->findAll();

        sleep(1);

        $return = [
            [
                'PurchaseTypeId' => 0,
                'PurchaseTypeName' => 'Sélectionner un type'
            ]
        ];
        if(count($all) > 0){
            /** @var PurchaseType $pu */
            foreach($all as $pu){
                $return[] = [
                    'PurchaseTypeId' => $pu->getId(),
                    'PurchaseTypeName' => $pu->getPurchaseName()
                ];
            }
            return new JsonResponse(['valid' => true,'result' => $return]);
        }


        return new JsonResponse(['valid' => false]);
    }

    /**
     * @Route("/getreceipt", name="get_receipt")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function getAllReceipt(Request $request, EntityManagerInterface $em)
    {
        $params = array();
        $content = $request->getContent();

        sleep(1);

        if (!empty($content))
        {
            $params = json_decode($content, true);
        }

        $mode = !array_key_exists('mode',$params) ? 'current-month' : $params['mode'];

        $receiptUtil = $em->getRepository('App\\Entity\\Receipt');
        $list = $receiptUtil->findReceiptByMonth(date('Y-m'));

        $return = [];
        if(count($list) > 0){
            /** @var Receipt $receipt */
            foreach ($list as $receipt){
                $return[]  = [
                    'PurchaseType' => $receipt->getPurchaseType()->getPurchaseName(),
                    'PurchaseAmount' => $receipt->getAmount(),
                    'PurchaseDate' => $receipt->getPurchaseDate()->format('d/m/Y'),
                ];
            }
        }
        return new JsonResponse(['valid' => true, 'result' => $return]);
    }

    /**
     * @Route("/sendamount", name="sendamount")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return JsonResponse
     * @throws \Exception
     */
    public function sendAmount(Request $request, EntityManagerInterface $em)
    {
        $params = array();
        $content = $request->getContent();

        sleep(1);

        if (!empty($content))
        {
            $params = json_decode($content, true);

        }

        if (!array_key_exists('ptid',$params)) {
            return new JsonResponse(["valid" => false, "error" => "Missing type parameter"]);
        }
        $pt = $em->getRepository('App\\Entity\\PurchaseType')->find($params['ptid']);

        $receipt = new Receipt();
        $receipt
            ->setPurchaseType($pt);
        $receipt
            ->setAmount($params['amount']);
        $receipt
            ->setPurchaseDate(new \DateTime());
        $em->persist($receipt);
        $em->flush();

        $return = [
            'PurchaseType' => $receipt->getPurchaseType()->getPurchaseName(),
            'PurchaseAmount' => $receipt->getAmount(),
            'PurchaseDate' => $receipt->getPurchaseDate()->format('d/m/Y'),
        ];

        return new JsonResponse(['valid' => true,'result' => $return]);
    }
}