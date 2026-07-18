# Despliegue en producción

El document root de Apache/Nginx debe apuntar exclusivamente a `public/`, con HTTPS y redirección desde HTTP. Configure un límite de solicitud superior a 20 MB (por ejemplo 25 MB).

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --class=RolesAndPermissionsSeeder --force
php artisan rimis:migrate-existing-researchers
php artisan rimis:prepare-public-researcher-profiles
npm ci && npm run build
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Use `APP_ENV=production`, `APP_DEBUG=false`, HTTPS, cookies seguras y credenciales externas al repositorio. Bloquee `.env`, `.git`, `vendor`, `storage`, dumps y archivos de configuración desde el servidor web.

En Linux, adapte el usuario web y evite permisos 777:

```bash
chown -R www-data:www-data storage bootstrap/cache
find storage bootstrap/cache -type d -exec chmod 775 {} \;
find storage bootstrap/cache -type f -exec chmod 664 {} \;
```

No hay tareas programadas propias actualmente. Si se agregan, configure `* * * * * cd /ruta/rimis-cms && php artisan schedule:run >> /dev/null 2>&1`. Si cambia a `QUEUE_CONNECTION=database`, cree la tabla correspondiente y ejecute un worker no-root con Supervisor/systemd: `php artisan queue:work --sleep=3 --tries=3 --timeout=120`.
