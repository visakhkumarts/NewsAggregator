# News Aggregator API Testing Guide

## 📋 Table of Contents
- [Overview](#overview)
- [Setup & Prerequisites](#setup--prerequisites)
- [Authentication](#authentication)
- [API Endpoints](#api-endpoints)
- [Testing Commands](#testing-commands)
- [Sample Requests](#sample-requests)
- [Error Handling](#error-handling)

## 🎯 Overview

This guide provides comprehensive testing documentation for the News Aggregator API. The API allows users to register, authenticate, fetch news articles from multiple sources, and manage personalized preferences.

**Base URL**: `http://localhost:8000/api`

## 🛠️ Setup & Prerequisites

### 1. Start the Application
```bash
# Start Laravel development server
php artisan serve

# Or if using WAMP/XAMPP, ensure your server is running
# Default URL: http://localhost:8000
```

### 2. Run Database Migrations
```bash
php artisan migrate
```

### 3. Seed Sample Data (Optional)
```bash
php artisan db:seed
```

## 🔐 Authentication

The API uses Laravel Sanctum for token-based authentication. All protected endpoints require a Bearer token in the Authorization header.

### Authentication Flow:
1. **Register** or **Login** to get a token
2. Include token in `Authorization: Bearer {token}` header
3. Use token for protected endpoints

---

## 📡 API Endpoints

### 🔑 Authentication Endpoints

#### 1. User Registration
```http
POST /api/auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```



#### 2. User Login
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```



#### 4. Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

#### 5. Refresh Token
```http
POST /api/auth/refresh
Authorization: Bearer {token}
```

---

### 📰 News Articles Endpoints

#### 1. Get All Articles
```http
GET /api/articles?page=1&limit=20
```

#### 2. Get Featured Articles
```http
GET /api/articles/featured?limit=5
```

#### 3. Get Latest Articles
```http
GET /api/articles/latest?limit=10
```

#### 4. Search Articles
```http
GET /api/articles/search?q=technology&limit=15
```

#### 5. Get Article by ID
```http
GET /api/articles/{id}
```

#### 6. Get Articles by Category
```http
GET /api/articles/category/{categoryId}?limit=20
```

#### 7. Get Articles by Source
```http
GET /api/articles/source/{sourceId}?limit=20
```

---

### 🏷️ Categories Endpoints

#### 1. Get All Categories
```http
GET /api/categories
```

#### 2. Get Active Categories
```http
GET /api/categories/active
```

#### 3. Get Category Statistics
```http
GET /api/categories/statistics
```

#### 4. Get Category by ID
```http
GET /api/categories/{id}
```

---

### 📡 News Sources Endpoints

#### 1. Get All Sources
```http
GET /api/sources
```

#### 2. Get Active Sources
```http
GET /api/sources/active
```

#### 3. Get Source Statistics
```http
GET /api/sources/statistics
```

#### 4. Get Source by ID
```http
GET /api/sources/{id}
```

---

### 👤 User Preferences Endpoints (Protected)

#### 1. Get User Preferences
```http
GET /api/user/preferences
Authorization: Bearer {token}
```

#### 2. Update User Preferences
```http
PUT /api/user/preferences
Authorization: Bearer {token}
Content-Type: application/json

{
    "language": "en",
    "country": "us",
    "articles_per_page": 20,
    "show_images": true,
    "auto_refresh": false,
    "refresh_interval": 300
}
```

#### 3. Add Preferred Source
```http
POST /api/user/preferences/sources
Authorization: Bearer {token}
Content-Type: application/json

{
    "source_id": 1
}
```

#### 4. Remove Preferred Source
```http
DELETE /api/user/preferences/sources
Authorization: Bearer {token}
Content-Type: application/json

{
    "source_id": 1
}
```

#### 5. Add Preferred Category
```http
POST /api/user/preferences/categories
Authorization: Bearer {token}
Content-Type: application/json

{
    "category_id": 1
}
```

#### 6. Remove Preferred Category
```http
DELETE /api/user/preferences/categories
Authorization: Bearer {token}
Content-Type: application/json

{
    "category_id": 1
}
```


#### **2. Update User Preferences**
```http
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
  }
```


#### 7. Get Personalized Articles
```http
GET /api/user/personalized-articles?limit=20
Authorization: Bearer {token}
```

---

### 🔄 News Aggregation Endpoints

#### 1. Manual News Aggregation
```http
POST /api/aggregator/aggregate
```

**Response:**
```json
{
    "success": true,
    "message": "News aggregation completed successfully",
    "data": {
        "total_articles": 150,
        "sources_processed": 3,
        "new_articles": 45,
        "updated_articles": 12,
        "processing_time": "2.5 seconds"
    }
}
```

#### 2. Get Aggregation Dashboard
```http
GET /api/aggregator/dashboard
```

#### 3. Get Aggregation Statistics
```http
GET /api/aggregator/statistics
```

---

## 🧪 Testing Commands


## 🚨 Error Handling

### Common Error Responses

#### 401 Unauthorized
```json
{
    "success": false,
    "message": "Unauthenticated."
}
```

#### 422 Validation Error
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

#### 404 Not Found
```json
{
    "success": false,
    "message": "Article not found"
}
```

#### 500 Server Error
```json
{
    "success": false,
    "message": "Internal server error"
}
```

---

## 🔧 Rate Limiting

The API implements rate limiting for different endpoints:

- **Authentication endpoints**: 5 requests per minute
- **Protected endpoints**: 20-50 requests per minute
- **Public endpoints**: No rate limiting

### Rate Limit Headers
```
X-RateLimit-Limit: 5
X-RateLimit-Remaining: 4
X-RateLimit-Reset: 1640995200
```

---

## 📊 Testing Checklist

### ✅ Authentication Flow
- [ ] User registration works
- [ ] User login works
- [ ] Token is returned on login
- [ ] Protected endpoints require token
- [ ] Logout invalidates token
- [ ] Token refresh works

### ✅ News Articles
- [ ] Can fetch all articles
- [ ] Can get featured articles
- [ ] Can search articles
- [ ] Can filter by category
- [ ] Can filter by source
- [ ] Pagination works

### ✅ User Preferences
- [ ] Can get preferences
- [ ] Can update preferences
- [ ] Can add/remove preferred sources
- [ ] Can add/remove preferred categories
- [ ] Personalized articles work

### ✅ News Aggregation
- [ ] Manual aggregation works
- [ ] Dashboard shows statistics
- [ ] New articles are fetched
- [ ] Duplicate articles are handled

---

## 🎯 Quick Test Script

Save this as `test-api.sh` and run with `bash test-api.sh`:

```bash
#!/bin/bash

BASE_URL="http://localhost:8000/api"

echo "🚀 Testing News Aggregator API"
echo "================================"

# Test 1: Register User
echo "1. Registering user..."
REGISTER_RESPONSE=$(curl -s -X POST $BASE_URL/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }')

echo "Register Response: $REGISTER_RESPONSE"

# Extract token
TOKEN=$(echo $REGISTER_RESPONSE | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
echo "Token: $TOKEN"

# Test 2: Get Articles
echo "2. Fetching articles..."
curl -s -X GET "$BASE_URL/articles?limit=5" | head -c 200
echo "..."

# Test 3: Get User Info
echo "3. Getting user info..."
curl -s -X GET $BASE_URL/auth/me \
  -H "Authorization: Bearer $TOKEN" | head -c 200
echo "..."

# Test 4: Trigger Aggregation
echo "4. Triggering news aggregation..."
curl -s -X POST $BASE_URL/aggregator/aggregate | head -c 200
echo "..."

echo "✅ API Testing Complete!"
```

---

## 📞 Support

For issues or questions:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Verify database connection
3. Ensure all migrations are run
4. Check API key configurations for news services

**Happy Testing! 🎉**
