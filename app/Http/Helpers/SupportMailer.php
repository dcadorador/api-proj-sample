<?php

namespace App\Api\V1\Helpers;

class SupportMailer extends AbstractMailer {

    public function sendPaymentMail($order,$d)
    {
        $to = $order->concept->feedback_email;
        $subject = 'Customer Payment';

        $data = [
            'email' => $d['customer_email'],
            'order_id' => $order->id,
            'order_code' => $order->code,
            'fort_id' => $d['fort_id'],
            'amount' => ((int)$d['amount'] / 100) . ' ' . 'SAR',
            'merchant_reference' => $d['merchant_reference']
        ];

        $this->sendSupport($to,$subject,'payfort',$data);
        return null;
    }

    public function sendFailedCapture($order,$d)
    {
        $to = $order->concept->feedback_email;
        $subject = ucwords($order->concept->label).' Payfort Payment Capture Failed';

        $data = [
            'order_id' => $order->id,
            'customer_name' => ucwords($order->customer->first_name).' '.ucwords($order->customer->last_name),
            'customer_mobile' => $order->customer->mobile,
            'email' => $d['customer_email'],
            'fort_id' => $d['fort_id'],
            'amount' => ((int)$d['amount'] / 100).' '.'SAR',
            'merchant_reference' => $d['merchant_reference'],
            'response_message' => $order->concept->default_pos
        ];

        $this->sendSupport($to,$subject,'payfortfailed',$data);
        return null;
    }

    public function sendFailedPosPosting($order,$d)
    {
        $pos = $order->concept->default_pos;
        $to = $order->concept->feedback_email;
        $subject = ucwords($order->concept->label).' Order POS Posting Failed';

        $data = [
            'order_id' => $order->id,
            'customer_name' => ucwords($order->customer->first_name).' '.ucwords($order->customer->last_name),
            'customer_mobile' => $order->customer->mobile,
            'email' => $d['customer_email'],
            'fort_id' => $d['fort_id'],
            'amount' => ((int)$d['amount'] / 100).' '.'SAR',
            'merchant_reference' => $d['merchant_reference'],
            'pos' => $pos
        ];

        $this->sendSupport($to,$subject,'failedorder',$data);
        return null;
    }

    public function sendFailedPosPostingSD($order, $fs_order, $request)
    {

        $to = 'support@skylinedynamics.com';
        $subject = 'Order Posting Failed - Concept: '.ucwords($order->concept->label);
        $data = [
            'customer_name' => ucwords($order->customer->first_name).' '.ucwords($order->customer->last_name),
            'customer_mobile' => $order->customer->mobile,
            'email' => $order->customer->email,
            'pos' => $order->concept->default_pos,
            'order_request' => json_encode($request->all()),
            'pos_response' => json_encode($fs_order)
        ];

        $this->sendSupport($to,$subject,'sdfailedorder',$data);
        return null;
    }

    public function sendFailedPosPostingCustomer($order, $d)
    {
        $to = $d['customer_email'];
        $subject = 'Order Processing Failed';
        $concept = $order->concept;
        $from = $concept->feedback_email;

        $data = [
            'customer_name' => ucwords($order->customer->first_name).' '.ucwords($order->customer->last_name),
            'concept_email' => $concept->feedback_email,
            'concept_label' => $concept->label,
            'order_items' => $order->orderItems
        ];

        $this->send($to,$from,$subject,'posfailed',$data,ucwords($concept->label));
        return null;
    }

    public function sendHyperpayPaymentMail($order,$d)
    {
        $to = $d['customer']['email'];
        $subject = 'Customer Payment';

        $data = [
            'email' => $d['customer']['email'],
            'order_id' => $order->id,
            'order_code' => $order->code,
            'amount' => ((int)$d['amount']) . ' ' . 'SAR',
            'merchant_reference' => $d['id']
        ];

        $this->sendSupport($to,$subject,'hyperpaysuccess',$data);
        return null;
    }
}
