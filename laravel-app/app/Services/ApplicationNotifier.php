<?php

namespace App\Services;

use App\Application;
use App\JobPosting;
use App\Support\WhatsAppMessage;
use Illuminate\Support\Facades\Log;

class ApplicationNotifier
{
    protected $whatsapp;

    public function __construct(BeyondWasenderService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function notifyPhone(Application $application)
    {
        return $application->whatsapp_number ?: $application->phone;
    }

    public function send(Application $application, $message)
    {
        $phone = $this->notifyPhone($application);
        if (! $phone) {
            return ['success' => false, 'error' => 'No WhatsApp number'];
        }

        $result = $this->whatsapp->sendText($phone, $message);
        if (empty($result['success'])) {
            Log::warning('Application WhatsApp failed', [
                'application_id' => $application->id,
                'error' => $result['error'] ?? 'unknown',
            ]);
        }

        return $result;
    }

    public function underReview(Application $application, JobPosting $job)
    {
        $message = WhatsAppMessage::applicationUnderReview(
            $application->full_name,
            $job->title,
            $application->reference_number,
            $job->isInternship()
        );

        return $this->send($application, $message);
    }

    public function selected(Application $application, JobPosting $job, $agreementUrl)
    {
        $message = WhatsAppMessage::applicationSelected(
            $application->full_name,
            $job->title,
            $application->reference_number,
            $agreementUrl,
            $job->isInternship()
        );

        return $this->send($application, $message);
    }

    public function rejected(Application $application, JobPosting $job)
    {
        $message = WhatsAppMessage::applicationRejected(
            $application->full_name,
            $job->title,
            $application->reference_number,
            $application->rejection_reason
        );

        return $this->send($application, $message);
    }

    public function agreementSigned(Application $application, JobPosting $job)
    {
        $message = WhatsAppMessage::applicationAgreementSigned(
            $application->full_name,
            $job->title,
            $application->reference_number,
            $job->isInternship()
        );

        return $this->send($application, $message);
    }
}
