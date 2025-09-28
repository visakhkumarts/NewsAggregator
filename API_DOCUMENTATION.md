# News Aggregator API Documentation

Complete API reference for the News Aggregator backend service.

## Base URL

```
http://localhost:8000/api
```

## Request Headers

**Standard Headers for All Requests:**
- `Accept: application/json` - Required for all requests
- `Content-Type: application/json` - Required for POST/PUT/DELETE requests with body
- `Authorization: Bearer YOUR_TOKEN` - Required for protected endpoints  (only if authentication needed)

*Note: All examples below assume these standard headers are included.*

## Authentication

The API uses Bearer token authentication for protected endpoints. Obtain a token by registering or logging in.

### Register New User
```http
POST /api/auth/register

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### Login
```http
POST /api/auth/login

{
    "email": "john@example.com",
    "password": "password123"
}
```

### Use Token for Protected Endpoints
```http
GET /api/user/preferences
Authorization: Bearer YOUR_TOKEN_HERE
```

### Token Management

**Refresh Token:**
```http
POST /api/auth/refresh
Authorization: Bearer YOUR_CURRENT_TOKEN
```

**Logout:**
```http
POST /api/auth/logout
Authorization: Bearer YOUR_CURRENT_TOKEN
```

**Get Current User Info:**
```http
GET /api/auth/me
Authorization: Bearer YOUR_CURRENT_TOKEN
```


## HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `404` - Not Found
- `422` - Validation Error
- `429` - Too Many Requests
- `500` - Internal Server Error

## Endpoints

### Articles

#### Get All Articles
```http
GET /api/articles
```

**Query Parameters:**
- `search` - Search in title, description, or content
- `category_id` - Filter by category ID
- `source_id` - Filter by news source ID  
- `author` - Filter by author name
- `date_from` & `date_to` - Date range (YYYY-MM-DD format)
- `featured` - Show only featured articles (true/false)
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 20, max: 100)

#### Get Specific Article
```http
GET /api/articles/{id}
```

#### Get Featured Articles
```http
GET /api/articles/featured?limit=5
```

#### Get Latest Articles
```http
GET /api/articles/latest?limit=20
```

#### Search Articles
```http
GET /api/articles/search?q=search term
```

#### Get Articles by Category
```http
GET /api/articles/category/{categoryId}?limit=20
```

#### Get Articles by Source
```http
GET /api/articles/source/{sourceId}?limit=20
```

### News Sources

#### Get All Sources
```http
GET /api/sources
```

#### Get Active Sources
```http
GET /api/sources/active
```

#### Get Source Statistics
```http
GET /api/sources/statistics
```

#### Get Specific Source
```http
GET /api/sources/{id}
```

### Categories

#### Get All Categories
```http
GET /api/categories
```

#### Get Active Categories
```http
GET /api/categories/active
```

#### Get Category Statistics
```http
GET /api/categories/statistics
```

#### Get Specific Category
```http
GET /api/categories/{id}
```

### News Aggregator

#### Trigger News Aggregation
```http
POST /api/aggregator/aggregate

{
  "sources": ["newsapi", "guardian", "nytimes"],
  "categories": ["technology", "business"],
  "limit": 50
}
```

#### Get System Statistics
```http
GET /api/aggregator/statistics
```

#### Get Dashboard Data
```http
GET /api/aggregator/dashboard
```

### User Preferences (Protected)

> **Important:** User preferences are automatically created with default values when first accessed. You don't need to create them manually.

#### Get User Preferences (Auto-creates if not exists)
```http
GET /api/user/preferences
Authorization: Bearer YOUR_TOKEN
```

#### Update User Preferences
```http
PUT /api/user/preferences
Authorization: Bearer YOUR_TOKEN

{
  "preferred_sources": [1, 2, 3],
  "preferred_categories": [1, 2],
  "preferred_authors": ["John Doe", "Jane Smith"],
    "language": "en",
    "country": "us",
    "articles_per_page": 25,
    "show_images": true,
    "auto_refresh": true,
    "refresh_interval": 180
}
```

**Available Fields:**
- `preferred_sources` - Array of news source IDs
- `preferred_categories` - Array of category IDs
- `preferred_authors` - Array of author names
- `language` - Language code (default: "en")
- `country` - Country code (default: "us")
- `articles_per_page` - Articles per page (default: 20, max: 100)
- `show_images` - Show article images (default: true)
- `auto_refresh` - Enable auto-refresh (default: false)
- `refresh_interval` - Refresh interval in seconds (default: 300, min: 60, max: 3600)

#### Manage Preferred Sources

**Add Source:**
```http
POST /api/user/preferences/sources
Authorization: Bearer YOUR_TOKEN

{
  "source_id": 1
}
```

**Remove Source:**
```http
DELETE /api/user/preferences/sources
Authorization: Bearer YOUR_TOKEN

{
  "source_id": 1
}
```

#### Manage Preferred Categories


**Add Category:**
```http
POST /api/user/preferences/categories
Authorization: Bearer YOUR_TOKEN

{
  "category_id": 1
}
```

**Remove Category:**
```http
DELETE /api/user/preferences/categories
Authorization: Bearer YOUR_TOKEN

{
  "category_id": 1
}
```

#### Get Personalized Articles
```http
GET /api/user/personalized-articles
Authorization: Bearer YOUR_TOKEN
```

Supports same query parameters as regular articles endpoint, plus user preferences are automatically applied.

## Search Queries & Filtering

### Search Queries
The API supports powerful search functionality across multiple fields:

**Basic Search:**
```http
GET /api/articles?search=technology
GET /api/articles?search=artificial intelligence
GET /api/articles?search=climate change
```

**Search with Filters:**
```http
GET /api/articles?search=AI&category_id=1&source_id=2
GET /api/articles?search=bitcoin&date_from=2024-01-01
```

**Search in Specific Fields:**
- **Title**: Searches article titles
- **Description**: Searches article descriptions  
- **Content**: Searches article content
- **Author**: Searches author names

## Advanced Filtering

The API supports comprehensive filtering across multiple criteria:

### Date Filtering
```http
# Articles from a specific date
GET /api/articles?date_from=2024-01-15&date_to=2024-01-15

# Articles from the last week
GET /api/articles?date_from=2024-01-08&date_to=2024-01-15

# Articles from a specific month
GET /api/articles?date_from=2024-01-01&date_to=2024-01-31

# Articles from today
GET /api/articles?date_from=2024-01-15&date_to=2024-01-15
```

**Date Format:** YYYY-MM-DD (ISO 8601)
**Parameters:**
- `date_from` - Start date (inclusive)
- `date_to` - End date (inclusive)

### Category Filtering
```http
# Get all technology articles
GET /api/articles?category_id=1

# Get business articles with pagination
GET /api/articles?category_id=2&page=1&per_page=10

# Get health articles from last week
GET /api/articles?category_id=4&date_from=2024-01-08&date_to=2024-01-15
```

**Available Categories:**
- Technology (1)
- Business (2) 
- Sports (3)
- Health (4)
- Science (5)
- Entertainment (6)
- Politics (7)

### Source Filtering
```http
# Get articles from NewsAPI only
GET /api/articles?source_id=1

# Get articles from The Guardian only
GET /api/articles?source_id=2

# Get articles from New York Times only
GET /api/articles?source_id=3
```

**Available Sources:**
- NewsAPI (1)
- The Guardian (2)
- New York Times (3)

### Author Filtering
```http
# Get articles by specific author
GET /api/articles?author=John%20Doe

# Get articles by author with partial name match
GET /api/articles?author=Smith

# Get articles by multiple authors (use multiple requests)
GET /api/articles?author=Jane%20Smith
```

### Featured Articles
```http
# Get only featured articles
GET /api/articles?featured=true

# Get non-featured articles
GET /api/articles?featured=false

# Get featured articles from specific category
GET /api/articles?featured=true&category_id=1

# Get featured technology articles from last month
GET /api/articles?featured=true&category_id=1&date_from=2024-01-01&date_to=2024-01-31
```

**Featured Parameter Values:**
- `true` - Show only featured articles
- `false` - Show only non-featured articles
- Omit parameter - Show all articles (both featured and non-featured)


### Search with Filtering
```http
# Search for "AI" in technology category
GET /api/articles?search=AI&category_id=1

# Search for "climate" from specific source
GET /api/articles?search=climate&source_id=2

# Search for "bitcoin" in business category from last week
GET /api/articles?search=bitcoin&category_id=2&date_from=2024-01-08&date_to=2024-01-15
```


### Complex Filtering
```http
# Multiple criteria combined
GET /api/articles?category_id=1&source_id=2&date_from=2024-01-01&featured=true&search=technology

# Technology articles from NewsAPI, last month, with pagination
GET /api/articles?category_id=1&source_id=1&date_from=2024-01-01&date_to=2024-01-31&page=1&per_page=20

# Featured business articles by specific author
GET /api/articles?category_id=2&author=John%20Doe&featured=true
```

## Search Queries

### Basic Search
```javascript
// Search in all articles
GET /api/articles/search?q=artificial intelligence

// Search with pagination
GET /api/articles/search?q=technology&page=2&per_page=10
```

### Advanced Search
```javascript
// Search with filters
GET /api/articles/search?q=climate change&category_id=1&date_from=2024-01-01

// Search in specific source
GET /api/articles/search?q=AI&source_id=2&featured=true
```

## User Preferences

### How User Preferences Work

1. **Automatic Creation**: User preferences are automatically created with default values when first accessed
2. **No Manual Setup**: You don't need to create preferences manually - just call `GET /api/user/preferences`
3. **Default Values**: New preferences start with sensible defaults
4. **Individual Management**: You can add/remove individual sources and categories
5. **Bulk Updates**: You can update multiple preferences at once

### User Preference Fields

**Selected Sources:**
- `preferred_sources` - Array of news source IDs (e.g., [1, 2, 3])
- **Available Sources**: NewsAPI (1), The Guardian (2), New York Times (3)
- **Usage**: Filters articles to show only from preferred sources

**Selected Categories:**
- `preferred_categories` - Array of category IDs (e.g., [1, 2, 4])
- **Available Categories**: Technology (1), Business (2), Sports (3), Health (4), etc.
- **Usage**: Filters articles to show only from preferred categories

**Selected Authors:**
- `preferred_authors` - Array of author names (e.g., ["John Doe", "Jane Smith"])
- **Usage**: Filters articles to show only from preferred authors

**Display Settings:**
- `language` - Preferred language code (default: "en")
- `country` - Preferred country code (default: "us")
- `articles_per_page` - Number of articles per page (default: 20, max: 100)
- `show_images` - Whether to show article images (default: true)
- `auto_refresh` - Enable auto-refresh (default: false)
- `refresh_interval` - Refresh interval in seconds (default: 300, min: 60, max: 3600)

### Managing Preferences

**Get Current Preferences:**
```http
GET /api/user/preferences
Accept: application/json
Authorization: Bearer YOUR_TOKEN
```

**Update Multiple Preferences:**
```http
PUT /api/user/preferences
Accept: application/json
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
    "preferred_sources": [1, 2, 3],
    "preferred_categories": [1, 2],
    "preferred_authors": ["John Doe", "Jane Smith"],
    "articles_per_page": 25,
    "show_images": true,
    "auto_refresh": true,
    "refresh_interval": 180
}
```

**Add Individual Preferences:**

Add Preferred Source:
```http
POST /api/user/preferences/sources
Accept: application/json
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
    "source_id": 1
}
```

Add Preferred Category:
```http
POST /api/user/preferences/categories
Accept: application/json
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
    "category_id": 1
}
```

**Remove Individual Preferences:**

Remove Preferred Source:
```http
DELETE /api/user/preferences/sources
Accept: application/json
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
  "source_id": 1
}
```

Remove Preferred Category:
```http
DELETE /api/user/preferences/categories
Accept: application/json
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
  "category_id": 1
}
```

## Personalized Articles

### Get Personalized Feed
```http
GET /api/user/personalized-articles
Accept: application/json
Authorization: Bearer YOUR_TOKEN
```

### Personalized Feed with Search
```http
GET /api/user/personalized-articles?search=technology
Accept: application/json
Authorization: Bearer YOUR_TOKEN
```

### Personalized Feed with Filters
```http
GET /api/user/personalized-articles?search=AI&featured=true&date_from=2024-01-01&page=1&per_page=15
Accept: application/json
Authorization: Bearer YOUR_TOKEN
```

## Rate Limiting

The API implements rate limiting to ensure fair usage:

| Endpoint Category | Rate Limit | Description |
|------------------|------------|-------------|
| Authentication | 5 requests/minute | Login and registration |
| User Preferences | 50 requests/minute | Authenticated user endpoints |
| Articles | 100 requests/minute | Public article endpoints |
| Sources/Categories | 60 requests/minute | News sources and categories |
| Aggregator | 30 requests/minute | News aggregation and statistics |

When rate limits are exceeded, the API returns a 429 status code with retry information.

## Error Handling

### Common Error Scenarios

**401 Unauthorized (missing or invalid token):**
```json
{
  "success": false,
  "message": "Unauthenticated. Please provide a valid token."
}
```

**422 Validation Error:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

**429 Too Many Requests:**
```json
{
  "success": false,
  "message": "Too Many Attempts.",
  "retry_after": 60
}
```

## Pagination

All list endpoints support pagination with the following parameters:
- `page`: Page number (starts from 1)
- `per_page`: Items per page (default: 20, maximum: 100)

Pagination information is included in the response:
```json
{
  "pagination": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 20,
    "total": 200,
    "has_more": true
  }
}
```

## No Results Responses

All API endpoints provide meaningful responses when no data is found, including descriptive messages and applied filters information.



## Examples

### Complete User Flow

1. **Register/Login:**
```http
POST /api/auth/login

{
  "email": "user@example.com",
  "password": "password123"
}
```

2. **Get User Preferences (Auto-creates defaults):**
```http
GET /api/user/preferences
Authorization: Bearer YOUR_TOKEN
```
*This automatically creates preferences with default values if they don't exist*

3. **Update Preferences (Optional):**
```http
PUT /api/user/preferences
Authorization: Bearer YOUR_TOKEN

{
  "articles_per_page": 25,
  "show_images": true,
  "auto_refresh": true
}
```

4. **Add Preferred Categories:**
```http
POST /api/user/preferences/categories
Authorization: Bearer YOUR_TOKEN

{
  "category_id": 1
}
```

5. **Add Preferred Sources:**
```http
POST /api/user/preferences/sources
Authorization: Bearer YOUR_TOKEN

{
  "source_id": 2
}
```

6. **Get Personalized Articles:**
```http
GET /api/user/personalized-articles?page=1&per_page=10
Authorization: Bearer YOUR_TOKEN
```

### News Aggregation

**Trigger Aggregation:**
```http
POST /api/aggregator/aggregate

{
  "sources": ["newsapi", "guardian"],
  "limit": 100
}
```

**Check Statistics:**
```http
GET /api/aggregator/statistics
```

**Get Dashboard Data:**
```http
GET /api/aggregator/dashboard
```