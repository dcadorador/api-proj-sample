<?php
namespace App\Api\V1\Helpers;


abstract class AbstractMailer {

    public function sendSupport($to, $subject, $view, $data = [], $identifier = 'Skyline Dynamics Support')
    {
        $from = 'support@skylinedynamics.com';
        if($view == 'sdfailedorder') {
            $bcc = ['hwhitmore@skylinedynamics.com'];
            //$bcc = ['aadorador@skylinedynamics.com'];
        } else {
            $bcc = ['support@skylinedynamics.com'];
        }
        app('log')->debug('SENDING EMAIL TO: '.$to.' with SUBJECT: '.$subject);
        app('mailer')->send($view, $data, function($message) use ($to, $from, $subject, $identifier, $bcc){
            $message->from($from,$identifier)
                ->bcc($bcc)
                ->to($to)
                ->subject($subject);
        });

        return null;
    }

    public function send($to, $from, $subject, $view, $data = [], $identifier)
    {
        $bcc = ['support@skylinedynamics.com'];

        app('log')->debug('SENDING EMAIL TO: '.$to.' with SUBJECT: '.$subject);
        app('mailer')->send($view, $data, function($message) use ($to, $from, $subject, $identifier, $bcc){
            $message->from($from,$identifier)
                ->bcc($bcc)
                ->to($to)
                ->subject($subject);
        });

        return null;
    }
}