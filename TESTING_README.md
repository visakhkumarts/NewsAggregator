# News Aggregator API Testing Documentation

## 📁 Files Overview

This directory contains comprehensive testing documentation and tools for the News Aggregator API:

### 📋 Documentation Files
- **`API_TESTING_GUIDE.md`** - Complete API documentation with all endpoints, examples, and testing scenarios
- **`TESTING_README.md`** - This file - Quick start guide for testing

### 🧪 Test Scripts
- **`test-api.sh`** - Bash script for Linux/Mac testing
- **`test-api.bat`** - Windows batch file for testing
- **`test-api.ps1`** - PowerShell script for Windows (recommended)

### 📮 Postman Files
- **`NewsAggregator_API.postman_collection.json`** - Complete Postman collection
- **`NewsAggregator_Environment.postman_environment.json`** - Postman environment variables

---

## 🚀 Quick Start Testing

### Option 1: PowerShell Script (Recommended for Windows)
```powershell
# Run the PowerShell test script
powershell -ExecutionPolicy Bypass -File test-api.ps1
```

### Option 2: Batch File (Windows)
```cmd
# Run the batch file
test-api.bat
```

### Option 3: Bash Script (Linux/Mac)
```bash
# Make executable and run
chmod +x test-api.sh
./test-api.sh
```

### Option 4: Manual cURL Commands
```bash
# Register a user
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123","password_confirmation":"password123"}'

# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'

# Get articles
curl -X GET "http://localhost:8000/api/articles?limit=5"

# Trigger aggregation
curl -X POST http://localhost:8000/api/aggregator/aggregate
```

---

## 📮 Postman Setup

### 1. Import Collection
1. Open Postman
2. Click "Import"
3. Select `NewsAggregator_API.postman_collection.json`

### 2. Import Environment
1. Click "Import" again
2. Select `NewsAggregator_Environment.postman_environment.json`

### 3. Set Environment
1. Click the environment dropdown (top right)
2. Select "News Aggregator Environment"

### 4. Start Testing
1. Run "Register User" or "Login User" first
2. The token will be automatically set in the environment
3. All other requests will use the token automatically

---

## 🔧 Prerequisites

### 1. Start the Server
```bash
php artisan serve
```
Server will run at: `http://localhost:8000`

### 2. Run Migrations
```bash
php artisan migrate
```

### 3. (Optional) Seed Data
```bash
php artisan db:seed
```

---

## 📊 What Gets Tested

The test scripts automatically verify:

### ✅ Authentication Flow
- User registration
- User login
- Token generation and validation
- Protected endpoint access
- User logout

### ✅ Public Endpoints
- Get all articles
- Get featured articles
- Get latest articles
- Search articles
- Get categories
- Get news sources

### ✅ Protected Endpoints
- Get user info
- Get user preferences
- Update user preferences
- Add/remove preferred sources
- Add/remove preferred categories
- Get personalized articles

### ✅ News Aggregation
- Manual news aggregation
- Fetch new articles from APIs
- Update existing articles

---

## 🎯 Testing Scenarios

### Scenario 1: New User Registration
1. Register new user
2. Login to get token
3. Set preferences
4. Get personalized articles

### Scenario 2: Existing User Login
1. Login with existing credentials
2. Update preferences
3. Add preferred categories/sources
4. Get personalized content

### Scenario 3: News Aggregation
1. Trigger manual aggregation
2. Check for new articles
3. Verify article data integrity

### Scenario 4: Search and Filter
1. Search articles by keyword
2. Filter by category
3. Filter by source
4. Test pagination

---

## 🚨 Troubleshooting

### Common Issues

#### Server Not Running
```
Error: Server is not running
Solution: Run `php artisan serve`
```

#### Database Issues
```
Error: Migration failed
Solution: Run `php artisan migrate`
```

#### Token Issues
```
Error: Unauthenticated
Solution: Login first to get a valid token
```

#### Rate Limiting
```
Error: Too many requests
Solution: Wait a minute and try again
```

### Debug Commands
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check database
php artisan tinker
>>> App\Models\User::count()

# Check routes
php artisan route:list --path=api
```

---

## 📈 Performance Testing

### Load Testing with cURL
```bash
# Test multiple concurrent requests
for i in {1..10}; do
  curl -X GET "http://localhost:8000/api/articles?limit=5" &
done
wait
```

### Memory Usage
```bash
# Monitor memory usage
php artisan tinker --execute="echo memory_get_usage(true) / 1024 / 1024 . ' MB';"
```

---

## 🔍 API Response Examples

### Successful Response
```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {
        // Response data here
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

---

## 📞 Support

### Getting Help
1. Check the detailed `API_TESTING_GUIDE.md`
2. Review Laravel logs: `storage/logs/laravel.log`
3. Verify all prerequisites are met
4. Test with Postman for advanced debugging

### Useful Commands
```bash
# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Check application status
php artisan about

# Test database connection
php artisan tinker --execute="DB::connection()->getPdo();"
```

---

## 🎉 Success Indicators

When everything is working correctly, you should see:
- ✅ All test scripts complete without errors
- ✅ Postman collection runs successfully
- ✅ API returns proper JSON responses
- ✅ Authentication tokens work for protected endpoints
- ✅ News aggregation fetches new articles
- ✅ User preferences are saved and retrieved

**Happy Testing! 🚀**
