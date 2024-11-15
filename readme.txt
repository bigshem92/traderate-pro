# TradeRate Pro

Professional Trade Rating and Management System

## Requirements

- PHP >= 7.4
- MySQL >= 5.7
- Composer
- Node.js & NPM (for frontend assets)

## Installation

1. Clone the repository
```bash
git clone https://github.com/yourusername/traderate-pro.git
cd traderate-pro
```

2. Install dependencies
```bash
composer install
```

3. Setup environment file
```bash
cp .env.example .env
```

4. Configure your .env file with your database and other service credentials

5. Create database and import schema
```bash
mysql -u your_username -p your_database < database/schema.sql
```

6. Set directory permissions
```bash
chmod -R 755 storage
chmod -R 755 public/uploads
```

7. Generate application key
```bash
php artisan key:generate
```

## Features

- User Authentication (Traders & Customers)
- Job Posting and Management
- Quote System
- Review and Rating System
- Secure Payment Processing
- Real-time Notifications
- Admin Dashboard
- API Integration

## Security

For any security issues, please email security@traderate.pro

## License

The TradeRate Pro is proprietary software. Unauthorized copying, modification, distribution, or use is strictly prohibited.
