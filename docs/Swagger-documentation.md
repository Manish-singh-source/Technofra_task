# Laravel Swagger (L5-Swagger) Setup Guide

This guide will help you integrate Swagger (OpenAPI) into any Laravel project using L5-Swagger.

---

## 🚀 Step 1: Install Package

Run the following command:

```bash
composer require "darkaonline/l5-swagger"
```

---

## ⚙️ Step 2: Publish Configuration

```bash
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
```

This will create the config file:

```
config/l5-swagger.php
```

---

## 🧾 Step 3: Add Swagger Info (Required)

Create a controller:

```
app/Http/Controllers/SwaggerController.php
```

Add this code:

```php
<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Your Project API",
 *     version="1.0.0",
 *     description="API documentation for your Laravel project"
 * )
 */
class SwaggerController extends Controller
{
    //
}
```

---

## 🔄 Step 4: Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
```

---

## 📄 Step 5: Generate Swagger Docs

```bash
php artisan l5-swagger:generate
```

---

## 🌐 Step 6: Run Project

```bash
php artisan serve
```

Open in browser:

```
http://localhost:8000/api/documentation
```

---

## ✍️ Step 7: Add API Documentation

Example in a controller:

```php
/**
 * @OA\Get(
 *     path="/api/users",
 *     summary="Get all users",
 *     @OA\Response(
 *         response=200,
 *         description="Success"
 *     )
 * )
 */
public function index()
{
    return User::all();
}
```

---

## 🔁 Step 8: Regenerate After Changes

```bash
php artisan l5-swagger:generate
```

---

## 📌 Notes

* Always add annotations above controller methods
* Keep your APIs under `/api` routes
* Regenerate docs after every change

---

## ✅ Result

You now have:

* Auto-generated API documentation
* Interactive API testing UI
* Structured API documentation using OpenAPI

---

## 🎯 Ready to Use

You can reuse this setup in any Laravel project by following these steps.
