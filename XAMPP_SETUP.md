# Apache Configuration for Snake Game Laravel API

## Setup Instructions for XAMPP

### 1. Copy Laravel Project
Copy the "Laravel Backend" folder to: `C:\xampp\htdocs\snake-api`

### 2. Create Virtual Host (Optional but Recommended)
Add this to `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/snake-api/public"
    ServerName snake-api.local
    <Directory "C:/xampp/htdocs/snake-api/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Add this line to `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1 snake-api.local
```

### 3. Update .env file
```env
APP_URL=http://snake-api.local
# or if not using virtual host:
APP_URL=http://localhost/snake-api/public
```

### 4. Update Frontend API URL
In React Native `services/apiService.ts`:
```typescript
const API_BASE_URL = 'http://snake-api.local/api';
// or if not using virtual host:
const API_BASE_URL = 'http://localhost/snake-api/public/api';
```

### 5. Test URLs
- Health Check: http://snake-api.local/api/health
- Or: http://localhost/snake-api/public/api/health
