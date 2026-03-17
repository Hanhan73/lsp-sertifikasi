<?php

namespace App\Mail;

use App\Models\Schedule;
use App\Models\Asesor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AsesorAssignmentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Schedule $schedule;
    public Asesor $asesor;
    public string $action;

    public function __construct(Schedule $schedule, Asesor $asesor, string $action)
    {
        $this->schedule = $schedule;
        $this->asesor   = $asesor;
        $this->action   = $action;
    }

    public function build()
    {
        $subject = $this->action === 'reassigned' 
            ? 'Penugasan Ulang Jadwal Asesmen'
            : 'Penugasan Jadwal Asesmen Baru';

        return $this->subject($subject)
                    ->view('emails.asesor-assignment');
    }
}