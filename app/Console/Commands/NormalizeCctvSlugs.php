<?php

namespace App\Console\Commands;

use App\Models\Cctv;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class NormalizeCctvSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cctv:normalize-slugs {--dry-run : Only show changes without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normalize existing CCTV slugs to match sanitized folder naming used by HLS output.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Normalizing CCTV slugs...');

        $query = Cctv::query();

        // Focus on known problematic legacy typos (but we still normalize all matched rows)
        $query->where(function ($q) {
            $q->where('slug', 'like', '%not fixedddd%')
              ->orWhere('name', 'like', '%not fixedddd%');
        });

        $cctvs = $query->get();

        if ($cctvs->isEmpty()) {
            $this->info('No CCTV slugs to normalize.');
            return 0;
        }

        $updated = 0;

        foreach ($cctvs as $cctv) {
            $old = $cctv->slug;
            $new = Cctv::sanitizeStreamName($cctv->name);

            // As a safety net, ensure slug stays string and trimmed.
            $new = trim((string) $new);

            if ($old === $new) {
                continue;
            }

            $this->line("- id={$cctv->id} name='{$cctv->name}' : '{$old}' => '{$new}'");

            if (!$dryRun) {
                $cctv->slug = $new;
                $cctv->save();
                $updated++;
            }
        }

        if ($dryRun) {
            $this->info('Dry-run complete (no changes saved).');
        } else {
            $this->info("Done. Updated {$updated} CCTV slug(s). ");
        }

        return 0;
    }
}

