<?php

namespace App\Services;

use App\BeyondUser;
use App\Http\Controllers\Controller;
use App\Task;
use App\TaskAssignment;
use App\TaskCc;
use App\User;
use App\Support\TaskPersonalization;
use App\Support\WhatsAppMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Sends WhatsApp notifications for task assignment, CC, accept, decline, progress, complete, reminders.
 */
class TaskNotificationService extends Controller
{
    /**
     * @return string sent|skip|retry
     */
    protected function sendPhone($phone, $message)
    {
        if (empty(trim((string) $phone))) {
            return 'skip';
        }
        try {
            $this->sendWhatsAppToPhone($phone, $message);

            return 'sent';
        } catch (\Exception $e) {
            $err = $e->getMessage();
            Log::warning('Task WhatsApp failed: ' . $err);
            if (preg_match('/rate|protection|429|timeout|temporar|try again/i', $err)) {
                return 'retry';
            }

            return 'skip';
        }
    }

    protected function phoneForUser(BeyondUser $user)
    {
        return $this->resolveBeyondUserPhone($user);
    }

    public function notifyAssignment(TaskAssignment $assignment)
    {
        $assignment->load(['task']);
        $task = $assignment->task;
        $user = BeyondUser::find($assignment->user_id);
        if (! $task || ! $user) {
            return 'skip';
        }

        $link = url('/task-invite/' . $assignment->invite_token);
        $userVars = TaskPersonalization::userVars($user);
        $description = TaskPersonalization::personalize($task->description ?: '', $userVars);
        $template = $task->notification_template ?: TaskPersonalization::defaultAssignmentTemplate();
        $vars = array_merge($userVars, TaskPersonalization::taskVars($task, $link), [
            'description' => $description,
            'task_message' => $description,
        ]);
        $message = TaskPersonalization::personalize($template, $vars);

        return $this->sendPhone($this->phoneForUser($user), $message);
    }

    /**
     * Prefer BeyondUser.phone; fall back to profile / customer directory phone.
     */
    protected function resolveBeyondUserPhone(BeyondUser $user)
    {
        $phone = trim((string) ($user->phone ?? ''));
        if ($phone !== '') {
            return $phone;
        }
        try {
            $profile = \App\BeyondProfile::find($user->id);
            if ($profile && trim((string) $profile->phone) !== '') {
                $phone = trim((string) $profile->phone);
                $user->phone = $phone;
                $user->save();

                return $phone;
            }
        } catch (\Throwable $e) {
        }
        try {
            if (! empty($user->email)) {
                $customer = \App\Customer::where('is_active', 1)
                    ->whereRaw('LOWER(email) = ?', [strtolower($user->email)])
                    ->whereNotNull('phone_number')
                    ->orderByDesc('id')
                    ->first();
                if ($customer && trim((string) $customer->phone_number) !== '') {
                    $phone = trim((string) $customer->phone_number);
                    $user->phone = $phone;
                    $user->save();

                    return $phone;
                }
            }
        } catch (\Throwable $e) {
        }

        return $phone;
    }

    public function notifyCcRecipient(Task $task, TaskCc $cc)
    {
        $task->loadMissing('assignments');
        $assigneeNames = BeyondUser::whereIn('id', $task->assignments->pluck('user_id'))
            ->pluck('name')->filter()->implode(', ') ?: 'the assignee(s)';

        $user = BeyondUser::find($cc->user_id);
        if (! $user) {
            return 'skip';
        }
        $phone = $this->phoneForUser($user);
        if ($phone === '') {
            return 'skip';
        }

        $start = $task->start_date
            ? $task->start_date->format('d M Y') . ($task->start_time ? ' ' . substr((string) $task->start_time, 0, 5) : '')
            : '—';
        $deadline = $task->deadline
            ? $task->deadline->format('d M Y') . ($task->deadline_time ? ' ' . substr((string) $task->deadline_time, 0, 5) : '')
            : '—';
        $desc = TaskPersonalization::personalize($task->description ?: '', TaskPersonalization::userVars($user));
        $msg = WhatsAppMessage::statusBlock('📋', 'Task CC notification');
        $msg .= WhatsAppMessage::greeting($user->name ?: 'Team Member');
        $msg .= "You have been CC'd on a task assigned to *{$assigneeNames}*.\n";
        $msg .= WhatsAppMessage::bullet('Task', $task->title);
        $msg .= WhatsAppMessage::bullet('Priority', $task->priority ?: 'Medium');
        $msg .= WhatsAppMessage::bullet('Start', $start);
        $msg .= WhatsAppMessage::bullet('Deadline', $deadline);
        if (trim($desc) !== '') {
            $msg .= "\n{$desc}\n";
        }
        $msg .= "\nYou will receive progress updates on this task.";
        $msg .= WhatsAppMessage::actionLink('View tasks', url('/user/tasks'));
        $msg .= WhatsAppMessage::footer();

        return $this->sendPhone($phone, $msg);
    }

    public function notifyCcOnAssignment(Task $task)
    {
        $task->load(['assignments', 'ccRecipients']);
        $sent = 0;
        foreach ($task->ccRecipients as $cc) {
            if ($this->notifyCcRecipient($task, $cc) === 'sent') {
                $sent++;
            }
        }

        return $sent;
    }

    public function notifyAccepted(TaskAssignment $assignment)
    {
        $this->notifyStatusChange($assignment, 'accepted');
    }

    public function notifyDeclined(TaskAssignment $assignment)
    {
        $this->notifyStatusChange($assignment, 'declined');
    }

    protected function notifyStatusChange(TaskAssignment $assignment, $action)
    {
        $assignment->load('task');
        $task = $assignment->task;
        $assignee = BeyondUser::find($assignment->user_id);
        if (! $task || ! $assignee) {
            return;
        }

        $assigneeName = $assignee->name ?: 'Assignee';
        $accepted = $action === 'accepted';
        $adminTitle = WhatsAppMessage::statusBlock($accepted ? '📊' : '❌', $accepted ? 'Task accepted' : 'Task declined');
        $adminLine = $accepted
            ? "*{$assigneeName}* has accepted the task."
            : "*{$assigneeName}* has declined the task.";
        $ccTitle = WhatsAppMessage::statusBlock($accepted ? '📊' : '❌', $accepted ? 'Task CC — accepted' : 'Task CC — declined');
        $ccLine = $accepted
            ? "*{$assigneeName}* has accepted the task you are CC'd on."
            : "*{$assigneeName}* has declined the task you are CC'd on.";

        $admin = $task->created_by_admin_id ? User::find($task->created_by_admin_id) : null;
        if ($admin && ! empty($admin->phone)) {
            $this->sendPhone($admin->phone, $adminTitle.$adminLine."\n".WhatsAppMessage::bullet('Task', $task->title).WhatsAppMessage::footer());
        }

        foreach (TaskCc::where('task_id', $task->id)->get() as $cc) {
            $user = BeyondUser::find($cc->user_id);
            if (! $user) {
                continue;
            }
            $phone = $this->phoneForUser($user);
            if ($phone === '') {
                continue;
            }
            $this->sendPhone(
                $phone,
                $ccTitle.WhatsAppMessage::greeting($user->name ?: 'CC')."{$ccLine}\n".WhatsAppMessage::bullet('Task', $task->title).WhatsAppMessage::footer()
            );
        }
    }

    public function notifyProgress(TaskAssignment $assignment, $progress, $status, $comment = null)
    {
        $assignment->load('task');
        $task = $assignment->task;
        $assignee = BeyondUser::find($assignment->user_id);
        if (! $task || ! $assignee) {
            return;
        }
        $assigneeName = $assignee->name ?: 'Assignee';

        if ($status === 'Completed') {
            $admin = $task->created_by_admin_id ? User::find($task->created_by_admin_id) : null;
            if ($admin && ! empty($admin->phone)) {
                $this->sendPhone($admin->phone, WhatsAppMessage::statusBlock('✅', 'Task completed')."*{$assigneeName}* completed this task.\n".WhatsAppMessage::bullet('Task', $task->title).WhatsAppMessage::footer());
            }
            foreach (TaskCc::where('task_id', $task->id)->get() as $cc) {
                $user = BeyondUser::find($cc->user_id);
                if (! $user) {
                    continue;
                }
                $phone = $this->phoneForUser($user);
                if ($phone === '') {
                    continue;
                }
                $this->sendPhone(
                    $phone,
                    WhatsAppMessage::statusBlock('✅', 'Task CC — completed')
                    .WhatsAppMessage::greeting($user->name ?: 'CC')
                    ."*{$assigneeName}* completed the task you are CC'd on.\n"
                    .WhatsAppMessage::bullet('Task', $task->title)
                    .WhatsAppMessage::footer()
                );
            }

            return;
        }

        foreach (TaskCc::where('task_id', $task->id)->get() as $cc) {
            $user = BeyondUser::find($cc->user_id);
            if (! $user) {
                continue;
            }
            $phone = $this->phoneForUser($user);
            if ($phone === '') {
                continue;
            }
            $this->sendPhone(
                $phone,
                WhatsAppMessage::statusBlock('📋', 'Task CC — progress update')
                .WhatsAppMessage::greeting($user->name ?: 'CC')
                ."You are CC on a task assigned to *{$assigneeName}*.\n"
                .WhatsAppMessage::bullet('Task', $task->title)
                .WhatsAppMessage::bullet('Realization', $progress.'%')
                .WhatsAppMessage::bullet('Status', $status)
                .($comment ? WhatsAppMessage::bullet('Note', $comment) : '')
                .WhatsAppMessage::footer()
            );
        }
    }

    public function notifyReminder(Task $task)
    {
        $task->load('assignments');
        foreach ($task->assignments as $assignment) {
            if (in_array($assignment->status, ['Completed', 'Declined'], true)) {
                continue;
            }
            $user = BeyondUser::find($assignment->user_id);
            if (! $user) {
                continue;
            }
            $phone = $this->phoneForUser($user);
            if ($phone === '') {
                continue;
            }
            $deadline = $task->deadline
                ? $task->deadline->format('d M Y') . ($task->deadline_time ? ' ' . substr((string) $task->deadline_time, 0, 5) : '')
                : '—';
            $desc = TaskPersonalization::personalize($task->description ?: '', TaskPersonalization::userVars($user));
            $descBlock = trim($desc) !== '' ? "\n{$desc}\n" : '';
            $this->sendPhone(
                $phone,
                WhatsAppMessage::statusBlock('⏰', 'Task reminder')
                .WhatsAppMessage::greeting($user->name ?: 'Team Member')
                ."Reminder for your task.\n"
                .WhatsAppMessage::bullet('Task', $task->title)
                .WhatsAppMessage::bullet('Deadline', $deadline)
                .$descBlock
                .WhatsAppMessage::actionLink('Update progress', url('/user/tasks'))
                .WhatsAppMessage::footer()
            );
        }
    }

    /**
     * Send outstanding assignment + CC WhatsApps for one task, respecting a send budget
     * so Wasender rate limits do not drop the rest of the list.
     *
     * @return int number of API send attempts that succeeded
     */
    public function dispatchPending(Task $task, $budget = 8)
    {
        $task->load(['assignments', 'ccRecipients']);
        $sent = 0;
        $track = $this->tracksWhatsappSent();
        $paused = false;

        foreach ($task->assignments as $assignment) {
            if ($sent >= $budget) {
                break;
            }
            if ($track && $assignment->whatsapp_sent) {
                continue;
            }
            $outcome = $this->notifyAssignment($assignment);
            if ($outcome === 'retry') {
                $paused = true;
                break;
            }
            if ($track) {
                $assignment->whatsapp_sent = true;
                $assignment->save();
            }
            if ($outcome === 'sent') {
                $sent++;
            }
        }

        if (! $paused && $sent < $budget) {
            foreach ($task->ccRecipients as $cc) {
                if ($sent >= $budget) {
                    break;
                }
                if ($track && $cc->whatsapp_sent) {
                    continue;
                }
                $outcome = $this->notifyCcRecipient($task, $cc);
                if ($outcome === 'retry') {
                    break;
                }
                if ($track) {
                    $cc->whatsapp_sent = true;
                    $cc->save();
                }
                if ($outcome === 'sent') {
                    $sent++;
                }
            }
        }

        if ($track) {
            $task->unsetRelation('assignments');
            $task->unsetRelation('ccRecipients');
            $task->load(['assignments', 'ccRecipients']);
            $pendingAssignees = $task->assignments->filter(function ($a) {
                return empty($a->whatsapp_sent);
            })->count();
            $pendingCc = $task->ccRecipients->filter(function ($c) {
                return empty($c->whatsapp_sent);
            })->count();
            if ($pendingAssignees === 0 && $pendingCc === 0) {
                $task->notifications_sent = true;
                $task->is_scheduled = false;
                $task->save();
            }
        } else {
            $task->notifications_sent = true;
            $task->is_scheduled = false;
            $task->save();
        }

        return $sent;
    }

    public function dispatchTaskNotifications(Task $task)
    {
        return $this->dispatchPending($task, 500);
    }

    protected function tracksWhatsappSent()
    {
        return Schema::hasColumn('task_assignments', 'whatsapp_sent')
            && Schema::hasColumn('task_cc', 'whatsapp_sent');
    }
}
