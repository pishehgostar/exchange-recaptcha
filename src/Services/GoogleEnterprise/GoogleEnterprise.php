<?php

namespace Pishehgostar\ExchangeRecaptcha\Services\GoogleEnterprise;

use Google\Cloud\RecaptchaEnterprise\V1\Assessment;
use Google\Cloud\RecaptchaEnterprise\V1\Event;
use Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient;
use Pishehgostar\ExchangeRecaptcha\Abstracts\Recaptcha;

class GoogleEnterprise extends Recaptcha
{
    public function loadScript(): void
    {
        echo '<script src="https://www.google.com/recaptcha/enterprise.js?render=' . config('ex-recaptcha.google_enterprise.site_key') . '"></script>';
    }

    public function render(string $callback,string $action,string $title,array $attributes): void
    {
        $site_key = config('ex-recaptcha.google_enterprise.site_key');
        $attributes = array_merge($attributes,[
            'type'=>'submit',
            'data-callback'=>$callback,
            'data-sitekey'=>$site_key,
            'data-action'=>$action
        ]);
        $attributes['class'] = 'g-recaptcha ' . ($attributes['class']??'');

        $item = view('ex-recaptcha::google_enterprise.submit',compact('title','attributes'))->render();

        echo $item;
    }

    public function verify(string $token,string $action):bool
    {
        $project = config('ex-recaptcha.google_enterprise.project_name');
        $recaptchaKey = config('ex-recaptcha.google_enterprise.site_key');
        // Create the reCAPTCHA client.
        // TODO: Cache the client generation code (recommended) or call client.close() before exiting the method.
        $path = config('ex-recaptcha.google_enterprise.credentials_path');
        $credentials = null;
        if (file_exists($path)){
            $credentials = json_decode(file_get_contents(config('ex-recaptcha.google_enterprise.credentials_path')),true);
        }
        $client = new RecaptchaEnterpriseServiceClient([
            'credentials'=>$credentials
        ]);
        $projectName = $client->projectName($project);

        // Set the properties of the event to be tracked.
        $event = (new Event())
            ->setSiteKey($recaptchaKey)
            ->setToken($token);

        // Build the assessment request.
        $assessment = (new Assessment())
            ->setEvent($event);

        try {
            $response = $client->createAssessment(
                $projectName,
                $assessment
            );

            // Check if the token is valid.
            if ($response->getTokenProperties()->getValid() == false) {
//                printf('The CreateAssessment() call failed because the token was invalid for the following reason: ');
//                printf(InvalidReason::name($response->getTokenProperties()->getInvalidReason()));
                return false;
            }

            // Check if the expected action was executed.
            if ($response->getTokenProperties()->getAction() != $action) {
//                printf('The action attribute in your reCAPTCHA tag does not match the action you are expecting to score');
                return false;
            }

            // Check if the score meets your criteria here
            $score = $response->getRiskAnalysis()->getScore();
//            printf("The score for the protection action is:$score");
            // For more information on interpreting the assessment, see:
            // https://cloud.google.com/recaptcha-enterprise/docs/interpret-assessment
            if ($score >= 0.7) {
                // Score meets the required threshold
                return true;
            } else {
                // Score doesn't meet the required threshold
                return false;
            }


        } catch (\Exception $e) {
//            printf('CreateAssessment() call failed with the following error: ');
//            printf($e);
            return false;
        }
    }

    public function getInputName():string
    {
        return config('ex-recaptcha.google_enterprise.input_name');
    }
}
