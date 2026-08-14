# Publicar cambios en GitHub

Este procedimiento permite guardar cambios locales en `luis2201/rimis-cms` mediante una rama y una solicitud de cambios (Pull Request). Los comandos deben ejecutarse desde la raíz del proyecto.

## 1. Verificar la autenticación y el repositorio

Comprueba que GitHub CLI esté instalado y que la sesión corresponda a la cuenta autorizada:

```powershell
gh --version
gh auth status
git remote -v
```

El remoto `origin` debe apuntar a:

```text
https://github.com/luis2201/rimis-cms
```

Si GitHub CLI no está autenticado:

```powershell
gh auth login
```

## 2. Revisar la rama y los cambios locales

```powershell
git branch --show-current
git status -sb
git diff --stat
git diff --check
```

`git diff --check` no debe reportar errores. Antes de continuar, identifica qué archivos pertenecen al cambio y cuáles son temporales o ajenos.

No se deben incluir automáticamente:

- La carpeta `dumb/`.
- Reportes temporales dentro de `tmp/`.
- Credenciales, archivos `.env`, respaldos o datos locales.

## 3. Crear una rama de trabajo

Si estás en `main`, primero actualízala y crea una rama descriptiva:

```powershell
git switch main
git pull --ff-only origin main
git switch -c agent/descripcion-del-cambio
```

Si ya estás en una rama destinada al cambio, continúa en ella.

## 4. Ejecutar las validaciones

Ejecuta las comprobaciones relacionadas con el cambio. Para una validación completa:

```powershell
php artisan test
php artisan view:cache
npm run build
```

En PowerShell, si la política del sistema bloquea `npm.ps1`, usa:

```powershell
npm.cmd run build
```

Si `php` no está configurado en el `PATH`, utiliza el ejecutable instalado por Laragon. La versión puede variar:

```powershell
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test
```

No publiques cambios si las pruebas relevantes fallan sin haber determinado y documentado la causa.

## 5. Agregar solamente los archivos correspondientes

Prefiere indicar las rutas de manera explícita:

```powershell
git add app/Http/Controllers/ExampleController.php
git add resources/views/example.blade.php
git add tests/Feature/ExampleTest.php
```

Evita `git add -A` cuando existan archivos ajenos o temporales en el directorio de trabajo.

Comprueba exactamente qué quedará incluido:

```powershell
git status -sb
git diff --cached --stat
git diff --cached
git diff --cached --check
```

Si agregaste un archivo por error, retíralo del área de preparación sin borrar su contenido:

```powershell
git restore --staged ruta/del/archivo
```

## 6. Crear el commit

Utiliza un mensaje breve que describa el resultado:

```powershell
git commit -m "Corregir formulario de suscripción"
```

Confirma el commit creado:

```powershell
git log -1 --oneline
```

## 7. Subir la rama

```powershell
git push -u origin $(git branch --show-current)
```

En las siguientes actualizaciones de la misma rama bastará con:

```powershell
git push
```

## 8. Crear la Pull Request

La primera vez que publiques una rama, abre una PR en borrador contra `main`:

```powershell
gh pr create --draft --base main --head $(git branch --show-current) --fill
```

La descripción de la PR debe indicar:

- Qué cambió.
- Por qué fue necesario.
- El impacto para usuarios o administradores.
- Las pruebas y compilaciones ejecutadas.
- Las migraciones o comandos requeridos durante el despliegue.

Si ya existe una PR abierta para la rama, cada nuevo `git push` la actualizará automáticamente. Si la PR anterior ya fue fusionada, deberá abrirse una nueva.

## 9. Verificar el resultado en GitHub

```powershell
gh pr view --web
gh pr checks
git status -sb
```

Comprueba que:

- La PR apunte de la rama correcta hacia `main`.
- Todos los archivos esperados aparezcan en la pestaña **Files changed**.
- No se hayan incluido archivos locales o sensibles.
- Las verificaciones automáticas finalicen correctamente.

## 10. Después de fusionar

Actualiza la copia local:

```powershell
git switch main
git pull --ff-only origin main
```

Cuando la rama ya no sea necesaria, puede eliminarse:

```powershell
git branch -d agent/descripcion-del-cambio
git push origin --delete agent/descripcion-del-cambio
```

La eliminación debe hacerse solamente después de confirmar que la PR fue fusionada y que no quedan cambios pendientes en la rama.

