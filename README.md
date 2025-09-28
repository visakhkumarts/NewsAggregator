# News Aggregator Backend

A professional Laravel-based news aggregator backend that fetches articles from multiple news sources and provides a comprehensive REST API for frontend applications.

## Features

- **Multi-Source Integration**: NewsAPI, The Guardian, and New York Times
- **RESTful API**: Comprehensive endpoints for articles, sources, and categories
- **User Authentication**: Laravel Sanctum-based token authentication
- **Personalized Content**: User preferences and personalized article feeds
- **Rate Limiting**: Built-in throttling for API protection
- **Caching**: Performance optimization with intelligent caching
- **Scheduled Updates**: Automated news aggregation
- **Professional Architecture**: Clean service layer with SOLID principles

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
   SANCTUM_TOKEN_EXPIRATION=20160 #(14 days)
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

For complete API documentation including endpoints, authentication, filtering, and examples, see [API_DOCUMENTATION.md](API_DOCUMENTATION.md).

### Quick Start


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

- **users**: User accounts with Sanctum authentication
- **personal_access_tokens**: Sanctum token storage
- **news_sources**: News source configuration
- **categories**: Article categories
- **articles**: News articles
- **user_preferences**: User preferences and settings

### Key Relationships

- Articles belong to NewsSource and Category
- UserPreferences belong to User
- Articles have many-to-one relationships with sources and categories
- Users have many personal access tokens


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
- **Category**: Article categorization with validation and mutators
- **UserPreference**: User preferences management
- **User**: User model with Sanctum authentication

### Controllers
- **ArticleController**: Article API endpoints with comprehensive filtering
- **NewsSourceController**: News source management
- **CategoryController**: Category management
- **NewsAggregatorController**: Aggregation control
- **AuthController**: Authentication with Sanctum
- **UserPreferenceController**: User preferences management

### Middleware
- **Rate Limiting**: Laravel's built-in throttle middleware
- **Authentication**: Laravel Sanctum for API authentication
- **Exception Handling**: Custom exception handler for consistent JSON responses

## Configuration

### API Keys
Configure your API keys in the `.env` file:

```env
NEWS_API_KEY=your_newsapi_key_here
GUARDIAN_API_KEY=your_guardian_key_here
NYTIMES_API_KEY=your_nytimes_key_here
SANCTUM_TOKEN_EXPIRATION=20160 #(14 days)
```
