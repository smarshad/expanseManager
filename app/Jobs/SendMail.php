<?php
namespace App\Jobs;

use App\Mail\SendOtpMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMail implements ShouldQueue
{
    use Queueable;

    public function __construct(public $otp, public $subject, public $email)
    {
        Log::info("Job created", [
            'email' => $email,
            'time' => now()->toDateTimeString(),
            'queue' => $this->queue,
            'connection' => config('queue.default')
        ]);
    }

    public function handle(): void
    {

        Log::info("SendMail job started", [
            'email' => $this->email,
            'otp' => $this->otp
        ]);

        Mail::to($this->email)->send(
            new SendOtpMail($this->otp, $this->subject)
        );
    }

    public function failed(\Throwable $exception)
    {
        Log::error("Job failed", [
            'email' => $this->email,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}