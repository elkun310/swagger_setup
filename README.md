# Laravel Swagger API Documentation Setup

This guide walks you through the basic steps to integrate Swagger into your Laravel project for API documentation.

## Prerequisites

Before you begin, ensure you have:

- Laravel installed and a project set up.
- Composer installed to manage dependencies.

## Installation Steps

### 1. Install Swagger Package

Run the following commands in your terminal to install the `l5-swagger` package:

```bash
composer require darkaonline/l5-swagger
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
```

### 2. Configure .env File

Add the following to your `.env` file:

```bash
L5_SWAGGER_CONST_HOST=http://project.test/api/v1
```
Replace http://project.test/api/v1 with the actual base URL for your API if it's different.

### 3. Add Swagger Annotations to Your Controller
app/Http/Controllers/Controller.php
```bash
<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 * title="APIs For Thrift Store",
 * version="1.0.0",
 * ),
 * @OA\SecurityScheme(
 * securityScheme="bearerAuth",
 * in="header",
 * name="bearerAuth",
 * type="http",
 * scheme="bearer",
 * bearerFormat="JWT",
 * ),
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}

?>
```

### 4. Generate Swagger Documentation
Once you’ve added the necessary annotations to your controllers, run the following Artisan command to generate the Swagger API documentation:

```bash
php artisan l5-swagger:generate
```

### 5. Access Swagger Documentation

Once the documentation is generated, you can access it at the following URL:

```bash
http://project.test/api/v1/documentation
```

## Troubleshooting
- Ensure that your controller methods are properly annotated with Swagger annotations to avoid missing documentation.
- If you encounter any issues, run the flowing command to clear the configuration cache:
```bash
php artisan config:clear
```

