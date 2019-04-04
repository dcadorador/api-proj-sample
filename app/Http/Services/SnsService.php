<?php

namespace App\Api\V1\Services;

use Aws\Sns\SnsClient;
use Aws\Sns\Exception;

class SnsService
{
    public function registerDevice($userData, $appArn, $deviceToken)
    {
        $sns = \AWS::createClient('Sns');

        try
        {
            $result = $sns->createPlatformEndpoint([
                'CustomUserData' => $userData,
                'PlatformApplicationArn' => $appArn, // REQUIRED
                'Token' => $deviceToken, // REQUIRED
            ]);
        }
        catch(\Aws\Sns\Exception\SnsException $e)
        {
            return [
                'error' => true,
                'error_msg' => $e->getMessage(),
                'error_code' => $e->getAwsErrorCode(),
                'error_type' => $e->getAwsErrorType()
            ];
        }

        return $result['EndpointArn'];
    }

    public function getEndPointAttributes($endpointArn,$token)
    {
        $sns = \AWS::createClient('Sns');

        try
        {
            $result = $sns->getEndpointAttributes([
                'EndpointArn' => $endpointArn,
            ]);
            return ($result['Attributes']['Token'] != $token || $result['Attributes']['Enabled'] == "false");
        }
        catch(\Aws\Sns\Exception\SnsException $e)
        {
            return -1;
        }
    }

    public function getEndPointAttributes2($endpointArn,$token)
    {
        $sns = \AWS::createClient('Sns');

        try
        {
            $result = $sns->getEndpointAttributes([
                'EndpointArn' => $endpointArn,
            ]);
            return $result;
        }
        catch(\Aws\Sns\Exception\SnsException $e)
        {
            $data = [];
            $data['Attributes']['Enabled'] = "false";
            if($e->getAwsErrorCode() == "NotFound"){
                return $data;
            }
            return false;
        }
    }

    public function deleteEndpoint($endpointArn){
        $sns = \AWS::createClient('Sns');
        try
        {
            $result = $sns->deleteEndpoint([
                'EndpointArn' => $endpointArn,
            ]);
            return $result;
        }
        catch(\Aws\Sns\Exception\SnsException $e)
        {
            return false;
        }
    }

    public function setEndpointAttributes($token,$enabled,$arn)
    {
        $sns = \AWS::createClient('Sns');

        try
        {
            $result = $sns->setEndpointAttributes([
                // Attributes is required
                'Attributes' => array(
                    // Associative array of custom 'String' key names
                    'Token' => $token,
                    'Enabled' => $enabled
                ),
                // EndpointArn is required
                'EndpointArn' => $arn,
            ]);
            return $result;
        }
        catch(\Aws\Sns\Exception\SnsException $e)
        {
            return false;
        }
    }

    public function setEndpointSubscription($endpoint,$protocol,$topicArn)
    {
        $sns = \AWS::createClient('Sns');

        try {
            $result = $sns->subscribe([
                'Endpoint' => $endpoint,
                'Protocol' => $protocol,
                'TopicArn' => $topicArn
            ]);
            return $result;
        }
        catch(\Aws\Sns\Exception\SubscriptionLimitExceededException $e) {
            app('log')->debug('Subscription Error: '.json_encode($e->getAwsErrorMessage()));
            return false;
        }
        catch(\Aws\Sns\Exception\InvalidParameterException $e) {
            app('log')->debug('Subscription Error: '.json_encode($e->getAwsErrorMessage()));
            return false;
        }
        catch(\Aws\Sns\Exception\InternalErrorException $e) {
            app('log')->debug('Subscription Error: '.json_encode($e->getAwsErrorMessage()));
            return false;
        }
        catch(\Aws\Sns\Exception\NotFoundException $e) {
            app('log')->debug('Subscription Error: '.json_encode($e->getAwsErrorMessage()));
            return false;
        }
        catch(\Aws\Sns\Exception\AuthorizationErrorException $e) {
            app('log')->debug('Subscription Error: '.json_encode($e->getAwsErrorMessage()));
            return false;
        }
        catch(\Aws\Sns\Exception\SnsException $e) {
            app('log')->debug('Subscription Error: '.json_encode($e->getAwsErrorMessage()));
            return false;
        }
    }

    public function unsubscribeEndpoint($topic_sub_arn)
    {
        $sns = \AWS::createClient('Sns');

        try {
            $result = $sns->unsubscribe([
                'SubscriptionArn' => $topic_sub_arn
            ]);
            app('log')->debug('Unsubscribe Result: '.json_encode($result));
            return $result;
        }
        catch(\Aws\Sns\Exception\InvalidParameterException $e) {
            app('log')->debug('Invalid Parameter Error: '.json_encode($e->getAwsErrorMessage()));
            return false;
        }
        catch(\Aws\Sns\Exception\InternalErrorException $e) {
            app('log')->debug('Internal Error: '.json_encode($e->getAwsErrorMessage()));
            return false;
        }
        catch(\Aws\Sns\Exception\AuthorizationErrorException $e) {
            app('log')->debug('Authorization Error: '.json_encode($e->getAwsErrorMessage()));
            return false;
        }
        catch(\Aws\Sns\Exception\NotFoundException $e) {
            app('log')->debug('Not Found Error: '.json_encode($e->getAwsErrorMessage()));
            return false;
        }
        catch(\Aws\Sns\Exception\SnsException $e) {
            app('log')->debug('SNS Error: '.json_encode($e->getAwsErrorMessage()));
            return false;
        }
    }


    public function publishNotificationAndroid($application, $message, $title)
    {
        $sns = \AWS::createClient('Sns');
        $broadcast_arn = $application->broadcast_topic_arn;
        $broadcast_arn = (array)json_decode($broadcast_arn);
        $topic_arn_ar = $broadcast_arn['ar-sa'];
        $topic_arn_en = $broadcast_arn['en-us'];
        $concept_label = ucwords($application->concept->label);
        $message_en = $message['en-us'];
        $message_ar = $message['ar-sa'];
        $title_en = $title['en-us'];
        $title_ar = $title['ar-sa'];

        try {
            $sns->publish(
                array(
                    'TargetArn' => $topic_arn_ar,
                    'MessageStructure' => 'json',
                    'Message' => json_encode(array(
                        //'default' => $concept_label,
                        'GCM' => json_encode(array(
                            'data' => array(
                                'title' => $title_ar,
                                'message' => $message_ar
                            )
                        ))
                    ))
                )
            );
        } catch (\Aws\Sns\Exception\SnsException $e) {
            app('log')->error('Error in Android Publishing(ar-sa)'.json_encode($e->getAwsErrorMessage()));
        }

        try {
            $sns->publish(
                array(
                    'TargetArn' => $topic_arn_en,
                    'MessageStructure' => 'json',
                    'Message' => json_encode(array(
                        //'default' => $concept_label,
                        'GCM' => json_encode(array(
                            'data' => array(
                                'title' => $title_en,
                                'message' => $message_en
                            )
                        ))
                    ))
                )
            );
        } catch (\Aws\Sns\Exception\SnsException $e) {
            app('log')->error('Error in Android Publishing(en-us)'.json_encode($e->getAwsErrorMessage()));
        }

        return null;
    }

    public function publishNotificationApple($application, $message, $title)
    {
        $sns = \AWS::createClient('Sns');
        $broadcast_arn = $application->broadcast_topic_arn;
        $broadcast_arn = (array)json_decode($broadcast_arn);
        $topic_arn_ar = $broadcast_arn['ar-sa'];
        $topic_arn_en = $broadcast_arn['en-us'];
        $concept_label = ucwords($application->concept->label);
        $message_en = $message['en-us'];
        $message_ar = $message['ar-sa'];
        $title_en = $title['en-us'];
        $title_ar = $title['ar-sa'];

        $apnsArray = [
            'alert' => $concept_label,
            'sound' => 'default'
        ];

        try {
            $sns->publish (
                  array(
                      'TargetArn' => $topic_arn_ar,
                      'MessageStructure' => 'json',
                      'Message' => json_encode(array(
                          //'default' => $concept_label,
                          'APNS_SANDBOX' => json_encode(array(
                              'aps' => $apnsArray,
                              'data' => array(
                                  'title' => $title_ar,
                                  'message' => $message_ar
                              )
                          )),
                          'APNS' => json_encode(array(
                              'aps' => $apnsArray,
                              'data' => array(
                                  'title' => $title_ar,
                                  'message' => $message_ar
                              )
                          ))
                      ))
                  ));
        } catch (\Aws\Sns\Exception\SnsException $e) {
            app('log')->error('Error in Apple Publishing(ar-sa)'.json_encode($e->getAwsErrorMessage()));
        }

        try {
            $sns->publish (
                array(
                    'TargetArn' => $topic_arn_en,
                    'MessageStructure' => 'json',
                    'Message' => json_encode(array(
                        //'default' => $concept_label,
                        'APNS_SANDBOX' => json_encode(array(
                            'aps' => $apnsArray,
                            'data' => array(
                                'title' => $title_en,
                                'message' => $message_en
                            )
                        )),
                        'APNS' => json_encode(array(
                            'aps' => $apnsArray,
                            'data' => array(
                                'title' => $title_en,
                                'message' => $message_en
                            )
                        ))
                    ))
                ));
        } catch (\Aws\Sns\Exception\SnsException $e) {
            app('log')->error('Error in Apple Publishing(en-us)'.json_encode($e->getAwsErrorMessage()));
        }

        return null;
    }

    public function publishNotificationToTopic($application, $message, $title)
    {
        $sns = \AWS::createClient('Sns');
        $broadcast_arn = $application->broadcast_topic_arn;
        $broadcast_arn = (array)json_decode($broadcast_arn);
        $topic_arn_ar = $broadcast_arn['ar-sa'];
        $topic_arn_en = $broadcast_arn['en-us'];
        $concept_label = ucwords($application->concept->label);
        $message_en = $message['en-us'];
        $message_ar = $message['ar-sa'];
        $title_en = $title['en-us'];
        $title_ar = $title['ar-sa'];

        $apnsArrayAr = [
            'alert' => $title_ar,
            'sound' => 'default'
        ];

        try {
            $sns->publish (
                array(
                    'TargetArn' => $topic_arn_ar,
                    'MessageStructure' => 'json',
                    'Message' => json_encode(array(
                        'default' => $concept_label,
                        'APNS_SANDBOX' => json_encode(array(
                            'aps' => $apnsArrayAr,
                            'campaign_payload' => array(
                                'campaign_name' => $title_ar,
                                'campaign_content' => $message_ar
                            )
                        )),
                        'APNS' => json_encode(array(
                            'aps' => $apnsArrayAr,
                            'campaign_payload' => array(
                                'campaign_name' => $title_ar,
                                'campaign_content' => $message_ar
                            )
                        )),
                        'GCM' => json_encode(array(
                            'data' => array(
                                'title' => $title_ar,
                                'body' => $message_ar
                            )
                        ))
                    ))
                ));
        } catch (\Aws\Sns\Exception\SnsException $e) {
            app('log')->error('Error in Apple Publishing(ar-sa)'.json_encode($e->getAwsErrorMessage()));
        }

        $apnsArrayEn = [
            'alert' => $title_en,
            'sound' => 'default'
        ];

        try {
            $sns->publish (
                array(
                    'TargetArn' => $topic_arn_en,
                    'MessageStructure' => 'json',
                    'Message' => json_encode(array(
                        'default' => $concept_label,
                        'APNS_SANDBOX' => json_encode(array(
                            'aps' => $apnsArrayEn,
                            'campaign_payload' => array(
                                'campaign_name' => $title_en,
                                'campaign_content' => $message_en
                            )
                        )),
                        'APNS' => json_encode(array(
                            'aps' => $apnsArrayEn,
                            'campaign_payload' => array(
                                'campaign_name' => $title_en,
                                'campaign_content' => $message_en
                            )
                        )),
                        'GCM' => json_encode(array(
                            'data' => array(
                                'title' => $title_en,
                                'body' => $message_en
                            )
                        ))
                    ))
                ));
        } catch (\Aws\Sns\Exception\SnsException $e) {
            app('log')->error('Error in Apple Publishing(en-us)'.json_encode($e->getAwsErrorMessage()));
        }


        return null;
    }
}