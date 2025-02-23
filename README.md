# Crypto Price Tracker

A real-time cryptocurrency price aggregation system built with Laravel, Livewire, and WebSockets.

## Architecture Overview

### Components

1. **Price Fetcher Service**
   - Long-running Laravel job that fetches cryptocurrency prices
   - Parallel API requests using HTTP client
   - Configurable pairs and exchanges
   - Error handling and retry mechanisms

2. **WebSocket Server (Laravel Reverb)**
   - Real-time price updates to connected clients
   - Automatic reconnection handling
   - Scalable WebSocket implementation

3. **Frontend Components**
   - Livewire components for reactive UI
   - Real-time updates with animations
   - Responsive design with Tailwind CSS

### Data Flow

1. Price Fetcher Job fetches data from exchanges
2. Prices are averaged and stored in database
3. Updates are broadcast via WebSockets
4. Livewire components update UI in real-time

## Setup Instructions

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & NPM
- MySQL/PostgreSQL
- Redis (for queue)
- Supervisor

### Installation

1. Clone the repository:
```bash
git clone https://github.com/evidenze/crypto-tracker.git
cd crypto-tracker
```

2. Install dependencies:
```bash
composer install
npm install
```

3. Configure environment:
```bash
cp .env.example .env
```

Update .env with your configurations:
```env
DB_CONNECTION=mysql
DB_DATABASE=crypto_tracker

REVERB_APP_KEY=your_key
REVERB_APP_SECRET=your_secret

CRYPTO_PAIRS="BTCUSDC,BTCUSDT,BTCETH"
CRYPTO_EXCHANGES="binance,mexc,huobi"
PRICE_FETCH_INTERVAL=5
```

4. Set up database:
```bash
php artisan migrate
```

5. Start services:
```bash
# Start Laravel dev server
php artisan serve

# Start Vite for frontend assets
npm run dev

# Start Reverb WebSocket server
php artisan reverb:start

# Start Queue
php artisan queue:work

# Run scheduled tasks
php artisan schedule:work
```

### Docker Setup (Optional)

Start all services:
```bash
docker-compose up -d
```

Stop services:
```bash
docker-compose down
```

## Supervisor Setup (Production)

### Prerequisites
- Ubuntu/Debian server
- Supervisor installed: `sudo apt-get install supervisor`

### Installation Steps

1. Install Supervisor if not already installed:
```bash
sudo apt-get update
sudo apt-get install supervisor
```

2. Copy the Supervisor configuration file:
```bash
sudo cp supervisor/conf.d/laravel-services.conf /etc/supervisor/conf.d/
```

Or create a symbolic link:
```bash
sudo ln -s /path/to/your/project/supervisor/conf.d/laravel-services.conf /etc/supervisor/conf.d/
```

3. Update Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
```

4. Start services:
```bash
sudo supervisorctl start all
```

### Available Commands

Check status of all services:
```bash
sudo supervisorctl status
```

Individual service management:
```bash
# Queue Worker
sudo supervisorctl start queue:*
sudo supervisorctl stop queue:*
sudo supervisorctl restart queue:*

# Reverb (WebSocket)
sudo supervisorctl start reverb:*
sudo supervisorctl stop reverb:*
sudo supervisorctl restart reverb:*

# Reverb (Scheduler)
sudo supervisorctl start scheduler:*
sudo supervisorctl stop scheduler:*
sudo supervisorctl restart scheduler:*
```

### Log Locations
- Queue Worker: `storage/logs/queue-worker.log`
- Reverb: `storage/logs/reverb-worker.log`
- Scheduler: `storage/logs/schedule-worker.log`

### Troubleshooting

If services don't start:
1. Check logs:
```bash
sudo supervisorctl tail queue:*
sudo supervisorctl tail reverb:*
sudo supervisorctl tail scheduler:*
```

2. Verify permissions:
```bash
sudo chown -R www-data:www-data storage/logs/
sudo chmod -R 755 storage/logs/
```

3. Ensure Supervisor is running:
```bash
sudo service supervisor status
```

### Development Environment

For local development, use these commands instead of Supervisor:

```bash
# Terminal 1 - Queue Worker
php artisan queue:work

# Terminal 2 - Reverb
php artisan reverb:start

# Terminal 3 - Vite/NPM (if needed)
npm run dev

# Terminal  - Scheduler
php artisan schedule:work
```

## Design Decisions & Trade-offs

1. **Parallel Request Processing**
   - Pro: More accurate price averaging
   - Con: Higher memory usage

2. **Caching Strategy**
   - Pro: Reduced database load
   - Con: Slight data staleness

3. **WebSocket Implementation**
   - Pro: Native Laravel integration
   - Con: Additional server requirements

## Known Limitations

1. **API Rate Limits**
   - Current implementation doesn't handle rate limiting
   - Consider implementing token bucket algorithm

2. **Historical Data**
   - Limited historical price storage
   - Consider implementing data pruning

3. **Scalability**
   - Single WebSocket server
   - Consider horizontal scaling for production

## Future Improvements

1. **Features**
   - Historical price charts
   - Price alerts
   - More detailed analytics

2. **Technical**
   - Implement rate limiting
   - Add more comprehensive error handling
   - Improve test coverage

## Testing

Run tests:
```bash
php artisan test
```

## License

MIT License