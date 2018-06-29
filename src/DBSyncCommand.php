<?php

namespace MWI\Commands;

use Illuminate\Console\Command;
use DB;
use App;
use File;
use phpseclib\Net\SSH2;
use phpseclib\Net\SFTP;

class DbSyncCommand extends Command
{
    /**
     * The name and signature of the console command.x
     *
     * @var string
     */
    protected $signature = 'mwi:db:sync';

    /**
     * The console command description.
     *s
     * @var string
     */
    protected $description = 'Allows you to sync your local database to your remote.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {   
        $remote_url = env('REMOTE_SYNC_URL');
        $remote_db = env('REMOTE_SYNC_DB_NAME');
        $ssh_user = env('REMOTE_SYNC_SSH_USERNAME');
        $ssh_pass = env('REMOTE_SYNC_SSH_PASSWORD');

        // Checking to make sure this isn't production.
        if (App::environment('production')) {
            $this->error("Please don't try and run this in production... will not end well.");
            return;
        }
        // Connect via ssh to dump the db on the remote server.
        $ssh = new SSH2($remote_url);
        if (!$ssh->login($ssh_user, $ssh_pass)) {
            exit('Login failed make sure your ssh username and password is set in your env file.');
        }
        $ssh->exec('mysqldump -u ' . $ssh_user . '  -pxyzzy ' . $remote_db . ' > sync_backup.sql');

        // Connect via sftp to d/l the dump
        $sftp = new SFTP($remote_url);

        if (!$sftp->login($ssh_user, $ssh_pass)) {
            exit('Login failed make sure your SSH username and password is set in your env file.');
        }

        // Temporarily remove memory limit
        ini_set('memory_limit', '-1');

        $this->info('Getting the backup.');
        
        $sftp->get('sync_backup.sql', storage_path('sync_backup.sql'));

        $this->info('Importing...');
        DB::unprepared(File::get(storage_path('sync_backup.sql')));

        $this->info('Migrating...');
        $this->call('migrate');
        $this->info('Removing back up files.');
        $ssh->exec('rm sync_backup.sql');
        File::delete(storage_path('sync_backup.sql'));
        $this->info('Complete! You are synced with the remote DB.');
    } 
}
