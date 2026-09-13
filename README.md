Chea Panha, [12/11/2025 2:04 PM]
Chea Panha, [12/11/2025 1:56 PM]
Ńhä, [11/9/2025 3:34 PM]
Nha🤍, [10/17/2025 8:18 PM]
composer global require laravel/installer
laravel new myapp
php artisan servelaravel
php artisan serve --no-reload

//Note: Video = (44th)
Build Laravel Project Continue Okay

//For create file = api.php = in ROUTES
php artisan install:api
php artisan serve --host=192.168.56.1 --port=8000
// or create file = create_filenames_table = in Database/migration
php artisan make:migration create_blogs_table



//For create DataBase table , Then open DataBase.Sqlite
php artisan migrate
php artisan migrate:fresh
php artisan make:migration add_status_to_users_table --table=users




// Create filename in Models,
php artisan make:model Blog
php artisan make:model Category -cmdf
((// Note: Controller that created in APP/Http/ in API (Filename)= LIke(API\RegisterController)for make api
//In Controller For API**\_**))
php artisan make:controller API\RegisterController
php artisan make:controller AuthController

// Genarate MiddleWare
php artisan make:middleware EnsureTokenIsValid

Nha🤍, [10/17/2025 8:19 PM]
php artisan make:model Cart_more -cmsrf
php artisan make:model Product -cmsrf
php artisan make:model Category -cmsrf
php artisan make:model OrderItem -cmsrf

//Install file API
php artisan install:api

# Database schema for the project

## Table: users

### Store the user information

| Column Name | Data Type   | Description                      |
| ----------- | ----------- | -------------------------------- |
| id          | SERIAL      | Primary Key                      |
| username    | VARCHAR(50) | Not Null, Unique                 |
| password    | VARCHAR(50) | Not Null                         |
| email       | VARCHAR(50) | Not Null, Unique                 |
| acvatar     | VARCHAR(50) | Not Null, Default: 'default.jpg' |

| created_on | TIMESTAMP | Not Null, Default: CURRENT_TIMESTAMP |
| updated_on | TIMESTAMP | Not Null, Default: CURRENT_TIMESTAMP |

### Table: addresses

#### Store the address information of the user to deliver the products

| Column Name | Data Type   | Description                          |
| ----------- | ----------- | ------------------------------------ |
| id          | Data Type   | Primary Key                          |
| user_id     | INTEGER     | Foreign Key(users.id)                |
| line1       | TEXT        | Not Null                             |
| line2       | TEXT        | Not Null                             |
| city        | VARCHAR(50) | Not Null                             |
| state       | VARCHAR(50) | Not Null                             |
| country     | VARCHAR(50) | Not Null                             |
| postal_code | VARCHAR(50) | Not Null                             |
| longitude   | FLOAT       | Not Null                             |
| latitude    | FLOAT       | Not Null                             |
| created_on  | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |
| updated_on  | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |

### Table: categories

#### Store the categories of the products

| Column Name | Data Type   | Description                          |
| ----------- | ----------- | ------------------------------------ |
| id          | SERIAL      | Primary Key                          |
| name        | VARCHAR(50) | Not Null, Unique                     |
| description | TEXT        | Not Null                             |
| created_on  | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |
| updated_on  | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |

### Table: products

#### Store the products information

| Column Name | Data Type   | Description                          |
| ----------- | ----------- | ------------------------------------ |
| id          | SERIAL      | Primary Key                          |
| category_id | INTEGER     | Foreign Key(categories.id)           |
| name        | VARCHAR(50) | Not Null                             |
| description | TEXT        | Not Null                             |
| price       | FLOAT       | Not Null                             |
| image       | VARCHAR(50) | Not Null, Default: 'default.jpg      |
| created_on  | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |
| updated_on  | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |

### Table: orders

#### Store the order information

Chea Panha, [12/11/2025 2:04 PM]
Chea Panha, [12/11/2025 1:56 PM]
Ńhä, [11/9/2025 3:34 PM]
| Column Name | Data Type | Description |
| ------------ | ----------- | ------------------------------------ |
| id | SERIAL | Primary Key |
| user_id | INTEGER | Foreign Key(users.id) |
| address_id | INTEGER | Foreign Key(addresses.id) |
| status | VARCHAR(50) | Not Null, Default: 'PENDING' |
| total_amount | FLOAT | Not Null |
| cart_id | INTEGER | Foreign Key(carts.id) |
| created_on | TIMESTAMP | Not Null, Default: CURRENT_TIMESTAMP |
| updated_on | TIMESTAMP | Not Null, Default: CURRENT_TIMESTAMP |

### Order: order_items

| Column Name | Data Type | Description                          |
| ----------- | --------- | ------------------------------------ |
| id          | SERIAL    | Primary Key                          |
| order_id    | INTEGER   | Foreign Key(orders.id)               |
| product_id  | INTEGER   | Foreign Key(products.id)             |
| quantity    | INTEGER   | Not Null                             |
| price       | FLOAT     | Not Null                             |
| created_on  | TIMESTAMP | Not Null, Default: CURRENT_TIMESTAMP |
| updated_on  | TIMESTAMP | Not Null, Default: CURRENT_TIMESTAMP |

### Table: carts

| Column Name | Data Type   | Description                          |
| ----------- | ----------- | ------------------------------------ |
| id          | SERIAL      | Primary Key                          |
| user_id     | INTEGER     | Foreign Key(users.id)                |
| status      | VARCHAR(50) | Not Null, Default: 'ACTIVE'          |
| total       | FLOAT       | Not Null                             |
| created_on  | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |
| updated_on  | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |

### Table: cart_items

| Column Name | Data Type | Description                          |
| ----------- | --------- | ------------------------------------ |
| id          | SERIAL    | Primary Key                          |
| cart_id     | INTEGER   | Foreign Key(carts.id)                |
| product_id  | INTEGER   | Foreign Key(products.id)             |
| quantity    | INTEGER   | Not Null                             |
| price       | FLOAT     | Not Null                             |
| created_on  | TIMESTAMP | Not Null, Default: CURRENT_TIMESTAMP |
| updated_on  | TIMESTAMP | Not Null, Default: CURRENT_TIMESTAMP |

### Table: Payments

| Column Name    | Data Type   | Description                          |
| -------------- | ----------- | ------------------------------------ |
| id             | SERIAL      | Primary Key                          |
| order_id       | INTEGER     | Foreign Key(orders.id)               |
| user_id        | INTEGER     | Foreign Key(users.id)                |
| amount         | FLOAT       | Not Null                             |
| payment_method | VARCHAR(50) | Not Null                             |
| status         | VARCHAR(50) | Not Null, Default: 'PENDING'         |
| created_on     | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |
| updated_on     | TIMESTAMP   | Not Null, Default: CURRENT_TIMESTAMP |
