<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NewsAggregatorService;
use Illuminate\Support\Facades\Log;

class UpdateNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:update {--sources=* : Specific sources to update} {--limit=50 : Maximum articles per source}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update news articles from all configured sources';

    protected NewsAggregatorService $newsAggregatorService;

    public function __construct(NewsAggregatorService $newsAggregatorService)
    {
        parent::__construct();
        $this->newsAggregatorService = $newsAggregatorService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting news aggregation...');

        $options = [];
        
        if ($this->option('sources')) {
            $options['sources'] = $this->option('sources');
        }
        
        if ($this->option('limit')) {
            $options['limit'] = (int) $this->option('limit');
        }

        try {
            $results = $this->newsAggregatorService->aggregateNews($options);

            $this->info('News aggregation completed successfully!');
            $this->newLine();

            $totalFetched = 0;
            $totalStored = 0;

            foreach ($results as $source => $result) {
                $status = $result['status'] === 'success' ? '✓' : '✗';
                $this->line("{$status} {$source}: {$result['fetched']} fetched, {$result['stored']} stored");
                
                if ($result['status'] === 'error') {
                    $this->error("   Error: {$result['error']}");
                }

                $totalFetched += $result['fetched'];
                $totalStored += $result['stored'];
            }

            $this->newLine();
            $this->info("Total: {$totalFetched} articles fetched, {$totalStored} articles stored");

            Log::info('News aggregation completed via command', [
                'results' => $results,
                'total_fetched' => $totalFetched,
                'total_stored' => $totalStored
            ]);

        } catch (\Exception $e) {
            $this->error('News aggregation failed: ' . $e->getMessage());
            Log::error('News aggregation command failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }

        return 0;
    }
}
