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
- Docker

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

4. Start all services:
```bash
docker-compose up -d
```

Stop services:
```bash
docker-compose down
```

### How It Works

This section provides a detailed explanation of the workflow and interactions between the components of the application. Here's a step-by-step breakdown of how the system operates:

---

#### 1. **Job Scheduling and Execution**
   - **Trigger**: A Laravel scheduled job is configured to run every 5 seconds (configurable via the `INTERVAL` environment variable).
   - **Action**: The job dispatches a `FetchCryptoPrices` job to the Laravel queue.
   - **Parallel Execution**: The `FetchCryptoPrices` job uses Guzzle's asynchronous requests to fetch cryptocurrency prices from all configured exchanges (e.g., Binance, MEXC, Huobi) simultaneously.

---

#### 2. **Fetching Prices**
   - **API Calls**: The job makes HTTP requests to the APIs of the configured exchanges to fetch the latest prices for the specified cryptocurrency pairs (e.g., BTCUSDC, BTCUSDT, BTCETH).
   - **Error Handling**: If an API call fails, the job logs the error and retries the request (if applicable) or skips the exchange for that iteration.
   - **Caching**: API responses are cached to reduce latency and avoid hitting rate limits. The cache is invalidated after the configured interval.

---

#### 3. **Calculating the Average Price**
   - **Aggregation**: Once prices are fetched from all exchanges, the job calculates the average price for each cryptocurrency pair.
   - **Validation**: The job ensures that prices from all exchanges are valid before calculating the average.

---

#### 4. **Saving to the Database**
   - **Database Insertion**: The calculated average prices are saved to the database in a `price_aggregate` table. Each record includes:
     - Cryptocurrency pair (e.g., BTCUSDC)
     - Average price
     - Timestamp of the calculation
     - List of exchanges used for the calculation
   - **Database Structure**: The `price_aggregate` table is designed to store historical data for future analysis or reporting.

---

#### 5. **Pushing Data to the Frontend**
   - **Event Triggering**: After saving the data to the database, the job triggers a `PriceUpdated` event.
   - **WebSocket Broadcast**: The `PriceUpdated` event broadcasts the updated cryptocurrency prices to all connected WebSocket clients.
   - **Real-Time Updates**: The frontend listens for WebSocket messages and updates the UI in real-time to reflect the latest prices.

---

#### 6. **Frontend Interaction**
   - **Initial Load**: When the page loads, the frontend makes a REST API call to fetch the latest cryptocurrency prices from the database.
   - **Real-Time Updates**: After the initial load, the frontend subscribes to the WebSocket channel to receive real-time updates.
   - **UI Updates**: The LiveWire components dynamically update the UI to show:
     - Current average price for each cryptocurrency pair
     - Price change (indicated by green up or red down arrows)
     - List of exchanges used for the calculation
     - Timestamp of the last update

---

#### 7. **Error Recovery and Stability**
   - **WebSocket Reconnection**: If the WebSocket connection is lost due to internet fluctuations, the frontend automatically reconnects to the WebSocket server.
   - **Job Retries**: If the `FetchCryptoPrices` job fails, Laravel's queue system retries the job based on the configured retry policy.

---

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