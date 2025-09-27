# News Aggregator Backend

A professional Laravel-based news aggregator backend that fetches articles from multiple news sources and provides a comprehensive REST API for frontend applications.

## Features

- **Multi-Source News Aggregation**: Integrates with NewsAPI, The Guardian, and New York Times APIs
- **RESTful API**: Complete API endpoints for articles, sources, categories, and user preferences
- **Advanced Filtering**: Search, filter by category, source, author, date range, and more
- **Scheduled Updates**: Automated news fetching every hour
- **User Preferences**: Personalized news feeds based on user preferences
- **Comprehensive Logging**: Detailed logging for monitoring and debugging
- **Professional Architecture**: Follows SOLID principles and Laravel best practices

## Technology Stack

- **Framework**: Laravel 11
- **Database**: SQLite (configurable to MySQL/PostgreSQL)
- **API**: RESTful JSON API
- **Testing**: PHPUnit with feature and unit tests
- **Scheduling**: Laravel Task Scheduler

## Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- SQLite (or MySQL/PostgreSQL)

### Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd NewsAggregator
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure API Keys**
   Add your API keys to the `.env` file:
   ```env
   NEWS_API_KEY=your_newsapi_key_here
   GUARDIAN_API_KEY=your_guardian_key_here
   NYTIMES_API_KEY=your_nytimes_key_here
   ```

5. **Database Setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Start the application**
   ```bash
   php artisan serve
   ```

## API Documentation

### Base URL
```
http://localhost:8000/api
```

### Authentication
Currently, the API is open. For production, implement authentication middleware.

### Endpoints

#### Articles

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/articles` | Get all articles with filtering and pagination |
| GET | `/articles/{id}` | Get a specific article |
| GET | `/articles/featured` | Get featured articles |
| GET | `/articles/latest` | Get latest articles |
| GET | `/articles/search` | Search articles |
| GET | `/articles/category/{id}` | Get articles by category |
| GET | `/articles/source/{id}` | Get articles by source |

#### News Sources

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/sources` | Get all news sources |
| GET | `/sources/active` | Get active news sources |
| GET | `/sources/statistics` | Get source statistics |
| GET | `/sources/{id}` | Get a specific source |

#### Categories

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/categories` | Get all categories |
| GET | `/categories/active` | Get active categories |
| GET | `/categories/statistics` | Get category statistics |
| GET | `/categories/{id}` | Get a specific category |

#### News Aggregator

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/aggregator/aggregate` | Trigger news aggregation |
| GET | `/aggregator/statistics` | Get aggregation statistics |
| GET | `/aggregator/dashboard` | Get dashboard data |

### Query Parameters

#### Articles Endpoint
- `search`: Search term
- `category_id`: Filter by category ID
- `source_id`: Filter by source ID
- `author`: Filter by author name
- `date_from`: Start date (YYYY-MM-DD)
- `date_to`: End date (YYYY-MM-DD)
- `featured`: Filter featured articles (true/false)
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 20, max: 100)

#### Example Requests

**Get latest articles:**
```bash
GET /api/articles?page=1&per_page=20
```

**Search articles:**
```bash
GET /api/articles/search?q=technology&page=1
```

**Filter by category:**
```bash
GET /api/articles?category_id=1&date_from=2024-01-01
```

**Get featured articles:**
```bash
GET /api/articles/featured?limit=5
```


## Commands

### News Update Command
```bash
php artisan news:update
```

Options:
- `--sources`: Specific sources to update (newsapi, guardian, nytimes)
- `--limit`: Maximum articles per source (default: 50)

Examples:
```bash
# Update all sources
php artisan news:update

# Update specific sources
php artisan news:update --sources=newsapi,guardian

# Limit articles per source
php artisan news:update --limit=100
```

### Scheduled Updates
News updates are automatically scheduled to run every hour. To start the scheduler:

```bash
php artisan schedule:work
```

## Database Schema

### Tables

- **news_sources**: News source configuration
- **categories**: Article categories
- **articles**: News articles
- **user_preferences**: User preferences (for future use)

### Key Relationships

- Articles belong to NewsSource and Category
- UserPreferences belong to User
- Articles have many-to-one relationships with sources and categories

## Testing

Run the test suite:

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=ArticleApiTest

# Run with coverage
php artisan test --coverage
```

## Architecture

### Service Layer
- **NewsServiceInterface**: Base interface for news services
- **BaseNewsService**: Abstract base class with common functionality
- **NewsApiService**: NewsAPI integration
- **GuardianApiService**: The Guardian API integration
- **NewYorkTimesApiService**: New York Times API integration
- **NewsServiceFactory**: Factory for creating news services
- **NewsAggregatorService**: Main aggregation service

### Models
- **Article**: News article model with relationships and scopes
- **NewsSource**: News source configuration
- **Category**: Article categorization
- **UserPreference**: User preferences management

### Controllers
- **ArticleController**: Article API endpoints
- **NewsSourceController**: News source management
- **CategoryController**: Category management
- **NewsAggregatorController**: Aggregation control

## Configuration

### API Keys
Configure your API keys in the `.env` file:

```env
NEWS_API_KEY=your_newsapi_key_here
GUARDIAN_API_KEY=your_guardian_key_here
NYTIMES_API_KEY=your_nytimes_key_here
```

### Service Configuration
API service configuration is in `config/services.php`:

```php
'newsapi' => [
    'key' => env('NEWS_API_KEY'),
    'base_url' => 'https://newsapi.org/v2/',
    'rate_limit' => 1000,
],
```

## Error Handling

The application includes comprehensive error handling:

- **API Errors**: Proper HTTP status codes and error messages
- **Logging**: Detailed logging for debugging and monitoring
- **Validation**: Input validation with clear error messages
- **Graceful Degradation**: Continues operation if some sources fail

## Performance Considerations

- **Database Indexing**: Optimized indexes for common queries
- **Caching**: Statistics are cached for 5 minutes
- **Pagination**: All list endpoints support pagination
- **Rate Limiting**: Respects API rate limits
- **Background Processing**: News updates run in background

## Security

- **Input Validation**: All inputs are validated
- **SQL Injection Protection**: Uses Eloquent ORM
- **XSS Protection**: Output is properly escaped
- **Rate Limiting**: API rate limiting (to be implemented)

## Deployment

### Production Checklist

1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false` in `.env`
3. Configure proper database (MySQL/PostgreSQL)
4. Set up proper logging
5. Configure web server (Nginx/Apache)
6. Set up SSL certificates
7. Configure queue workers for background jobs
8. Set up monitoring and alerting

### Docker Support
```dockerfile
FROM php:8.2-fpm
# Add Docker configuration here
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

This project is licensed under the MIT License.

## Support

For support and questions, please create an issue in the repository.

## Changelog

### Version 1.0.0
- Initial release
- Multi-source news aggregation
- RESTful API
- Scheduled updates
- Comprehensive testing
- Professional documentation