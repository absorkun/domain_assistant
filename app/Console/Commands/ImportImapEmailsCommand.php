<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\ImportedEmail;
use Carbon\Carbon;

#[Signature('imap:import-emails {--host=} {--port=993} {--encryption=ssl} {--mailbox=INBOX} {--username=} {--password=} {--limit=0}')]
#[Description('Import emails from IMAP into the database')]
class ImportImapEmailsCommand extends Command
{
    public function handle(): int
    {
        if (! function_exists('imap_open')) {
            $this->error('PHP IMAP extension is not installed.');

            return self::FAILURE;
        }

        $host = (string) ($this->option('host') ?: env('IMAP_HOST'));
        $port = (int) ($this->option('port') ?: env('IMAP_PORT', 993));
        $encryption = (string) ($this->option('encryption') ?: env('IMAP_ENCRYPTION', 'ssl'));
        $mailbox = (string) ($this->option('mailbox') ?: env('IMAP_MAILBOX', 'INBOX'));
        $username = (string) ($this->option('username') ?: env('IMAP_USERNAME'));
        $password = (string) ($this->option('password') ?: env('IMAP_PASSWORD'));
        $limit = (int) $this->option('limit');

        if ($host === '' || $username === '' || $password === '') {
            $this->error('IMAP host, username, and password are required.');

            return self::FAILURE;
        }

        $connection = imap_open(sprintf('{%s:%d/%s}%s', $host, $port, $encryption, $mailbox), $username, $password);

        if ($connection === false) {
            $this->error(imap_last_error() ?: 'Unable to open IMAP mailbox.');

            return self::FAILURE;
        }

        $uids = imap_search($connection, 'ALL', SE_UID) ?: [];
        sort($uids);

        if ($limit > 0) {
            $uids = array_slice($uids, 0, $limit);
        }

        $rows = [];
        $total = 0;
        $now = now();

        foreach ($uids as $uid) {
            $overview = imap_fetch_overview($connection, (string) $uid, FT_UID);
            $header = imap_headerinfo($connection, (int) imap_msgno($connection, (int) $uid));

            $rows[] = [
                'mailbox' => $mailbox,
                'message_uid' => $uid,
                'from_email' => isset($header->from[0]->mailbox, $header->from[0]->host)
                    ? $header->from[0]->mailbox.'@'.$header->from[0]->host
                    : null,
                'to_email' => isset($header->to[0]->mailbox, $header->to[0]->host)
                    ? $header->to[0]->mailbox.'@'.$header->to[0]->host
                    : null,
                'subject' => $overview[0]->subject ?? null,
                'body' => imap_body($connection, (int) $uid, FT_UID) ?: null,
                'sent_at' => isset($overview[0]->date) ? Carbon::parse($overview[0]->date) : null,
                'imported_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) === 500) {
                ImportedEmail::query()->upsert(
                    $rows,
                    ['mailbox', 'message_uid'],
                    ['from_email', 'to_email', 'subject', 'body', 'sent_at', 'imported_at', 'updated_at']
                );

                $total += count($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            ImportedEmail::query()->upsert(
                $rows,
                ['mailbox', 'message_uid'],
                ['from_email', 'to_email', 'subject', 'body', 'sent_at', 'imported_at', 'updated_at']
            );

            $total += count($rows);
        }

        imap_close($connection);

        $this->info("Imported {$total} emails.");

        return self::SUCCESS;
    }
}
