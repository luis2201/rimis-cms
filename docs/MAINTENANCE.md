# Mantenimiento

Supervise `/health`, logs de Laravel, cola, espacio en disco, correo y expiración TLS. Revise mensualmente `composer audit`, `npm audit`, actualizaciones compatibles y backups. Pruebe restauración periódicamente.

Laravel 9 está fuera de soporte de seguridad y conserva avisos que no pueden resolverse sin actualización mayor. Planifique la migración a una versión LTS soportada. No use `npm audit fix --force` sin una fase separada: Vite 8 y AdminLTE 4 implican cambios incompatibles.

Los archivos privados deben permanecer en el disco local. Investigue archivos faltantes mediante logs sin registrar contenido sensible. Antes de cada despliegue ejecute pruebas, migraciones, compilación frontend y caché de rutas.
