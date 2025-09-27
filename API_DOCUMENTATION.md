# News Aggregator API Documentation

## Overview

The News Aggregator API provides a comprehensive RESTful interface for accessing news articles from multiple sources including NewsAPI, The Guardian, and New York Times.

## Base URL

```
http://localhost:8000/api
```

## Authentication

The API uses **Bearer Token Authentication** for protected endpoints. You need to obtain an API token by logging in or registering a user account.

### 🔐 **Token Generation Process**

#### **Step 1: Register a New User (Optional)**
```bash
curl -X POST "http://localhost:8000/api/auth/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```


#### **Step 2: Login with Existing User**
```bash
curl -X POST "http://localhost:8000/api/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```


#### **Step 3: Use Token for Protected Endpoints**
```bash
curl -X GET "http://localhost:8000/api/user/preferences" \
  -H "Authorization: Bearer fCkUeiFr5SqkewBtGhBM8K9L2N3P4Q5R6S7T8U9V0W1X2Y3Z4A5B6C7D8E9F0G1H2I3J4K5L6M7N8O9P0Q1R2S3T4U5V6W7X8Y9Z0" \
  -H "Accept: application/json"
```


### 🔄 **Token Management**

#### **Refresh Token**
```bash
curl -X POST "http://localhost:8000/api/auth/refresh" \
  -H "Authorization: Bearer YOUR_CURRENT_TOKEN" \
  -H "Accept: application/json"
```

#### **Logout (Revoke Token)**
```bash
curl -X POST "http://localhost:8000/api/auth/logout" \
  -H "Authorization: Bearer YOUR_CURRENT_TOKEN" \
  -H "Accept: application/json"
```

#### **Get Current User Info**
```bash
curl -X GET "http://localhost:8000/api/auth/me" \
  -H "Authorization: Bearer YOUR_CURRENT_TOKEN" \
  -H "Accept: application/json"
```

### 🛡️ **Security Notes**

- **Token Format**: 80-character random string
- **Token Expiry**: Tokens don't expire automatically (logout to revoke)
- **Token Storage**: Store tokens securely in your frontend application
- **HTTPS**: Always use HTTPS in production
- **Token Rotation**: Use refresh endpoint to rotate tokens periodically

## Response Format

All API responses follow a consistent JSON structure:

### Success Response
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 20,
    "total": 200,
    "has_more": true
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["Error message"]
  }
}
```

## HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## Endpoints

### Articles

#### Get All Articles
```http
GET /api/articles
```

**Query Parameters:**
- `search` (string, optional): Search term for title, description, or content
- `category_id` (integer, optional): Filter by category ID
- `source_id` (integer, optional): Filter by news source ID
- `author` (string, optional): Filter by author name
- `date_from` (date, optional): Start date (YYYY-MM-DD)
- `date_to` (date, optional): End date (YYYY-MM-DD)
- `featured` (boolean, optional): Filter featured articles
- `page` (integer, optional): Page number (default: 1)
- `per_page` (integer, optional): Items per page (default: 20, max: 100)

**Example Request:**
```bash
GET /api/articles?search=technology&category_id=1&page=1&per_page=20
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Latest Technology News",
      "description": "Breaking news in technology...",
      "url": "https://example.com/article",
      "image_url": "https://example.com/image.jpg",
      "author": "John Doe",
      "published_at": "2024-01-15T10:30:00Z",
      "view_count": 150,
      "is_featured": true,
      "news_source": {
        "id": 1,
        "name": "Tech News",
        "slug": "tech-news"
      },
      "category": {
        "id": 1,
        "name": "Technology",
        "slug": "technology"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100,
    "has_more": true
  }
}
```

#### Get Specific Article
```http
GET /api/articles/{id}
```

**Parameters:**
- `id` (integer, required): Article ID

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Article Title",
    "description": "Article description...",
    "content": "Full article content...",
    "url": "https://example.com/article",
    "image_url": "https://example.com/image.jpg",
    "author": "John Doe",
    "published_at": "2024-01-15T10:30:00Z",
    "view_count": 151,
    "is_featured": true,
    "news_source": {
      "id": 1,
      "name": "Tech News",
      "slug": "tech-news"
    },
    "category": {
      "id": 1,
      "name": "Technology",
      "slug": "technology"
    }
  }
}
```

#### Get Featured Articles
```http
GET /api/articles/featured
```

**Query Parameters:**
- `limit` (integer, optional): Number of articles to return (default: 5)

**Example Request:**
```bash
GET /api/articles/featured?limit=10
```

#### Get Latest Articles
```http
GET /api/articles/latest
```

**Query Parameters:**
- `limit` (integer, optional): Number of articles to return (default: 20)

#### Search Articles
```http
GET /api/articles/search
```

**Query Parameters:**
- `q` (string, required): Search query (minimum 2 characters)
- `page` (integer, optional): Page number (default: 1)
- `per_page` (integer, optional): Items per page (default: 20, max: 100)

**Example Request:**
```bash
GET /api/articles/search?q=artificial intelligence&page=1
```

**Response:**
```json
{
  "success": true,
  "data": [...],
  "pagination": {...},
  "query": "artificial intelligence"
}
```

#### Get Articles by Category
```http
GET /api/articles/category/{categoryId}
```

**Parameters:**
- `categoryId` (integer, required): Category ID

**Query Parameters:**
- `limit` (integer, optional): Number of articles to return (default: 20)

#### Get Articles by Source
```http
GET /api/articles/source/{sourceId}
```

**Parameters:**
- `sourceId` (integer, required): News source ID

**Query Parameters:**
- `limit` (integer, optional): Number of articles to return (default: 20)

### News Sources

#### Get All News Sources
```http
GET /api/sources
```

**Query Parameters:**
- `active` (boolean, optional): Filter by active status

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "NewsAPI",
      "slug": "newsapi",
      "api_provider": "newsapi",
      "description": "Comprehensive news API...",
      "is_active": true,
      "priority": 100,
      "logo_url": "https://example.com/logo.png",
      "website_url": "https://newsapi.org"
    }
  ]
}
```

#### Get Active News Sources
```http
GET /api/sources/active
```

#### Get News Source Statistics
```http
GET /api/sources/statistics
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "NewsAPI",
      "articles_count": 1500
    }
  ]
}
```

#### Get Specific News Source
```http
GET /api/sources/{id}
```

### Categories

#### Get All Categories
```http
GET /api/categories
```

**Query Parameters:**
- `active` (boolean, optional): Filter by active status

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Technology",
      "slug": "technology",
      "description": "Technology news and updates",
      "color": "#10B981",
      "is_active": true
    }
  ]
}
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
```

**Request Body:**
```json
{
  "sources": ["newsapi", "guardian", "nytimes"],
  "categories": ["technology", "business"],
  "limit": 50
}
```

**Response:**
```json
{
  "success": true,
  "message": "News aggregation completed",
  "data": {
    "NewsAPI": {
      "fetched": 50,
      "stored": 45,
      "status": "success"
    },
    "The Guardian": {
      "fetched": 30,
      "stored": 28,
      "status": "success"
    }
  }
}
```

#### Get Aggregation Statistics
```http
GET /api/aggregator/statistics
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_articles": 5000,
    "articles_today": 150,
    "articles_this_week": 1000,
    "total_sources": 3,
    "total_categories": 10,
    "most_active_source": {
      "id": 1,
      "name": "NewsAPI",
      "articles_count": 2500
    },
    "most_popular_category": {
      "id": 1,
      "name": "Technology",
      "articles_count": 800
    }
  }
}
```

#### Get Dashboard Data
```http
GET /api/aggregator/dashboard
```

**Response:**
```json
{
  "success": true,
  "data": {
    "statistics": {...},
    "featured_articles": [...],
    "latest_articles": [...]
  }
}
```

## 🔑 **Complete Token Generation Scenario**

### **Scenario: Getting Started with User Preferences**

This scenario demonstrates the complete flow from user registration/login to accessing protected user preference endpoints.

#### **Step 1: Login with Test Credentials**
```bash
# Login with pre-created test user
curl -X POST "http://localhost:8000/api/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Test User",
      "email": "test@example.com",
      "created_at": "2024-01-15T10:30:00Z"
    },
    "token": "fCkUeiFr5SqkewBtGhBM8K9L2N3P4Q5R6S7T8U9V0W1X2Y3Z4A5B6C7D8E9F0G1H2I3J4K5L6M7N8O9P0Q1R2S3T4U5V6W7X8Y9Z0",
    "token_type": "Bearer"
  }
}
```

#### **Step 2: Extract and Store Token**
```bash
# Save the token from the response above
TOKEN="fCkUeiFr5SqkewBtGhBM8K9L2N3P4Q5R6S7T8U9V0W1X2Y3Z4A5B6C7D8E9F0G1H2I3J4K5L6M7N8O9P0Q1R2S3T4U5V6W7X8Y9Z0"
```

#### **Step 3: Access User Preferences**
```bash
# Get user preferences using the token
curl -X GET "http://localhost:8000/api/user/preferences" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "preferred_sources": [],
    "preferred_categories": [],
    "preferred_authors": [],
    "language": "en",
    "country": "us",
    "articles_per_page": 20,
    "show_images": true,
    "auto_refresh": false,
    "refresh_interval": 300,
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T10:30:00Z"
  }
}
```

#### **Step 4: Update User Preferences**
```bash
# Update user preferences
curl -X PUT "http://localhost:8000/api/user/preferences" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "articles_per_page": 25,
    "show_images": true,
    "auto_refresh": true,
    "refresh_interval": 180
  }'
```

#### **Step 5: Get Personalized Articles**
```bash
# Get personalized articles based on preferences
curl -X GET "http://localhost:8000/api/user/personalized-articles?limit=5" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### **Alternative: Register New User Scenario**

#### **Step 1: Register New User**
```bash
curl -X POST "http://localhost:8000/api/auth/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "password": "mypassword123",
    "password_confirmation": "mypassword123"
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 2,
      "name": "Jane Smith",
      "email": "jane@example.com",
      "created_at": "2024-01-15T11:00:00Z"
    },
    "token": "AbC123DeF456GhI789JkL012MnO345PqR678StU901VwX234YzA567BcD890EfG123HiJ456KlM789NoP012QrS345TuV678WxY901ZaB234CdE567FgH890IjK123LmN456OpQ789RsT012UvW345XyZ678",
    "token_type": "Bearer"
  }
}
```

#### **Step 2: Use New User Token**
```bash
# Save the new token
NEW_TOKEN="AbC123DeF456GhI789JkL012MnO345PqR678StU901VwX234YzA567BcD890EfG123HiJ456KlM789NoP012QrS345TuV678WxY901ZaB234CdE567FgH890IjK123LmN456OpQ789RsT012UvW345XyZ678"

# Get preferences (will create defaults automatically)
curl -X GET "http://localhost:8000/api/user/preferences" \
  -H "Authorization: Bearer $NEW_TOKEN" \
  -H "Accept: application/json"
```

// Run the demonstration
demonstrateTokenUsage();
```

### **Common Error Scenarios**

#### **Invalid Login Credentials**
```bash
curl -X POST "http://localhost:8000/api/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "wrongpassword"
  }'
```

**Response (401):**
```json
{
  "success": false,
  "message": "Invalid credentials",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

#### **Register Existing User**
```bash
curl -X POST "http://localhost:8000/api/auth/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Response (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["This email address is already registered."]
  }
}
```

#### **Invalid Email Format**
```bash
curl -X POST "http://localhost:8000/api/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "invalid-email",
    "password": "password123"
  }'
```

**Response (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["Please provide a valid email address."]
  }
}
```

#### **Missing Password Confirmation**
```bash
curl -X POST "http://localhost:8000/api/auth/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "New User",
    "email": "newuser@example.com",
    "password": "password123"
  }'
```

**Response (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "password": ["Password confirmation does not match."]
  }
}
```

#### **Missing Token**
```bash
curl -X GET "http://localhost:8000/api/user/preferences" \
  -H "Accept: application/json"
```

**Response (401):**
```json
{
  "success": false,
  "message": "Token not provided"
}
```

#### **Invalid Token**
```bash
curl -X GET "http://localhost:8000/api/user/preferences" \
  -H "Authorization: Bearer invalid_token_here" \
  -H "Accept: application/json"
```

**Response (401):**
```json
{
  "success": false,
  "message": "Invalid token"
}
```

## 📚 API Examples & Scenarios

### 🔍 **Article Fetching Examples**

#### **1. Fetch All Articles (Basic)**
```bash
curl -X GET "http://localhost:8000/api/articles" \
  -H "Accept: application/json"
```

```

#### **2. Fetch Featured Articles**
```bash
curl -X GET "http://localhost:8000/api/articles/featured?limit=5" \
  -H "Accept: application/json"
```

#### **3. Fetch Latest Articles**
```bash
curl -X GET "http://localhost:8000/api/articles/latest?limit=10" \
  -H "Accept: application/json"
```

#### **4. Fetch Single Article**
```bash
curl -X GET "http://localhost:8000/api/articles/123" \
  -H "Accept: application/json"
```

---

### 🔎 **Search Examples**

#### **1. Basic Search**
```bash
curl -X GET "http://localhost:8000/api/articles/search?q=technology" \
  -H "Accept: application/json"
```

#### **2. Advanced Search with Pagination**
```bash
curl -X GET "http://localhost:8000/api/articles/search?q=artificial%20intelligence&page=2&per_page=10" \
  -H "Accept: application/json"
```


### 🎯 **Filtering Examples**

#### **1. Filter by Category**
```bash
curl -X GET "http://localhost:8000/api/articles?category_id=1" \
  -H "Accept: application/json"
```

#### **2. Filter by News Source**
```bash
curl -X GET "http://localhost:8000/api/articles?source_id=2" \
  -H "Accept: application/json"
```

#### **3. Filter by Author**
```bash
curl -X GET "http://localhost:8000/api/articles?author=John%20Doe" \
  -H "Accept: application/json"
```

#### **4. Filter by Date Range**
```bash
curl -X GET "http://localhost:8000/api/articles?date_from=2024-01-01&date_to=2024-01-31" \
  -H "Accept: application/json"
```

#### **5. Filter Featured Articles Only**
```bash
curl -X GET "http://localhost:8000/api/articles?featured=true" \
  -H "Accept: application/json"
```

#### **6. Complex Filtering (Multiple Criteria)**
```bash
curl -X GET "http://localhost:8000/api/articles?category_id=1&source_id=2&author=John%20Doe&featured=true&date_from=2024-01-01&date_to=2024-01-31&page=1&per_page=20" \
  -H "Accept: application/json"
```

---

### 👤 **User Preferences Examples**

#### **1. Get User Preferences**
```bash
curl -X GET "http://localhost:8000/api/user/preferences" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```



#### **2. Update User Preferences**
```bash
curl -X PUT "http://localhost:8000/api/user/preferences" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "preferred_sources": [1, 2, 3],
    "preferred_categories": [1, 2],
    "preferred_authors": ["John Doe", "Jane Smith"],
    "articles_per_page": 25,
    "show_images": true,
    "auto_refresh": true,
    "refresh_interval": 180
  }'
```

#### **3. Add Preferred Source**
```bash
curl -X POST "http://localhost:8000/api/user/preferences/sources" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "source_id": 1
  }'
```

#### **4. Remove Preferred Source**
```bash
curl -X DELETE "http://localhost:8000/api/user/preferences/sources" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "source_id": 1
  }'
```

#### **5. Add Preferred Category**
```bash
curl -X POST "http://localhost:8000/api/user/preferences/categories" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "category_id": 1
  }'
```

#### **6. Remove Preferred Category**
```bash
curl -X DELETE "http://localhost:8000/api/user/preferences/categories" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "category_id": 1
  }'
```

---

### 🎯 **Personalized Articles Examples**

#### **1. Get Personalized Feed**
```bash
curl -X GET "http://localhost:8000/api/user/personalized-articles" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### **2. Personalized Feed with Search**
```bash
curl -X GET "http://localhost:8000/api/user/personalized-articles?search=technology" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### **3. Personalized Feed with Filters**
```bash
curl -X GET "http://localhost:8000/api/user/personalized-articles?search=AI&featured=true&date_from=2024-01-01&page=1&per_page=15" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```



### 📊 **News Sources Examples**

#### **1. Get All News Sources**
```bash
curl -X GET "http://localhost:8000/api/sources" \
  -H "Accept: application/json"
```

#### **2. Get Active News Sources Only**
```bash
curl -X GET "http://localhost:8000/api/sources/active" \
  -H "Accept: application/json"
```

#### **3. Get News Source Statistics**
```bash
curl -X GET "http://localhost:8000/api/sources/statistics" \
  -H "Accept: application/json"
```

#### **4. Get Specific News Source**
```bash
curl -X GET "http://localhost:8000/api/sources/1" \
  -H "Accept: application/json"
```

---

### 📂 **Categories Examples**

#### **1. Get All Categories**
```bash
curl -X GET "http://localhost:8000/api/categories" \
  -H "Accept: application/json"
```

#### **2. Get Active Categories Only**
```bash
curl -X GET "http://localhost:8000/api/categories/active" \
  -H "Accept: application/json"
```

#### **3. Get Category Statistics**
```bash
curl -X GET "http://localhost:8000/api/categories/statistics" \
  -H "Accept: application/json"
```

#### **4. Get Articles by Category**
```bash
curl -X GET "http://localhost:8000/api/articles/category/1?limit=10" \
  -H "Accept: application/json"
```

#### **5. Get Articles by Source**
```bash
curl -X GET "http://localhost:8000/api/articles/source/1?limit=10" \
  -H "Accept: application/json"
```

---

### 🔧 **News Aggregator Examples**

#### **1. Trigger News Aggregation**
```bash
curl -X POST "http://localhost:8000/api/aggregator/aggregate" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "sources": ["newsapi", "guardian"],
    "limit": 50
  }'
```


#### **2. Get System Statistics**
```bash
curl -X GET "http://localhost:8000/api/aggregator/statistics" \
  -H "Accept: application/json"
```

#### **3. Get Dashboard Data**
```bash
curl -X GET "http://localhost:8000/api/aggregator/dashboard" \
  -H "Accept: application/json"
```

---


## Rate Limiting

Currently, rate limiting is not implemented. For production use, implement proper rate limiting to prevent abuse.

## Caching

Some endpoints use caching to improve performance:
- Statistics are cached for 5 minutes
- Dashboard data is cached for 2 minutes

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

## Filtering and Sorting

### Articles Filtering
- **Search**: Full-text search across title, description, and content
- **Category**: Filter by specific category
- **Source**: Filter by news source
- **Author**: Filter by author name
- **Date Range**: Filter by publication date
- **Featured**: Filter featured articles

### Sorting
- Articles are sorted by publication date (newest first) by default
- Featured articles are prioritized in search results


