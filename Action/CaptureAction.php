<?php
namespace App\Payum\Action;

use Payum\Action\ActionInterface;
use Payum\Request\CaptureRequest;

class CaptureAction implements ActionInterface
{
    protected $gatewayUsername;

    protected $gatewayPassword;

    public function __construct($gatewayUsername, $gatewayPassword)
    {
        $this->gatewayUsername = $gatewayUsername;
        $this->gatewayPassword = $gatewayPassword;
    }

    public function execute($request)
    {
        $model = $request->getModel();

        if (isset($model['amount']) && isset($model['currency'])) {

            //do purchase call to the payment gateway using username and password.

            /*Capture Buyer*/

            $buyer = new DineroMailBuyer();
            $buyer->setName($model['Name']);
            $buyer->setLastName($model['LastName']);
            $buyer->setAddress($model['Address']);
            $buyer->setCity($model['City']);
            $buyer->setCountry($model['Country']);
            $buyer->setEmail($model['Email']);
            $buyer->setPhone($model['Phone']);

            /* Capture Items */

            foreach($model['Item'] as $item){

                $currentItem = new DineroMailItem();
                $currentItem->setCode($item['Code']);
                $currentItem->setName($item['Name']);
                $currentItem->setDescription($item['Description']);

                if(isset($item['Quantity']))
                $currentItem->setQuantity($item['Quantity']);

                $currentItem->setAmount($item['Amount']);

                if(isset($item['Currency']))
                $currentItem->setCurrency($item['Currency']);

                $items[] = $currentItem;
            }

            /* Execute transaction */

            try {

                //call the webservice
                $transaction = new DineroMailAction();
                $transaction->doPaymentWithReference($items, $buyer, $model['TransactionId'],$model['Message'],$model['Subject']);
                DineroMailDumper::dump($transaction,10,true);

            } catch (DineroMailException $e) {

                // drive the exception
                DineroMailDumper::dump($e,10,true);
            }

            $model['status'] = 'success';
        } else {
            $model['status'] = 'error';
        }
    }

    public function supports($request)
    {
        return
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}