<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\Asesor;
use App\Models\ScheduleAsesorHistory;
use App\Models\AsesorNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AsesorAssignmentService
{
    /**
     * Assign asesor ke schedule
     */
    public function assignAsesor(Schedule $schedule, Asesor $asesor, ?string $notes = null): bool
    {
        DB::beginTransaction();
        try {
            $oldAsesorId = $schedule->asesor_id;
            $action = $oldAsesorId ? 'reassigned' : 'assigned';

            Log::info("Assigning asesor {$asesor->id} to schedule {$schedule->id}", [
                'schedule_id' => $schedule->id,
                'asesor_id'   => $asesor->id,
                'old_asesor'  => $oldAsesorId,
                'action'      => $action,
            ]);

            // Update schedule - PASTIKAN ini berhasil
            $updated = $schedule->update([
                'asesor_id'        => $asesor->id,
                'assigned_by'      => auth()->id(),
                'assigned_at'      => now(),
                'assignment_notes' => $notes,
            ]);

            if (!$updated) {
                Log::error("Failed to update schedule {$schedule->id}");
                throw new \Exception("Failed to update schedule");
            }

            // Refresh model untuk memastikan data terupdate
            $schedule->refresh();

            // Verify assignment
            if ($schedule->asesor_id !== $asesor->id) {
                Log::error("Schedule asesor_id mismatch after update", [
                    'expected' => $asesor->id,
                    'actual'   => $schedule->asesor_id,
                ]);
                throw new \Exception("Assignment verification failed");
            }

            // Log history
            ScheduleAsesorHistory::create([
                'schedule_id'  => $schedule->id,
                'asesor_id'    => $asesor->id,
                'assigned_by'  => auth()->id(),
                'action'       => $action,
                'notes'        => $notes,
                'action_at'    => now(),
            ]);

            // Kirim notifikasi ke asesor baru
            $this->sendAssignmentNotification($schedule, $asesor, $action);

            // Jika reassign, kirim notifikasi ke asesor lama
            if ($oldAsesorId && $oldAsesorId != $asesor->id) {
                $oldAsesor = Asesor::find($oldAsesorId);
                if ($oldAsesor) {
                    $this->sendUnassignmentNotification($schedule, $oldAsesor, 'Ditugaskan ulang ke asesor lain');
                }
            }

            DB::commit();

            Log::info("Asesor {$asesor->nama} successfully assigned to schedule #{$schedule->id}");
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asesor assignment error: ' . $e->getMessage(), [
                'schedule_id' => $schedule->id,
                'asesor_id'   => $asesor->id,
                'trace'       => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Unassign asesor dari schedule
     */
    public function unassignAsesor(Schedule $schedule, ?string $notes = null): bool
    {
        DB::beginTransaction();
        try {
            $asesor = $schedule->asesor;
            
            if (!$asesor) {
                Log::warning("Attempting to unassign from schedule {$schedule->id} with no asesor");
                return false;
            }

            Log::info("Unassigning asesor {$asesor->id} from schedule {$schedule->id}");

            // Log history sebelum update
            ScheduleAsesorHistory::create([
                'schedule_id'  => $schedule->id,
                'asesor_id'    => $asesor->id,
                'assigned_by'  => auth()->id(),
                'action'       => 'unassigned',
                'notes'        => $notes,
                'action_at'    => now(),
            ]);

            // Update schedule
            $updated = $schedule->update([
                'asesor_id'        => null,
                'assigned_by'      => null,
                'assigned_at'      => null,
                'assignment_notes' => null,
            ]);

            if (!$updated) {
                Log::error("Failed to unassign schedule {$schedule->id}");
                throw new \Exception("Failed to unassign schedule");
            }

            // Kirim notifikasi pembatalan
            $this->sendUnassignmentNotification($schedule, $asesor, $notes);

            DB::commit();

            Log::info("Asesor {$asesor->nama} unassigned from schedule #{$schedule->id}");
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asesor unassignment error: ' . $e->getMessage(), [
                'schedule_id' => $schedule->id,
                'trace'       => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Kirim notifikasi assignment
     */
    private function sendAssignmentNotification(Schedule $schedule, Asesor $asesor, string $action): void
    {
        try {
            $title = $action === 'reassigned' 
                ? 'Penugasan Ulang Jadwal Asesmen'
                : 'Penugasan Jadwal Asesmen Baru';

            $message = sprintf(
                'Anda ditugaskan untuk melaksanakan asesmen pada %s pukul %s di %s. Total %d asesi.',
                $schedule->assessment_date->format('d M Y'),
                $schedule->start_time,
                $schedule->tuk->name,
                $schedule->asesmens->count()
            );

            // Create in-app notification
            AsesorNotification::create([
                'asesor_id' => $asesor->id,
                'type'      => 'assignment',
                'title'     => $title,
                'message'   => $message,
                'data'      => [
                    'schedule_id'     => $schedule->id,
                    'assessment_date' => $schedule->assessment_date->format('Y-m-d'),
                    'tuk_name'        => $schedule->tuk->name,
                    'asesi_count'     => $schedule->asesmens->count(),
                ],
            ]);

            // Send email (jika asesor punya email)
            if ($asesor->email) {
                try {
                    Mail::to($asesor->email)->send(
                        new \App\Mail\AsesorAssignmentNotification($schedule, $asesor, $action)
                    );
                    Log::info("Assignment email sent to {$asesor->email}");
                } catch (\Exception $e) {
                    Log::error('Email notification failed: ' . $e->getMessage());
                    // Don't throw - email failure shouldn't break assignment
                }
            }
        } catch (\Exception $e) {
            Log::error('Notification error: ' . $e->getMessage());
            // Don't throw - notification failure shouldn't break assignment
        }
    }

    /**
     * Kirim notifikasi unassignment
     */
    private function sendUnassignmentNotification(Schedule $schedule, Asesor $asesor, ?string $notes = null): void
    {
        try {
            $message = sprintf(
                'Penugasan Anda untuk jadwal asesmen pada %s di %s telah dibatalkan.',
                $schedule->assessment_date->format('d M Y'),
                $schedule->tuk->name
            );

            if ($notes) {
                $message .= ' Alasan: ' . $notes;
            }

            AsesorNotification::create([
                'asesor_id' => $asesor->id,
                'type'      => 'schedule_update',
                'title'     => 'Pembatalan Penugasan',
                'message'   => $message,
                'data'      => [
                    'schedule_id'     => $schedule->id,
                    'assessment_date' => $schedule->assessment_date->format('Y-m-d'),
                    'notes'           => $notes,
                ],
            ]);

            if ($asesor->email) {
                try {
                    Mail::to($asesor->email)->send(
                        new \App\Mail\AsesorUnassignmentNotification($schedule, $asesor, $notes)
                    );
                } catch (\Exception $e) {
                    Log::error('Email notification failed: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error('Unassignment notification error: ' . $e->getMessage());
        }
    }

    /**
     * Get available asesors untuk schedule tertentu
     * (yang belum ditugaskan pada tanggal/waktu yang sama)
     */
    public function getAvailableAsesors(Schedule $schedule)
    {
        return Asesor::where('is_active', true)
            ->where('status_reg', 'aktif')
            ->whereDoesntHave('schedules', function ($query) use ($schedule) {
                $query->where('assessment_date', $schedule->assessment_date)
                      ->where('id', '!=', $schedule->id)
                      ->where(function ($q) use ($schedule) {
                          // Check time overlap
                          $q->whereBetween('start_time', [$schedule->start_time, $schedule->end_time])
                            ->orWhereBetween('end_time', [$schedule->start_time, $schedule->end_time])
                            ->orWhere(function ($q2) use ($schedule) {
                                $q2->where('start_time', '<=', $schedule->start_time)
                                   ->where('end_time', '>=', $schedule->end_time);
                            });
                      });
            })
            ->orderBy('nama')
            ->get();
    }
}