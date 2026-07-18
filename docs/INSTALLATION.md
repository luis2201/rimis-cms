# Instalación

1. Cree una base MySQL con `utf8mb4` y un usuario de privilegios limitados.
2. Ejecute `composer install`, copie `.env.example` a `.env` y genere `APP_KEY`.
3. Configure `APP_URL`, base de datos, correo, sesión y filesystem.
4. Ejecute migraciones, `RolesAndPermissionsSeeder` y los comandos RIMIS indicados en el README.
5. Ejecute `npm ci && npm run build` y compruebe `php artisan test`.

En Windows/Laragon use los binarios PHP y Composer de Laragon. Los datos de demostración son opcionales y nunca deben ejecutarse como parte automática del despliegue.
