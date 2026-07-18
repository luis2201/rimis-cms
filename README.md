# RIMIS CMS

Portal institucional de la Red de Investigadores RIMIS. Gestiona usuarios, postulaciones, membresías, perfiles públicos, eventos, boletines, convocatorias, aportes editoriales y publicaciones científicas.

## Tecnología y requisitos

- PHP 8.0.2 o superior con extensiones Laravel/MySQL, ZIP y Fileinfo.
- Composer 2, MySQL 8 o MariaDB compatible, Node.js y npm.
- Laravel 9, Blade, Spatie Permission, Vite y Tailwind/AdminLTE.

## Instalación local

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan rimis:migrate-existing-researchers
php artisan rimis:prepare-public-researcher-profiles
npm ci
npm run build
php artisan serve
```

Configure primero la base, correo y `APP_URL` en `.env`. Los archivos privados utilizan el disco `local`; no mueva CV o PDF privados a `public/`.

## Pruebas y operación

```bash
php artisan optimize:clear
php artisan test
php artisan route:list
php artisan migrate:status
composer audit
npm audit
```

Roles: `USUARIO`, `INVESTIGADOR`, `WEBMASTER` y `ADMINISTRADOR`. La aprobación editorial no publica automáticamente. Consulte [instalación](docs/INSTALLATION.md), [despliegue](docs/DEPLOYMENT.md), [roles](docs/ROLES_AND_PERMISSIONS.md), [flujo editorial](docs/EDITORIAL_WORKFLOW.md), [backups](docs/BACKUP_AND_RECOVERY.md) y [mantenimiento](docs/MAINTENANCE.md).

El endpoint `GET /health` comprueba aplicación y base de datos sin revelar credenciales.
