<?php

namespace App\Mail;

use App\Models\Schedule;
use App\Models\Asesor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AsesorUnassignmentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Schedule $schedule;
    public Asesor $asesor;
    public ?string $notes;

    public function __construct(Schedule $schedule, Asesor $asesor, ?string $notes = null)
    {
        $this->schedule = $schedule;
        $this->asesor   = $asesor;
        $this->notes    = $notes;
    }

    public function build()
    {
        return $this->subject('Pembatalan Penugasan Jadwal Asesmen')
                    ->view('emails.asesor-unassignment');
    }
}