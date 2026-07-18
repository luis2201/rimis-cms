# Backup y recuperación

Respaldar diariamente base de datos, `storage/app` privado, `storage/app/public`, `.env` mediante un almacén cifrado y configuración del servidor. Mantener retención definida, copia fuera del servidor y pruebas periódicas de restauración.

Restauración: desplegar el mismo commit, instalar dependencias, restaurar `.env`, importar la base, restaurar storage conservando permisos, ejecutar `migrate --force`, limpiar/crear cachés y verificar `/health`, archivos y flujos críticos. Nunca publique dumps ni respaldos en Git.
