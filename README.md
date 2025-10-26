# News Aggregator Demo

A modern Laravel 12 news aggregator application that fetches and displays news articles from multiple sources including NewsAPI.org, The Guardian, and The New York Times. Built with Livewire and Flux UI components.

## Features

- 📰 Aggregate news from multiple sources (NewsAPI.org, The Guardian, NY Times)
- 🔐 User authentication with Laravel Fortify (registration, login, password reset)
- 🔒 Two-factor authentication (2FA) support
- 🎨 Beautiful, modern UI with Flux UI components and Tailwind CSS v4
- ⚡ Real-time interactivity with Livewire v3 and Volt
- 📱 Responsive design for mobile and desktop
- 🌙 Dark mode support
- 🔑 API key management interface
- 📊 Dashboard with news statistics
- 🔍 Public news browsing (no authentication required)

## Technology Stack

- **Backend**: PHP 8.4, Laravel 12
- **Frontend**: Livewire 3, Flux UI, Tailwind CSS v4, Alpine.js
- **Authentication**: Laravel Fortify
- **Database**: MySQL (default, configurable)
- **Testing**: Pest v4 (with browser testing support)
- **Build Tool**: Vite

## Prerequisites

Before you begin, ensure you have the following installed on your system:

- **PHP**: >= 8.2 (PHP 8.4 recommended)
- **Composer**: Latest version
- **Node.js**: >= 18.x
- **NPM**: >= 9.x
- **MySQL**: Enabled in PHP (or another database of your choice)

### PHP Extensions Required

- OpenSSL
- PDO
- Mbstring
- Tokenizer
- XML
- Ctype
- JSON
- BCMath
- Fileinfo
- SQLite (if using SQLite database)

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url> news-api-demo
cd news-api-demo
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install JavaScript Dependencies

```bash
npm install
```

### 4. Environment Configuration

Create your environment file:

```bash
# On Linux/Mac
cp .env.example .env

# On Windows
copy .env.example .env
```

If `.env.example` doesn't exist, create a `.env` file with the following essential configuration:

```env
APP_NAME="News Aggregator"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=news_api_demo
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 7. Run Database Migrations

```bash
php artisan migrate
```

This will create all necessary tables:
- `users` - User accounts
- `news_sources` - News source information
- `news` - News articles
- `api_keys` - API keys for news sources
- `cache`, `jobs`, `sessions` - Framework tables

### 8. Seed API Keys

**Important**: Before you can fetch news, you must seed the API keys into the database:

```bash
php artisan db:seed --class=ApiKeySeeder
```

This will populate the database with API keys for:
- NewsAPI.org
- The Guardian
- The New York Times

**Note**: The seeder includes demo API keys. For production use, you should:
1. Register for your own API keys from each service
2. Update the API keys in the database or modify the seeder

#### Getting Your Own API Keys

- **NewsAPI.org**: https://newsapi.org/register
- **The Guardian**: https://open-platform.theguardian.com/access/
- **The New York Times**: https://developer.nytimes.com/get-started

### 9. Build Frontend Assets

```bash
npm run build
```

For development with hot module replacement:

```bash
npm run dev
```

Keep this running in a separate terminal during development.

## Running the Application

### Development Server

#### Option 1: Using Artisan (Simple)

```bash
php artisan serve
```

The application will be available at: http://localhost:8000

#### Option 2: Using Composer Dev Script (Recommended)

This runs the Laravel server, queue worker, and Vite dev server concurrently:

```bash
composer run dev
```

This will start:
- Laravel development server on http://localhost:8000
- Vite dev server for hot module replacement
- Queue worker for background jobs

### Production Server

For production deployment:

1. Build assets:
```bash
npm run build
```

2. Configure your web server (Apache, Nginx) to point to the `public` directory
3. Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`
4. Run migrations: `php artisan migrate --force`
5. Optimize: `php artisan optimize`
6. Set up a queue worker: `php artisan queue:work`

## Accessing the Application

### Public Pages (No Authentication Required)

- **Home/News Listing**: http://localhost:8000
- **News Detail**: http://localhost:8000/news/{id}

Browse and read news articles without logging in.

### Authentication Pages

- **Register**: http://localhost:8000/register
- **Login**: http://localhost:8000/login
- **Password Reset**: http://localhost:8000/forgot-password

### Authenticated Pages

After logging in, you can access:

- **Dashboard**: http://localhost:8000/dashboard
- **Dashboard News**: http://localhost:8000/dashboard/news
- **API Keys Management**: http://localhost:8000/api-keys
- **User Settings**: http://localhost:8000/settings
  - Profile: http://localhost:8000/settings/profile
  - Password: http://localhost:8000/settings/password
  - Appearance: http://localhost:8000/settings/appearance
  - Two-Factor Auth: http://localhost:8000/settings/two-factor

### Default User Accounts

No default user accounts are created. You must register a new account through the registration page.

## Managing API Keys

1. Log in to the application
2. Navigate to **API Keys** (http://localhost:8000/api-keys)
3. Here you can:
   - View all configured API keys
   - Create new API keys for additional sources
   - Edit existing API keys
   - Update API endpoints and keys

## Fetching News

The application fetches news through service classes:
- `App\Services\NewsApiService` - NewsAPI.org
- `App\Services\GuardianService` - The Guardian
- `App\Services\NYTimesService` - The New York Times

To manually test fetching news, you can use Laravel Tinker:

```bash
php artisan tinker
```

Then run:

```php
// Fetch from NewsAPI.org
app(App\Services\NewsApiService::class)->fetchNewsFromNewsApi();

// Fetch from The Guardian
app(App\Services\GuardianService::class)->fetchNewsFromGuardian();

// Fetch from NY Times
app(App\Services\NYTimesService::class)->fetchNewsFromNYTimes();
```

Or create a scheduled command or manual trigger in your application.

## Testing

This application uses Pest v4 for testing.

### Run All Tests

```bash
php artisan test
```

Or using Composer:

```bash
composer run test
```

### Run Specific Test File

```bash
php artisan test tests/Feature/DashboardTest.php
```

### Run Tests with Filter

```bash
php artisan test --filter=testName
```

### Browser Testing

Pest v4 includes browser testing capabilities. Browser tests are located in `tests/Browser/`.

## Code Formatting

This project uses Laravel Pint for code formatting:

```bash
vendor/bin/pint
```

To check formatting without making changes:

```bash
vendor/bin/pint --test
```

## Project Structure

```
news-api-demo/
├── app/
│   ├── Actions/          # Fortify actions
│   ├── Http/
│   │   ├── Controllers/  # Controllers
│   │   └── Resources/    # API Resources
│   ├── Livewire/         # Livewire components
│   ├── Models/           # Eloquent models
│   ├── Providers/        # Service providers
│   └── Services/         # News fetching services
├── database/
│   ├── factories/        # Model factories
│   ├── migrations/       # Database migrations
│   └── seeders/          # Database seeders
├── resources/
│   ├── css/             # CSS files
│   ├── js/              # JavaScript files
│   └── views/           # Blade templates
│       ├── components/  # Blade components
│       ├── flux/        # Flux UI customizations
│       └── livewire/    # Livewire Volt pages
├── routes/
│   ├── web.php          # Web routes
│   └── console.php      # Console routes
├── tests/
│   ├── Feature/         # Feature tests
│   └── Unit/            # Unit tests
└── public/              # Public assets
```

## Key Models

- **User** (`App\Models\User`) - User accounts with 2FA support
- **News** (`App\Models\News`) - News articles
- **NewsSource** (`App\Models\NewsSource`) - News source information
- **ApiKey** (`App\Models\ApiKey`) - API keys for external services

## Configuration

### Database

Edit `config/database.php` or use environment variables to configure your database connection.

### News APIs

API keys are stored in the `api_keys` table. You can manage them through:
- The web interface (http://localhost:8000/api-keys)
- Direct database access
- The `ApiKeySeeder`

### Authentication

Authentication is configured in:
- `config/fortify.php` - Fortify configuration
- `app/Providers/FortifyServiceProvider.php` - Fortify customization

Enable/disable features in `config/fortify.php`:
```php
'features' => [
    Features::registration(),
    Features::resetPasswords(),
    Features::updateProfileInformation(),
    Features::updatePasswords(),
    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]),
],
```

## Troubleshooting

### Issue: "Vite manifest not found"

**Solution**: Run `npm run build` or start the dev server with `npm run dev`

### Issue: Database connection errors

**Solution**: 
- Ensure the MySQL server exists
- Check the database settings in `.env`

### Issue: API keys not working

**Solution**:
- Verify you've run `php artisan db:seed --class=ApiKeySeeder`
- Check the API keys are valid (they may expire or have rate limits)
- Register for your own API keys from the respective services

### Issue: Frontend changes not reflecting

**Solution**:
- Run `npm run build` to rebuild assets
- Or run `npm run dev` for development with hot reload
- Clear browser cache

### Issue: Authentication not working

**Solution**:
- Ensure migrations have been run: `php artisan migrate`
- Check `APP_KEY` is set in `.env`
- Clear config cache: `php artisan config:clear`

## Development Commands

```bash
# Start development environment (server + queue + vite)
composer run dev

# Run tests
composer run test
php artisan test

# Format code
vendor/bin/pint

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# List all routes
php artisan route:list

# Create a new migration
php artisan make:migration create_example_table

# Create a new model with factory and migration
php artisan make:model Example -mf

# Create a new Livewire component
php artisan make:livewire ExampleComponent

# Create a new Volt page
php artisan make:volt example-page

# Create a new test
php artisan make:test ExampleTest --pest
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For issues, questions, or contributions, please open an issue on the repository.

## Credits

Built with:
- [Laravel](https://laravel.com) - PHP Framework
- [Livewire](https://livewire.laravel.com) - Full-stack framework
- [Flux UI](https://flux.laravel.com) - UI Component library
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS framework
- [Laravel Fortify](https://github.com/laravel/fortify) - Authentication backend
- [Pest](https://pestphp.com) - Testing framework

News provided by:
- [NewsAPI.org](https://newsapi.org)
- [The Guardian Open Platform](https://open-platform.theguardian.com)
- [The New York Times API](https://developer.nytimes.com)

